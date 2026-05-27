<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\OdooService;

class AsistenciasController extends Controller
{
    private const LIMA_H = 5;   // UTC-5
    private const TOL    = 10;  // minutos tolerancia tardanza

    public function __construct(private OdooService $odoo) {}

    // GET /asistencias  →  vista blade
    public function index()
    {
        return view('asistencias.index');
    }

    // ─────────────────────────────────────────────────────────
    //  GET /api/asistencias/vivo
    //  Empleados con estado en tiempo real
    // ─────────────────────────────────────────────────────────
    public function vivo()
    {
        $limaNow  = now()->subHours(self::LIMA_H);
        $todayStr = $limaNow->format('Y-m-d');
        $nowMin   = $limaNow->hour * 60 + $limaNow->minute;
        $utcStart = $limaNow->copy()->startOfDay()->addHours(self::LIMA_H)->format('Y-m-d H:i:s');
        $utcEnd   = $limaNow->copy()->endOfDay()->addHours(self::LIMA_H)->format('Y-m-d H:i:s');

        $toLima = fn($s) => $s ? Carbon::parse($s)->subHours(self::LIMA_H) : null;
        $hhmm   = fn($m) => sprintf('%02d:%02d', intdiv((int)round($m), 60), (int)round($m) % 60);
        $isFree = fn($n) => $n && preg_match('/libre|flexible|apoyo|demanda/i', $n);

        // Empleados activos
        try {
            $allEmps = $this->odoo->searchRead(
                'hr.employee',
                [['active', '=', true]],
                ['id', 'name', 'image_128', 'department_id', 'resource_calendar_id', 'job_title'],
                ['order' => 'name asc', 'limit' => 300]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error empleados: ' . $e->getMessage()]);
        }

        // Filtro solo ventas
        $emps = array_values(array_filter(
            $allEmps,
            fn($e) => $e['department_id'] && stripos($e['department_id'][1], 'venta') !== false
        ));

        if (empty($emps)) {
            return response()->json(['empleados' => [], 'resumen' => ['total' => 0, 'presentes' => 0, 'ausentes' => 0, 'libres' => 0]]);
        }

        // Calendarios
        $calIds   = array_unique(array_filter(array_map(fn($e) => $e['resource_calendar_id'][0] ?? null, $emps)));
        $calNames = [];
        $calWeek  = [];
        if ($calIds) {
            try {
                foreach ($this->odoo->read('resource.calendar', array_values($calIds), ['id', 'name']) as $c) {
                    $calNames[$c['id']] = $c['name'];
                }
                foreach ($this->odoo->searchRead('resource.calendar.attendance',
                    [['calendar_id', 'in', array_values($calIds)]],
                    ['calendar_id', 'dayofweek', 'hour_from', 'hour_to']) as $ln) {
                    $cid = $ln['calendar_id'][0];
                    $dow = (int)$ln['dayofweek'];
                    if (!isset($calWeek[$cid][$dow])) {
                        $calWeek[$cid][$dow] = ['hour_from' => $ln['hour_from'], 'hour_to' => $ln['hour_to']];
                    } else {
                        $calWeek[$cid][$dow]['hour_from'] = min($calWeek[$cid][$dow]['hour_from'], $ln['hour_from']);
                        $calWeek[$cid][$dow]['hour_to']   = max($calWeek[$cid][$dow]['hour_to'],   $ln['hour_to']);
                    }
                }
            } catch (\Exception $e) {}
        }

        // Asistencias de hoy
        $attToday = [];
        try {
            foreach ($this->odoo->searchRead('hr.attendance',
                [['check_in', '>=', $utcStart], ['check_in', '<=', $utcEnd]],
                ['employee_id', 'check_in', 'check_out'],
                ['limit' => 2000]) as $a) {
                $eid = $a['employee_id'][0];
                $ci  = $toLima($a['check_in']);
                $co  = $toLima($a['check_out'] ?? null);
                if (!isset($attToday[$eid]) || ($ci && $ci->timestamp > Carbon::parse($attToday[$eid]['check_in_raw'] ?? 0)->timestamp)) {
                    $attToday[$eid] = [
                        'check_in'     => $ci ? $ci->format('H:i') : null,
                        'check_out'    => $co ? $co->format('H:i') : null,
                        'check_in_raw' => $a['check_in'],
                        'ci_min'       => $ci ? $ci->hour * 60 + $ci->minute : null,
                        'co_min'       => $co ? $co->hour * 60 + $co->minute : null,
                        'working'      => $co === null, // aún dentro
                    ];
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error asistencias: ' . $e->getMessage()]);
        }

        $dow = $limaNow->dayOfWeek === 0 ? 6 : $limaNow->dayOfWeek - 1; // 0=Mon

        $resultado = [];
        $cnt = ['total' => count($emps), 'presentes' => 0, 'ausentes' => 0, 'libres' => 0];

        foreach ($emps as $emp) {
            $eid     = $emp['id'];
            $cdata   = $emp['resource_calendar_id'] ?? false;
            $cid     = $cdata ? $cdata[0] : null;
            $calName = $cid ? ($calNames[$cid] ?? '') : '';
            $freeSch = $isFree($calName);
            $sched   = ($cid && isset($calWeek[$cid][$dow])) ? $calWeek[$cid][$dow] : null;
            $att     = $attToday[$eid] ?? null;

            $mf  = $sched ? (int)round($sched['hour_from'] * 60) : null;
            $mt  = $sched ? (int)round($sched['hour_to'] * 60)   : null;
            $ti  = $mf !== null ? $mf + self::TOL : null;

            if ($freeSch) {
                $status = $att ? 'presente' : 'libre';
                $cnt[$att ? 'presentes' : 'libres']++;
            } elseif (!$sched) {
                $status = 'dia_libre';
                $cnt['libres']++;
            } elseif ($att) {
                $lateM = $ti !== null ? max(0, $att['ci_min'] - $ti) : 0;
                $status = $lateM > 0 ? 'tardanza' : ($att['working'] ? 'trabajando' : 'salio');
                $cnt['presentes']++;
            } else {
                $status = (!$limaNow->isToday() || $nowMin >= ($ti ?? 0)) ? 'ausente' : 'pendiente';
                $cnt['ausentes']++;
            }

            $resultado[] = [
                'id'          => $eid,
                'name'        => $emp['name'],
                'photo'       => $emp['image_128'] ?? null,
                'department'  => $emp['department_id'][1] ?? '',
                'job_title'   => $emp['job_title'] ?? '',
                'calendar'    => $calName,
                'status'      => $status,
                'check_in'    => $att['check_in']  ?? null,
                'check_out'   => $att['check_out'] ?? null,
                'expected_in'  => $mf !== null ? $hhmm($mf) : null,
                'expected_out' => $mt !== null ? $hhmm($mt) : null,
                'late_min'    => ($att && $ti !== null) ? max(0, ($att['ci_min'] ?? 0) - $ti) : 0,
                'working'     => $att['working'] ?? false,
            ];
        }

        return response()->json(['empleados' => $resultado, 'resumen' => $cnt, 'hora' => $limaNow->format('H:i')]);
    }

    // ─────────────────────────────────────────────────────────
    //  GET /api/asistencias/semana?week_start=YYYY-MM-DD
    // ─────────────────────────────────────────────────────────
    public function semana(Request $request)
    {
        $LIMA_H = self::LIMA_H;
        $TOL    = self::TOL;

        $limaNow  = now()->subHours($LIMA_H);
        $todayStr = $limaNow->format('Y-m-d');
        $nowMin   = $limaNow->hour * 60 + $limaNow->minute;

        $wsStr     = $request->get('week_start', '');
        $weekStart = $wsStr ? Carbon::parse($wsStr)->startOfDay() : $limaNow->copy()->startOfWeek(Carbon::MONDAY);
        $days      = collect(range(0, 6))->map(fn($i) => $weekStart->copy()->addDays($i));
        $weekEnd   = $days->last();

        $utcStart = $weekStart->copy()->addHours($LIMA_H)->format('Y-m-d H:i:s');
        $utcEnd   = $weekEnd->copy()->addDays(1)->addHours($LIMA_H)->subSecond()->format('Y-m-d H:i:s');

        $toLima = fn($s) => $s ? Carbon::parse($s)->subHours($LIMA_H) : null;
        $hhmm   = fn($m) => sprintf('%02d:%02d', intdiv((int)round($m), 60), (int)round($m) % 60);
        $isFree = fn($n) => $n && preg_match('/libre|flexible|apoyo|demanda/i', $n);

        try {
            $allEmps = $this->odoo->searchRead(
                'hr.employee',
                [['active', '=', true]],
                ['id', 'name', 'image_128', 'department_id', 'resource_calendar_id'],
                ['order' => 'name asc', 'limit' => 300]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error empleados: ' . $e->getMessage()]);
        }

        $emps   = array_values(array_filter($allEmps, fn($e) => $e['department_id'] && stripos($e['department_id'][1], 'venta') !== false));
        $empMap = array_column($emps, null, 'id');
        if (empty($emps)) return response()->json(['error' => 'No hay empleados en Ventas']);

        // Calendarios
        $calIds   = array_unique(array_filter(array_map(fn($e) => $e['resource_calendar_id'][0] ?? null, $emps)));
        $calNames = [];
        $calWeek  = [];
        if ($calIds) {
            try {
                foreach ($this->odoo->read('resource.calendar', array_values($calIds), ['id', 'name']) as $c) {
                    $calNames[$c['id']] = $c['name'];
                }
                foreach ($this->odoo->searchRead('resource.calendar.attendance',
                    [['calendar_id', 'in', array_values($calIds)]],
                    ['calendar_id', 'dayofweek', 'hour_from', 'hour_to']) as $ln) {
                    $cid = $ln['calendar_id'][0];
                    $dow = (int)$ln['dayofweek'];
                    if (!isset($calWeek[$cid][$dow])) {
                        $calWeek[$cid][$dow] = ['hour_from' => $ln['hour_from'], 'hour_to' => $ln['hour_to']];
                    } else {
                        $calWeek[$cid][$dow]['hour_from'] = min($calWeek[$cid][$dow]['hour_from'], $ln['hour_from']);
                        $calWeek[$cid][$dow]['hour_to']   = max($calWeek[$cid][$dow]['hour_to'],   $ln['hour_to']);
                    }
                }
            } catch (\Exception $e) {}
        }

        // Asistencias semana
        $attBy = [];
        try {
            foreach ($this->odoo->searchRead('hr.attendance',
                [['check_in', '>=', $utcStart], ['check_in', '<=', $utcEnd]],
                ['employee_id', 'check_in', 'check_out'],
                ['limit' => 5000]) as $a) {
                $eid = $a['employee_id'][0];
                if (!isset($empMap[$eid])) continue;
                $ci = $toLima($a['check_in']);
                if (!$ci) continue;
                $d  = $ci->format('Y-m-d');
                $co = $toLima($a['check_out'] ?? null);
                $attBy[$eid][$d][] = [
                    'check_in'  => $ci->format('H:i'),
                    'check_out' => $co ? $co->format('H:i') : null,
                    'ci_min'    => $ci->hour * 60 + $ci->minute,
                    'co_min'    => $co ? $co->hour * 60 + $co->minute : null,
                ];
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error asistencias: ' . $e->getMessage()]);
        }

        $labels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

        $resultado = [];
        foreach ($emps as $emp) {
            $eid     = $emp['id'];
            $cdata   = $emp['resource_calendar_id'] ?? false;
            $cid     = $cdata ? $cdata[0] : null;
            $calName = $cid ? ($calNames[$cid] ?? '') : '';
            $freeSch = $isFree($calName);
            $empSch  = $cid ? ($calWeek[$cid] ?? []) : [];
            $empAtts = $attBy[$eid] ?? [];

            $dayDetails = [];
            foreach ($days as $i => $day) {
                $ds      = $day->format('Y-m-d');
                $dow     = $day->dayOfWeek === 0 ? 6 : $day->dayOfWeek - 1;
                $isToday = $ds === $todayStr;
                $recs    = $empAtts[$ds] ?? [];
                $sched   = $empSch[$dow] ?? null;

                if ($freeSch) {
                    $dayDetails[] = [
                        'date'   => $ds, 'label' => $labels[$i],
                        'status' => $recs ? 'presente' : 'libre',
                        'check_in' => $recs ? $recs[0]['check_in'] : null,
                        'check_out' => $recs ? $recs[0]['check_out'] : null,
                        'expected_in' => null, 'expected_out' => null,
                        'late_min' => 0, 'is_free' => true, 'is_day_off' => false,
                    ];
                    continue;
                }

                if (!$sched) {
                    $dayDetails[] = [
                        'date'   => $ds, 'label' => $labels[$i],
                        'status' => 'dia_libre',
                        'check_in' => $recs ? $recs[0]['check_in'] : null,
                        'check_out' => $recs ? $recs[0]['check_out'] : null,
                        'expected_in' => null, 'expected_out' => null,
                        'late_min' => 0, 'is_free' => false, 'is_day_off' => true,
                    ];
                    continue;
                }

                $mf = (int)round($sched['hour_from'] * 60);
                $mt = (int)round($sched['hour_to'] * 60);
                $ti = $mf + $TOL;

                if (empty($recs)) {
                    $status = (!$isToday || $nowMin >= $ti) ? 'falta' : 'pendiente';
                    $dayDetails[] = [
                        'date'   => $ds, 'label' => $labels[$i],
                        'status' => $status,
                        'check_in' => null, 'check_out' => null,
                        'expected_in' => $hhmm($mf), 'expected_out' => $hhmm($mt),
                        'late_min' => 0, 'is_free' => false, 'is_day_off' => false,
                    ];
                } else {
                    $rec   = collect($recs)->sortBy('ci_min')->first();
                    $lateM = max(0, $rec['ci_min'] - $ti);
                    $dayDetails[] = [
                        'date'   => $ds, 'label' => $labels[$i],
                        'status' => $lateM > 0 ? 'tardanza' : 'puntual',
                        'check_in'  => $rec['check_in'],
                        'check_out' => $rec['check_out'],
                        'expected_in'  => $hhmm($mf), 'expected_out' => $hhmm($mt),
                        'late_min' => $lateM, 'is_free' => false, 'is_day_off' => false,
                    ];
                }
            }

            $faltas    = count(array_filter($dayDetails, fn($d) => $d['status'] === 'falta'));
            $tardanzas = count(array_filter($dayDetails, fn($d) => $d['status'] === 'tardanza'));

            $resultado[] = [
                'id'         => $eid,
                'name'       => $emp['name'],
                'photo'      => $emp['image_128'] ?? null,
                'department' => $emp['department_id'][1] ?? '',
                'calendar'   => $calName,
                'days'       => $dayDetails,
                'faltas'     => $faltas,
                'tardanzas'  => $tardanzas,
            ];
        }

        return response()->json([
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end'   => $weekEnd->format('Y-m-d'),
            'empleados'  => $resultado,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  GET /api/asistencias/horarios
    //  Devuelve todos los resource.calendar con sus líneas
    // ─────────────────────────────────────────────────────────
    public function horarios()
    {
        try {
            $cals = $this->odoo->searchRead(
                'resource.calendar',
                [['active', '=', true]],
                ['id', 'name', 'company_id'],
                ['order' => 'name asc', 'limit' => 50]
            );

            if (empty($cals)) {
                return response()->json(['calendars' => []]);
            }

            $calIds = array_column($cals, 'id');

            $lines = $this->odoo->searchRead(
                'resource.calendar.attendance',
                [['calendar_id', 'in', $calIds]],
                ['id', 'name', 'calendar_id', 'dayofweek', 'day_period', 'hour_from', 'hour_to'],
                ['order' => 'calendar_id asc, dayofweek asc, hour_from asc', 'limit' => 500]
            );

            $linesByCalendar = [];
            foreach ($lines as $line) {
                $cid = $line['calendar_id'][0];
                $linesByCalendar[$cid][] = [
                    'id'         => $line['id'],
                    'name'       => $line['name'],
                    'dayofweek'  => (int)$line['dayofweek'],
                    'day_period' => $line['day_period'] ?? '',
                    'hour_from'  => $line['hour_from'],
                    'hour_to'    => $line['hour_to'],
                ];
            }

            $result = array_map(fn($cal) => [
                'id'      => $cal['id'],
                'name'    => $cal['name'],
                'company' => $cal['company_id'][1] ?? '',
                'lines'   => $linesByCalendar[$cal['id']] ?? [],
            ], $cals);

            return response()->json(['calendars' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  PUT /api/asistencias/horarios/{lineId}
    //  Actualiza una línea de horario en Odoo
    // ─────────────────────────────────────────────────────────
    public function updateHorario(Request $request, int $lineId)
    {
        $request->validate([
            'hour_from'  => 'required|numeric|min:0|max:24',
            'hour_to'    => 'required|numeric|min:0|max:24',
            'name'       => 'nullable|string|max:150',
            'dayofweek'  => 'nullable|in:0,1,2,3,4,5,6',
            'day_period' => 'nullable|in:morning,afternoon',
        ]);

        $data = [
            'hour_from' => (float)$request->hour_from,
            'hour_to'   => (float)$request->hour_to,
        ];
        if ($request->filled('name'))       $data['name']       = $request->name;
        if ($request->filled('dayofweek'))  $data['dayofweek']  = (string)$request->dayofweek;
        if ($request->filled('day_period')) $data['day_period'] = $request->day_period;

        try {
            $this->odoo->execute('resource.calendar.attendance', 'write', [[$lineId], $data]);
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  POST /api/asistencias/horarios/{calendarId}/lineas
    //  Crea una nueva línea de horario en Odoo
    // ─────────────────────────────────────────────────────────
    public function createHorarioLine(Request $request, int $calendarId)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'dayofweek'  => 'required|string|in:0,1,2,3,4,5,6',
            'day_period' => 'required|string|in:morning,afternoon',
            'hour_from'  => 'required|numeric|min:0|max:24',
            'hour_to'    => 'required|numeric|min:0|max:24',
        ]);

        try {
            $newId = $this->odoo->execute('resource.calendar.attendance', 'create', [[
                'name'        => $request->name,
                'calendar_id' => $calendarId,
                'dayofweek'   => $request->dayofweek,
                'day_period'  => $request->day_period,
                'hour_from'   => (float)$request->hour_from,
                'hour_to'     => (float)$request->hour_to,
            ]]);

            return response()->json(['ok' => true, 'id' => $newId]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  GET /api/asistencias/empleados-calendario
    //  Lista de empleados de Ventas con su resource.calendar asignado
    // ─────────────────────────────────────────────────────────
    public function empleadosConCalendario()
    {
        try {
            $emps = $this->odoo->searchRead(
                'hr.employee',
                [['active', '=', true]],
                ['id', 'name', 'job_title', 'department_id', 'resource_calendar_id', 'image_128'],
                ['order' => 'name asc', 'limit' => 300]
            );

            $filtered = array_values(array_filter(
                $emps,
                fn($e) => $e['department_id'] && stripos($e['department_id'][1], 'venta') !== false
            ));

            return response()->json(['empleados' => array_map(fn($e) => [
                'id'          => $e['id'],
                'name'        => $e['name'],
                'job_title'   => $e['job_title'] ?: ($e['department_id'][1] ?? ''),
                'photo'       => $e['image_128'] ?? null,
                'calendar_id' => $e['resource_calendar_id'] ? $e['resource_calendar_id'][0] : null,
                'calendar'    => $e['resource_calendar_id'] ? $e['resource_calendar_id'][1] : 'Sin horario asignado',
            ], $filtered)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  GET /api/asistencias/calendarios/{calendarId}/lineas
    //  Líneas de un calendario específico
    // ─────────────────────────────────────────────────────────
    public function calendarioLineas(int $calendarId)
    {
        try {
            $cal = $this->odoo->read('resource.calendar', [$calendarId], ['id', 'name']);
            if (empty($cal)) return response()->json(['error' => 'Calendario no encontrado'], 404);

            $lines = $this->odoo->searchRead(
                'resource.calendar.attendance',
                [['calendar_id', '=', $calendarId]],
                ['id', 'name', 'dayofweek', 'day_period', 'hour_from', 'hour_to'],
                ['order' => 'dayofweek asc, hour_from asc', 'limit' => 100]
            );

            return response()->json([
                'calendar_id'   => $calendarId,
                'calendar_name' => $cal[0]['name'],
                'lines'         => array_map(fn($ln) => [
                    'id'         => $ln['id'],
                    'name'       => $ln['name'],
                    'dayofweek'  => (int)$ln['dayofweek'],
                    'day_period' => $ln['day_period'] ?? 'afternoon',
                    'hour_from'  => $ln['hour_from'],
                    'hour_to'    => $ln['hour_to'],
                ], $lines),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  DELETE /api/asistencias/horarios/lineas/{lineId}
    //  Elimina una línea de horario en Odoo
    // ─────────────────────────────────────────────────────────
    public function deleteHorarioLine(int $lineId)
    {
        try {
            $this->odoo->execute('resource.calendar.attendance', 'unlink', [[$lineId]]);
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
