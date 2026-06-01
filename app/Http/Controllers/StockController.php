<?php

namespace App\Http\Controllers;

use App\Services\OdooService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StockController extends Controller
{
    protected OdooService $odoo;

    public function __construct(OdooService $odoo)
    {
        $this->odoo = $odoo;
    }

    /**
     * GET /stock
     * Renderiza la vista principal (SPA) del Módulo de Stock.
     */
    public function index()
    {
        return view('stock.index');
    }

    /**
     * GET /api/stock/productos
     * Obtiene el catálogo de productos con sus cantidades desglosadas (Almacén vs Tienda).
     * Aplica caché de 5 minutos y fallback automático de contingencia.
     */
    public function productos(Request $request)
    {
        $forceRefresh = $request->input('refresh', 'false') === 'true';

        if ($forceRefresh) {
            Cache::forget('odoo_stock_productos');
        }

        try {
            $data = Cache::remember('odoo_stock_productos', 300, function () {
                return $this->fetchFromOdoo();
            });

            return response()->json([
                'success' => true,
                'products' => $data['products'],
                'categories' => $data['categories'],
                'offline' => false,
                'cached_at' => now()->format('H:i:s'),
            ]);
        } catch (\Exception $e) {
            // Fallback Mode - Contingencia Offline
            $fallbackData = $this->getMockProductsAndCategories();

            return response()->json([
                'success' => true,
                'products' => $fallbackData['products'],
                'categories' => $fallbackData['categories'],
                'offline' => true,
                'error_msg' => $e->getMessage(),
                'cached_at' => now()->format('H:i:s'),
            ]);
        }
    }

    /**
     * GET /api/stock/imagenes
     * Descarga diferida de miniaturas de productos (batches de 50) en Base64.
     */
    public function imagenes(Request $request)
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        }
        if (empty($ids)) {
            return response()->json(['success' => false, 'error' => 'Sin IDs'], 400);
        }

        $ids = array_slice(array_values($ids), 0, 50);

        try {
            $products = $this->odoo->searchRead(
                'product.product',
                [['id', 'in', $ids]],
                ['id', 'image_128']
            );

            $images = [];
            foreach ($products as $p) {
                $images[(string)$p['id']] = ($p['image_128'] && $p['image_128'] !== false)
                    ? $p['image_128']
                    : null;
            }

            return response()->json(['success' => true, 'images' => $images, 'offline' => false]);
        } catch (\Exception $e) {
            // Resiliencia: si Odoo falla, se devuelven nulas sin romper la UI
            $images = [];
            foreach ($ids as $id) {
                $images[(string)$id] = null;
            }
            return response()->json(['success' => true, 'images' => $images, 'offline' => true]);
        }
    }

    /**
     * Consulta Odoo en tiempo real para estructurar productos y su stock por ubicaciones.
     */
    private function fetchFromOdoo(): array
    {
        // 1. Obtener productos activos
        $products = $this->odoo->searchRead(
            'product.product',
            [['active', '=', true]],
            ['id', 'name', 'default_code', 'barcode', 'qty_available', 'standard_price', 'list_price', 'categ_id', 'uom_id']
        );

        // 2. Obtener categorías
        $categories = $this->odoo->searchRead(
            'product.category',
            [],
            ['id', 'name', 'complete_name']
        );

        // Formatear categorías
        $categoryMap = [];
        foreach ($categories as $cat) {
            $name = $cat['complete_name'] ?? $cat['name'];
            if (str_starts_with($name, 'Todas / ')) {
                $name = substr($name, 8);
            }
            $categoryMap[(int)$cat['id']] = $name;
        }

        // 3. Obtener ubicaciones internas
        $ubicaciones = $this->odoo->getUbicaciones();
        $locationIds = array_column($ubicaciones, 'id');

        $tiendaLocationIds = [];
        $almacenLocationIds = [];

        foreach ($ubicaciones as $loc) {
            $fullName = strtolower($loc['complete_name'] ?? $loc['name'] ?? '');
            if (preg_match('/tienda|tda|pos|shop|mostrador|venta/', $fullName)) {
                $tiendaLocationIds[] = (int)$loc['id'];
            } else {
                $almacenLocationIds[] = (int)$loc['id'];
            }
        }

        // 4. Obtener stock detallado de stock.quant
        $quants = [];
        if (!empty($locationIds)) {
            $quants = $this->odoo->searchRead(
                'stock.quant',
                [['location_id', 'in', $locationIds]],
                ['product_id', 'location_id', 'quantity']
            );
        }

        // Agrupar stock por producto y ubicación (Tienda vs Almacén)
        $productStock = [];
        foreach ($quants as $q) {
            $pId = is_array($q['product_id']) ? (int)$q['product_id'][0] : (int)$q['product_id'];
            $lId = is_array($q['location_id']) ? (int)$q['location_id'][0] : (int)$q['location_id'];
            $qty = (float)($q['quantity'] ?? 0);

            if (!isset($productStock[$pId])) {
                $productStock[$pId] = [
                    'almacen_qty' => 0.0,
                    'tienda_qty' => 0.0,
                    'locations' => []
                ];
            }

            if (in_array($lId, $tiendaLocationIds)) {
                $productStock[$pId]['tienda_qty'] += $qty;
            } else {
                $productStock[$pId]['almacen_qty'] += $qty;
            }

            $locName = is_array($q['location_id']) ? $q['location_id'][1] : 'Ubicación';
            $productStock[$pId]['locations'][] = [
                'location_id' => $lId,
                'name' => $locName,
                'qty' => $qty
            ];
        }

        // 5. Procesar productos finales
        $processedProducts = [];
        foreach ($products as $p) {
            $pId = (int)$p['id'];
            $categId = is_array($p['categ_id']) ? (int)$p['categ_id'][0] : (int)$p['categ_id'];
            $categName = $categoryMap[$categId] ?? (is_array($p['categ_id']) ? $p['categ_id'][1] : 'Sin Categoría');

            if (str_starts_with($categName, 'Todas / ')) {
                $categName = substr($categName, 8);
            }

            $stockInfo = $productStock[$pId] ?? [
                'almacen_qty' => 0.0,
                'tienda_qty' => 0.0,
                'locations' => []
            ];

            $processedProducts[] = [
                'id' => $pId,
                'name' => $p['name'],
                'default_code' => $p['default_code'] !== false ? $p['default_code'] : null,
                'barcode' => $p['barcode'] !== false ? $p['barcode'] : null,
                'standard_price' => (float)($p['standard_price'] ?? 0),
                'list_price' => (float)($p['list_price'] ?? 0),
                'categ_id' => $categId,
                'categ_name' => $categName,
                'qty_available' => (float)($p['qty_available'] ?? 0),
                'almacen_qty' => $stockInfo['almacen_qty'],
                'tienda_qty' => $stockInfo['tienda_qty'],
                'locations' => $stockInfo['locations']
            ];
        }

        // 6. Obtener categorías con productos
        $activeCategoryIds = array_unique(array_column($processedProducts, 'categ_id'));
        $processedCategories = [];
        foreach ($categoryMap as $id => $name) {
            if (in_array($id, $activeCategoryIds)) {
                $processedCategories[] = [
                    'id' => $id,
                    'name' => $name
                ];
            }
        }

        // Ordenar categorías por nombre
        usort($processedCategories, fn($a, $b) => strcmp($a['name'], $b['name']));

        return [
            'products' => $processedProducts,
            'categories' => $processedCategories
        ];
    }

    /**
     * MOCK DATA de contingencia local si Odoo 18 falla (offline).
     * Proporciona ~30 productos reales clasificados de Tecnigas para demostración y operatividad.
     */
    private function getMockProductsAndCategories(): array
    {
        $categories = [
            ['id' => 1, 'name' => 'Válvulas y Grifería'],
            ['id' => 2, 'name' => 'Bridas y Conexiones'],
            ['id' => 3, 'name' => 'Pernos y Fijaciones'],
            ['id' => 4, 'name' => 'Tuberías y Mangueras'],
            ['id' => 5, 'name' => 'Empaques y Sellos'],
            ['id' => 6, 'name' => 'Accesorios Generales']
        ];

        $mockProductsData = [
            // Válvulas
            ['id' => 101, 'name' => 'Válvula Premium de Bronce 1/2"', 'code' => 'VAL-001', 'barcode' => '775012340011', 'price' => 45.90, 'cost' => 28.50, 'cat_id' => 1, 'cat_name' => 'Válvulas y Grifería', 'almacen' => 120.0, 'tienda' => 15.0],
            ['id' => 102, 'name' => 'Válvula Premium de Bronce 3/4"', 'code' => 'VAL-002', 'barcode' => '775012340012', 'price' => 58.50, 'cost' => 35.00, 'cat_id' => 1, 'cat_name' => 'Válvulas y Grifería', 'almacen' => 85.0, 'tienda' => 0.0], // Stock crítico en tienda
            ['id' => 103, 'name' => 'Válvula Premium de Bronce 1"', 'code' => 'VAL-003', 'barcode' => '775012340013', 'price' => 82.00, 'cost' => 51.20, 'cat_id' => 1, 'cat_name' => 'Válvulas y Grifería', 'almacen' => 0.0, 'tienda' => 2.0],  // Stock crítico en almacén
            ['id' => 104, 'name' => 'Válvula de Bola de Acero Forjado 2"', 'code' => 'VAL-010', 'barcode' => '775012340020', 'price' => 185.00, 'cost' => 120.00, 'cat_id' => 1, 'cat_name' => 'Válvulas y Grifería', 'almacen' => 12.0, 'tienda' => 3.0],
            ['id' => 105, 'name' => 'Válvula de Aguja de Acero Inoxidable 1/4"', 'code' => 'VAL-015', 'barcode' => '775012340025', 'price' => 125.00, 'cost' => 78.00, 'cat_id' => 1, 'cat_name' => 'Válvulas y Grifería', 'almacen' => 0.0, 'tienda' => 0.0], // Totalmente agotada

            // Bridas
            ['id' => 201, 'name' => 'Brida Deslizable ANSI Clase 150 2"', 'code' => 'BRI-101', 'barcode' => '775012340101', 'price' => 65.00, 'cost' => 41.00, 'cat_id' => 2, 'cat_name' => 'Bridas y Conexiones', 'almacen' => 250.0, 'tienda' => 12.0],
            ['id' => 202, 'name' => 'Brida Deslizable ANSI Clase 150 3"', 'code' => 'BRI-102', 'barcode' => '775012340102', 'price' => 89.00, 'cost' => 56.50, 'cat_id' => 2, 'cat_name' => 'Bridas y Conexiones', 'almacen' => 180.0, 'tienda' => 6.0],
            ['id' => 203, 'name' => 'Brida Deslizable ANSI Clase 150 4"', 'code' => 'BRI-103', 'barcode' => '775012340103', 'price' => 124.00, 'cost' => 79.00, 'cat_id' => 2, 'cat_name' => 'Bridas y Conexiones', 'almacen' => 95.0, 'tienda' => 4.0],
            ['id' => 204, 'name' => 'Codo SCH40 Soldable 90 Grados 2"', 'code' => 'CON-201', 'barcode' => '775012340201', 'price' => 22.50, 'cost' => 14.20, 'cat_id' => 2, 'cat_name' => 'Bridas y Conexiones', 'almacen' => 340.0, 'tienda' => 45.0],
            ['id' => 205, 'name' => 'Tee SCH40 Soldable 2"', 'code' => 'CON-210', 'barcode' => '775012340210', 'price' => 31.80, 'cost' => 19.80, 'cat_id' => 2, 'cat_name' => 'Bridas y Conexiones', 'almacen' => 150.0, 'tienda' => 18.0],

            // Pernos
            ['id' => 301, 'name' => 'Perno Grado 5 Zincado 1/2" x 2"', 'code' => 'PER-301', 'barcode' => '775012340301', 'price' => 2.20, 'cost' => 0.90, 'cat_id' => 3, 'cat_name' => 'Pernos y Fijaciones', 'almacen' => 1500.0, 'tienda' => 250.0],
            ['id' => 302, 'name' => 'Perno Grado 5 Zincado 5/8" x 2 1/2"', 'code' => 'PER-302', 'barcode' => '775012340302', 'price' => 3.80, 'cost' => 1.65, 'cat_id' => 3, 'cat_name' => 'Pernos y Fijaciones', 'almacen' => 1200.0, 'tienda' => 180.0],
            ['id' => 303, 'name' => 'Tuerca Hexagonal Zincada 1/2"', 'code' => 'PER-351', 'barcode' => '775012340351', 'price' => 0.80, 'cost' => 0.30, 'cat_id' => 3, 'cat_name' => 'Pernos y Fijaciones', 'almacen' => 3000.0, 'tienda' => 400.0],
            ['id' => 304, 'name' => 'Arandela Plana Zincada 1/2"', 'code' => 'PER-381', 'barcode' => '775012340381', 'price' => 0.40, 'cost' => 0.15, 'cat_id' => 3, 'cat_name' => 'Pernos y Fijaciones', 'almacen' => 4500.0, 'tienda' => 600.0],

            // Tuberías
            ['id' => 401, 'name' => 'Tubo de Cobre ASTM B88 Tipo L 1/2" (Tramo 6m)', 'code' => 'TUB-401', 'barcode' => '775012340401', 'price' => 115.00, 'cost' => 74.00, 'cat_id' => 4, 'cat_name' => 'Tuberías y Mangueras', 'almacen' => 60.0, 'tienda' => 8.0],
            ['id' => 402, 'name' => 'Tubo de Cobre ASTM B88 Tipo L 3/4" (Tramo 6m)', 'code' => 'TUB-402', 'barcode' => '775012340402', 'price' => 165.00, 'cost' => 105.00, 'cat_id' => 4, 'cat_name' => 'Tuberías y Mangueras', 'almacen' => 45.0, 'tienda' => 5.0],
            ['id' => 403, 'name' => 'Manguera de Alta Presión para GLP 1/2" (Por metro)', 'code' => 'MAN-451', 'barcode' => '775012340451', 'price' => 14.50, 'cost' => 8.20, 'cat_id' => 4, 'cat_name' => 'Tuberías y Mangueras', 'almacen' => 200.0, 'tienda' => 25.0],

            // Empaques
            ['id' => 501, 'name' => 'Empaque para Olla a Presión Standard 24cm', 'code' => 'EMP-501', 'barcode' => '775012340501', 'price' => 18.00, 'cost' => 9.50, 'cat_id' => 5, 'cat_name' => 'Empaques y Sellos', 'almacen' => 450.0, 'tienda' => 35.0],
            ['id' => 502, 'name' => 'Empaque Espiralado Inox/Grafito ASME 150 2"', 'code' => 'EMP-520', 'barcode' => '775012340520', 'price' => 24.50, 'cost' => 15.00, 'cat_id' => 5, 'cat_name' => 'Empaques y Sellos', 'almacen' => 140.0, 'tienda' => 12.0],
            ['id' => 503, 'name' => 'Empaque Espiralado Inox/Grafito ASME 150 3"', 'code' => 'EMP-521', 'barcode' => '775012340521', 'price' => 32.00, 'cost' => 19.50, 'cat_id' => 5, 'cat_name' => 'Empaques y Sellos', 'almacen' => 90.0, 'tienda' => 4.0],
            ['id' => 504, 'name' => 'Cinta de Teflón Profesional 1/2" x 12m', 'code' => 'EMP-550', 'barcode' => '775012340550', 'price' => 3.50, 'cost' => 1.20, 'cat_id' => 5, 'cat_name' => 'Empaques y Sellos', 'almacen' => 850.0, 'tienda' => 120.0],

            // Accesorios
            ['id' => 601, 'name' => 'Manómetro de Presión Conexión Posterior 0-100 PSI', 'code' => 'ACC-601', 'barcode' => '775012340601', 'price' => 48.00, 'cost' => 29.50, 'cat_id' => 6, 'cat_name' => 'Accesorios Generales', 'almacen' => 75.0, 'tienda' => 8.0],
            ['id' => 602, 'name' => 'Regulador de Gas Doméstico Premium', 'code' => 'ACC-610', 'barcode' => '775012340610', 'price' => 35.00, 'cost' => 21.00, 'cat_id' => 6, 'cat_name' => 'Accesorios Generales', 'almacen' => 120.0, 'tienda' => 15.0],
            ['id' => 603, 'name' => 'Abrazadera de Acero Inoxidable 1/2"', 'code' => 'ACC-650', 'barcode' => '775012340650', 'price' => 1.80, 'cost' => 0.70, 'cat_id' => 6, 'cat_name' => 'Accesorios Generales', 'almacen' => 1200.0, 'tienda' => 150.0]
        ];

        $processedProducts = [];
        foreach ($mockProductsData as $p) {
            $processedProducts[] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'default_code' => $p['code'],
                'barcode' => $p['barcode'],
                'standard_price' => $p['cost'],
                'list_price' => $p['price'],
                'categ_id' => $p['cat_id'],
                'categ_name' => $p['cat_name'],
                'qty_available' => $p['almacen'] + $p['tienda'],
                'almacen_qty' => $p['almacen'],
                'tienda_qty' => $p['tienda'],
                'locations' => [
                    ['location_id' => 10, 'name' => 'Almacén Principal (WH/Stock)', 'qty' => $p['almacen']],
                    ['location_id' => 20, 'name' => 'Ubicación Tienda (WH/Tienda)', 'qty' => $p['tienda']]
                ]
            ];
        }

        return [
            'products' => $processedProducts,
            'categories' => $categories
        ];
    }
}
