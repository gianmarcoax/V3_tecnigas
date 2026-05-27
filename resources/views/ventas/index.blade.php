<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ranking de Ventas</h2>
    </x-slot>

    <div class="py-6 px-4 max-w-7xl mx-auto" id="app">

        {{-- Filtros de período --}}
        <div class="bg-white rounded-2xl shadow p-4 mb-6 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Desde</label>
                <input type="date" id="date_from"
                    class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                <input type="date" id="date_to"
                    class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-2 flex-wrap">
                <button onclick="setPeriod('today')" class="btn-period">Hoy</button>
                <button onclick="setPeriod('week')" class="btn-period">Esta semana</button>
                <button onclick="setPeriod('month')" class="btn-period">Este mes</button>
                <button onclick="setPeriod('year')" class="btn-period">Este año</button>
                <button onclick="loadRanking()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Buscar</button>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" id="kpis">
            <div class="bg-white rounded-2xl shadow p-4">
                <p class="text-xs text-gray-500">Total General</p>
                <p class="text-2xl font-bold text-gray-800 mt-1" id="kpi_total">S/ —</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-4">
                <p class="text-xs text-gray-500">Efectivo</p>
                <p class="text-2xl font-bold text-green-600 mt-1" id="kpi_efectivo">S/ —</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-4">
                <p class="text-xs text-gray-500">Yape / Plin</p>
                <p class="text-2xl font-bold text-purple-600 mt-1" id="kpi_yape">S/ —</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-4">
                <p class="text-xs text-gray-500">Tarjeta</p>
                <p class="text-2xl font-bold text-blue-600 mt-1" id="kpi_tarjeta">S/ —</p>
            </div>
        </div>

        {{-- Loading --}}
        <div id="loading" class="hidden text-center py-16 text-gray-400">
            <svg class="animate-spin h-8 w-8 mx-auto mb-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
            </svg>
            Cargando ranking...
        </div>

        {{-- Tabla ranking --}}
        <div class="bg-white rounded-2xl shadow overflow-hidden" id="tabla-container">
            <table class="w-full text-sm" id="tabla-ranking">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cajero</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th
                            class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">
                            Órdenes</th>
                        <th
                            class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">
                            Promedio</th>
                        <th
                            class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">
                            Efectivo</th>
                        <th
                            class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">
                            Yape</th>
                        <th
                            class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">
                            Tarjeta</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Participación
                        </th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody id="tbody-ranking">
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center text-gray-400">Selecciona un período para cargar
                            el ranking</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Panel lateral de órdenes --}}
        <div id="panel-ordenes"
            class="hidden fixed inset-y-0 right-0 w-full sm:w-96 bg-white shadow-2xl z-50 flex flex-col">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="font-semibold text-gray-800" id="panel-titulo">Órdenes</h3>
                <button onclick="cerrarPanel()" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <div class="overflow-y-auto flex-1 p-4" id="panel-body">
                <p class="text-gray-400 text-center py-8">Cargando...</p>
            </div>
            <div class="p-4 border-t">
                <button onclick="abrirExport()"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-medium transition"
                    id="btn-export-pdf">
                    Exportar PDF
                </button>
            </div>
        </div>

        {{-- Modal PDF --}}
        <div id="modal-pdf" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="font-semibold text-gray-800">Vista previa PDF</h3>
                    <button onclick="cerrarModal()" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <div class="overflow-y-auto flex-1 p-6" id="pdf-content">
                    <p class="text-center text-gray-400 py-8">Cargando datos...</p>
                </div>
                <div class="p-4 border-t flex justify-end gap-3">
                    <button onclick="cerrarModal()"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</button>
                    <button onclick="window.print()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Imprimir
                        / Guardar PDF</button>
                </div>
            </div>
        </div>

    </div>

    <style>
        .btn-period {
            @apply bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition;
        }

        @media print {

            nav,
            header,
            #tabla-container,
            #panel-ordenes,
            .no-print {
                display: none !important;
            }

            #modal-pdf {
                position: static !important;
                background: none !important;
            }

            #pdf-content {
                overflow: visible !important;
            }
        }
    </style>

    <script>
        let currentSeller = null;
        let currentPeriod = { from: '', to: '' };

        const AVATAR_SVG = (sz) => `<div class="${sz} rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0"><svg class="w-3/5 h-3/5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>`;

        function avatarError(img) {
            const cls = img.className;
            const sz = cls.match(/(w-\S+)/)?.[1] ?? 'w-8';
            const h = cls.match(/(h-\S+)/)?.[1] ?? 'h-8';
            img.outerHTML = `<div class="${sz} ${h} rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0"><svg class="w-3/5 h-3/5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>`;
        }

        // ── Utilidades de fecha ──────────────────────────────────────────
        function today() { return new Date().toISOString().split('T')[0]; }
        function fmt(d) { return d.toISOString().split('T')[0]; }

        function setPeriod(type) {
            const now = new Date();
            let from, to = today();
            if (type === 'today') {
                from = today();
            } else if (type === 'week') {
                const day = now.getDay() || 7;
                const mon = new Date(now); mon.setDate(now.getDate() - day + 1);
                from = fmt(mon);
            } else if (type === 'month') {
                from = fmt(new Date(now.getFullYear(), now.getMonth(), 1));
            } else if (type === 'year') {
                from = now.getFullYear() + '-01-01';
            }
            document.getElementById('date_from').value = from;
            document.getElementById('date_to').value = to;
            loadRanking();
        }

        function fmtMoney(n) { return 'S/ ' + Number(n).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

        // ── Cargar ranking ───────────────────────────────────────────────
        async function loadRanking() {
            const from = document.getElementById('date_from').value;
            const to = document.getElementById('date_to').value;
            if (!from || !to) return;
            currentPeriod = { from, to };

            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('tbody-ranking').innerHTML = '';

            try {
                const res = await fetch(`/api/ventas/ranking?date_from=${from}&date_to=${to}`);
                const data = await res.json();
                renderRanking(data);
            } catch (e) {
                document.getElementById('tbody-ranking').innerHTML = `<tr><td colspan="10" class="px-4 py-8 text-center text-red-400">Error al cargar datos</td></tr>`;
            } finally {
                document.getElementById('loading').classList.add('hidden');
            }
        }

        function renderRanking(data) {
            const { ranking, total_global } = data;

            // KPIs
            document.getElementById('kpi_total').textContent = fmtMoney(total_global);
            const ef = ranking.reduce((s, r) => s + r.pay_efectivo, 0);
            const ya = ranking.reduce((s, r) => s + r.pay_yape, 0);
            const ta = ranking.reduce((s, r) => s + r.pay_tarjeta, 0);
            document.getElementById('kpi_efectivo').textContent = fmtMoney(ef);
            document.getElementById('kpi_yape').textContent = fmtMoney(ya);
            document.getElementById('kpi_tarjeta').textContent = fmtMoney(ta);

            if (!ranking.length) {
                document.getElementById('tbody-ranking').innerHTML = `<tr><td colspan="10" class="px-4 py-12 text-center text-gray-400">Sin datos en este período</td></tr>`;
                return;
            }

            const medals = ['🥇', '🥈', '🥉'];
            const rows = ranking.map((r, i) => {
                const photo = (r.photo && r.photo !== false)
                    ? `<img src="data:image/png;base64,${r.photo}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" onerror="avatarError(this)">`
                    : AVATAR_SVG('w-8 h-8');
                return `
                <tr class="border-b hover:bg-gray-50 cursor-pointer transition" onclick="verDetalle(${r.employee_id ?? 'null'}, ${r.user_id ?? 'null'}, '${r.name}')">
                    <td class="px-4 py-3 font-bold text-gray-500">${medals[i] ?? r.rank}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            ${photo}
                            <div>
                                <p class="font-semibold text-gray-800">${r.name}</p>
                                <p class="text-xs text-gray-400">${r.orders} órdenes</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-gray-800">${fmtMoney(r.total)}</td>
                    <td class="px-4 py-3 text-right text-gray-600 hidden md:table-cell">${r.orders}</td>
                    <td class="px-4 py-3 text-right text-gray-600 hidden md:table-cell">${fmtMoney(r.average)}</td>
                    <td class="px-4 py-3 text-right text-green-600 hidden lg:table-cell">${fmtMoney(r.pay_efectivo)}</td>
                    <td class="px-4 py-3 text-right text-purple-600 hidden lg:table-cell">${fmtMoney(r.pay_yape)}</td>
                    <td class="px-4 py-3 text-right text-blue-600 hidden lg:table-cell">${fmtMoney(r.pay_tarjeta)}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width:${r.pct}%"></div>
                            </div>
                            <span class="text-xs text-gray-500 w-10 text-right">${r.pct}%</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-xs text-blue-500 hover:underline">Ver →</span>
                    </td>
                </tr>`;
            }).join('');

            document.getElementById('tbody-ranking').innerHTML = rows;
        }

        // ── Panel de órdenes ─────────────────────────────────────────────
        async function verDetalle(empId, userId, name) {
            currentSeller = { empId, userId, name };
            document.getElementById('panel-titulo').textContent = name;
            document.getElementById('panel-ordenes').classList.remove('hidden');
            document.getElementById('panel-body').innerHTML = '<p class="text-center text-gray-400 py-8">Cargando...</p>';

            const params = new URLSearchParams({
                date_from: currentPeriod.from,
                date_to: currentPeriod.to,
            });
            if (empId) params.set('employee_id', empId);
            else if (userId) params.set('user_id', userId);
            else params.set('user_id', '__none__');

            try {
                const res = await fetch(`/api/ventas/detail?${params}`);
                const data = await res.json();
                renderOrdenes(data.orders);
            } catch (e) {
                document.getElementById('panel-body').innerHTML = '<p class="text-center text-red-400 py-8">Error al cargar</p>';
            }
        }

        function renderOrdenes(orders) {
            if (!orders.length) {
                document.getElementById('panel-body').innerHTML = '<p class="text-center text-gray-400 py-8">Sin órdenes</p>';
                return;
            }
            const payColor = { 'Yape': 'text-purple-600', 'Plin': 'text-purple-600', 'Tarjeta': 'text-blue-600' };
            const html = orders.map(o => {
                const pm = o.payment_method_name ?? '';
                const color = Object.entries(payColor).find(([k]) => pm.toLowerCase().includes(k.toLowerCase()))?.[1] ?? 'text-green-600';
                return `<div class="flex items-center justify-between py-2 border-b last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-800">${o.name}</p>
                        <p class="text-xs ${color}">${pm}</p>
                    </div>
                    <p class="font-bold text-gray-800">${fmtMoney(o.amount_total)}</p>
                </div>`;
            }).join('');
            document.getElementById('panel-body').innerHTML = html;
        }

        function cerrarPanel() {
            document.getElementById('panel-ordenes').classList.add('hidden');
            currentSeller = null;
        }

        // ── Modal PDF ────────────────────────────────────────────────────
        async function abrirExport() {
            if (!currentSeller) return;
            document.getElementById('modal-pdf').classList.remove('hidden');
            document.getElementById('pdf-content').innerHTML = '<p class="text-center text-gray-400 py-8">Cargando datos...</p>';

            const params = new URLSearchParams({
                date_from: currentPeriod.from,
                date_to: currentPeriod.to,
            });
            if (currentSeller.empId) params.set('employee_id', currentSeller.empId);
            else if (currentSeller.userId) params.set('user_id', currentSeller.userId);

            try {
                const res = await fetch(`/api/ventas/export?${params}`);
                const data = await res.json();
                renderPDF(data);
            } catch (e) {
                document.getElementById('pdf-content').innerHTML = '<p class="text-center text-red-400 py-8">Error al cargar datos</p>';
            }
        }

        function renderPDF(data) {
            const { by_payment, totals, seller_name, total_orders } = data;
            const payLabels = { efectivo: '💴 Efectivo', yape: '📱 Yape / Plin', tarjeta: '💳 Tarjeta' };

            let html = `
            <div style="font-family:Inter,sans-serif;max-width:800px;margin:0 auto;">
                <div style="text-align:center;margin-bottom:24px;">
                    <h1 style="font-size:20px;font-weight:700;color:#0f172a;">Reporte de Ventas — Tecnigas</h1>
                    <p style="color:#64748b;font-size:13px;">${seller_name} · ${currentPeriod.from} al ${currentPeriod.to} · ${total_orders} órdenes</p>
                </div>`;

            for (const pm of ['efectivo', 'yape', 'tarjeta']) {
                const sellers = by_payment[pm];
                if (!sellers.length) continue;
                html += `<div style="margin-bottom:24px;">
                    <h2 style="font-size:15px;font-weight:600;color:#1e293b;border-bottom:2px solid #e2e8f0;padding-bottom:6px;margin-bottom:12px;">${payLabels[pm]}</h2>`;
                for (const seller of sellers) {
                    if (sellers.length > 1) html += `<p style="font-size:13px;font-weight:600;color:#475569;margin:8px 0 4px;">👤 ${seller.employee_name}</p>`;
                    html += `<table style="width:100%;border-collapse:collapse;font-size:12px;">
                        <thead><tr style="background:#f8fafc;">
                            <th style="text-align:left;padding:6px 8px;color:#64748b;">Producto</th>
                            <th style="text-align:right;padding:6px 8px;color:#64748b;">Cant.</th>
                            <th style="text-align:right;padding:6px 8px;color:#64748b;">Subtotal</th>
                        </tr></thead><tbody>`;
                    for (const p of seller.products) {
                        html += `<tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:5px 8px;color:#334155;">${p.name}</td>
                            <td style="padding:5px 8px;text-align:right;color:#334155;">${p.qty}</td>
                            <td style="padding:5px 8px;text-align:right;font-weight:500;">S/ ${p.total.toFixed(2)}</td>
                        </tr>`;
                    }
                    if (sellers.length > 1) {
                        html += `<tr><td colspan="2" style="padding:6px 8px;font-weight:600;text-align:right;color:#475569;">Subtotal ${seller.employee_name}</td>
                            <td style="padding:6px 8px;text-align:right;font-weight:700;">S/ ${seller.subtotal.toFixed(2)}</td></tr>`;
                    }
                    html += `</tbody></table>`;
                }
                html += `</div>`;
            }

            // Resumen final
            html += `<table style="width:100%;border-collapse:collapse;margin-top:16px;border-top:2px solid #e2e8f0;">
                <tbody>
                    <tr><td style="padding:8px;color:#475569;">💴 Efectivo</td><td style="text-align:right;padding:8px;font-weight:600;">S/ ${totals.efectivo.toFixed(2)}</td></tr>
                    <tr><td style="padding:8px;color:#475569;">📱 Yape / Plin</td><td style="text-align:right;padding:8px;font-weight:600;">S/ ${totals.yape.toFixed(2)}</td></tr>
                    <tr><td style="padding:8px;color:#475569;">💳 Tarjeta</td><td style="text-align:right;padding:8px;font-weight:600;">S/ ${totals.tarjeta.toFixed(2)}</td></tr>
                    <tr style="background:#0f172a;"><td style="padding:10px;color:#fff;font-weight:700;font-size:14px;">TOTAL GENERAL</td>
                        <td style="text-align:right;padding:10px;color:#fff;font-weight:800;font-size:16px;">S/ ${totals.grand_total.toFixed(2)}</td></tr>
                </tbody>
            </table></div>`;

            document.getElementById('pdf-content').innerHTML = html;
        }

        function cerrarModal() {
            document.getElementById('modal-pdf').classList.add('hidden');
        }

        // Cargar este mes al entrar
        setPeriod('month');
    </script>
</x-app-layout>