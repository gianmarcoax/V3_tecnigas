<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    // GET /api/empleados
    public function index()
    {
        return response()->json(Employee::ventas()->with('salaryConfig')->get());
    }

    // POST /api/empleados/sync
    // Recibe array de empleados desde Odoo y sincroniza la tabla local
    public function sync(Request $request)
    {
        $request->validate([
            'empleados'           => 'required|array',
            'empleados.*.odoo_id' => 'required|integer',
            'empleados.*.name'    => 'required|string',
        ]);

        foreach ($request->empleados as $data) {
            Employee::updateOrCreate(
                ['odoo_id' => $data['odoo_id']],
                [
                    'name'       => $data['name'],
                    'department' => $data['department'] ?? null,
                    'shift'      => $data['shift'] ?? null,
                    'active'     => $data['active'] ?? true,
                ]
            );
        }

        return response()->json(['ok' => true, 'synced' => count($request->empleados)]);
    }

    // PUT /api/empleados/{id}
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'shift'  => 'sometimes|in:manana,tarde',
            'active' => 'sometimes|boolean',
        ]);

        $employee->update($request->only('shift', 'active'));

        return response()->json($employee);
    }
}   