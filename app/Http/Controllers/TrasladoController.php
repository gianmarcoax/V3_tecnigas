<?php
namespace App\Http\Controllers;

use App\Models\Traslado;
use App\Models\TrasladoItem;
use App\Services\OdooService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TrasladoController extends Controller
{
    protected OdooService $odoo;

    public function __construct(OdooService $odoo)
    {
        $this->odoo = $odoo;
    }

    public function index()
    {
        return view('traslado.index');
    }

    public function resumen()
    {
        $traslados = Traslado::all();
        return response()->json(['success' => true, 'resumen' => [
            'total_traslados' => $traslados->count(),
            'total_productos' => Traslado::with('items')->get()->sum(fn($t) => $t->items->count()),
            'pendientes'      => $traslados->where('estado','pendiente')->count(),
            'confirmados'     => $traslados->where('estado','confirmado')->count(),
        ]]);
    }

    public function historial()
    {
        return response()->json(['success' => true, 'traslados' =>
            Traslado::with('items')->orderBy('fecha','desc')->orderBy('created_at','desc')->get()
        ]);
    }

    public function show(string $id)
    {
        $t = Traslado::with('items')->find($id);
        if (!$t) return response()->json(['success'=>false,'error'=>'Traslado no encontrado'],404);
        return response()->json(['success'=>true,'traslado'=>$t]);
    }

    public function productos()
    {
        try {
            // Tiempo real desde Odoo, sin caché ni límite
            $products = $this->odoo->searchRead(
                'product.product',
                [['active', '=', true]],
                ['id', 'name', 'default_code', 'barcode', 'qty_available', 'uom_id', 'categ_id']
            );
            return response()->json(['success' => true, 'products' => $products]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

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
                    ? $p['image_128'] : null;
            }
            return response()->json(['success' => true, 'images' => $images]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function almacenes()
    {
        try {
            // Usar stock.location internas — igual que Recepción (con caché 10 min)
            $ubicaciones = Cache::remember('odoo_ubicaciones', 600, function () {
                return $this->odoo->getUbicaciones();
            });
            return response()->json(['success' => true, 'almacenes' => $ubicaciones]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha'                   => 'required|date',
            'almacen_origen_id'       => 'required|integer',
            'almacen_origen_nombre'   => 'required|string',
            'almacen_destino_id'      => 'required|integer',
            'almacen_destino_nombre'  => 'required|string',
            'usuario'                 => 'nullable|string',
            'observaciones'           => 'nullable|string',
            'items'                   => 'required|array|min:1',
            'items.*.producto_id'     => 'required|integer',
            'items.*.producto_nombre' => 'required|string',
            'items.*.cantidad'        => 'required|numeric|min:0.001',
            'items.*.unidad'          => 'nullable|string',
        ]);

        try {
            $traslado = \DB::transaction(function () use ($data) {

                // 1. Guardar en BD local
                $traslado = Traslado::create([
                    'fecha'                  => $data['fecha'],
                    'almacen_origen_id'      => $data['almacen_origen_id'],
                    'almacen_origen_nombre'  => $data['almacen_origen_nombre'],
                    'almacen_destino_id'     => $data['almacen_destino_id'],
                    'almacen_destino_nombre' => $data['almacen_destino_nombre'],
                    'usuario'                => $data['usuario'] ?? null,
                    'observaciones'          => $data['observaciones'] ?? null,
                    'estado'                 => 'pendiente',
                ]);

                $traslado->items()->createMany($data['items']);

                // 2. Crear picking en Odoo 18 (stock.picking tipo internal)
                $odooItems = array_map(fn($i) => [
                    'product_id' => (int) $i['producto_id'],
                    'name'       => $i['producto_nombre'],
                    'qty'        => (float) $i['cantidad'],
                    'uom_id'     => 1, // UdM por defecto; se puede mejorar con uom_id del producto
                ], $data['items']);

                $pickingId = $this->odoo->crearTrasladoOdoo(
                    (int) $data['almacen_origen_id'],
                    (int) $data['almacen_destino_id'],
                    $odooItems,
                    'WEB-' . ($data['usuario'] ?? 'SISTEMA')
                );

                // 3. Guardar picking ID y marcar confirmado
                $traslado->update([
                    'odoo_picking_id' => $pickingId,
                    'estado'          => 'confirmado',
                    'fecha_confirmacion' => now(),
                ]);

                return $traslado;
            });

            return response()->json([
                'success'  => true,
                'traslado' => $traslado->load('items'),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Error al procesar traslado: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $traslado = Traslado::find($id);
        if (!$traslado) return response()->json(['success'=>false,'error'=>'No encontrado'],404);

        $data = $request->validate([
            'fecha'                   => 'sometimes|date',
            'almacen_origen_id'       => 'nullable|integer',
            'almacen_origen_nombre'   => 'sometimes|string',
            'almacen_destino_id'      => 'nullable|integer',
            'almacen_destino_nombre'  => 'sometimes|string',
            'usuario'                 => 'nullable|string',
            'observaciones'           => 'nullable|string',
            'items'                   => 'sometimes|array|min:1',
            'items.*.producto_id'     => 'nullable|integer',
            'items.*.producto_nombre' => 'required_with:items|string',
            'items.*.cantidad'        => 'required_with:items|numeric',
            'items.*.unidad'          => 'nullable|string',
        ]);

        $traslado->update($data);
        if (!empty($data['items'])) {
            $traslado->items()->delete();
            $traslado->items()->createMany($data['items']);
        }
        return response()->json(['success'=>true,'traslado'=>$traslado->load('items')]);
    }

    public function confirm(string $id)
    {
        $traslado = Traslado::find($id);
        if (!$traslado) return response()->json(['success'=>false,'error'=>'No encontrado'],404);
        $traslado->update(['estado'=>'confirmado','fecha_confirmacion'=>now()]);
        return response()->json(['success'=>true]);
    }

    public function destroy(string $id)
    {
        $traslado = Traslado::find($id);
        if (!$traslado) return response()->json(['success'=>false,'error'=>'No encontrado'],404);
        $traslado->items()->delete();
        $traslado->delete();
        return response()->json(['success'=>true]);
    }
}
