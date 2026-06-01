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

    // GET /stock → vista SPA
    public function index()
    {
        return view('stock.index');
    }

    /**
     * GET /api/stock/catalogo
     * Retorna nombre, código, barcode, precio, categoría — SIN cantidades.
     * Caché: 24 horas (estos datos cambian raramente).
     * ?refresh=true para forzar recarga.
     */
    public function catalogo(Request $request)
    {
        if ($request->input('refresh') === 'true') {
            Cache::forget('stock_catalogo');
            Cache::forget('stock_categorias');
        }

        try {
            $products   = Cache::remember('stock_catalogo',   86400, fn() => $this->fetchCatalogo());
            $categories = Cache::remember('stock_categorias', 86400, fn() => $this->fetchCategorias());

            return response()->json([
                'success'    => true,
                'products'   => $products,
                'categories' => $categories,
                'offline'    => false,
                'cached_at'  => now()->format('H:i:s'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/stock/cantidades
     * Retorna SOLO product_id → almacen_qty + tienda_qty.
     * Caché: 3 minutos (stock cambia con ventas/traslados).
     * ?refresh=true para forzar recarga.
     */
    public function cantidades(Request $request)
    {
        if ($request->input('refresh') === 'true') {
            Cache::forget('stock_cantidades');
        }

        try {
            $stock = Cache::remember('stock_cantidades', 180, fn() => $this->fetchStockQuants());

            return response()->json([
                'success'   => true,
                'stock'     => $stock,
                'offline'   => false,
                'cached_at' => now()->format('H:i:s'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'stock'   => [],
                'offline' => true,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * GET /api/stock/imagenes?ids=1,2,3,...
     * Retorna miniaturas en base64 (image_128).
     * Caché: 7 días POR producto_id — se acumulan con el tiempo.
     * Máximo 50 IDs por llamada.
     */
    public function imagenes(Request $request)
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        }
        $ids = array_slice(array_values($ids), 0, 50);

        if (empty($ids)) {
            return response()->json(['success' => false, 'error' => 'Sin IDs'], 400);
        }

        // Separar los que ya están en caché de los que hay que pedir a Odoo
        $images  = [];
        $missing = [];

        foreach ($ids as $id) {
            $cached = Cache::get("stock_img_{$id}");
            if ($cached !== null) {
                $images[(string)$id] = $cached ?: null;
            } else {
                $missing[] = $id;
            }
        }

        // Solo consultar Odoo por los que no están en caché
        if (!empty($missing)) {
            try {
                $products = $this->odoo->searchRead(
                    'product.product',
                    [['id', 'in', $missing]],
                    ['id', 'image_128']
                );

                foreach ($products as $p) {
                    $imgValue = ($p['image_128'] && $p['image_128'] !== false)
                        ? $p['image_128']
                        : '';
                    // Guardar 7 días (604800 seg). Guardamos '' para saber que no tiene imagen.
                    Cache::put("stock_img_{$p['id']}", $imgValue, 604800);
                    $images[(string)$p['id']] = $imgValue ?: null;
                }

                // Para IDs que Odoo no devolvió, marcar como sin imagen
                $returnedIds = array_column($products, 'id');
                foreach ($missing as $id) {
                    if (!in_array($id, $returnedIds)) {
                        Cache::put("stock_img_{$id}", '', 604800);
                        $images[(string)$id] = null;
                    }
                }
            } catch (\Exception $e) {
                foreach ($missing as $id) {
                    $images[(string)$id] = null;
                }
            }
        }

        return response()->json(['success' => true, 'images' => $images]);
    }

    // ─── Métodos privados ────────────────────────────────────────────────────

    /**
     * Descarga solo los campos estáticos del catálogo (sin stock).
     */
    private function fetchCatalogo(): array
    {
        $products    = $this->odoo->searchRead(
            'product.product',
            [['active', '=', true]],
            ['id', 'name', 'default_code', 'barcode', 'standard_price', 'list_price', 'categ_id', 'uom_id']
        );

        $categoryMap = $this->buildCategoryMap();

        $result = [];
        foreach ($products as $p) {
            $pId      = (int)$p['id'];
            $categId  = is_array($p['categ_id']) ? (int)$p['categ_id'][0] : (int)$p['categ_id'];
            $categName = $categoryMap[$categId] ?? (is_array($p['categ_id']) ? $p['categ_id'][1] : 'Sin Categoría');

            $result[] = [
                'id'             => $pId,
                'name'           => $p['name'],
                'default_code'   => $p['default_code'] !== false ? $p['default_code'] : null,
                'barcode'        => $p['barcode'] !== false ? $p['barcode'] : null,
                'standard_price' => (float)($p['standard_price'] ?? 0),
                'list_price'     => (float)($p['list_price'] ?? 0),
                'categ_id'       => $categId,
                'categ_name'     => $categName,
            ];
        }

        return $result;
    }

    /**
     * Descarga las categorías con productos activos.
     */
    private function fetchCategorias(): array
    {
        $categories = $this->odoo->searchRead(
            'product.category',
            [],
            ['id', 'name', 'complete_name']
        );

        $result = [];
        foreach ($categories as $cat) {
            $name = $cat['complete_name'] ?? $cat['name'];
            if (str_starts_with($name, 'Todas / ')) {
                $name = substr($name, 8);
            }
            $result[] = ['id' => (int)$cat['id'], 'name' => $name];
        }

        usort($result, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $result;
    }

    /**
     * Descarga SOLO cantidades por ubicación (stock.quant).
     * Retorna: [ product_id => [ almacen_qty, tienda_qty, qty_available, locations ] ]
     */
    private function fetchStockQuants(): array
    {
        $ubicaciones = $this->odoo->getUbicaciones();
        $locationIds = array_column($ubicaciones, 'id');

        $tiendaIds  = [];
        $almacenIds = [];
        foreach ($ubicaciones as $loc) {
            $fullName = strtolower($loc['complete_name'] ?? $loc['name'] ?? '');
            if (preg_match('/tienda|tda|pos|shop|mostrador|venta/', $fullName)) {
                $tiendaIds[] = (int)$loc['id'];
            } else {
                $almacenIds[] = (int)$loc['id'];
            }
        }

        $quants = [];
        if (!empty($locationIds)) {
            $quants = $this->odoo->searchRead(
                'stock.quant',
                [['location_id', 'in', $locationIds]],
                ['product_id', 'location_id', 'quantity']
            );
        }

        $stock = [];
        foreach ($quants as $q) {
            $pId  = is_array($q['product_id'])  ? (int)$q['product_id'][0]  : (int)$q['product_id'];
            $lId  = is_array($q['location_id']) ? (int)$q['location_id'][0] : (int)$q['location_id'];
            $qty  = (float)($q['quantity'] ?? 0);
            $locName = is_array($q['location_id']) ? $q['location_id'][1] : 'Ubicación';

            if (!isset($stock[$pId])) {
                $stock[$pId] = ['almacen_qty' => 0.0, 'tienda_qty' => 0.0, 'locations' => []];
            }

            if (in_array($lId, $tiendaIds)) {
                $stock[$pId]['tienda_qty'] += $qty;
            } else {
                $stock[$pId]['almacen_qty'] += $qty;
            }

            $stock[$pId]['locations'][] = ['location_id' => $lId, 'name' => $locName, 'qty' => $qty];
        }

        // Agregar qty_available (suma total)
        foreach ($stock as &$s) {
            $s['qty_available'] = $s['almacen_qty'] + $s['tienda_qty'];
        }
        unset($s);

        return $stock;
    }

    /**
     * Helper: mapa id → nombre de categorías.
     */
    private function buildCategoryMap(): array
    {
        $categories = $this->odoo->searchRead(
            'product.category',
            [],
            ['id', 'name', 'complete_name']
        );

        $map = [];
        foreach ($categories as $cat) {
            $name = $cat['complete_name'] ?? $cat['name'];
            if (str_starts_with($name, 'Todas / ')) {
                $name = substr($name, 8);
            }
            $map[(int)$cat['id']] = $name;
        }
        return $map;
    }
}
