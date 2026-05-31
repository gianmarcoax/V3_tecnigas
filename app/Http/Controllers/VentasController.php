<?php

namespace App\Http\Controllers;

use App\Services\OdooService;
use Illuminate\Http\Request;

class VentasController extends Controller
{
    public function __construct(private OdooService $odoo) {}

    // Vista principal
    public function index()
    {
        return view('ventas.index');
    }

    // GET /api/ventas/debug?date_from=&date_to=  ← DIAGNÓSTICO TEMPORAL
    public function debug(Request $request)
    {
        $dateFrom = $request->get('date_from', '2026-05-18');
        $dateTo   = $request->get('date_to',   '2026-05-24');

        $utcFrom = \Carbon\Carbon::parse($dateFrom . ' 00:00:00', 'America/Lima')->setTimezone('UTC')->format('Y-m-d H:i:s');
        $utcTo   = \Carbon\Carbon::parse($dateTo   . ' 23:59:59', 'America/Lima')->setTimezone('UTC')->format('Y-m-d H:i:s');

        $domain = [
            ['state', 'in', ['paid', 'done', 'invoiced']],
            ['date_order', '>=', $utcFrom],
            ['date_order', '<=', $utcTo],
        ];

        $orders = $this->odoo->searchRead('pos.order', $domain,
            ['id', 'name', 'amount_total', 'employee_id', 'user_id'],
            ['limit' => 5000]
        );

        // Agrupar combinaciones únicas de (employee_id, user_id)
        $combos = [];
        foreach ($orders as $o) {
            $eid = $o['employee_id'] ?? false;
            $uid = $o['user_id'] ?? false;
            $eidVal  = ($eid && $eid[0]) ? $eid[0] : null;
            $eidName = ($eid && $eid[0]) ? $eid[1] : 'null';
            $uidVal  = ($uid && $uid[0]) ? $uid[0] : null;
            $uidName = ($uid && $uid[0]) ? $uid[1] : 'null';
            $comboKey = "emp:{$eidVal}|usr:{$uidVal}";
            if (!isset($combos[$comboKey])) {
                $combos[$comboKey] = [
                    'employee_id'   => $eidVal,
                    'employee_name' => $eidName,
                    'user_id'       => $uidVal,
                    'user_name'     => $uidName,
                    'order_count'   => 0,
                    'total'         => 0.0,
                ];
            }
            $combos[$comboKey]['order_count']++;
            $combos[$comboKey]['total'] += floatval($o['amount_total'] ?? 0);
        }

        // Mapa de empleados activos con su user_id
        $empMap = [];
        try {
            $allEmps = $this->odoo->searchRead(
                'hr.employee',
                [['active', 'in', [true, false]]],  // incluir archivados
                ['id', 'name', 'user_id', 'active'],
                ['limit' => 500]
            );
            foreach ($allEmps as $e) {
                $empMap[] = [
                    'id'      => $e['id'],
                    'name'    => $e['name'],
                    'active'  => $e['active'] ?? true,
                    'user_id' => ($e['user_id'] && $e['user_id'][0]) ? $e['user_id'][0] : null,
                    'user_name' => ($e['user_id'] && $e['user_id'][0]) ? $e['user_id'][1] : null,
                ];
            }
        } catch (\Exception $ex) {
            $empMap = ['error' => $ex->getMessage()];
        }

        return response()->json([
            'date_range'         => "$dateFrom → $dateTo",
            'total_orders'       => count($orders),
            'unique_combos'      => array_values($combos),
            'hr_employee_list'   => $empMap,
        ], 200, [], JSON_PRETTY_PRINT);
    }

