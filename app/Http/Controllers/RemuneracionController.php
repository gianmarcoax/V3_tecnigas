<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\GoalTier;
use App\Models\TardinessConfig;
use App\Models\AttendanceJustification;
use App\Models\MonthlyBonusDelivery;
use App\Models\OrderCleanlinessScore;
use App\Models\OrderCleanlinessConfig;
use App\Services\OdooService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RemuneracionController extends Controller
{
    public function __construct(private OdooService $odoo) {}

    public function index()
    {
        return view('remuneracion.index');
    }

    // ── GET /api/remuneracion/config ─────────────────────────────────
    public function config()
    {
        $tiers = GoalTier::orderBy('owner_type')->orderBy('area')->orderBy('period_type')->orderBy('shift')->orderByDesc('sales_goal')->get();
        $tardiness = TardinessConfig::current();

        return response()->json([
            'goal_tiers' => $tiers,
            'tardiness'  => $tardiness,
        ]);
    }

    // ── GET /api/remuneracion/empleados ──────────────────────────────
    public function empleados()
    {
        // Puestos excluidos
        $excluded = ['administrador', 'gerente', 'practicante'];

        try {
            $odooEmps = $this->odoo->searchRead('hr.employee',
                [['active', '=', true]],
                ['id', 'name', 'image_128', 'department_id', 'job_id', 'resource_calendar_id'],
                ['order' => 'name asc', 'limit' => 300]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        // Filtrar excluidos y Standard 40h
        $filtered = array_filter($odooEmps, function ($e) use ($excluded) {
            $job = strtolower($e['job_id'][1] ?? '');
            if (!$e['job_id']) return false; // sin puesto → excluir
            foreach ($excluded as $ex) {
                if (str_contains($job, $ex)) return false;
            }
            $cal = strtolower($e['resource_calendar_id'][1] ?? '');
            if (str_contains($cal, 'standard 40')) return false; // sin horario → excluir
            return true;
        });

        $result = array_map(function ($e) {
            $empLocal = Employee::where('odoo_id', $e['id'])->first();
            $sal      = $empLocal?->salaryConfig;
            $assW     = $empLocal?->weeklyAssignment;
            $assM     = $empLocal?->monthlyAssignment;

            // Tiers individuales de este empleado (para el panel de bono)
            $empTiers    = $empLocal
                ? GoalTier::forEmployee($empLocal->id)->orderBy('sales_goal')->get()
                : collect();

            return [
                'id'                  => $e['id'],        // Odoo ID (para asistencias, ventas)
                'local_id'            => $empLocal?->id,  // BD local ID (para salarios/tiers)
                'name'                => $e['name'],
                'photo'               => $e['image_128'] ?? null,
                'department'          => $e['department_id'][1] ?? '',
                'job'                 => $e['job_id'][1] ?? '',
                'calendar'            => $e['resource_calendar_id'][1] ?? '',
                'base_salary'         => floatval($sal?->base_salary ?? 0),
                'extra_bonus'         => floatval($sal?->extra_bonus ?? 0),
                'extra_bonus_reason'  => $sal?->extra_bonus_reason ?? '',
                'weekly_area'         => $assW?->area ?? null,
                'monthly_area'        => $assM?->area ?? null,
                'emp_tiers'           => $empTiers->map(fn($t) => [
                    'id'           => $t->id,
                    'label'        => $t->label,
                    'sales_goal'   => floatval($t->sales_goal),
                    'bonus_amount' => floatval($t->bonus_amount),
                    'bonus_pct'    => $t->bonus_pct !== null ? floatval($t->bonus_pct) : null,
                    'sort_order'   => $t->sort_order,
                ])->values(),
                'emp_tier_ids'        => $empTiers->pluck('id')->values(),
            ];
        }, array_values($filtered));

        return response()->json($result);
    }

    // ── GET /api/remuneracion/semana ─────────────────────────────────
    public function semana(Request $request)
    {
        $LIMA_H = 5;
        $limaNow  = now()->subHours($LIMA_H);
        $nowMin   = $limaNow->hour * 60 + $limaNow->minute;
        $todayStr = $limaNow->format('Y-m-d');

        $wsStr     = $request->get('week_start', '');
        $weekStart = $wsStr ? Carbon::parse($wsStr) : $limaNow->copy()->startOfWeek(Carbon::MONDAY);
        $days      = collect(range(0, 6))->map(fn($i) => $weekStart->copy()->addDays($i));
        $weekEnd   = $days->last();

        $utcStart = $weekStart->copy()->addHours($LIMA_H)->format('Y-m-d H:i:s');
        $utcEnd   = $weekEnd->copy()->addDays(1)->addHours($LIMA_H)->subSecond()->format('Y-m-d H:i:s');

        $toLima = fn($s) => $s ? Carbon::parse($s)->subHours($LIMA_H) : null;
        $hhmm   = fn($m)  => sprintf('%02d:%02d', intdiv((int)round($m), 60), (int)round($m) % 60);

        $tardiness = TardinessConfig::current();
        $TOL       = $tardiness->threshold_minutes;

        // Puestos excluidos
        $excluded = ['administrador', 'gerente', 'practicante'];

        // Empleados Odoo
        try {
            $allEmps = $this->odoo->searchRead('hr.employee',
                [['active', '=', true]],
                ['id', 'name', 'image_128', 'department_id', 'job_id', 'resource_calendar_id'],
                ['order' => 'name asc', 'limit' => 300]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error empleados: ' . $e->getMessage()]);
        }

        $emps = array_filter($allEmps, function ($e) use ($excluded) {
            if (!$e['job_id']) return false;
            $job = strtolower($e['job_id'][1] ?? '');
            foreach ($excluded as $ex) {
                if (str_contains($job, $ex)) return false;
            }
            $cal = strtolower($e['resource_calendar_id'][1] ?? '');
            if (str_contains($cal, 'standard 40')) return false;
            return true;
        });
        $emps   = array_values($emps);
        $empMap = array_column($emps, null, 'id');

        if (empty($emps)) return response()->json(['error' => 'No hay empleados remunerables']);

        // Calendarios
        $calIds   = array_unique(array_filter(array_map(fn($e) => $e['resource_calendar_id'][0] ?? null, $emps)));
        $calNames = [];
        $calWeek  = []; // {cal_id: {dow: [{hour_from, hour_to, day_period}]}}

        if ($calIds) {
            try {
                foreach ($this->odoo->read('resource.calendar', array_values($calIds), ['id', 'name']) as $c) {
                    $calNames[$c['id']] = $c['name'];
                }
                foreach ($this->odoo->searchRead('resource.calendar.attendance',
                    [['calendar_id', 'in', array_values($calIds)]],
                    ['calendar_id', 'dayofweek', 'hour_from', 'hour_to', 'day_period']) as $ln) {
                    $cid = $ln['calendar_id'][0];
                    $dow = (int)$ln['dayofweek'];
                    $calWeek[$cid][$dow][] = [
                        'hour_from'  => $ln['hour_from'],
                        'hour_to'    => $ln['hour_to'],
                        'day_period' => $ln['day_period'] ?? 'morning',
                    ];
                }
            } catch (\Exception $e) {}
        }

        // Asistencias
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
                    'check_in'  => $ci,
                    'check_out' => $co,
                    'ci_min'    => $ci->hour * 60 + $ci->minute,
                    'co_min'    => $co ? $co->hour * 60 + $co->minute : null,
                ];
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error asistencias: ' . $e->getMessage()]);
        }

        // Ventas POS
        $ventasPorEmp = [];
        try {
            foreach ($this->odoo->searchRead('pos.order',
                [['state', 'in', ['paid', 'done', 'invoiced']],
                 ['date_order', '>=', $utcStart],
                 ['date_order', '<=', $utcEnd]],
                ['employee_id', 'user_id', 'amount_total'],
                ['limit' => 10000]) as $o) {
                $emp = $o['employee_id'] ?? false;
                $eid = ($emp && $emp[0]) ? $emp[0] : null;
                if (!$eid) { $usr = $o['user_id'] ?? false; $eid = ($usr && $usr[0]) ? $usr[0] : null; }
                if ($eid && isset($empMap[$eid])) {
                    $ventasPorEmp[$eid] = ($ventasPorEmp[$eid] ?? 0) + floatval($o['amount_total'] ?? 0);
                }
            }
        } catch (\Exception $e) {}

        // Tiers individuales — se cargan por empleado dentro del loop
        // (no hay bono grupal)

        $turnoManEmps = [];
        $turnoTarEmps = [];
        $resultados   = [];

        foreach ($emps as $emp) {
            $eid     = $emp['id'];
            $cdata   = $emp['resource_calendar_id'] ?? false;
            if (!$cdata) continue;
            $cid      = $cdata[0];
            $empSched = $calWeek[$cid] ?? [];

            $empLocal  = Employee::where('odoo_id', $eid)->first();
            $sal       = $empLocal?->salaryConfig;
            $salario   = floatval($sal?->base_salary ?? 0);
            $extraBonus = floatval($sal?->extra_bonus ?? 0);
            $extraReason = $sal?->extra_bonus_reason ?? '';

            // Tiers individuales del empleado (por employee_local_id)
            $empTiersW = $empLocal
                ? GoalTier::forEmployee($empLocal->id)->weekly()->orderByDesc('sales_goal')->get()
                : collect();

            $ventas    = floatval($ventasPorEmp[$eid] ?? 0);

            // Justificaciones
            $justifMap = collect();
            if ($empLocal) {
                $justifMap = AttendanceJustification::where('employee_id', $empLocal->id)
                    ->where('justified', true)
                    ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                    ->get()->keyBy(fn($j) => $j->date->format('Y-m-d') . '_' . $j->type);
            }

            $faltasDias    = [];
            $tardanzasDias = [];
            $ciMinsAll     = [];
            $totalDescuento = 0.0;

            foreach ($days as $day) {
                $ds  = $day->format('Y-m-d');
                $dow = $day->dayOfWeek === 0 ? 6 : $day->dayOfWeek - 1;
                $isToday = $ds === $todayStr;

                $daySlots = $empSched[$dow] ?? [];
                // Filtrar descansos
                $workSlots = array_filter($daySlots, fn($s) => ($s['day_period'] ?? '') !== 'lunch');
                if (empty($daySlots)) continue;

                // Horas laborables del día (sin descanso)
                $dailyHours = array_sum(array_map(fn($s) => ($s['hour_to'] - $s['hour_from']), $workSlots));

                // Para empleados con doble turno, buscar el slot más cercano a la hora real de entrada
                $recs = $attBy[$eid][$ds] ?? [];
                $ciMinTemp = !empty($recs) ? collect($recs)->sortBy('ci_min')->first()['ci_min'] : null;

                // Seleccionar el slot de trabajo más cercano a la hora de entrada
                $workSlotsCollection = collect($workSlots)->sortBy('hour_from');
                if ($ciMinTemp !== null) {
                    $matchedSlot = $workSlotsCollection->sortBy(function($slot) use ($ciMinTemp) {
                        return abs($slot['hour_from'] * 60 - $ciMinTemp);
                    })->first();
                } else {
                    $matchedSlot = $workSlotsCollection->first();
                }
                $matchedSlot = $matchedSlot ?? collect($daySlots)->sortBy('hour_from')->first();

                $mf = (int)round($matchedSlot['hour_from'] * 60);
                $mt = (int)round($matchedSlot['hour_to'] * 60);
                $ti = $mf + $TOL;

                $recs = $attBy[$eid][$ds] ?? [];
                $hhmm_mf = $hhmm($mf);
                $hhmm_mt = $hhmm($mt);

                // Día futuro: no se puede marcar como falta todavía
                $isFuture = $ds > $todayStr;

                if (empty($recs)) {
                    if ($isFuture || ($isToday && $nowMin < $ti)) {
                        // Aún no termina el día — no contar como falta
                        continue;
                    }
                    $justified   = $justifMap->has("{$ds}_falta");
                    $costPerHour = $dailyHours > 0 ? $salario / (26 * $dailyHours) : 0;
                    $descuento   = $justified ? 0 : round($dailyHours * $costPerHour, 2);
                    $totalDescuento += $descuento;
                    $faltasDias[] = [
                        'date'         => $ds,
                        'label'        => $day->locale('es')->isoFormat('ddd DD/MM'),
                        'expected_in'  => $hhmm_mf,
                        'expected_out' => $hhmm_mt,
                        'daily_hours'  => $dailyHours,
                        'descuento'    => $descuento,
                        'justificado'  => $justified,
                    ];
                } else {
                    $rec   = collect($recs)->sortBy('ci_min')->first();
                    $ciMin = $rec['ci_min'];
                    $ciMinsAll[] = $ciMin;
                    if ($ciMin > $ti) {
                        $justified = $justifMap->has("{$ds}_tardanza");
                        $tardanzasDias[] = [
                            'date'        => $ds,
                            'label'       => $day->locale('es')->isoFormat('ddd DD/MM'),
                            'check_in'    => $rec['check_in']->format('H:i'),
                            'expected_in' => $hhmm_mf,
                            'minutos'     => $ciMin - $ti,
                            'justificado' => $justified,
                        ];
                    }
                }
            }

            // Turno inferido
            $turnoInf = 'indefinido';
            if ($ciMinsAll) {
                $manDias  = count(array_filter($ciMinsAll, fn($m) => $m < 11 * 60));
                $turnoInf = $manDias >= count($ciMinsAll) / 2 ? 'manana' : 'tarde';
            } else {
                // Inferir por horario configurado
                $allSlots = array_merge(...array_values($empSched));
                $avgFrom  = count($allSlots) > 0 ? array_sum(array_column($allSlots, 'hour_from')) / count($allSlots) : 12;
                $turnoInf = $avgFrom < 11 ? 'manana' : 'tarde';
            }

            if ($turnoInf === 'manana') $turnoManEmps[] = $eid;
            elseif ($turnoInf === 'tarde') $turnoTarEmps[] = $eid;

            $faltasCount    = count(array_filter($faltasDias,    fn($f) => !$f['justificado']));
            $tardanzasCount = count(array_filter($tardanzasDias, fn($t) => !$t['justificado']));

            $pierdeBono   = false;
            $razonPerdida = [];
            if ($faltasCount >= 1)    { $pierdeBono = true; $razonPerdida[] = "{$faltasCount} falta(s)"; }
            if ($tardanzasCount >= 3) { $pierdeBono = true; $razonPerdida[] = "{$tardanzasCount} tardanza(s)"; }

            // Nivel alcanzado individual
            $tierAlcanzado  = !$pierdeBono ? GoalTier::reached($ventas, $empTiersW) : null;
            $bonoIndivBruto = $tierAlcanzado ? $tierAlcanzado->calculateBonus($ventas) : 0;
            $tierLabel      = $tierAlcanzado?->label ?? null;

            // ── Orden y Limpieza ──────────────────────────────────────
            $olCfg        = OrderCleanlinessConfig::current();
            $olPuntos     = null;   // null = sin datos aún
            $olPromedio   = null;
            $olDescPct    = 0;
            $bonoIndiv    = $bonoIndivBruto;

            if ($empLocal) {
                // Obtener scores de la semana para este empleado
                $olScores = OrderCleanlinessScore::where('employee_local_id', $empLocal->id)
                    ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                    ->get()->keyBy(fn($s) => $s->date->format('Y-m-d'));

                // Calcular promedio sobre días laborables del empleado
                $olValues  = [];
                foreach ($days as $day) {
                    $ds  = $day->format('Y-m-d');
                    $dow = $day->dayOfWeek === 0 ? 6 : $day->dayOfWeek - 1;
                    $daySlots = $empSched[$dow] ?? [];
                    if (empty($daySlots)) continue;  // día libre → no cuenta en O&L

                    if ($olScores->has($ds)) {
                        $olValues[] = floatval($olScores[$ds]->score);
                    } else {
                        // Día laborable sin score: falta justificada → excluir, falta no justificada → 0
                        $isFaltaJustif = $justifMap->has("{$ds}_falta");
                        if (!$isFaltaJustif) {
                            // Revisar si tiene asistencia registrada en ese día
                            $tieneAsistencia = !empty($attBy[$eid][$ds] ?? []);
                            if (!$tieneAsistencia) {
                                // Falta no justificada → 0
                                $olValues[] = 0.0;
                            }
                            // Si asistió pero no tiene score aún → no incluir (pendiente de calificar)
                        }
                        // Falta justificada → excluir del promedio
                    }
                }

                if (!empty($olValues)) {
                    $olPromedio = round(array_sum($olValues) / count($olValues), 2);
                    $olPuntos   = $olCfg->resolvePoints($olPromedio);
                    $olDescPct  = $olCfg->discountPct($olPuntos);
                    // Aplicar descuento O&L al bono (sobre el bruto)
                    $bonoIndiv  = round($bonoIndivBruto * (1 - $olDescPct / 100), 2);
                }
            }

            $resultados[] = [
                'id'              => $eid,
                'local_id'        => $empLocal?->id,
                'name'            => $emp['name'],
                'photo'           => $emp['image_128'] ?? null,
                'department'      => $emp['department_id'][1] ?? '',
                'job'             => $emp['job_id'][1] ?? '',
                'salario_base'    => $salario,
                'extra_bonus'     => $extraBonus,
                'extra_bonus_reason' => $extraReason,
                'ventas_semana'   => round($ventas, 2),
                'faltas'          => $faltasCount,
                'tardanzas'       => $tardanzasCount,
                'faltas_dias'     => $faltasDias,
                'tardanzas_dias'  => $tardanzasDias,
                'dias_laborables' => array_values(array_filter(array_map(function($day) use ($empSched) {
                    $dow = $day->dayOfWeek === 0 ? 6 : $day->dayOfWeek - 1;
                    return !empty($empSched[$dow]) ? $day->format('Y-m-d') : null;
                }, $days->all()))),
                'descuento_total' => round($totalDescuento, 2),
                'pierde_bono'     => $pierdeBono,
                'razon_perdida'   => implode(' · ', $razonPerdida),
                'turno'           => $turnoInf,
                'tier_individual' => $tierLabel,
                'bono_individual' => $bonoIndiv,        // ya con descuento O&L aplicado
                'bono_bruto'      => $bonoIndivBruto,   // sin descuento O&L
                'ol_promedio'     => $olPromedio,
                'ol_puntos'       => $olPuntos,
                'ol_desc_pct'     => $olDescPct,
                'afp'             => round($salario * 0.13, 2),
                'total_estimado'  => round(($salario * 0.87) + $extraBonus - $totalDescuento + $bonoIndiv, 2),
            ];
        }

        $totalNomina   = array_sum(array_column($resultados, 'total_estimado'));
        $totalBonos    = array_sum(array_column($resultados, 'bono_individual'));
        $bonosPerdidos = count(array_filter($resultados, fn($r) => $r['pierde_bono']));

        return response()->json([
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end'   => $weekEnd->format('Y-m-d'),
            'empleados'  => $resultados,
            'resumen' => [
                'total_nomina'    => round($totalNomina, 2),
                'total_bonos'     => round($totalBonos, 2),
                'bonos_perdidos'  => $bonosPerdidos,
                'total_empleados' => count($resultados),
            ],
        ]);
    }

    // ── GET /api/remuneracion/detalle ────────────────────────────────
    public function detalle(Request $request)
    {
        $LIMA_H = 5;
        $empOdooId = (int)$request->get('emp_id', 0);
        if (!$empOdooId) return response()->json(['error' => 'Falta emp_id']);

        $limaNow   = now()->subHours($LIMA_H);
        $nowMin    = $limaNow->hour * 60 + $limaNow->minute;
        $todayStr  = $limaNow->format('Y-m-d');
        $tardiness = TardinessConfig::current();
        $TOL       = $tardiness->threshold_minutes;

        $wsStr     = $request->get('week_start', '');
        $weekStart = $wsStr ? Carbon::parse($wsStr) : $limaNow->copy()->startOfWeek(Carbon::MONDAY);
        $days      = collect(range(0, 6))->map(fn($i) => $weekStart->copy()->addDays($i));
        $weekEnd   = $days->last();
        $labels    = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

        $utcStart = $weekStart->copy()->addHours($LIMA_H)->format('Y-m-d H:i:s');
        $utcEnd   = $weekEnd->copy()->addDays(1)->addHours($LIMA_H)->subSecond()->format('Y-m-d H:i:s');
        $toLima   = fn($s) => $s ? Carbon::parse($s)->subHours($LIMA_H) : null;
        $hhmm     = fn($m) => sprintf('%02d:%02d', intdiv((int)round($m), 60), (int)round($m) % 60);

        try {
            $emps = $this->odoo->read('hr.employee', [$empOdooId],
                ['id', 'name', 'resource_calendar_id', 'department_id', 'job_id']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
        if (empty($emps)) return response()->json(['error' => 'Empleado no encontrado']);
        $emp   = $emps[0];
        $cdata = $emp['resource_calendar_id'] ?? false;

        $calName  = '';
        $empSched = [];
        if ($cdata) {
            $cid = $cdata[0];
            try {
                $cr = $this->odoo->read('resource.calendar', [$cid], ['id', 'name']);
                $calName = $cr[0]['name'] ?? '';
                foreach ($this->odoo->searchRead('resource.calendar.attendance',
                    [['calendar_id', '=', $cid]],
                    ['dayofweek', 'hour_from', 'hour_to', 'day_period']) as $ln) {
                    $dow = (int)$ln['dayofweek'];
                    $empSched[$dow][] = [
                        'hour_from'  => $ln['hour_from'],
                        'hour_to'    => $ln['hour_to'],
                        'day_period' => $ln['day_period'] ?? 'morning',
                    ];
                }
            } catch (\Exception $e) {}
        }

        // Asistencias
        $attMap = [];
        try {
            foreach ($this->odoo->searchRead('hr.attendance',
                [['employee_id', '=', $empOdooId],
                 ['check_in', '>=', $utcStart],
                 ['check_in', '<=', $utcEnd]],
                ['check_in', 'check_out'], ['limit' => 100]) as $a) {
                $ci = $toLima($a['check_in']);
                if (!$ci) continue;
                $d  = $ci->format('Y-m-d');
                $co = $toLima($a['check_out'] ?? null);
                $r  = ['ci' => $ci, 'co' => $co, 'ci_min' => $ci->hour * 60 + $ci->minute];
                if (!isset($attMap[$d]) || $r['ci_min'] < $attMap[$d]['ci_min']) $attMap[$d] = $r;
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error asistencias: ' . $e->getMessage()]);
        }

        $empLocal  = Employee::where('odoo_id', $empOdooId)->first();
        $sal       = $empLocal?->salaryConfig;
        $salario   = floatval($sal?->base_salary ?? 0);

        $justifMap = collect();
        if ($empLocal) {
            $justifMap = AttendanceJustification::where('employee_id', $empLocal->id)
                ->where('justified', true)
                ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                ->get()->keyBy(fn($j) => $j->date->format('Y-m-d') . '_' . $j->type);
        }

        $resultDays     = [];
        $totalDescuento = 0.0;

        foreach ($days as $i => $day) {
            $ds      = $day->format('Y-m-d');
            $dow     = $day->dayOfWeek === 0 ? 6 : $day->dayOfWeek - 1;
            $isToday = $ds === $todayStr;
            $rec     = $attMap[$ds] ?? null;

            $daySlots  = $empSched[$dow] ?? [];
            $workSlots = array_filter($daySlots, fn($s) => ($s['day_period'] ?? '') !== 'lunch');

            if (empty($daySlots)) {
                $resultDays[] = [
                    'date' => $ds, 'label' => $labels[$i],
                    'status' => 'dia_libre', 'obs' => 'Día libre',
                    'check_in' => $rec ? $rec['ci']->format('H:i') : null,
                    'check_out' => ($rec && $rec['co']) ? $rec['co']->format('H:i') : null,
                    'expected_in' => null, 'expected_out' => null,
                    'daily_hours' => 0, 'descuento' => 0,
                    'minutos_tardanza' => 0,
                    'justif_falta' => false, 'justif_tardanza' => false,
                    'is_day_off' => true,
                ];
                continue;
            }

            $dailyHours = array_sum(array_map(fn($s) => $s['hour_to'] - $s['hour_from'], $workSlots));
            // Usar $attMap (definido en este scope, por empleado) para buscar la entrada del día
            $ciMinTemp = isset($attMap[$ds]) ? $attMap[$ds]['ci_min'] : null;

            // Seleccionar el slot de trabajo más cercano a la hora de entrada
            $workSlotsCollection = collect($workSlots)->sortBy('hour_from');
            if ($ciMinTemp !== null) {
                $matchedSlot = $workSlotsCollection->sortBy(function($slot) use ($ciMinTemp) {
                    return abs($slot['hour_from'] * 60 - $ciMinTemp);
                })->first();
            } else {
                $matchedSlot = $workSlotsCollection->first();
            }
            $matchedSlot = $matchedSlot ?? collect($daySlots)->sortBy('hour_from')->first();

            $mf = (int)round($matchedSlot['hour_from'] * 60);
            $mt = (int)round($matchedSlot['hour_to'] * 60);
            $ti = $mf + $TOL;

            if (!$rec) {
                // Día futuro o aún en curso hoy
                $isFuture = $ds > $todayStr;
                if ($isFuture || ($isToday && $nowMin < $ti)) {
                    $status = 'pendiente';
                    $resultDays[] = [
                        'date' => $ds, 'label' => $labels[$i],
                        'status' => 'pendiente',
                        'obs'    => 'Aún no transcurrido',
                        'check_in' => null, 'check_out' => null,
                        'expected_in' => $hhmm($mf), 'expected_out' => $hhmm($mt),
                        'daily_hours' => $dailyHours, 'descuento' => 0,
                        'minutos_tardanza' => 0,
                        'justif_falta'    => false,
                        'justif_tardanza' => false,
                        'is_day_off' => false,
                    ];
                    continue;
                }
                $status = 'falta';
                $justified   = $justifMap->has("{$ds}_falta");
                $costPerHour = $dailyHours > 0 ? $salario / (26 * $dailyHours) : 0;
                $descuento   = ($status === 'falta' && !$justified) ? round($dailyHours * $costPerHour, 2) : 0;
                $totalDescuento += $descuento;

                $resultDays[] = [
                    'date' => $ds, 'label' => $labels[$i],
                    'status' => $status,
                    'obs'    => $status === 'falta' ? 'Ausente' : 'Pendiente',
                    'check_in' => null, 'check_out' => null,
                    'expected_in' => $hhmm($mf), 'expected_out' => $hhmm($mt),
                    'daily_hours' => $dailyHours, 'descuento' => $descuento,
                    'minutos_tardanza' => 0,
                    'justif_falta'    => $justified,
                    'justif_tardanza' => false,
                    'is_day_off' => false,
                ];
            } else {
                $ciMin = $rec['ci_min'];
                $lateM = max(0, $ciMin - $ti);
                $status = $lateM > 0 ? 'tardanza' : 'puntual';

                $resultDays[] = [
                    'date' => $ds, 'label' => $labels[$i],
                    'status' => $status,
                    'obs'    => $lateM > 0 ? "Tardanza {$lateM} min" : 'Puntual',
                    'check_in'  => $rec['ci']->format('H:i'),
                    'check_out' => $rec['co'] ? $rec['co']->format('H:i') : null,
                    'expected_in' => $hhmm($mf), 'expected_out' => $hhmm($mt),
                    'daily_hours' => $dailyHours, 'descuento' => 0,
                    'minutos_tardanza' => $lateM,
                    'justif_falta'    => false,
                    'justif_tardanza' => $justifMap->has("{$ds}_tardanza"),
                    'is_day_off' => false,
                ];
            }
        }

        return response()->json([
            'emp_id'          => $empOdooId,
            'name'            => $emp['name'],
            'calendar'        => $calName,
            'salario_base'    => $salario,
            'days'            => $resultDays,
            'descuento_total' => round($totalDescuento, 2),
            'week_start'      => $weekStart->format('Y-m-d'),
        ]);
    }

    // ── POST /api/remuneracion/salarios ──────────────────────────────
    // Acepta employee_id como Odoo ID o local_id como BD local ID
    public function saveSalarios(Request $request)
    {
        $request->validate([
            'salarios'                      => 'required|array',
            'salarios.*.base_salary'        => 'required|numeric|min:0',
            'salarios.*.extra_bonus'        => 'nullable|numeric|min:0',
            'salarios.*.extra_bonus_reason' => 'nullable|string|max:255',
        ]);

        foreach ($request->salarios as $item) {
            // Soporta local_id (BD) o employee_id (Odoo ID)
            if (!empty($item['local_id'])) {
                $empLocal = Employee::find((int)$item['local_id']);
            } else {
                $empLocal = Employee::where('odoo_id', (int)$item['employee_id'])->first();
                // Si no existe en local, crear registro mínimo
                if (!$empLocal) {
                    $empLocal = Employee::create([
                        'odoo_id' => (int)$item['employee_id'],
                        'name'    => $item['name'] ?? 'Empleado ' . $item['employee_id'],
                        'active'  => true,
                    ]);
                }
            }
            if (!$empLocal) continue;

            \App\Models\SalaryConfig::updateOrCreate(
                ['employee_id' => $empLocal->id],
                [
                    'base_salary'        => $item['base_salary'],
                    'extra_bonus'        => $item['extra_bonus'] ?? 0,
                    'extra_bonus_reason' => $item['extra_bonus_reason'] ?? null,
                ]
            );
        }

        return response()->json(['ok' => true]);
    }

    // ── POST /api/remuneracion/metas ─────────────────────────────────
    public function saveMetas(Request $request)
    {
        // Guardar/actualizar tiers
        if ($request->has('tiers')) {
            foreach ($request->tiers as $tier) {
                // Resolver employee_local_id: puede venir como local_id o employee_id (odoo)
                $empLocalId = null;
                if (!empty($tier['local_id'])) {
                    $empLocalId = (int)$tier['local_id'];
                } elseif (!empty($tier['employee_local_id'])) {
                    $empLocalId = (int)$tier['employee_local_id'];
                } elseif (!empty($tier['employee_id'])) {
                    // Buscar por odoo_id
                    $emp = Employee::where('odoo_id', (int)$tier['employee_id'])->first();
                    if (!$emp) {
                        $emp = Employee::create(['odoo_id' => (int)$tier['employee_id'], 'name' => 'Empleado', 'active' => true]);
                    }
                    $empLocalId = $emp->id;
                }

                if (isset($tier['id'])) {
                    GoalTier::where('id', $tier['id'])->update([
                        'employee_local_id' => $empLocalId,
                        'label'             => $tier['label'],
                        'sales_goal'        => $tier['sales_goal'],
                        'bonus_amount'      => $tier['bonus_amount'] ?? 0,
                        'bonus_pct'         => isset($tier['bonus_pct']) ? floatval($tier['bonus_pct']) : null,
                        'sort_order'        => $tier['sort_order'] ?? 0,
                    ]);
                } else {
                    GoalTier::create([
                        'employee_local_id' => $empLocalId,
                        'owner_type'        => $tier['owner_type'] ?? 'individual',
                        'area'              => $tier['area'] ?? 'ventas',
                        'period_type'       => $tier['period_type'] ?? 'weekly',
                        'shift'             => $tier['shift'] ?? null,
                        'label'             => $tier['label'],
                        'sales_goal'        => $tier['sales_goal'],
                        'bonus_amount'      => $tier['bonus_amount'] ?? 0,
                        'bonus_pct'         => isset($tier['bonus_pct']) ? floatval($tier['bonus_pct']) : null,
                        'sort_order'        => $tier['sort_order'] ?? 0,
                    ]);
                }
            }
        }

        // Eliminar tiers por ID
        if ($request->has('delete_tier_ids') && !empty($request->delete_tier_ids)) {
            GoalTier::whereIn('id', $request->delete_tier_ids)->delete();
        }

        // Config tardanzas
        if ($request->has('tardiness')) {
            $t = TardinessConfig::current();
            $t->update([
                'threshold_minutes' => $request->tardiness['threshold_minutes'] ?? 10,
                'deduction_amount'  => $request->tardiness['deduction_amount'] ?? null,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    // ── GET /api/remuneracion/justificaciones ────────────────────────
    public function justificaciones(Request $request)
    {
        $empOdooId = (int)$request->get('emp_id', 0);
        $wsStr     = $request->get('week_start', '');

        $empLocal = $empOdooId ? Employee::where('odoo_id', $empOdooId)->first() : null;
        if (!$empLocal) return response()->json([]);

        $weekStart = $wsStr ? Carbon::parse($wsStr) : now()->startOfWeek(Carbon::MONDAY);
        $weekEnd   = $weekStart->copy()->addDays(6);

        $justifs = AttendanceJustification::where('employee_id', $empLocal->id)
            ->where('justified', true)
            ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->get()
            ->map(fn($j) => [
                'date'      => $j->date->format('Y-m-d'),
                'type'      => $j->type,
                'justified' => $j->justified,
                'reason'    => $j->reason,
            ]);

        return response()->json($justifs);
    }

    // ── POST /api/remuneracion/justificacion ─────────────────────────
    public function saveJustificacion(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'date'        => 'required|date',
            'type'        => 'required|in:falta,tardanza',
            'justified'   => 'required|boolean',
            'reason'      => 'nullable|string|max:500',
        ]);

        $empLocal = Employee::where('odoo_id', $request->employee_id)
            ->orWhere('id', $request->employee_id)
            ->first();
        if (!$empLocal) {
            $oe = $this->odoo->read('hr.employee', [$request->employee_id], ['name', 'department_id', 'job_id']);
            if (empty($oe)) return response()->json(['error' => 'Empleado no encontrado en Odoo ni en base local.'], 404);
            $empLocal = Employee::create([
                'odoo_id'    => $oe[0]['id'],
                'name'       => $oe[0]['name'],
                'department' => $oe[0]['department_id'][1] ?? 'Sin Departamento',
                'shift'      => 'manana',
                'active'     => true,
            ]);
        }

        AttendanceJustification::updateOrCreate(
            ['employee_id' => $empLocal->id, 'date' => $request->date, 'type' => $request->type],
            ['justified' => $request->justified, 'reason' => $request->reason, 'created_by' => auth()->id() ?? 1]
        );

        return response()->json(['ok' => true]);
    }

    // ── GET /api/nomina/emp-mes?local_id=X&year=Y&month=M ─────────────
    public function empMes(Request $request)
    {
        $localId = (int)$request->get('local_id', 0);
        $year    = (int)($request->year  ?? now()->year);
        $month   = (int)($request->month ?? now()->month);

        $empLocal = Employee::with('salaryConfig')->find($localId);
        if (!$empLocal || !$empLocal->odoo_id) return response()->json(['error' => 'Empleado no encontrado'], 404);

        $sal        = $empLocal->salaryConfig;
        $salarioBase = floatval($sal?->base_salary ?? 0);
        $sueldoNeto  = round($salarioBase * 0.87 + floatval($sal?->extra_bonus ?? 0), 2);

        $LIMA_H   = 5;
        $limaNow  = now()->subHours($LIMA_H);
        $todayStr = $limaNow->format('Y-m-d');
        $nowMin   = $limaNow->hour * 60 + $limaNow->minute;
        $tardiness = TardinessConfig::current();
        $TOL       = $tardiness->threshold_minutes;
        $hhmm      = fn($m) => sprintf('%02d:%02d', intdiv((int)round($m), 60), (int)round($m) % 60);
        $toLima    = fn($s) => $s ? Carbon::parse($s)->subHours($LIMA_H) : null;
        $labels    = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];

        // Odoo employee info
        try {
            $emps = $this->odoo->read('hr.employee', [$empLocal->odoo_id],
                ['id','name','resource_calendar_id','job_id','image_128']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
        if (empty($emps)) return response()->json(['error' => 'No encontrado en Odoo']);
        $emp = $emps[0];

        // Calendar/schedule
        $empSched = [];
        $calName  = '';
        if ($cdata = ($emp['resource_calendar_id'] ?? false)) {
            $cid = $cdata[0];
            try {
                $cr = $this->odoo->read('resource.calendar', [$cid], ['name']);
                $calName = $cr[0]['name'] ?? '';
                foreach ($this->odoo->searchRead('resource.calendar.attendance',
                    [['calendar_id','=',$cid]],
                    ['dayofweek','hour_from','hour_to','day_period']) as $ln) {
                    $empSched[(int)$ln['dayofweek']][] = [
                        'hour_from'  => $ln['hour_from'],
                        'hour_to'    => $ln['hour_to'],
                        'day_period' => $ln['day_period'] ?? 'morning',
                    ];
                }
            } catch (\Exception $e) {}
        }

        // Weeks for this month (Sunday defines the month)
        $firstOfMonth = Carbon::create($year, $month, 1);
        $lastOfMonth  = $firstOfMonth->copy()->endOfMonth();
        $cursor       = $firstOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $semanas      = [];
        while ($cursor->lte($lastOfMonth)) {
            $ws = $cursor->copy();
            $we = $cursor->copy()->endOfWeek(Carbon::SUNDAY);
            if ($we->month === $month) $semanas[] = ['start' => $ws, 'end' => $we];
            $cursor->addWeek();
        }
        if (empty($semanas)) return response()->json(['error' => 'Sin semanas para este mes']);

        $firstWs = $semanas[0]['start'];
        $lastWe  = end($semanas)['end'];
        $utcStart = $firstWs->copy()->addHours($LIMA_H)->format('Y-m-d H:i:s');
        $utcEnd   = $lastWe->copy()->addDays(1)->addHours($LIMA_H)->subSecond()->format('Y-m-d H:i:s');

        // Attendance for full month range
        $attMap = [];
        try {
            foreach ($this->odoo->searchRead('hr.attendance',
                [['employee_id','=',$empLocal->odoo_id],
                 ['check_in','>=',$utcStart],['check_in','<=',$utcEnd]],
                ['check_in','check_out'], ['limit'=>500]) as $a) {
                $ci = $toLima($a['check_in']);
                if (!$ci) continue;
                $d  = $ci->format('Y-m-d');
                $co = $toLima($a['check_out'] ?? null);
                $r  = ['ci'=>$ci,'co'=>$co,'ci_min'=>$ci->hour*60+$ci->minute];
                if (!isset($attMap[$d]) || $r['ci_min'] < $attMap[$d]['ci_min']) $attMap[$d] = $r;
            }
        } catch (\Exception $e) {}

        // Tiers
        $empTiers = GoalTier::forEmployee($localId)->weekly()->orderByDesc('sales_goal')->get();

        // Justifications
        $justifMap = AttendanceJustification::where('employee_id', $localId)
            ->where('justified', true)
            ->whereBetween('date', [$firstWs->format('Y-m-d'), $lastWe->format('Y-m-d')])
            ->get()->keyBy(fn($j) => $j->date->format('Y-m-d').'_'.$j->type);

        // Deliveries
        $weekStarts = array_map(fn($s) => $s['start']->format('Y-m-d'), $semanas);
        $deliveries = MonthlyBonusDelivery::where('employee_local_id', $localId)
            ->whereIn('week_start', $weekStarts)->get()->keyBy(fn($d) => $d->week_start->format('Y-m-d'));

        // Ventas POS per week (one batch query)
        $posOrders = [];
        try {
            foreach ($this->odoo->searchRead('pos.order',
                [['state','in',['paid','done','invoiced']],
                 ['date_order','>=',$utcStart],['date_order','<=',$utcEnd]],
                ['employee_id','user_id','amount_total','date_order'], ['limit'=>5000]) as $o) {
                $oeid = ($o['employee_id'][0] ?? null) ?: ($o['user_id'][0] ?? null);
                if ($oeid == $empLocal->odoo_id) $posOrders[] = $o;
            }
        } catch (\Exception $e) {}

        $semanasResult = [];
        foreach ($semanas as $sem) {
            $wsStr = $sem['start']->format('Y-m-d');
            $weStr = $sem['end']->format('Y-m-d');
            $days  = collect(range(0,6))->map(fn($i) => $sem['start']->copy()->addDays($i));

            // Ventas of this week
            $wsUtc = $sem['start']->copy()->addHours($LIMA_H)->format('Y-m-d H:i:s');
            $weUtc = $sem['end']->copy()->addDays(1)->addHours($LIMA_H)->subSecond()->format('Y-m-d H:i:s');
            $ventas = array_sum(array_map(
                fn($o) => ($o['date_order'] >= $wsUtc && $o['date_order'] <= $weUtc) ? floatval($o['amount_total']) : 0,
                $posOrders
            ));

            $faltasCount = $tardanzasCount = 0;
            $totalDescuento = 0.0;
            $resultDays = [];

            foreach ($days as $i => $day) {
                $ds      = $day->format('Y-m-d');
                $dow     = $day->dayOfWeek === 0 ? 6 : $day->dayOfWeek - 1;
                $isToday = $ds === $todayStr;
                $rec     = $attMap[$ds] ?? null;

                $daySlots  = $empSched[$dow] ?? [];
                $workSlots = array_filter($daySlots, fn($s) => ($s['day_period'] ?? '') !== 'lunch');

                if (empty($daySlots)) {
                    $resultDays[] = [
                        'date'=>$ds,'label'=>$labels[$i],'status'=>'dia_libre',
                        'check_in'=>$rec?$rec['ci']->format('H:i'):null,
                        'check_out'=>($rec&&$rec['co'])?$rec['co']->format('H:i'):null,
                        'expected_in'=>null,'descuento'=>0,'minutos_tardanza'=>0,
                        'justif_falta'=>false,'justif_tardanza'=>false,'is_day_off'=>true,'justif_reason'=>null,
                    ];
                    continue;
                }

                $dailyHours = array_sum(array_map(fn($s) => $s['hour_to']-$s['hour_from'], $workSlots));
                $ciMinTemp  = $rec['ci_min'] ?? null;
                $wsc        = collect($workSlots)->sortBy('hour_from');
                $ms         = $ciMinTemp !== null
                    ? $wsc->sortBy(fn($s) => abs($s['hour_from']*60-$ciMinTemp))->first()
                    : $wsc->first();
                $ms  = $ms ?? collect($daySlots)->sortBy('hour_from')->first();
                $mf  = (int)round($ms['hour_from']*60);
                $mt  = (int)round($ms['hour_to']*60);
                $ti  = $mf + $TOL;
                $isFuture = $ds > $todayStr;

                if (!$rec) {
                    if ($isFuture || ($isToday && $nowMin < $ti)) {
                        $resultDays[] = ['date'=>$ds,'label'=>$labels[$i],'status'=>'pendiente',
                            'check_in'=>null,'check_out'=>null,'expected_in'=>$hhmm($mf),
                            'descuento'=>0,'minutos_tardanza'=>0,
                            'justif_falta'=>false,'justif_tardanza'=>false,'is_day_off'=>false,'justif_reason'=>null];
                        continue;
                    }
                    $justified      = $justifMap->has("{$ds}_falta");
                    $costPerHour    = $dailyHours > 0 ? $salarioBase / (26*$dailyHours) : 0;
                    $descuento      = $justified ? 0 : round($dailyHours*$costPerHour, 2);
                    $totalDescuento += $descuento;
                    if (!$justified) $faltasCount++;
                    $jRec = $justifMap->get("{$ds}_falta");
                    $resultDays[] = ['date'=>$ds,'label'=>$labels[$i],'status'=>'falta',
                        'check_in'=>null,'check_out'=>null,'expected_in'=>$hhmm($mf),
                        'descuento'=>$descuento,'minutos_tardanza'=>0,
                        'justif_falta'=>$justified,'justif_tardanza'=>false,'is_day_off'=>false,
                        'justif_reason'=>$jRec?->reason];
                } else {
                    $ciMin  = $rec['ci_min'];
                    $lateM  = max(0, $ciMin - $ti);
                    $status = $lateM > 0 ? 'tardanza' : 'puntual';
                    $justT  = $status === 'tardanza' && $justifMap->has("{$ds}_tardanza");
                    if ($status === 'tardanza' && !$justT) $tardanzasCount++;
                    $jRec = $justT ? $justifMap->get("{$ds}_tardanza") : null;
                    $resultDays[] = ['date'=>$ds,'label'=>$labels[$i],'status'=>$status,
                        'check_in'=>$rec['ci']->format('H:i'),
                        'check_out'=>$rec['co']?$rec['co']->format('H:i'):null,
                        'expected_in'=>$hhmm($mf),'descuento'=>0,'minutos_tardanza'=>$lateM,
                        'justif_falta'=>false,'justif_tardanza'=>$justT,'is_day_off'=>false,
                        'justif_reason'=>$jRec?->reason];
                }
            }

            $pierdeBono   = $faltasCount >= 1 || $tardanzasCount >= 3;
            $tierAlc      = !$pierdeBono ? GoalTier::reached($ventas, $empTiers) : null;
            $bonoCalc     = $tierAlc ? $tierAlc->calculateBonus($ventas) : 0;
            $deliv        = $deliveries->get($wsStr);

            $semanasResult[] = [
                'week_start'      => $wsStr,
                'week_end'        => $weStr,
                'label'           => 'S: '.$sem['start']->locale('es')->isoFormat('D MMM').' – '.$sem['end']->locale('es')->isoFormat('D MMM'),
                'is_past'         => $sem['end']->lt(now()->startOfDay()),
                'is_current'      => now()->between($sem['start'], $sem['end']),
                'ventas'          => round($ventas, 2),
                'bono_calculado'  => $bonoCalc,
                'pierde_bono'     => $pierdeBono,
                'faltas'          => $faltasCount,
                'tardanzas'       => $tardanzasCount,
                'descuento_total' => round($totalDescuento, 2),
                'days'            => $resultDays,
                'delivered'       => $deliv?->delivered ?? false,
                'delivered_at'    => $deliv?->delivered_at?->format('d/m/Y'),
                'bonus_delivered' => floatval($deliv?->bonus_amount ?? 0),
            ];
        }

        return response()->json([
            'local_id'    => $localId,
            'name'        => $emp['name'],
            'photo'       => $emp['image_128'] ?? null,
            'job'         => $emp['job_id'][1] ?? '',
            'calendar'    => $calName,
            'sueldo_neto' => $sueldoNeto,
            'semanas'     => $semanasResult,
        ]);
    }

    // ── GET /api/nomina/mes?year=&month= ─────────────────────────────
    public function nominaMes(Request $request)
    {
        $year  = (int)($request->year  ?? now()->year);
        $month = (int)($request->month ?? now()->month);

        $firstOfMonth = Carbon::create($year, $month, 1);
        $lastOfMonth  = $firstOfMonth->copy()->endOfMonth();
        $payDate      = $firstOfMonth->copy()->addMonth()->format('d/m/Y');

        // Semanas cuyo Domingo cae dentro del mes
        $cursor  = $firstOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $semanas = [];
        $sNum    = 1;
        while ($cursor->lte($lastOfMonth)) {
            $weekStart = $cursor->copy();
            $weekEnd   = $cursor->copy()->endOfWeek(Carbon::SUNDAY);
            if ($weekEnd->month === $month) {
                $semanas[] = [
                    'week_start' => $weekStart->format('Y-m-d'),
                    'week_end'   => $weekEnd->format('Y-m-d'),
                    'label'      => 'S'.$sNum.': '.$weekStart->locale('es')->isoFormat('D MMM').' – '.$weekEnd->locale('es')->isoFormat('D MMM'),
                    'is_past'    => $weekEnd->lt(now()->startOfDay()),
                    'is_current' => now()->between($weekStart, $weekEnd),
                    'is_future'  => $weekStart->gt(now()),
                ];
                $sNum++;
            }
            $cursor->addWeek();
        }

        // Cargar entregas guardadas en BD
        $weekStarts = array_column($semanas, 'week_start');
        $deliveries = MonthlyBonusDelivery::whereIn('week_start', $weekStarts)->get();

        $delivMap = [];
        foreach ($deliveries as $d) {
            $delivMap[$d->week_start->format('Y-m-d')][$d->employee_local_id] = [
                'id'           => $d->id,
                'bonus_amount' => floatval($d->bonus_amount),
                'delivered'    => $d->delivered,
                'delivered_at' => $d->delivered_at?->format('d/m/Y H:i'),
            ];
        }

        // Reporte mensual por empleado
        $empleados = Employee::with('salaryConfig')->get()->filter(fn($e) => $e->salaryConfig !== null);
        $reporte   = [];
        foreach ($empleados as $emp) {
            $sal        = $emp->salaryConfig;
            $sueldoNeto = round(floatval($sal->base_salary) * 0.87 + floatval($sal->extra_bonus), 2);
            $bonosEnt   = 0;
            $bonosPend  = 0;
            $semanasEmp = [];
            foreach ($weekStarts as $ws) {
                $d = $delivMap[$ws][$emp->id] ?? null;
                $semanasEmp[$ws] = $d;
                if ($d) {
                    if ($d['delivered']) $bonosEnt  += $d['bonus_amount'];
                    else                 $bonosPend += $d['bonus_amount'];
                }
            }
            $reporte[] = [
                'local_id'         => $emp->id,
                'name'             => $emp->name,
                'sueldo_neto'      => $sueldoNeto,
                'bonos_entregados' => round($bonosEnt, 2),
                'bonos_pendientes' => round($bonosPend, 2),
                'total_dia1'       => round($sueldoNeto + $bonosPend, 2),
                'semanas'          => $semanasEmp,
            ];
        }

        return response()->json([
            'year'      => $year,
            'month'     => $month,
            'pay_date'  => $payDate,
            'semanas'   => $semanas,
            'reporte'   => $reporte,
            'deliv_map' => $delivMap,
        ]);
    }

    // ── POST /api/nomina/entrega ──────────────────────────────────────
    public function markDelivery(Request $request)
    {
        $request->validate([
            'employee_local_id' => 'required|exists:employees,id',
            'week_start'        => 'required|date',
            'bonus_amount'      => 'required|numeric|min:0',
            'delivered'         => 'required|boolean',
        ]);

        $delivery = MonthlyBonusDelivery::updateOrCreate(
            ['employee_local_id' => $request->employee_local_id, 'week_start' => $request->week_start],
            [
                'bonus_amount' => $request->bonus_amount,
                'delivered'    => $request->delivered,
                'delivered_at' => $request->delivered ? now() : null,
                'delivered_by' => $request->delivered ? (auth()->id() ?? 1) : null,
            ]
        );

        return response()->json(['ok' => true, 'id' => $delivery->id]);
    }

    // ── GET /api/orden-limpieza/config ───────────────────────────────
    public function olConfig()
    {
        return response()->json(OrderCleanlinessConfig::current());
    }

    // ── POST /api/orden-limpieza/config ──────────────────────────────
    public function saveOlConfig(Request $request)
    {
        $request->validate([
            'score_thresholds'   => 'required|array|min:1',
            'score_thresholds.*.from'   => 'required|numeric|min:0',
            'score_thresholds.*.to'     => 'required|numeric|max:2',
            'score_thresholds.*.points' => 'required|integer|min:0',
            'discount_rules'     => 'required|array|min:1',
            'discount_rules.*.points'       => 'required|integer|min:0',
            'discount_rules.*.discount_pct' => 'required|integer|min:0|max:100',
        ]);

        $cfg = OrderCleanlinessConfig::current();
        $cfg->update([
            'score_thresholds' => $request->score_thresholds,
            'discount_rules'   => $request->discount_rules,
        ]);

        return response()->json(['ok' => true, 'config' => $cfg->fresh()]);
    }

    // ── GET /api/orden-limpieza/scores?week_start=X ──────────────────
    // Returns: {localId: {date: score}, ...}
    public function olScores(Request $request)
    {
        $wsStr     = $request->get('week_start', '');
        $weekStart = $wsStr ? Carbon::parse($wsStr) : now()->startOfWeek(Carbon::MONDAY);
        $weekEnd   = $weekStart->copy()->addDays(6);

        $scores = OrderCleanlinessScore::whereBetween('date', [
            $weekStart->format('Y-m-d'),
            $weekEnd->format('Y-m-d'),
        ])->get();

        // Group by employee_local_id → {date: score}
        $result = [];
        foreach ($scores as $s) {
            $lid = $s->employee_local_id;
            $result[$lid][$s->date->format('Y-m-d')] = floatval($s->score);
        }

        return response()->json($result);
    }

    // ── POST /api/orden-limpieza/score ────────────────────────────────
    public function saveOlScore(Request $request)
    {
        $request->validate([
            'employee_local_id' => 'required|exists:employees,id',
            'date'              => 'required|date',
            'score'             => 'required|numeric|min:0|max:2',
        ]);

        $score = OrderCleanlinessScore::updateOrCreate(
            [
                'employee_local_id' => $request->employee_local_id,
                'date'              => $request->date,
            ],
            [
                'score'      => $request->score,
                'created_by' => auth()->id() ?? 1,
            ]
        );

        return response()->json(['ok' => true, 'score' => $score]);
    }
}