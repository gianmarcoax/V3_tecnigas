<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Orden y Limpieza — Tecnigas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            color: #1e293b;
        }

        /* ── Header ── */
        .top-bar {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
        }
        .top-bar-left { display: flex; align-items: center; gap: .75rem; }
        .top-bar-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, #ec4899, #db2777);
            display: flex; align-items: center; justify-content: center;
            color: white;
        }
        .top-bar-title { font-weight: 700; font-size: 1.05rem; color: #0f172a; }
        .top-bar-sub   { font-size: .75rem; color: #64748b; }
        .btn-back {
            display: flex; align-items: center; gap: .4rem;
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 8px; padding: .4rem .9rem;
            font-size: .8rem; font-weight: 500; color: #64748b;
            text-decoration: none; transition: all .15s;
        }
        .btn-back:hover { background: #f1f5f9; color: #334155; }

        /* ── Week Nav ── */
        .week-nav {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: .75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .week-label {
            font-weight: 600;
            font-size: .95rem;
            color: #1e293b;
            flex: 1;
            text-align: center;
        }
        .week-badge {
            background: #fdf4ff;
            color: #a21caf;
            border: 1px solid #f5d0fe;
            border-radius: 6px;
            padding: .25rem .75rem;
            font-size: .75rem;
            font-weight: 600;
        }
        .btn-nav {
            width: 36px; height: 36px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all .15s;
            color: #475569;
        }
        .btn-nav:hover { background: #f8fafc; border-color: #cbd5e1; color: #0f172a; }
        .btn-today {
            padding: .3rem .9rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            font-size: .78rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all .15s;
        }
        .btn-today:hover { background: #f1f5f9; }

        /* ── Main ── */
        .main { padding: 1.25rem 1.5rem; }

        /* ── Table container ── */
        .table-card {
            background: white;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .table-scroll { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 780px;
        }
        thead th {
            background: #f8fafc;
            padding: .65rem .75rem;
            font-size: .7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
            white-space: nowrap;
        }
        thead th:first-child { text-align: left; padding-left: 1rem; min-width: 180px; }
        thead th.today-col { background: #fdf4ff; color: #a21caf; }

        tbody tr { transition: background .12s; }
        tbody tr:hover { background: #fafbfc; }
        tbody tr:not(:last-child) td { border-bottom: 1px solid #f1f5f9; }

        td {
            padding: .5rem .75rem;
            vertical-align: middle;
            text-align: center;
        }
        td:first-child {
            text-align: left;
            padding-left: 1rem;
        }

        /* Employee name cell */
        .emp-cell { display: flex; align-items: center; gap: .65rem; }
        .emp-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .75rem; color: white;
            flex-shrink: 0;
        }
        .emp-name {
            font-size: .82rem;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.2;
        }

        /* Score cell */
        .score-cell {
            position: relative;
        }
        .score-input {
            width: 64px;
            height: 34px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            text-align: center;
            font-size: .85rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            background: white;
            outline: none;
            transition: all .15s;
            cursor: text;
        }
        .score-input:focus {
            border-color: #a855f7;
            box-shadow: 0 0 0 3px rgba(168,85,247,.12);
        }
        .score-input.saved {
            animation: flashSave .4s ease;
        }
        .score-input.error-val {
            border-color: #ef4444;
            background: #fef2f2;
        }

        /* Color states based on value */
        .score-input.val-high  { background: #f0fdf4; border-color: #86efac; color: #166534; }
        .score-input.val-mid   { background: #fffbeb; border-color: #fcd34d; color: #92400e; }
        .score-input.val-low   { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }

        /* Rest day cell */
        .rest-day {
            background: #f8fafc;
            border-radius: 8px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: .04em;
        }

        /* Summary cells */
        .prom-cell {
            font-weight: 700;
            font-size: .88rem;
            color: #334155;
        }
        .pts-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px; height: 28px;
            border-radius: 50%;
            font-weight: 800;
            font-size: .82rem;
        }
        .pts-2 { background: #dcfce7; color: #166534; }
        .pts-1 { background: #fef9c3; color: #854d0e; }
        .pts-0 { background: #fee2e2; color: #991b1b; }
        .pts-none { background: #f1f5f9; color: #94a3b8; font-size: .7rem; font-weight: 600; }

        /* Saving indicator */
        .saving-dot {
            position: absolute;
            top: 2px; right: 2px;
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #a855f7;
            display: none;
            animation: pulse 1s infinite;
        }
        .saving-dot.show { display: block; }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .5; transform: scale(.8); }
        }
        @keyframes flashSave {
            0%   { background: #f0fdf4; }
            50%  { background: #bbf7d0; }
            100% { background: #f0fdf4; }
        }

        /* ── Loading ── */
        .loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
            gap: 1rem;
            color: #64748b;
            font-size: .9rem;
        }
        .spinner {
            width: 36px; height: 36px;
            border: 3px solid #e2e8f0;
            border-top-color: #a855f7;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Toast ── */
        .toast {
            position: fixed;
            bottom: 1.5rem;
            left: 50%;
            transform: translateX(-50%) translateY(60px);
            background: #1e293b;
            color: white;
            padding: .7rem 1.4rem;
            border-radius: 10px;
            font-size: .82rem;
            font-weight: 500;
            z-index: 999;
            transition: transform .25s ease;
            pointer-events: none;
            white-space: nowrap;
        }
        .toast.show { transform: translateX(-50%) translateY(0); }
        .toast.error { background: #dc2626; }

        /* ── Summary bar ── */
        .summary-bar {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        .summary-card {
            flex: 1;
            min-width: 140px;
            background: white;
            border-radius: .75rem;
            border: 1px solid #e2e8f0;
            padding: .9rem 1.1rem;
        }
        .summary-card p { font-size: .7rem; color: #64748b; font-weight: 500; margin-bottom: .2rem; text-transform: uppercase; letter-spacing: .04em; }
        .summary-card span { font-size: 1.35rem; font-weight: 800; color: #1e293b; }
        .summary-card small { font-size: .7rem; color: #94a3b8; margin-left: .2rem; }

        /* Today column highlight */
        td.today-col-cell { background: #fdf4ff08; }
    </style>
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <div class="top-bar-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
        </div>
        <div>
            <div class="top-bar-title">Orden y Limpieza</div>
            <div class="top-bar-sub">Calificación semanal del personal</div>
        </div>
    </div>
    <a href="{{ route('dashboard') }}" class="btn-back">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Inicio
    </a>
</div>

<!-- Week Nav -->
<div class="week-nav">
    <button class="btn-nav" onclick="changeWeek(-1)" title="Semana anterior">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    <div class="week-label" id="weekLabel">Cargando...</div>
    <button class="btn-nav" onclick="changeWeek(1)" title="Semana siguiente">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </button>
    <button class="btn-today" onclick="goToday()">Hoy</button>
    <span class="week-badge" id="weekBadge">Sem. actual</span>
</div>

<!-- Main -->
<div class="main">
    <div class="table-card">
        <div class="table-scroll">
            <div id="tableContainer">
                <div class="loading-state">
                    <div class="spinner"></div>
                    <span>Cargando empleados y horarios...</span>
                </div>
            </div>
        </div>
    </div>

    <div class="summary-bar" id="summaryBar" style="display:none;">
        <div class="summary-card">
            <p>Personal calificado</p>
            <span id="sumCalificados">0</span><small id="sumTotal">/ 0</small>
        </div>
        <div class="summary-card">
            <p>Promedio general</p>
            <span id="sumPromedio">—</span>
        </div>
        <div class="summary-card">
            <p>Días sin calificar</p>
            <span id="sumPendientes" style="color:#f59e0b;">0</span>
        </div>
        <div class="summary-card">
            <p>Con 2 pts (sin descuento)</p>
            <span id="sumPts2" style="color:#16a34a;">0</span>
        </div>
        <div class="summary-card">
            <p>Con descuento al bono</p>
            <span id="sumDiscount" style="color:#dc2626;">0</span>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const DAYS_ES  = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
const MONTHS_ES = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

let weekStart   = getThisMonday();
let empleados   = [];
let scores      = {};   // {localId: {date: score}}
let olConfig    = null;
let todayStr    = toDateStr(new Date());
let saving      = {};   // debounce timers

// ── Date helpers ──────────────────────────────────────────
function getThisMonday() {
    const d = new Date();
    const day = d.getDay(); // 0=Sun
    const diff = (day === 0) ? -6 : 1 - day;
    d.setDate(d.getDate() + diff);
    d.setHours(0,0,0,0);
    return d;
}
function addDays(date, n) {
    const d = new Date(date);
    d.setDate(d.getDate() + n);
    return d;
}
function toDateStr(d) {
    return d.toISOString().slice(0, 10);
}
function fmtDate(d) {
    return `${d.getDate()} ${MONTHS_ES[d.getMonth()]}`;
}
function weekEndDate() {
    return addDays(weekStart, 6);
}
function isCurrentWeek() {
    const t = new Date(todayStr);
    const ws = weekStart;
    const we = weekEndDate();
    return t >= ws && t <= we;
}

// ── Week navigation ───────────────────────────────────────
function changeWeek(dir) {
    weekStart = addDays(weekStart, dir * 7);
    loadData();
}
function goToday() {
    weekStart = getThisMonday();
    loadData();
}
function updateWeekLabel() {
    const we = weekEndDate();
    document.getElementById('weekLabel').textContent =
        `${fmtDate(weekStart)} — ${fmtDate(we)} ${we.getFullYear()}`;
    const badge = document.getElementById('weekBadge');
    if (isCurrentWeek()) {
        badge.textContent = 'Semana actual';
        badge.style.background = '#fdf4ff';
        badge.style.color = '#a21caf';
    } else {
        const diff = Math.round((weekStart - getThisMonday()) / (7*86400*1000));
        badge.textContent = diff < 0 ? `Hace ${Math.abs(diff)} sem.` : `En ${diff} sem.`;
        badge.style.background = '#f1f5f9';
        badge.style.color = '#64748b';
    }
}

// ── Load data ─────────────────────────────────────────────
async function loadData() {
    updateWeekLabel();
    document.getElementById('tableContainer').innerHTML =
        `<div class="loading-state"><div class="spinner"></div><span>Cargando...</span></div>`;
    document.getElementById('summaryBar').style.display = 'none';

    const wsStr = toDateStr(weekStart);
    try {
        const [empRes, scoreRes, cfgRes] = await Promise.all([
            fetch('/api/limpieza/empleados').then(r => r.json()),
            fetch(`/api/orden-limpieza/scores?week_start=${wsStr}`).then(r => r.json()),
            fetch('/api/orden-limpieza/config').then(r => r.json()),
        ]);
        empleados = empRes.empleados || [];
        scores    = scoreRes;            // {localId: {date: score}}
        olConfig  = cfgRes;
        renderTable();
    } catch(e) {
        document.getElementById('tableContainer').innerHTML =
            `<div class="loading-state"><span style="color:#ef4444;">Error al cargar datos. Intenta de nuevo.</span></div>`;
    }
}

// ── Render table ──────────────────────────────────────────
function renderTable() {
    if (!empleados.length) {
        document.getElementById('tableContainer').innerHTML =
            `<div class="loading-state"><span>No hay empleados en el área de Ventas.</span></div>`;
        return;
    }

    const wsStr = toDateStr(weekStart);
    const days  = Array.from({length: 7}, (_, i) => addDays(weekStart, i));

    let thead = `<thead><tr>
        <th>Vendedor</th>`;
    days.forEach((d, i) => {
        const isToday = toDateStr(d) === todayStr;
        thead += `<th class="${isToday ? 'today-col' : ''}">${DAYS_ES[i]}<br><small style="font-weight:400;font-size:.65rem;">${d.getDate()}/${d.getMonth()+1}</small></th>`;
    });
    thead += `<th>Promedio</th><th>Pts</th></tr></thead>`;

    let tbody = '<tbody>';
    empleados.forEach(emp => {
        const empScores = scores[emp.local_id] || {};
        const avatarColor = strColor(emp.name);

        tbody += `<tr data-local-id="${emp.local_id}">
            <td>
                <div class="emp-cell">
                    <div class="emp-avatar" style="background:${avatarColor};">${emp.name.charAt(0)}</div>
                    <div class="emp-name">${shortName(emp.name)}</div>
                </div>
            </td>`;

        days.forEach((d, i) => {
            const ds      = toDateStr(d);
            const isToday = ds === todayStr;
            const isRest  = emp.rest_days.includes(i);  // índice 0=Lun..6=Dom
            const isFuture = ds > todayStr;

            if (isRest) {
                tbody += `<td class="${isToday ? 'today-col-cell' : ''}">
                    <div class="rest-day">DSC</div>
                </td>`;
            } else {
                const val = empScores[ds] !== undefined ? empScores[ds] : '';
                const colorClass = val !== '' ? scoreColorClass(parseFloat(val)) : '';
                const inputId = `inp-${emp.local_id}-${ds}`;
                tbody += `<td class="${isToday ? 'today-col-cell' : ''}" style="position:relative;">
                    <div class="saving-dot" id="dot-${emp.local_id}-${ds}"></div>
                    <input
                        type="text"
                        id="${inputId}"
                        class="score-input ${colorClass}"
                        value="${val !== '' ? val : ''}"
                        placeholder="${isFuture ? '' : '0–2'}"
                        ${isFuture ? 'disabled style="opacity:.35;cursor:not-allowed;"' : ''}
                        data-local-id="${emp.local_id}"
                        data-date="${ds}"
                        onkeydown="handleKey(event, this)"
                        oninput="onScoreInput(this)"
                        onblur="onScoreBlur(this)"
                    >
                </td>`;
            }
        });

        // Promedio y puntos
        const { avg, pts } = calcAvgPts(emp, empScores);
        tbody += `<td class="prom-cell">${avg !== null ? avg.toFixed(1) : '<span style="color:#94a3b8;">—</span>'}</td>`;
        tbody += `<td>${ptsBadge(pts)}</td>`;
        tbody += '</tr>';
    });

    tbody += '</tbody>';

    document.getElementById('tableContainer').innerHTML =
        `<table>${thead}${tbody}</table>`;
    updateSummary();
    document.getElementById('summaryBar').style.display = 'flex';
}

// ── Input handling ────────────────────────────────────────
function handleKey(e, inp) {
    if (e.key === 'Enter' || e.key === 'Tab') {
        e.preventDefault();
        inp.blur();
        // Focus next input in same column order
        const allInputs = Array.from(document.querySelectorAll('.score-input:not([disabled])'));
        const idx = allInputs.indexOf(inp);
        if (idx >= 0 && idx < allInputs.length - 1) allInputs[idx+1].focus();
    }
}

function onScoreInput(inp) {
    // Live color update while typing
    const raw = inp.value.replace(',', '.');
    const num = parseFloat(raw);
    inp.classList.remove('val-high','val-mid','val-low','error-val');
    if (!isNaN(num) && num >= 0 && num <= 2) {
        inp.classList.add(scoreColorClass(num));
    }
}

function onScoreBlur(inp) {
    const localId = inp.dataset.localId;
    const date    = inp.dataset.date;
    let raw = inp.value.replace(',', '.').trim();

    if (raw === '' || raw === '-') {
        inp.value = '';
        inp.classList.remove('val-high','val-mid','val-low','error-val');
        return;
    }

    const num = parseFloat(raw);
    if (isNaN(num) || num < 0 || num > 2) {
        inp.classList.add('error-val');
        showToast('Calificación inválida. Debe ser entre 0 y 2.', true);
        inp.focus();
        return;
    }

    // Normalize display value
    inp.value = num % 1 === 0 ? num.toFixed(0) : num.toFixed(1);
    inp.classList.remove('val-high','val-mid','val-low','error-val');
    inp.classList.add(scoreColorClass(num));

    // Debounced save
    clearTimeout(saving[`${localId}-${date}`]);
    saving[`${localId}-${date}`] = setTimeout(() => saveScore(localId, date, num, inp), 300);
}

// ── Save score ────────────────────────────────────────────
async function saveScore(localId, date, score, inp) {
    const dot = document.getElementById(`dot-${localId}-${date}`);
    if (dot) dot.classList.add('show');

    try {
        const res = await fetch('/api/orden-limpieza/score', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ employee_local_id: parseInt(localId), date, score }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Error al guardar');

        // Update local cache
        if (!scores[localId]) scores[localId] = {};
        scores[localId][date] = score;

        inp.classList.add('saved');
        setTimeout(() => inp.classList.remove('saved'), 600);

        // Recalculate row
        updateRow(localId);
        updateSummary();
    } catch(e) {
        showToast('Error al guardar. Intenta de nuevo.', true);
    } finally {
        if (dot) dot.classList.remove('show');
    }
}

// ── Row recalculation ─────────────────────────────────────
function updateRow(localId) {
    const emp = empleados.find(e => e.local_id == localId);
    if (!emp) return;
    const empScores = scores[localId] || {};
    const { avg, pts } = calcAvgPts(emp, empScores);

    const row = document.querySelector(`tr[data-local-id="${localId}"]`);
    if (!row) return;
    const tds = row.querySelectorAll('td');
    const lastTwo = tds.length - 2;
    tds[lastTwo].innerHTML = avg !== null ? avg.toFixed(1) : '<span style="color:#94a3b8;">—</span>';
    tds[lastTwo].className = 'prom-cell';
    tds[lastTwo+1].innerHTML = ptsBadge(pts);
}

// ── Calculations ──────────────────────────────────────────
function calcAvgPts(emp, empScores) {
    const days = Array.from({length:7}, (_,i) => addDays(weekStart, i));
    let sum = 0, count = 0;
    days.forEach((d, i) => {
        if (emp.rest_days.includes(i)) return; // skip rest day
        const ds = toDateStr(d);
        if (ds > todayStr) return; // skip future
        const s = empScores[ds];
        if (s !== undefined && s !== null) {
            sum += parseFloat(s);
            count++;
        }
    });
    if (count === 0) return { avg: null, pts: null };
    const avg = sum / count;
    const pts = resolvePoints(avg);
    return { avg, pts };
}

function resolvePoints(avg) {
    if (!olConfig || !olConfig.score_thresholds) return null;
    const rounded = Math.round(avg * 10) / 10;
    let pts = 0;
    for (const t of olConfig.score_thresholds) {
        if (rounded >= parseFloat(t.from) && rounded <= parseFloat(t.to)) {
            pts = parseInt(t.points);
        }
    }
    return pts;
}

function scoreColorClass(v) {
    if (v >= 1.5) return 'val-high';
    if (v >= 0.5) return 'val-mid';
    return 'val-low';
}

function ptsBadge(pts) {
    if (pts === null) return `<span class="pts-badge pts-none">—</span>`;
    const cls = pts === 2 ? 'pts-2' : pts === 1 ? 'pts-1' : 'pts-0';
    return `<span class="pts-badge ${cls}">${pts}</span>`;
}

// ── Summary bar ───────────────────────────────────────────
function updateSummary() {
    const days = Array.from({length:7}, (_,i) => addDays(weekStart, i));
    let calificados = 0, totalAct = 0, pendientes = 0, sumAll = 0, countAll = 0;
    let pts2 = 0, discount = 0;

    empleados.forEach(emp => {
        const empScores = scores[emp.local_id] || {};
        let hasAny = false;
        days.forEach((d, i) => {
            if (emp.rest_days.includes(i)) return;
            const ds = toDateStr(d);
            if (ds > todayStr) return;
            totalAct++;
            const s = empScores[ds];
            if (s !== undefined && s !== null) {
                hasAny = true;
                sumAll += parseFloat(s);
                countAll++;
            } else {
                pendientes++;
            }
        });
        if (hasAny) calificados++;

        const { pts } = calcAvgPts(emp, empScores);
        if (pts === 2) pts2++;
        if (pts !== null && pts < 2) discount++;
    });

    document.getElementById('sumCalificados').textContent = calificados;
    document.getElementById('sumTotal').textContent = `/ ${empleados.length}`;
    document.getElementById('sumPromedio').textContent = countAll > 0 ? (sumAll/countAll).toFixed(2) : '—';
    document.getElementById('sumPendientes').textContent = pendientes;
    document.getElementById('sumPts2').textContent = pts2;
    document.getElementById('sumDiscount').textContent = discount;
}

// ── Helpers ───────────────────────────────────────────────
function shortName(name) {
    const parts = name.trim().split(' ');
    if (parts.length <= 2) return name;
    return `${parts[0]} ${parts[1]}`;
}
function strColor(str) {
    const colors = ['#6366f1','#8b5cf6','#ec4899','#f97316','#14b8a6','#0ea5e9','#84cc16','#f59e0b'];
    let h = 0;
    for (let c of str) h = (h * 31 + c.charCodeAt(0)) & 0xffffffff;
    return colors[Math.abs(h) % colors.length];
}
function showToast(msg, isError = false) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = `toast${isError ? ' error' : ''} show`;
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('show'), 2500);
}

// ── Init ──────────────────────────────────────────────────
loadData();
</script>
</body>
</html>