    // GET /api/ventas/ranking?date_from=&date_to=
    public function ranking(Request $request)
    {
        $dateFrom = $request->get('date_from', '');
        $dateTo   = $request->get('date_to', '');

        $domain = [['state', 'in', ['paid', 'done', 'invoiced']]];
        if ($dateFrom) {
            $utcFrom = \Carbon\Carbon::parse($dateFrom . ' 00:00:00', 'America/Lima')->setTimezone('UTC')->format('Y-m-d H:i:s');
            $domain[] = ['date_order', '>=', $utcFrom];
        }
        if ($dateTo) {
            $utcTo = \Carbon\Carbon::parse($dateTo . ' 23:59:59', 'America/Lima')->setTimezone('UTC')->format('Y-m-d H:i:s');
            $domain[] = ['date_order', '<=', $utcTo];
        }

        $orders = $this->odoo->searchRead('pos.order', $domain,
            ['id', 'name', 'amount_total', 'date_order', 'employee_id', 'user_id'],
            ['order' => 'date_order desc', 'limit' => 5000]
        );

        if (empty($orders)) {
            return response()->json(['ranking' => [], 'total_global' => 0, 'order_count' => 0, 'seller_count' => 0]);
        }

        // Mapa user_id → {eid, name} para órdenes sin employee_id
        $userToEmp = [];
        try {
            $empsWithUser = $this->odoo->searchRead(
                'hr.employee',
                [['user_id', '!=', false]],
                ['id', 'name', 'user_id'],
                ['limit' => 500]
            );
            foreach ($empsWithUser as $e) {
                $uid = $e['user_id'] ?? false;
                if ($uid && $uid[0]) {
                    $userToEmp[$uid[0]] = ['eid' => $e['id'], 'name' => $e['name']];
                }
            }
        } catch (\Exception $e) {}

        // ── Agrupar por cajero ──────────────────────────────────────────────────
        $groups      = [];
        $orderKeyMap = [];

        foreach ($orders as $o) {
            $emp = $o['employee_id'] ?? false;
            $usr = $o['user_id'] ?? false;

            if ($emp && $emp[0]) {
                $key  = 'emp_' . $emp[0];
                $name = $emp[1];
                $eid  = $emp[0];
                $uid  = ($usr && $usr[0]) ? $usr[0] : null;
            } elseif ($usr && $usr[0]) {
                $uid = $usr[0];
                if (isset($userToEmp[$uid])) {
                    $key  = 'emp_' . $userToEmp[$uid]['eid'];
                    $name = $userToEmp[$uid]['name'];
                    $eid  = $userToEmp[$uid]['eid'];
                } else {
                    $key  = 'usr_' . $uid;
                    $name = $usr[1];
                    $eid  = null;
                }
            } else {
                $key  = 'none_0';
                $name = 'Sin asignar';
                $eid  = null;
                $uid  = null;
            }

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'name'         => $name,
                    'user_id'      => $uid ?? null,
                    'employee_id'  => $eid,
                    'total'        => 0.0,
                    'orders'       => 0,
                    'last_sale'    => null,
                    'photo'        => null,
                    'pay_efectivo' => 0.0,
                    'pay_yape'     => 0.0,
                    'pay_tarjeta'  => 0.0,
                    'order_ids'    => [],
                ];
            }
            $groups[$key]['total']      += floatval($o['amount_total'] ?? 0);
            $groups[$key]['orders']     += 1;
            $groups[$key]['order_ids'][] = $o['id'];
            $orderKeyMap[$o['id']] = $key;
            $ds = $o['date_order'] ?? null;
            if ($ds && (!$groups[$key]['last_sale'] || $ds > $groups[$key]['last_sale'])) {
                $groups[$key]['last_sale'] = $ds;
            }
        }

        // ── Medios de pago ──────────────────────────────────────────────────────
        $allOrderIds = array_column($orders, 'id');
        try {
            $payments = $this->odoo->searchRead('pos.payment',
                [['pos_order_id', 'in', $allOrderIds]],
                ['pos_order_id', 'amount', 'payment_method_id'],
                ['limit' => 50000]
            );
            foreach ($payments as $pmt) {
                $oid = $pmt['pos_order_id'];
                $oid = is_array($oid) ? $oid[0] : $oid;
                $key = $orderKeyMap[$oid] ?? null;
                if (!$key) continue;
                $pm = $pmt['payment_method_id'] ?? false;
                $methodName = $pm ? strtolower(is_array($pm) ? $pm[1] : $pm) : '';
                $amt = floatval($pmt['amount'] ?? 0);
                if (str_contains($methodName, 'yape') || str_contains($methodName, 'plin')) {
                    $groups[$key]['pay_yape'] += $amt;
                } elseif (str_contains($methodName, 'tarjeta') || str_contains($methodName, 'visa') || str_contains($methodName, 'master') || str_contains($methodName, 'card')) {
                    $groups[$key]['pay_tarjeta'] += $amt;
                } else {
                    $groups[$key]['pay_efectivo'] += $amt;
                }
            }
        } catch (\Exception $e) {}

        // ── Fotos ───────────────────────────────────────────────────────────────
        $empIds = array_filter(array_column(array_values($groups), 'employee_id'));
        if ($empIds) {
            try {
                $emps   = $this->odoo->searchRead('hr.employee', [['id', 'in', array_values($empIds)]], ['id', 'image_128']);
                $photos = array_column($emps, 'image_128', 'id');
                foreach ($groups as &$g) {
                    if ($g['employee_id']) $g['photo'] = $photos[$g['employee_id']] ?? null;
                }
                unset($g); // ← CRÍTICO: liberar la referencia para evitar
                           //   que el siguiente foreach sobreescriba el último elemento
            } catch (\Exception $e) {}
        }

        // ── Ordenar y devolver ──────────────────────────────────────────────────
        usort($groups, fn($a, $b) => $b['total'] <=> $a['total']);
        $totalGlobal = array_sum(array_column($groups, 'total'));

        $result = [];
        foreach ($groups as $i => $g) {
            $pct = $totalGlobal > 0 ? round($g['total'] / $totalGlobal * 100, 1) : 0;
            $result[] = [
                'rank'         => $i + 1,
                'name'         => $g['name'],
                'user_id'      => $g['user_id'],
                'employee_id'  => $g['employee_id'],
                'total'        => round($g['total'], 2),
                'orders'       => $g['orders'],
                'average'      => $g['orders'] > 0 ? round($g['total'] / $g['orders'], 2) : 0,
                'last_sale'    => $g['last_sale'],
                'pct'          => $pct,
                'photo'        => $g['photo'],
                'pay_efectivo' => round($g['pay_efectivo'], 2),
                'pay_yape'     => round($g['pay_yape'], 2),
                'pay_tarjeta'  => round($g['pay_tarjeta'], 2),
            ];
        }

        return response()->json([
            'ranking'      => $result,
            'total_global' => round($totalGlobal, 2),
            'order_count'  => count($orders),
            'seller_count' => count($result),
        ]);
    }



    // GET /api/ventas/detail?employee_id=&date_from=&date_to=
    public function detail(Request $request)
    {
        $rawEid   = $request->get('employee_id', '');
        $rawUid   = $request->get('user_id', '__none__');
        $dateFrom = $request->get('date_from', '');
        $dateTo   = $request->get('date_to', '');

        $domain = [['state', 'in', ['paid', 'done', 'invoiced']]];
        if ($dateFrom) {
            $utcFrom = \Carbon\Carbon::parse($dateFrom . ' 00:00:00', 'America/Lima')->setTimezone('UTC')->format('Y-m-d H:i:s');
            $domain[] = ['date_order', '>=', $utcFrom];
        }
        if ($dateTo) {
            $utcTo = \Carbon\Carbon::parse($dateTo . ' 23:59:59', 'America/Lima')->setTimezone('UTC')->format('Y-m-d H:i:s');
            $domain[] = ['date_order', '<=', $utcTo];
        }

        if ($rawEid) {
            $domain[] = ['employee_id', '=', (int)$rawEid];
        } elseif ($rawUid === '__none__') {
            $domain[] = ['employee_id', '=', false];
            $domain[] = ['user_id', '=', false];
        } else {
            $uid = (int)$rawUid;
            try {
                $emps = $this->odoo->searchRead('hr.employee', [['user_id', '=', $uid]], ['id'], ['limit' => 1]);
                $domain[] = $emps ? ['employee_id', '=', $emps[0]['id']] : ['user_id', '=', $uid];
            } catch (\Exception $e) {
                $domain[] = ['user_id', '=', $uid];
            }
        }

        $orders = $this->odoo->searchRead('pos.order', $domain,
            ['id', 'name', 'amount_total', 'date_order'],
            ['order' => 'date_order desc', 'limit' => 300]
        );

        if (empty($orders)) return response()->json(['orders' => []]);

        $orderIds = array_column($orders, 'id');
        $paymentMap = [];
        try {
            $payments = $this->odoo->searchRead('pos.payment',
                [['pos_order_id', 'in', $orderIds]],
                ['pos_order_id', 'payment_method_id'],
                ['limit' => 10000]
            );
            foreach ($payments as $pmt) {
                $oid = $pmt['pos_order_id'];
                $oid = is_array($oid) ? $oid[0] : $oid;
                if (!isset($paymentMap[$oid]) && ($pmt['payment_method_id'] ?? false)) {
                    $pm = $pmt['payment_method_id'];
                    $paymentMap[$oid] = is_array($pm) ? $pm[1] : $pm;
                }
            }
        } catch (\Exception $e) {}

        foreach ($orders as &$o) {
            $o['payment_method_name'] = $paymentMap[$o['id']] ?? 'Efectivo';
        }

        return response()->json(['orders' => $orders]);
    }

    // GET /api/ventas/export?employee_id=&date_from=&date_to=
    public function export(Request $request)
    {
        $rawEmpId  = $request->get('employee_id', '');
        $rawUid    = $request->get('user_id', 'all');
        $dateFrom  = $request->get('date_from', '');
        $dateTo    = $request->get('date_to', '');

        $domain = [['state', 'in', ['paid', 'done', 'invoiced']]];
        if ($dateFrom) {
            $utcFrom = \Carbon\Carbon::parse($dateFrom . ' 00:00:00', 'America/Lima')->setTimezone('UTC')->format('Y-m-d H:i:s');
            $domain[] = ['date_order', '>=', $utcFrom];
        }
        if ($dateTo) {
            $utcTo = \Carbon\Carbon::parse($dateTo . ' 23:59:59', 'America/Lima')->setTimezone('UTC')->format('Y-m-d H:i:s');
            $domain[] = ['date_order', '<=', $utcTo];
        }

        $singleSeller = (bool)$rawEmpId || $rawUid !== 'all';
        $sellerName   = 'Todo el equipo';

        if ($rawEmpId) {
            $empId = (int)$rawEmpId;
            $domain[] = ['employee_id', '=', $empId];
            try {
                $emps = $this->odoo->read('hr.employee', [$empId], ['name']);
                $sellerName = $emps[0]['name'] ?? 'Empleado';
            } catch (\Exception $e) {}
        } elseif ($rawUid !== 'all') {
            $uid = (int)$rawUid;
            try {
                $emps = $this->odoo->searchRead('hr.employee', [['user_id', '=', $uid]], ['id', 'name'], ['limit' => 1]);
                if ($emps) {
                    $sellerName = $emps[0]['name'];
                    $domain[] = ['employee_id', '=', $emps[0]['id']];
                } else {
                    $domain[] = ['user_id', '=', $uid];
                }
            } catch (\Exception $e) {
                $domain[] = ['user_id', '=', $uid];
            }
        }

        $orders = $this->odoo->searchRead('pos.order', $domain,
            ['id', 'amount_total', 'employee_id', 'user_id', 'lines'],
            ['limit' => 5000]
        );

        if (empty($orders)) {
            return response()->json([
                'products' => [], 'total_orders' => 0, 'total_amount' => 0,
                'by_payment' => ['efectivo' => [], 'yape' => [], 'tarjeta' => []],
                'totals' => ['efectivo' => 0, 'yape' => 0, 'tarjeta' => 0, 'grand_total' => 0],
                'seller_name' => $sellerName,
            ]);
        }

        $orderIds    = array_column($orders, 'id');
        $totalAmount = array_sum(array_map(fn($o) => floatval($o['amount_total'] ?? 0), $orders));

        // Medios de pago
        $payMethodMap = [];
        try {
            $payments = $this->odoo->searchRead('pos.payment',
                [['pos_order_id', 'in', $orderIds]],
                ['pos_order_id', 'payment_method_id'],
                ['limit' => 50000]
            );
            foreach ($payments as $pmt) {
                $oid = $pmt['pos_order_id'];
                $oid = is_array($oid) ? $oid[0] : $oid;
                if (isset($payMethodMap[$oid])) continue;
                $pm = $pmt['payment_method_id'] ?? false;
                $methodName = $pm ? strtolower(is_array($pm) ? $pm[1] : $pm) : '';
                if (str_contains($methodName, 'yape') || str_contains($methodName, 'plin')) {
                    $payMethodMap[$oid] = 'yape';
                } elseif (str_contains($methodName, 'tarjeta') || str_contains($methodName, 'visa') || str_contains($methodName, 'master') || str_contains($methodName, 'card')) {
                    $payMethodMap[$oid] = 'tarjeta';
                } else {
                    $payMethodMap[$oid] = 'efectivo';
                }
            }
        } catch (\Exception $e) {
            foreach ($orderIds as $oid) $payMethodMap[$oid] = 'efectivo';
        }

        // Empleado por orden
        $orderEmpMap = [];
        foreach ($orders as $o) {
            $emp = $o['employee_id'] ?? false;
            $usr = $o['user_id'] ?? false;
            if ($emp && $emp[0])      $orderEmpMap[$o['id']] = $emp[1];
            elseif ($usr && $usr[0])  $orderEmpMap[$o['id']] = $usr[1];
            else                      $orderEmpMap[$o['id']] = 'Sin asignar';
        }
        if ($singleSeller) {
            foreach ($orderIds as $oid) $orderEmpMap[$oid] = $sellerName;
        }

        // Líneas
        $lines = $this->odoo->searchRead('pos.order.line',
            [['order_id', 'in', $orderIds]],
            ['order_id', 'product_id', 'qty', 'price_subtotal_incl'],
            ['limit' => 50000]
        );

        $byPayment = ['efectivo' => [], 'yape' => [], 'tarjeta' => []];
        $globalAgg = [];

        foreach ($lines as $ln) {
            $pidData = $ln['product_id'] ?? false;
            if (!$pidData) continue;
            [$pid, $pname] = [$pidData[0], $pidData[1]];
            $oid       = $ln['order_id'];
            $oid       = is_array($oid) ? $oid[0] : $oid;
            $payMethod = $payMethodMap[$oid] ?? 'efectivo';
            $empName   = $orderEmpMap[$oid] ?? 'Sin asignar';
            $qty       = floatval($ln['qty'] ?? 0);
            $total     = floatval($ln['price_subtotal_incl'] ?? 0);

            if (!isset($byPayment[$payMethod][$empName])) $byPayment[$payMethod][$empName] = [];
            if (!isset($byPayment[$payMethod][$empName][$pid])) $byPayment[$payMethod][$empName][$pid] = ['name' => $pname, 'qty' => 0.0, 'total' => 0.0];
            $byPayment[$payMethod][$empName][$pid]['qty']   += $qty;
            $byPayment[$payMethod][$empName][$pid]['total'] += $total;

            if (!isset($globalAgg[$pid])) $globalAgg[$pid] = ['name' => $pname, 'qty' => 0.0, 'total' => 0.0];
            $globalAgg[$pid]['qty']   += $qty;
            $globalAgg[$pid]['total'] += $total;
        }

        $buildSellerList = function ($empGroup) {
            $result = [];
            foreach ($empGroup as $empName => $prodDict) {
                $products = array_values(array_map(fn($v) => ['name' => $v['name'], 'qty' => round($v['qty'], 2), 'total' => round($v['total'], 2)], $prodDict));
                usort($products, fn($a, $b) => $b['total'] <=> $a['total']);
                $result[] = ['employee_name' => $empName, 'products' => $products, 'subtotal' => round(array_sum(array_column($products, 'total')), 2)];
            }
            return $result;
        };

        $byPaymentOut = [];
        $totals = [];
        foreach (['efectivo', 'yape', 'tarjeta'] as $pm) {
            $byPaymentOut[$pm] = $buildSellerList($byPayment[$pm]);
            $totals[$pm] = round(array_sum(array_column($byPaymentOut[$pm], 'subtotal')), 2);
        }
        $totals['grand_total'] = round(array_sum([$totals['efectivo'], $totals['yape'], $totals['tarjeta']]), 2);

        $globalProducts = array_values(array_map(fn($v) => ['name' => $v['name'], 'qty' => round($v['qty'], 2), 'total' => round($v['total'], 2)], $globalAgg));
        usort($globalProducts, fn($a, $b) => $b['total'] <=> $a['total']);

        return response()->json([
            'products'     => $globalProducts,
            'total_orders' => count($orders),
            'total_amount' => round($totalAmount, 2),
            'by_payment'   => $byPaymentOut,
            'totals'       => $totals,
            'seller_name'  => $sellerName,
        ]);
    }
}