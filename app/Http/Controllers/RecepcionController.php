<?php

namespace App\Http\Controllers;

use App\Models\Recepcion;
use App\Models\RecepcionItem;
use App\Services\OdooService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RecepcionController extends Controller
{
    protected OdooService $odoo;

    public function __construct(OdooService $odoo)
    {
        $this->odoo = $odoo;
    }

    // =========================================================
    // GET /recepcion  →  vista principal
    // =========================================================
    public function index()
    {
        return view('recepcion.index');
    }

    // =========================================================
    // GET /recepcion/resumen
    // =========================================================
    public function resumen()
    {
        $recepciones = Recepcion::with('items')->get();

        return response()->json([
            'success' => true,
            'resumen' => [
                'total_recepciones' => $recepciones->count(),
                'total_productos'   => $recepciones->sum(fn($r) => $r->items->count()),
                'total_monto'       => $recepciones->sum('total'),
            ],
        ]);
    }

    // =========================================================
    // GET /recepcion/historial
    // =========================================================
    public function historial()
    {
        $recepciones = Recepcion::with('items')
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success'     => true,
            'recepciones' => $recepciones,
        ]);
    }

    // =========================================================
    // GET /recepcion/{id}
    // =========================================================
    public function show(string $id)
    {
        $recepcion = Recepcion::with('items')->find($id);

        if (!$recepcion) {
            return response()->json([
                'success' => false,
                'error'   => 'Recepción no encontrada',
            ], 404);
        }

        return response()->json([
            'success'   => true,
            'recepcion' => $recepcion,
        ]);
    }

    // =========================================================
    // GET /recepcion/productos  →  desde Odoo (con caché 5 min)
    // =========================================================
    public function productos()
    {
        try {
            $products = $this->odoo->getProductos(); // Tiempo real desde Odoo (sin caché)

            return response()->json([
                'success'  => true,
                'products' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // GET /recepcion/ubicaciones  →  desde Odoo (con caché 10 min)
    // =========================================================
    public function ubicaciones()
    {
        try {
            $ubicaciones = Cache::remember('odoo_ubicaciones', 600, function () {
                return $this->odoo->getUbicaciones();
            });

            return response()->json([
                'success'     => true,
                'ubicaciones' => $ubicaciones,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // GET /recepcion/proveedores  →  desde Odoo (con caché 10 min)
    // =========================================================
    public function proveedores()
    {
        try {
            $proveedores = Cache::remember('odoo_proveedores', 600, function () {
                return $this->odoo->getProveedores();
            });

            return response()->json([
                'success'     => true,
                'proveedores' => $proveedores,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // POST /recepcion
    // =========================================================
    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha'             => 'required|date',
            'proveedor_id'      => 'nullable|integer',
            'proveedor_nombre'  => 'required|string',
            'location_dest_id'  => 'nullable|integer',
            'documento'         => 'nullable|string',
            'usuario'           => 'nullable|string',
            'subtotal'          => 'required|numeric',
            'igv'               => 'required|numeric',
            'total'             => 'required|numeric',
            'observaciones'     => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.producto_id'     => 'nullable|integer',
            'items.*.producto_nombre' => 'required|string',
            'items.*.default_code'    => 'nullable|string',
            'items.*.cantidad'        => 'required|numeric',
            'items.*.tickets'         => 'nullable|integer',
            'items.*.costo'           => 'required|numeric',
            'items.*.list_price'      => 'nullable|numeric',
            'items.*.subtotal'        => 'required|numeric',
            'items.*.uom_id'          => 'nullable|integer',
        ]);

        $locationDestId = (int)($data['location_dest_id'] ?? 0);
        $proveedorId    = (int)($data['proveedor_id'] ?? 0);
        $usuario        = $data['usuario'] ?? 'web';
        $odooPickingId  = null;

        // 1. Crear picking en Odoo (si hay ubicación destino)
        if ($locationDestId > 0) {
            try {
                $odooItems = array_map(fn($item) => [
                    'product_id' => (int)($item['producto_id'] ?? 0),
                    'name'       => $item['producto_nombre'],
                    'qty'        => (float)$item['cantidad'],
                    'price_unit' => (float)$item['costo'],
                    'uom_id'     => (int)($item['uom_id'] ?? 1),
                ], $data['items']);

                $odooPickingId = $this->odoo->crearRecepcionOdoo(
                    proveedor_nombre: $data['proveedor_nombre'],
                    proveedor_id:     $proveedorId,
                    location_dest_id: $locationDestId,
                    items:            $odooItems,
                    origin:           'WEB-' . strtoupper($usuario)
                );
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Error al recepcionar en Odoo: ' . $e->getMessage(),
                ], 500);
            }
        }

        // 2. Guardar en base de datos local (transacción)
        $recepcion = DB::transaction(function () use ($data, $odooPickingId) {
            $rec = Recepcion::create(array_merge(
                collect($data)->except('items')->toArray(),
                ['odoo_picking_id' => $odooPickingId]
            ));
            $rec->items()->createMany($data['items']);
            return $rec->load('items');
        });

        return response()->json([
            'success'         => true,
            'recepcion'       => $recepcion,
            'odoo_picking_id' => $odooPickingId,
        ], 201);
    }

    // =========================================================
    // PUT /recepcion/{id}
    // =========================================================
    public function update(Request $request, string $id)
    {
        $recepcion = Recepcion::find($id);

        if (!$recepcion) {
            return response()->json([
                'success' => false,
                'error'   => 'Recepción no encontrada',
            ], 404);
        }

        $data = $request->validate([
            'fecha'             => 'sometimes|date',
            'proveedor_id'      => 'nullable|integer',
            'proveedor_nombre'  => 'sometimes|string',
            'documento'         => 'nullable|string',
            'usuario'           => 'nullable|string',
            'subtotal'          => 'sometimes|numeric',
            'igv'               => 'sometimes|numeric',
            'total'             => 'sometimes|numeric',
            'observaciones'     => 'nullable|string',
            'items'             => 'sometimes|array|min:1',
            'items.*.producto_id'     => 'nullable|integer',
            'items.*.producto_nombre' => 'required_with:items|string',
            'items.*.default_code'    => 'nullable|string',
            'items.*.cantidad'        => 'required_with:items|numeric',
            'items.*.tickets'         => 'nullable|integer',
            'items.*.costo'           => 'required_with:items|numeric',
            'items.*.subtotal'        => 'required_with:items|numeric',
        ]);

        $recepcion->update($data);

        if (!empty($data['items'])) {
            $recepcion->items()->delete();
            $recepcion->items()->createMany($data['items']);
        }

        $recepcion->load('items');

        return response()->json([
            'success'   => true,
            'recepcion' => $recepcion,
        ]);
    }

    // =========================================================
    // =========================================================
    // GET /recepcion/imagenes  →  Miniaturas de productos (lazy load)
    // =========================================================
    public function imagenes(Request $request)
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        }
        if (empty($ids)) {
            return response()->json(['success' => false, 'error' => 'Sin IDs'], 400);
        }

        // Máximo 50 IDs por request para no sobrecargar
        $ids = array_slice(array_values($ids), 0, 50);

        try {
            $products = $this->odoo->searchRead(
                'product.product',
                [['id', 'in', $ids]],
                ['id', 'image_128']   // 128×128 px
            );

            $images = [];
            foreach ($products as $p) {
                // false = sin imagen en Odoo → null para el frontend
                $images[(string)$p['id']] = ($p['image_128'] && $p['image_128'] !== false)
                    ? $p['image_128']
                    : null;
            }

            return response()->json(['success' => true, 'images' => $images]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // =========================================================
    // GET /recepcion/export-bartender  →  Exportar Excel para BarTender
    // =========================================================
    public function exportBartender(Request $request)
    {
        $ids = $request->input('ids', []); // IDs de recepcion_items

        // Convertir string a array si es necesario (ej: "1,2,3" → [1,2,3])
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        }

        // Asegurar que sea array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'error'   => 'No se especificaron items para exportar',
            ], 400);
        }

        // Obtener items con sus datos
        $items = RecepcionItem::whereIn('id', $ids)
            ->with('recepcion')
            ->get();

        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'error'   => 'No se encontraron items',
            ], 404);
        }

        // Generar XLSX real compatible con BarTender (Office Open XML)
        // Formato idéntico a plantilla "Imprimir 2.0" de Odoo 18:
        // Columnas: Cantidad a la mano | Nombre | Precio de venta | Referencia interna

        $filename = '010';
        $filepath = storage_path('app/public/' . $filename);

        // Crear directorio si no existe
        if (!file_exists(storage_path('app/public'))) {
            mkdir(storage_path('app/public'), 0755, true);
        }

        $this->generateBartenderExcel($items, $filepath);

        return response()->download($filepath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Genera un XLSX real (Office Open XML) idéntico a la plantilla
     * "Imprimir 2.0" exportada por Odoo 18.
     *
     * Estilos replicados del template.xlsx:
     *   s=0  → normal (sin uso explícito)
     *   s=1  → negrita (encabezados de columna)
     *   s=2  → número #,##0.00 + wrapText (Cantidad a la mano, Precio de venta)
     *   s=3  → texto + wrapText (Nombre, Referencia interna)
     */
    private function generateBartenderExcel($items, $filepath)
    {
        // ── 1. Construir la tabla de shared strings ────────────────────
        // Orden: primero los 4 encabezados, luego nombre y referencia de cada item
        $headers = ['Cantidad a la mano', 'Nombre', 'Precio de venta', 'Referencia interna'];

        // Índices compartidos: 0-3 = headers, luego pares (nombre, ref) por cada fila
        $strings   = $headers; // empieza con los 4 encabezados
        $dataRows  = []; // [['tickets'=>X,'nombre_idx'=>Y,'precio'=>Z,'ref_idx'=>W], ...]

        foreach ($items as $item) {
            $tickets    = (int)($item->tickets ?? $item->cantidad);
            $precio     = (float)($item->list_price ?? $item->costo); // Precio de venta (no costo)
            $nombre     = $item->producto_nombre ?? '';
            $referencia = $item->default_code ?? '';

            $nombreIdx = count($strings);
            $strings[] = $nombre;

            $refIdx = count($strings);
            $strings[] = $referencia;

            $dataRows[] = [
                'tickets'    => $tickets,
                'nombre_idx' => $nombreIdx,
                'precio'     => $precio,
                'ref_idx'    => $refIdx,
            ];
        }

        $totalStrings  = count($strings);
        $uniqueStrings = count(array_unique($strings));

        // ── 2. XML: [Content_Types].xml ───────────────────────────────
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . '</Types>';

        // ── 3. XML: _rels/.rels ───────────────────────────────────────
        $relsRoot = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        // ── 4. XML: xl/_rels/workbook.xml.rels ───────────────────────
        $relsWorkbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            . '</Relationships>';

        // ── 5. XML: xl/workbook.xml ───────────────────────────────────
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        // ── 6. XML: xl/styles.xml (idéntico al template de Odoo 18) ──
        // s=0: normal | s=1: negrita | s=2: #,##0.00 + wrap | s=3: texto + wrap
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.00"/></numFmts>'
            . '<fonts count="2">'
            .   '<font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/><scheme val="minor"/></font>'
            .   '<font><b/><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/><scheme val="minor"/></font>'
            . '</fonts>'
            . '<fills count="2">'
            .   '<fill><patternFill patternType="none"/></fill>'
            .   '<fill><patternFill patternType="gray125"/></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="4">'
            .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .   '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .   '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyAlignment="1"><alignment wrapText="1"/></xf>'
            .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment wrapText="1"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '<dxfs count="0"/>'
            . '</styleSheet>';

        // ── 7. XML: xl/sharedStrings.xml ─────────────────────────────
        $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . "<sst xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" count=\"{$totalStrings}\" uniqueCount=\"{$uniqueStrings}\">";
        foreach ($strings as $str) {
            $ssXml .= '<si><t>' . htmlspecialchars($str, ENT_XML1, 'UTF-8') . '</t></si>';
        }
        $ssXml .= '</sst>';

        // ── 8. XML: xl/worksheets/sheet1.xml ─────────────────────────
        $totalRows = count($dataRows) + 1; // +1 encabezado
        $dimRef    = "A1:D{$totalRows}";

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . "<dimension ref=\"{$dimRef}\"/>"
            . '<sheetViews><sheetView tabSelected="1" workbookViewId="0"/></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . '<cols><col min="1" max="4" width="30.7109375" customWidth="1"/></cols>'
            . '<sheetData>';

        // Fila 1: Encabezados (s="1" = negrita, t="s" = shared string)
        $sheetXml .= '<row r="1" spans="1:4">';
        $sheetXml .= '<c r="A1" s="1" t="s"><v>0</v></c>'; // "Cantidad a la mano"
        $sheetXml .= '<c r="B1" s="1" t="s"><v>1</v></c>'; // "Nombre"
        $sheetXml .= '<c r="C1" s="1" t="s"><v>2</v></c>'; // "Precio de venta"
        $sheetXml .= '<c r="D1" s="1" t="s"><v>3</v></c>'; // "Referencia interna"
        $sheetXml .= '</row>';

        // Filas de datos
        foreach ($dataRows as $i => $row) {
            $r = $i + 2; // fila Excel (empieza en 2)
            $sheetXml .= "<row r=\"{$r}\" spans=\"1:4\">";
            // Col A: tickets — número con s="2" (#,##0.00 + wrap)
            $sheetXml .= "<c r=\"A{$r}\" s=\"2\"><v>{$row['tickets']}</v></c>";
            // Col B: nombre — shared string con s="3" (texto + wrap)
            $sheetXml .= "<c r=\"B{$r}\" s=\"3\" t=\"s\"><v>{$row['nombre_idx']}</v></c>";
            // Col C: precio — número con s="2" (#,##0.00 + wrap)
            $sheetXml .= "<c r=\"C{$r}\" s=\"2\"><v>{$row['precio']}</v></c>";
            // Col D: referencia — shared string con s="3" (texto + wrap)
            $sheetXml .= "<c r=\"D{$r}\" s=\"3\" t=\"s\"><v>{$row['ref_idx']}</v></c>";
            $sheetXml .= '</row>';
        }

        $sheetXml .= '</sheetData>'
            . '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>'
            . '</worksheet>';

        // ── 9. Empaquetar en ZIP (.xlsx) ──────────────────────────────
        $zip = new \ZipArchive();
        if ($zip->open($filepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el archivo XLSX en: ' . $filepath);
        }

        $zip->addFromString('[Content_Types].xml',          $contentTypes);
        $zip->addFromString('_rels/.rels',                  $relsRoot);
        $zip->addFromString('xl/workbook.xml',              $workbook);
        $zip->addFromString('xl/_rels/workbook.xml.rels',   $relsWorkbook);
        $zip->addFromString('xl/styles.xml',                $styles);
        $zip->addFromString('xl/sharedStrings.xml',         $ssXml);
        $zip->addFromString('xl/worksheets/sheet1.xml',     $sheetXml);

        $zip->close();
    }

    // =========================================================
    // DELETE /recepcion/{id}
    // =========================================================
    public function destroy(string $id)
    {
        $recepcion = Recepcion::find($id);

        if (!$recepcion) {
            return response()->json([
                'success' => false,
                'error'   => 'Recepción no encontrada',
            ], 404);
        }

        $recepcion->items()->delete();
        $recepcion->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
