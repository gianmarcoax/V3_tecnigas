<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Asistencias</h2>
    </x-slot>

    <div class="py-6 px-4 max-w-7xl mx-auto">

        {{-- Nav tabs --}}
        <div class="bg-white rounded-2xl shadow p-4 mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex gap-2">
                <button onclick="switchTab(0,this)" id="tab0" class="tab-btn tab-active flex items-center gap-1.5"><svg
                        xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <circle cx="12" cy="12" r="3" fill="currentColor" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 2v2m0 16v2M4.22 4.22l1.42 1.42m12.72 12.72 1.42 1.42M2 12h2m16 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
                    </svg>En Vivo</button>
                <button onclick="switchTab(1,this)" id="tab1"
                    class="tab-btn tab-inactive flex items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2" />
                        <path stroke-linecap="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18" />
                    </svg>Semanal</button>
                <button onclick="switchTab(2,this)" id="tab2"
                    class="tab-btn tab-inactive flex items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle cx="12" cy="12" r="9" stroke-width="2" />
                        <path stroke-linecap="round" stroke-width="2" d="M12 7v5l3 3" />
                    </svg>Horarios</button>
            </div>
            <div class="flex items-center gap-2">
                <span id="lblHora" class="text-xs text-gray-400"></span>
                <button onclick="recargar()"
                    class="text-xs bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg transition">↻
                    Actualizar</button>
            </div>
        </div>

        {{-- TAB 0: En Vivo --}}
        <div id="panel0">
            <div id="kpisVivo" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5"></div>
            <div id="loadVivo" class="text-center py-16 text-gray-400">
                <svg class="animate-spin h-7 w-7 mx-auto mb-2 text-teal-500" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                </svg>
                Cargando asistencias en vivo...
            </div>
            <div id="cardsVivo" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3"></div>
        </div>

        {{-- TAB 1: Semanal --}}
        <div id="panel1" class="hidden">
            <div class="bg-white rounded-2xl shadow p-4 mb-5 flex items-center gap-3 w-fit">
                <button onclick="cambiarSemana(-1)"
                    class="p-2 rounded-lg hover:bg-gray-100 transition text-lg">←</button>
                <span id="lblSemana" class="font-semibold text-gray-800 text-sm min-w-[180px] text-center"></span>
                <button onclick="cambiarSemana(1)"
                    class="p-2 rounded-lg hover:bg-gray-100 transition text-lg">→</button>
            </div>
            <div id="loadSemana" class="hidden text-center py-16 text-gray-400">
                <svg class="animate-spin h-7 w-7 mx-auto mb-2 text-teal-500" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                </svg>
                Cargando semana...
            </div>
            <div id="cardsSemana" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3"></div>
        </div>

        {{-- TAB 2: Horarios --}}
        <div id="panel2" class="hidden">
            <div id="loadHorarios" class="text-center py-16 text-gray-400">
                <svg class="animate-spin h-7 w-7 mx-auto mb-2 text-teal-500" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                </svg>
                Cargando empleados...
            </div>
            <div id="listaEmpleadosHorario" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3"></div>
        </div>

    </div>

    {{-- Modal editar horario de empleado --}}
    <div id="modalHorario" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h3 id="mhTitle" class="font-bold text-gray-800 text-base"></h3>
                    <p id="mhSubtitle" class="text-xs text-gray-400 mt-0.5"></p>
                </div>
                <button onclick="cerrarModalHorario()"
                    class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body: tabla de líneas --}}
            <div class="flex-1 overflow-y-auto px-6 py-4">
                <div id="mhLoading" class="text-center py-10 text-gray-400">
                    <svg class="animate-spin h-6 w-6 mx-auto mb-2 text-teal-500" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                    </svg>
                    Cargando líneas...
                </div>
                <table id="mhTable" class="hidden w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="pb-2 text-left text-xs text-gray-400 font-medium pr-3">Nombre</th>
                            <th class="pb-2 text-left text-xs text-gray-400 font-medium pr-3">Día</th>
                            <th class="pb-2 text-left text-xs text-gray-400 font-medium pr-3">Período</th>
                            <th class="pb-2 text-left text-xs text-gray-400 font-medium pr-3">Inicio</th>
                            <th class="pb-2 text-left text-xs text-gray-400 font-medium pr-3">Fin</th>
                            <th class="pb-2 w-8"></th>
                        </tr>
                    </thead>
                    <tbody id="mhBody"></tbody>
                </table>
                <p id="mhEmpty" class="hidden text-center text-gray-300 text-sm py-6">Sin líneas de horario</p>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                <button onclick="mhAddLine()"
                    class="flex items-center gap-1.5 text-sm text-teal-600 hover:text-teal-700 font-medium transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    Añadir línea
                </button>
                <div class="flex items-center gap-3">
                    <p id="mhMsg" class="text-xs text-emerald-600 hidden">Cambios guardados</p>
                    <p id="mhError" class="text-xs text-red-500 hidden"></p>
                    <button onclick="mhGuardar()"
                        class="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .tab-btn {
            @apply px-4 py-2 rounded-lg text-sm font-medium transition;
        }

        .tab-active {
            @apply bg-teal-600 text-white;
        }

        .tab-inactive {
            @apply bg-gray-100 text-gray-600 hover:bg-gray-200;
        }

        .inp {
            @apply border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400;
        }

        /* Tarjeta empleado — compacta */
        .emp-card {
            @apply bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3 hover:shadow-md transition;
        }

        .emp-avatar {
            @apply w-9 h-9 rounded-full object-cover flex-shrink-0;
        }

        .emp-avatar-init {
            @apply w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0;
        }

        /* Pills de estado */
        .pill {
            @apply text-xs px-2 py-0.5 rounded-full font-medium;
        }

        .pill-verde {
            @apply bg-emerald-100 text-emerald-700;
        }

        .pill-rojo {
            @apply bg-red-100 text-red-600;
        }

        .pill-naranja {
            @apply bg-orange-100 text-orange-600;
        }

        .pill-gris {
            @apply bg-gray-100 text-gray-500;
        }

        .pill-azul {
            @apply bg-blue-100 text-blue-600;
        }

        .pill-amarillo {
            @apply bg-yellow-100 text-yellow-700;
        }

        /* Tabla horarios */
        .sch-table {
            @apply w-full text-sm border-separate border-spacing-0;
        }

        .sch-table th {
            @apply px-3 py-2.5 text-left text-xs font-semibold text-gray-500 bg-gray-50 border-b border-gray-100;
        }

        .sch-table td {
            @apply px-3 py-2 border-b border-gray-50 text-sm text-gray-700;
        }

        .sch-table tr:last-child td {
            @apply border-b-0;
        }

        .sch-table tr:hover td {
            @apply bg-gray-50/60;
        }

        .time-inp {
            @apply border border-gray-200 rounded-md px-2 py-1 text-xs text-center w-16 focus:outline-none focus:ring-1 focus:ring-teal-400;
        }
    </style>

    <script>
        const CSRF = document.querySelector('meta[name=csrf-token]')?.content ?? '';
        const DAYS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        const PERIODS = { morning: 'Mañana', afternoon: 'Tarde' };
        let semanaOffset = 0;

        // ── Tabs ──────────────────────────────────────────────────────────────
        function switchTab(n, btn) {
            [0, 1, 2].forEach(i => {
                document.getElementById('panel' + i).classList.toggle('hidden', i !== n);
                document.getElementById('tab' + i).className = 'tab-btn ' + (i === n ? 'tab-active' : 'tab-inactive');
            });
            if (n === 0) cargarVivo();
            if (n === 1) cargarSemana();
            if (n === 2) cargarHorarios();
        }

        function recargar() {
            const active = [0, 1, 2].find(i => !document.getElementById('panel' + i).classList.contains('hidden'));
            if (active === 0) cargarVivo();
            if (active === 1) cargarSemana();
            if (active === 2) cargarHorarios();
        }

        // ── Helpers ───────────────────────────────────────────────────────────
        function hhmm(dec) {
            const h = Math.floor(dec), m = Math.round((dec - h) * 60);
            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
        }
        function decFromTime(t) {
            const [h, m] = t.split(':').map(Number);
            return h + m / 60;
        }
        function getWeekStart(off) {
            const d = new Date(); const day = d.getDay() || 7;
            const mon = new Date(d); mon.setDate(d.getDate() - day + 1 + off * 7);
            return mon.toISOString().split('T')[0];
        }
        function fmtSemana(ws) {
            const d = new Date(ws + 'T12:00:00'), fin = new Date(d); fin.setDate(d.getDate() + 6);
            const o = { day: 'numeric', month: 'short' };
            return d.toLocaleDateString('es-PE', o) + ' — ' + fin.toLocaleDateString('es-PE', o);
        }
        function avatarError(img) {
            const cls = img.className;
            const sz = cls.match(/(w-\S+)/)?.[1] ?? 'w-9';
            const h = cls.match(/(h-\S+)/)?.[1] ?? 'h-9';
            img.outerHTML = `<div class="${sz} ${h} rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0"><svg class="w-3/5 h-3/5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>`;
        }

        function avatar(emp, size = 'w-9 h-9') {
            if (emp.photo && emp.photo !== false)
                return `<img src="data:image/png;base64,${emp.photo}" class="${size} rounded-full object-cover flex-shrink-0" onerror="avatarError(this)">`;
            return `<div class="${size} rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0"><svg class="w-3/5 h-3/5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>`;
        }

        // ── EN VIVO ───────────────────────────────────────────────────────────
        async function cargarVivo() {
            document.getElementById('loadVivo').classList.remove('hidden');
            document.getElementById('cardsVivo').innerHTML = '';
            document.getElementById('kpisVivo').innerHTML = '';
            try {
                const d = await fetch('/api/asistencias/vivo').then(r => r.json());
                document.getElementById('loadVivo').classList.add('hidden');
                if (d.error) { document.getElementById('cardsVivo').innerHTML = `<p class="text-red-400 col-span-3 text-center py-8">${d.error}</p>`; return; }

                document.getElementById('lblHora').textContent = 'Hora Lima: ' + d.hora;

                const r = d.resumen;
                document.getElementById('kpisVivo').innerHTML = `
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4"><p class="text-xs text-gray-400">Total</p><p class="text-2xl font-bold text-gray-800 mt-0.5">${r.total}</p></div>
                <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-4"><p class="text-xs text-gray-400">Presentes</p><p class="text-2xl font-bold text-emerald-600 mt-0.5">${r.presentes}</p></div>
                <div class="bg-white rounded-xl shadow-sm border border-red-100 p-4"><p class="text-xs text-gray-400">Ausentes</p><p class="text-2xl font-bold text-red-500 mt-0.5">${r.ausentes}</p></div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4"><p class="text-xs text-gray-400">Libres/ND</p><p class="text-2xl font-bold text-gray-400 mt-0.5">${r.libres}</p></div>`;

                document.getElementById('cardsVivo').innerHTML = d.empleados.map(e => cardVivo(e)).join('');
            } catch (ex) {
                document.getElementById('loadVivo').classList.add('hidden');
                document.getElementById('cardsVivo').innerHTML = '<p class="text-red-400 col-span-3 text-center py-8">Error al conectar</p>';
            }
        }

        function cardVivo(e) {
            const pillMap = {
                trabajando: ['pill-verde', 'Trabajando'],
                salio: ['pill-gris', 'Salió'],
                presente: ['pill-verde', 'Presente'],
                tardanza: ['pill-naranja', 'Tardanza'],
                ausente: ['pill-rojo', 'Ausente'],
                pendiente: ['pill-azul', 'Pendiente'],
                dia_libre: ['pill-gris', 'Día libre'],
                libre: ['pill-amarillo', 'Libre'],
            };
            const [pc, pl] = pillMap[e.status] ?? ['pill-gris', e.status];
            const timeInfo = e.check_in
                ? `<span class="text-xs text-gray-400">${e.check_in}${e.check_out ? ' → ' + e.check_out : ' →  —'}</span>`
                : (e.expected_in ? `<span class="text-xs text-gray-300">Esp. ${e.expected_in}</span>` : '');
            const lateTag = e.late_min > 0 ? `<span class="text-[10px] text-orange-500 font-medium">+${e.late_min}min</span>` : '';

            return `<div class="emp-card">
            ${avatar(e)}
            <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-800 text-sm truncate">${e.name}</p>
                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                    ${timeInfo} ${lateTag}
                </div>
            </div>
            <span class="pill ${pc} flex-shrink-0">${pl}</span>
        </div>`;
        }

        // ── SEMANAL ───────────────────────────────────────────────────────────
        function cambiarSemana(dir) {
            semanaOffset += dir;
            document.getElementById('lblSemana').textContent = fmtSemana(getWeekStart(semanaOffset));
            cargarSemana();
        }

        async function cargarSemana() {
            const ws = getWeekStart(semanaOffset);
            document.getElementById('lblSemana').textContent = fmtSemana(ws);
            document.getElementById('loadSemana').classList.remove('hidden');
            document.getElementById('cardsSemana').innerHTML = '';
            try {
                const d = await fetch(`/api/asistencias/semana?week_start=${ws}`).then(r => r.json());
                document.getElementById('loadSemana').classList.add('hidden');
                if (d.error) { document.getElementById('cardsSemana').innerHTML = `<p class="text-red-400 col-span-3 text-center py-8">${d.error}</p>`; return; }
                document.getElementById('cardsSemana').innerHTML = d.empleados.map(e => cardSemana(e)).join('');
            } catch (ex) {
                document.getElementById('loadSemana').classList.add('hidden');
                document.getElementById('cardsSemana').innerHTML = '<p class="text-red-400 col-span-3 text-center py-8">Error al conectar</p>';
            }
        }

        function cardSemana(e) {
            const ICON_OK = `<svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>`;
            const ICON_LATE = `<svg class="w-3.5 h-3.5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>`;
            const ICON_ABS = `<svg class="w-3.5 h-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>`;
            const ICON_OFF = `<svg class="w-3.5 h-3.5 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M18 12H6"/></svg>`;
            const ICON_PEND = `<svg class="w-3.5 h-3.5 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>`;
            const dayIconMap = { puntual: ICON_OK, tardanza: ICON_LATE, falta: ICON_ABS, dia_libre: ICON_OFF, pendiente: ICON_PEND, presente: ICON_OK, libre: ICON_OFF };

            const dots = e.days.map(d => `<span title="${d.label}: ${d.status}" class="flex items-center justify-center">${dayIconMap[d.status] ?? ICON_PEND}</span>`).join('');

            const ICON_X = `<svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>`;
            const ICON_W = `<svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01"/></svg>`;
            const ICON_CK = `<svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>`;
            const fTag = e.faltas > 0 ? `<span class="pill pill-rojo flex items-center gap-0.5">${ICON_X}${e.faltas} falta${e.faltas > 1 ? 's' : ''}</span>` : '';
            const tTag = e.tardanzas > 0 ? `<span class="pill pill-naranja flex items-center gap-0.5">${ICON_W}${e.tardanzas} tardanza${e.tardanzas > 1 ? 's' : ''}</span>` : '';
            const okTag = (!fTag && !tTag) ? `<span class="pill pill-verde flex items-center gap-0.5">${ICON_CK}Sin incidencias</span>` : '';

            return `<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-3">
                ${avatar(e)}
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-800 text-sm truncate">${e.name}</p>
                    <p class="text-xs text-gray-400 truncate">${e.calendar || e.department}</p>
                </div>
                <div class="flex gap-1 flex-wrap justify-end">${fTag}${tTag}${okTag}</div>
            </div>
            <div class="flex justify-between bg-gray-50 rounded-lg px-3 py-2">
                ${dots}
            </div>
            <div class="flex justify-between mt-2">
                ${e.days.map(d => `<span class="text-[10px] text-gray-400 w-6 text-center">${d.label.slice(0, 3)}</span>`).join('')}
            </div>
        </div>`;
        }

        // ── HORARIOS — nueva UX ───────────────────────────────────────────────
        let mhCalendarId = null;
        let mhCalendarName = '';
        let mhEmpName = '';

        async function cargarHorarios() {
            document.getElementById('loadHorarios').classList.remove('hidden');
            document.getElementById('listaEmpleadosHorario').innerHTML = '';
            try {
                const d = await fetch('/api/asistencias/empleados-calendario').then(r => r.json());
                document.getElementById('loadHorarios').classList.add('hidden');
                if (d.error) {
                    document.getElementById('listaEmpleadosHorario').innerHTML = `<p class="text-red-400 col-span-3 text-center py-8">${d.error}</p>`;
                    return;
                }
                document.getElementById('listaEmpleadosHorario').innerHTML = d.empleados.map(emp => empHorarioCard(emp)).join('');
            } catch (ex) {
                document.getElementById('loadHorarios').classList.add('hidden');
                document.getElementById('listaEmpleadosHorario').innerHTML = '<p class="text-red-400 col-span-3 text-center py-8">Error al conectar</p>';
            }
        }

        function empHorarioCard(emp) {
            const av = avatar(emp);
            const hasCalendar = !!emp.calendar_id;
            const calPill = hasCalendar
                ? `<span class="text-xs text-teal-600 font-medium truncate">${emp.calendar}</span>`
                : `<span class="text-xs text-gray-300 italic">Sin horario</span>`;
            const calEsc = (emp.calendar || '').replace(/'/g, "\\'");
            const nameEsc = emp.name.replace(/'/g, "\\'");
            const btn = hasCalendar ? `onclick="abrirModalHorario(${emp.calendar_id},'${calEsc}','${nameEsc}')"` : '';
            return `<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3 hover:shadow-md transition ${hasCalendar ? 'cursor-pointer hover:border-teal-200' : 'opacity-60'}" ${btn}>
            ${av}
            <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-800 text-sm truncate">${emp.name}</p>
                <p class="text-xs text-gray-400 truncate">${emp.job_title}</p>
                <div class="mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 7v5l3 3"/></svg>
                    ${calPill}
                </div>
            </div>
            ${hasCalendar ? `<svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>` : ''}
        </div>`;
        }

        async function abrirModalHorario(calId, calName, empName) {
            mhCalendarId = calId;
            mhCalendarName = calName;
            mhEmpName = empName;
            document.getElementById('mhTitle').textContent = empName;
            document.getElementById('mhSubtitle').textContent = calName;
            document.getElementById('mhLoading').classList.remove('hidden');
            document.getElementById('mhTable').classList.add('hidden');
            document.getElementById('mhEmpty').classList.add('hidden');
            document.getElementById('mhMsg').classList.add('hidden');
            document.getElementById('mhError').classList.add('hidden');
            document.getElementById('modalHorario').classList.remove('hidden');
            try {
                const d = await fetch(`/api/asistencias/calendarios/${calId}/lineas`).then(r => r.json());
                document.getElementById('mhLoading').classList.add('hidden');
                if (d.error) { showMhError(d.error); return; }
                renderMhLines(d.lines);
            } catch (ex) {
                document.getElementById('mhLoading').classList.add('hidden');
                showMhError('Error al cargar líneas');
            }
        }

        const MH_INP = 'border border-gray-200 rounded-md px-2 py-1 text-xs w-full focus:outline-none focus:ring-1 focus:ring-teal-400';
        const MH_SEL = 'border border-gray-200 rounded-md px-1 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-teal-400';
        const MH_TIME = 'border border-gray-200 rounded-md px-1 py-1 text-xs text-center w-20 focus:outline-none focus:ring-1 focus:ring-teal-400';

        function mhRow(id, name, dayofweek, day_period, hour_from, hour_to, isNew) {
            return `<tr data-id="${id}" class="border-b border-gray-50 hover:bg-gray-50/50 ${isNew ? 'bg-teal-50/30' : ''}">
            <td class="py-2 pr-2"><input class="${MH_INP}" data-f="name" value="${name.replace(/"/g, '&quot;')}"></td>
            <td class="py-2 pr-2"><select class="${MH_SEL}" data-f="dayofweek">
                ${DAYS.map((d, i) => `<option value="${i}" ${i === +dayofweek ? 'selected' : ''}>${d}</option>`).join('')}
            </select></td>
            <td class="py-2 pr-2"><select class="${MH_SEL}" data-f="day_period">
                <option value="morning" ${day_period === 'morning' ? 'selected' : ''}>Mañana</option>
                <option value="afternoon" ${day_period === 'afternoon' ? 'selected' : ''}>Tarde</option>
            </select></td>
            <td class="py-2 pr-2"><input type="time" class="${MH_TIME}" data-f="hour_from" value="${hhmm(+hour_from)}"></td>
            <td class="py-2 pr-2"><input type="time" class="${MH_TIME}" data-f="hour_to" value="${hhmm(+hour_to)}"></td>
            <td class="py-2 text-right">
                <button onclick="mhDeleteLine(${isNew ? 'null' : id}, this)" class="text-red-400 hover:text-red-600 p-1 rounded hover:bg-red-50 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </td>
        </tr>`;
        }

        function renderMhLines(lines) {
            if (!lines.length) { document.getElementById('mhEmpty').classList.remove('hidden'); return; }
            document.getElementById('mhTable').classList.remove('hidden');
            document.getElementById('mhBody').innerHTML = lines.map(ln =>
                mhRow(ln.id, ln.name, ln.dayofweek, ln.day_period, ln.hour_from, ln.hour_to, false)
            ).join('');
        }

        function mhAddLine() {
            document.getElementById('mhEmpty').classList.add('hidden');
            document.getElementById('mhTable').classList.remove('hidden');
            const tr = document.createElement('tr');
            tr.outerHTML; // placeholder
            const tbody = document.getElementById('mhBody');
            tbody.insertAdjacentHTML('beforeend',
                mhRow('new', '', 0, 'afternoon', 13, 21, true)
            );
            tbody.lastElementChild.querySelector('[data-f="name"]').focus();
        }

        async function mhDeleteLine(lineId, btn) {
            const tr = btn.closest('tr');
            if (lineId === null) { tr.remove(); return; }
            try {
                const r = await fetch(`/api/asistencias/horarios/lineas/${lineId}`, {
                    method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF }
                }).then(r => r.json());
                if (r.ok) tr.remove();
                else showMhError(r.error ?? 'Error al eliminar');
            } catch (e) { showMhError('Error de conexión'); }
        }

        async function mhGuardar() {
            document.getElementById('mhMsg').classList.add('hidden');
            document.getElementById('mhError').classList.add('hidden');
            const rows = [...document.querySelectorAll('#mhBody tr')];
            const get = (row, f) => row.querySelector(`[data-f="${f}"]`)?.value ?? '';
            const promises = [];

            for (const row of rows) {
                const id = row.dataset.id;
                const payload = {
                    name: get(row, 'name'),
                    dayofweek: get(row, 'dayofweek'),
                    day_period: get(row, 'day_period'),
                    hour_from: decFromTime(get(row, 'hour_from')),
                    hour_to: decFromTime(get(row, 'hour_to')),
                };
                if (!payload.name) { showMhError('Todas las líneas deben tener nombre'); return; }
                if (id === 'new') {
                    promises.push(fetch(`/api/asistencias/horarios/${mhCalendarId}/lineas`, {
                        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify(payload)
                    }).then(r => r.json()));
                } else {
                    promises.push(fetch(`/api/asistencias/horarios/${id}`, {
                        method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify(payload)
                    }).then(r => r.json()));
                }
            }

            try {
                const results = await Promise.all(promises);
                const failed = results.filter(r => !r.ok);
                if (failed.length) { showMhError('Error: ' + (failed[0].error ?? 'Verifica los datos')); return; }
                const fresh = await fetch(`/api/asistencias/calendarios/${mhCalendarId}/lineas`).then(r => r.json());
                renderMhLines(fresh.lines ?? []);
                const msg = document.getElementById('mhMsg');
                msg.classList.remove('hidden');
                setTimeout(() => msg.classList.add('hidden'), 2500);
            } catch (e) { showMhError('Error de conexión'); }
        }

        function cerrarModalHorario() {
            document.getElementById('modalHorario').classList.add('hidden');
            mhCalendarId = null;
        }
        function showMhError(msg) {
            const el = document.getElementById('mhError');
            el.textContent = msg; el.classList.remove('hidden');
        }

        // ── Init ──────────────────────────────────────────────────────────────
        document.getElementById('lblSemana').textContent = fmtSemana(getWeekStart(0));
        cargarVivo();
    </script>

</x-app-layout>