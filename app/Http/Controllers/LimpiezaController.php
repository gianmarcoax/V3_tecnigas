<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OdooService;
use App\Models\Employee;

class LimpiezaController extends Controller
{
    public function __construct(private OdooService $odoo) {}

    // GET /limpieza  → vista blade
    public function index()
    {
        return view('limpieza.index');
    }

    // GET /api/limpieza/empleados
    // Devuelve los empleados de Ventas con su día de descanso (obtenido de Odoo)
    public function empleados()
    {
        try {
            $allEmps = $this->odoo->searchRead(
                'hr.employee',
                [['active', '=', true]],
                ['id', 'name', 'image_128', 'department_id', 'resource_calendar_id'],
                ['order' => 'name asc', 'limit' => 300]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error Odoo: ' . $e->getMessage()], 500);
        }

        // Filtrar solo departamento de Ventas
        $emps = array_values(array_filter(
            $allEmps,
            fn($e) => $e['department_id'] && stripos($e['department_id'][1], 'venta') !== false
        ));

        if (empty($emps)) {
            return response()->json(['empleados' => []]);
        }

        // Obtener calendarios únicos
        $calIds = array_unique(array_filter(
            array_map(fn($e) => $e['resource_calendar_id'][0] ?? null, $emps)
        ));

        // Días configurados por calendario (0=Lun…6=Dom en Odoo)
        $calDays = []; // calId => set de días CON horario
        if ($calIds) {
            try {
                $lines = $this->odoo->searchRead(
                    'resource.calendar.attendance',
                    [['calendar_id', 'in', array_values($calIds)]],
                    ['calendar_id', 'dayofweek'],
                    ['limit' => 500]
                );
                foreach ($lines as $ln) {
                    $cid = $ln['calendar_id'][0];
                    $dow = (int)$ln['dayofweek']; // 0=Lun … 6=Dom (Odoo)
                    $calDays[$cid][$dow] = true;
                }
            } catch (\Exception $e) {}
        }

        // Mapear Odoo dayofweek (0=Lun) → nuestro índice semana (0=Lun…6=Dom)
        $ALL_DAYS = [0, 1, 2, 3, 4, 5, 6];

        // Buscar local_id en nuestra BD para poder guardar scores
        $localMap = Employee::whereNotNull('odoo_id')
            ->pluck('id', 'odoo_id')
            ->toArray();

        $result = [];
        foreach ($emps as $emp) {
            $cdata = $emp['resource_calendar_id'] ?? false;
            $cid   = $cdata ? $cdata[0] : null;
            $scheduled = $cid ? array_keys($calDays[$cid] ?? []) : [];

            // Día de descanso = día que NO tiene línea configurada en el calendario
            $restDays = array_values(array_diff($ALL_DAYS, $scheduled));

            $odooId  = $emp['id'];
            $localId = $localMap[$odooId] ?? null;

            // Si no tiene registro local, crear uno mínimo para poder asociar scores
            if (!$localId) {
                try {
                    $newEmp = Employee::create([
                        'odoo_id'    => $odooId,
                        'name'       => $emp['name'],
                        'department' => $emp['department_id'][1] ?? 'Ventas',
                        'active'     => true,
                    ]);
                    $localId = $newEmp->id;
                } catch (\Exception $e) {
                    continue; // skip si falla
                }
            }

            $result[] = [
                'odoo_id'   => $odooId,
                'local_id'  => $localId,
                'name'      => $emp['name'],
                'photo'     => $emp['image_128'] ?? null,
                'rest_days' => $restDays, // array de índices [0..6], 0=Lun, 6=Dom
            ];
        }

        return response()->json(['empleados' => $result]);
    }
}
