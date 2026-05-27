<script>
// ── Estado ──────────────────────────────────────────────────────
let semanaOffset = 0;
let allTiers     = [];
let delTierIds   = [];

const CSRF = () => document.querySelector('meta[name=csrf-token]')?.content ?? '';
const fmt  = n   => 'S/ ' + Number(n).toLocaleString('es-PE',{minimumFractionDigits:2,maximumFractionDigits:2});

// ── Semana ──────────────────────────────────────────────────────
function getWeekStart(offset) {
    const now = new Date(), day = now.getDay()||7;
    const mon = new Date(now);
    mon.setDate(now.getDate() - day + 1 + offset*7);
    return mon.toISOString().split('T')[0];
}
function fmtSemana(ws) {
    const d = new Date(ws+'T12:00:00');
    const f = new Date(d); f.setDate(d.getDate()+6);
    const o = {day:'numeric',month:'short'};
    return d.toLocaleDateString('es-PE',o)+' — '+f.toLocaleDateString('es-PE',o);
}
function cambiarSemana(dir) {
    semanaOffset += dir;
    document.getElementById('lblSemana').textContent = fmtSemana(getWeekStart(semanaOffset));
    cargarResumen();
}
function switchTab(n) {
    document.getElementById('panel0').classList.toggle('hidden', n!==0);
    document.getElementById('panel1').classList.toggle('hidden', n!==1);
    document.getElementById('tab0').className = 'tab-btn '+(n===0?'tab-active':'tab-inactive');
    document.getElementById('tab1').className = 'tab-btn '+(n===1?'tab-active':'tab-inactive');
    if (n===1) cargarConfig();
}

// ── Avatar ──────────────────────────────────────────────────────
function avatarError(img) {
    const sz = img.className.match(/(w-\S+)/)?.[1]??'w-10';
    const h  = img.className.match(/(h-\S+)/)?.[1]??'h-10';
    img.outerHTML = `<div class="${sz} ${h} rounded-full bg-gray-100 flex items-center justify-center"><svg class="w-3/5 h-3/5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>`;
}
const AVT = (cls, p) => (p && p!==false)
    ? `<img src="data:image/png;base64,${p}" class="${cls} rounded-full object-cover flex-shrink-0" onerror="avatarError(this)">`
    : `<div class="${cls} rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0"><svg class="w-3/5 h-3/5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>`;

// ── RESUMEN ──────────────────────────────────────────────────────
async function cargarResumen() {
    const ws = getWeekStart(semanaOffset);
    document.getElementById('loadingResumen').classList.remove('hidden');
    document.getElementById('empCards').innerHTML = '';
    document.getElementById('turnosRow').innerHTML = '';
    try {
        const d = await fetch(`/api/remuneracion/semana?week_start=${ws}`).then(r=>r.json());
        if (d.error) { document.getElementById('empCards').innerHTML=`<p class="col-span-3 text-center py-10 text-red-400">${d.error}</p>`; return; }

        document.getElementById('kNomina').textContent    = fmt(d.resumen.total_nomina);
        document.getElementById('kBonos').textContent     = fmt(d.resumen.total_bonos);
        document.getElementById('kEmpleados').textContent = d.resumen.total_empleados;
        document.getElementById('kPerdidos').textContent  = d.resumen.bonos_perdidos;

        document.getElementById('turnosRow').innerHTML =
            renderTurnoCard(d.turno_manana,'☀️ Turno Mañana','#7c3aed') +
            renderTurnoCard(d.turno_tarde, '🌙 Turno Tarde', '#4f46e5');

        document.getElementById('empCards').innerHTML =
            d.empleados.length ? d.empleados.map(e=>renderEmpCard(e,ws)).join('') :
            '<p class="col-span-3 text-center py-10 text-gray-400">Sin colaboradores remunerables esta semana</p>';
    } catch(e) {
        document.getElementById('empCards').innerHTML='<p class="col-span-3 text-center py-10 text-red-400">Error al cargar datos</p>';
    } finally {
        document.getElementById('loadingResumen').classList.add('hidden');
    }
}

function renderTurnoCard(t, label, color) {
    const ventas  = t.ventas_total ?? 0;
    const meta    = t.tier_meta ?? 0;
    const pct     = meta>0 ? Math.min(Math.round(ventas/meta*100),100) : 0;
    const tiers   = t.tiers ?? [];

    const tiersHtml = tiers.length
        ? tiers.map(tr => {
            const ok = ventas >= tr.sales_goal;
            return `<div class="flex justify-between text-xs py-1 border-b last:border-0">
                <span class="${ok?'font-semibold text-gray-700':'text-gray-400'}">${tr.label} — meta ${fmt(tr.sales_goal)}</span>
                <span class="${ok?'text-green-600 font-bold':'text-gray-300'}">+${fmt(tr.bonus_amount)}${ok?' ✓':''}</span>
            </div>`;
        }).join('')
        : '<p class="text-xs text-gray-400">Sin niveles configurados</p>';

    return `<div class="rem-card p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="font-bold text-gray-800">${label}</span>
            <div class="flex items-center gap-2">
                ${t.tier ? `<span class="badge bg-green-100 text-green-700">${t.tier}</span>` : ''}
                <span class="text-xs text-gray-400">${t.count??0} colaborador(es)</span>
            </div>
        </div>
        <div class="flex justify-between text-sm mb-2">
            <span class="text-gray-500">Ventas: <b class="text-gray-800">${fmt(ventas)}</b></span>
            ${meta>0 ? `<span class="text-gray-400 text-xs">Meta: ${fmt(meta)}</span>` : ''}
        </div>
        ${meta>0 ? `<div class="w-full bg-gray-100 rounded-full h-2 mb-3 overflow-hidden"><div class="h-2 rounded-full transition-all" style="width:${pct}%;background:${color}"></div></div>` : ''}
        <div class="border-t pt-2">${tiersHtml}</div>
        ${t.bono_grupal>0 ? `<p class="text-xs font-bold mt-2" style="color:${color}">Bono grupal activo: +${fmt(t.bono_grupal)}</p>` : ''}
    </div>`;
}

function renderEmpCard(e, ws) {
    const turno = e.turno==='manana'?'☀️ Mañana':e.turno==='tarde'?'🌙 Tarde':'—';

    let badges = '';
    if (e.pierde_bono) {
        badges = `<span class="badge bg-red-100 text-red-600">✗ Sin bono${e.razon_perdida?' · '+e.razon_perdida:''}</span>`;
    } else {
        if ((e.bono_grupal||0)>0)    badges += `<span class="badge bg-green-100 text-green-600 mr-1">Bono grupal (${e.tier_grupal||''}) +${fmt(e.bono_grupal)}</span>`;
        if ((e.bono_individual||0)>0) badges += `<span class="badge bg-violet-100 text-violet-600">Bono indiv. (${e.tier_individual||''}) +${fmt(e.bono_individual)}</span>`;
        if (!badges) badges = `<span class="badge bg-yellow-100 text-yellow-600">Sin bono aún</span>`;
    }

    const fBtn = `<button onclick="abrirDetalle(${e.id},'${ws}')" class="badge ${e.faltas>0?'bg-red-100 text-red-600 cursor-pointer':'bg-gray-100 text-gray-500'}">Faltas ${e.faltas}</button>`;
    const tBtn = `<button onclick="abrirDetalle(${e.id},'${ws}')" class="badge ${e.tardanzas>=3?'bg-red-100 text-red-600':e.tardanzas>0?'bg-orange-100 text-orange-600':'bg-gray-100 text-gray-500'} cursor-pointer">Tardanzas ${e.tardanzas}</button>`;

    const extraRow = e.extra_bonus>0
        ? `<div class="flex justify-between text-sm"><span class="text-gray-500">${e.extra_bonus_reason||'Bono cargo'}</span><span class="font-semibold text-blue-500">+${fmt(e.extra_bonus)}</span></div>`:'';
    const descRow  = e.descuento_total>0
        ? `<div class="flex justify-between text-sm"><span class="text-gray-500">Descuento faltas</span><span class="font-semibold text-red-500">−${fmt(e.descuento_total)}</span></div>`:'';
    const bonoGRow = (e.bono_grupal||0)>0
        ? `<div class="flex justify-between text-sm"><span class="text-gray-500">Bono grupal (${e.tier_grupal})</span><span class="font-semibold text-green-600">+${fmt(e.bono_grupal)}</span></div>`:'';
    const bonoIRow = (e.bono_individual||0)>0
        ? `<div class="flex justify-between text-sm"><span class="text-gray-500">Bono individual (${e.tier_individual})</span><span class="font-semibold text-violet-600">+${fmt(e.bono_individual)}</span></div>`:'';

    return `<div class="rem-card p-5 flex flex-col gap-3">
        <div class="flex items-center gap-3">
            ${AVT('w-11 h-11', e.photo)}
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900 truncate text-sm">${e.name}</p>
                <p class="text-xs text-gray-400">${e.job||e.department||''} · ${turno}</p>
            </div>
            <div class="flex flex-col gap-1 items-end text-xs">${fBtn}${tBtn}</div>
        </div>
        <div class="flex flex-wrap gap-1">${badges}</div>
        <div class="border-t pt-3 space-y-1.5">
            <div class="flex justify-between text-sm"><span class="text-gray-500">Salario base</span><span class="font-semibold">${fmt(e.salario_base)}</span></div>
            ${extraRow}${descRow}${bonoGRow}${bonoIRow}
            <div class="flex justify-between text-sm font-extrabold border-t pt-2 mt-1">
                <span>Total estimado</span><span class="text-violet-700">${fmt(e.total_estimado)}</span>
            </div>
        </div>
        <button onclick="abrirDetalle(${e.id},'${ws}')" class="w-full text-xs text-violet-600 hover:text-violet-700 font-medium border border-violet-100 hover:border-violet-300 rounded-lg py-1.5 transition">
            Ver detalle semana →
        </button>
    </div>`;
}

// ── MODAL DETALLE ────────────────────────────────────────────────
async function abrirDetalle(empId, ws) {
    document.getElementById('modalOverlay').classList.remove('hidden');
    document.getElementById('mBody').innerHTML = '<div class="flex justify-center py-12"><svg class="animate-spin h-8 w-8 text-violet-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg></div>';
    const d = await fetch(`/api/remuneracion/detalle?emp_id=${empId}&week_start=${ws}`).then(r=>r.json());
    if (d.error) { document.getElementById('mBody').innerHTML=`<p class="text-red-500 p-6">${d.error}</p>`; return; }
    document.getElementById('mTitle').textContent = d.name;
    document.getElementById('mSub').textContent   = `${d.calendar||''} · Semana ${ws} · Descuento: ${fmt(d.descuento_total)}`;

    const PC = {puntual:'bg-green-100 text-green-700',tardanza:'bg-orange-100 text-orange-700',falta:'bg-red-100 text-red-700',dia_libre:'bg-gray-100 text-gray-500',pendiente:'bg-blue-100 text-blue-600'};
    const PL = {puntual:'Puntual ✓',tardanza:'Tardanza',falta:'Falta',dia_libre:'Libre',pendiente:'Pendiente'};

    const rows = d.days.map(day => {
        const pc = PC[day.status]??'bg-gray-100 text-gray-500';
        const pl = PL[day.status]??day.status;
        let act = '';
        if (day.status==='falta' && !day.is_day_off) {
            act = day.justif_falta
                ? `<button onclick="toggleJustif(${empId},'${day.date}','falta',false,'${ws}')" class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded-lg transition">Quitar</button>`
                : `<button onclick="toggleJustif(${empId},'${day.date}','falta',true,'${ws}')"  class="text-xs bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded-lg transition">Justificar</button>`;
        }
        if (day.status==='tardanza') {
            act = day.justif_tardanza
                ? `<button onclick="toggleJustif(${empId},'${day.date}','tardanza',false,'${ws}')" class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded-lg transition">Quitar</button>`
                : `<button onclick="toggleJustif(${empId},'${day.date}','tardanza',true,'${ws}')"  class="text-xs bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded-lg transition">Justificar</button>`;
        }
        return `<tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3 text-sm font-medium text-gray-700">${day.label}</td>
            <td class="px-4 py-3 text-sm text-gray-600">${day.check_in??'—'}</td>
            <td class="px-4 py-3 text-sm text-gray-400">${day.expected_in?day.expected_in+' – '+day.expected_out:'—'}</td>
            <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded-full font-medium ${pc}">${pl}</span>${day.descuento>0?` <span class="text-xs text-red-400 ml-1">−${fmt(day.descuento)}</span>`:''}</td>
            <td class="px-4 py-3">${act}</td>
        </tr>`;
    }).join('');

    document.getElementById('mBody').innerHTML = `<table class="w-full text-sm">
        <thead class="bg-gray-50 border-b sticky top-0"><tr>
            <th class="px-4 py-2 text-left text-xs text-gray-500">Día</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500">Entrada</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500">Horario</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500">Estado</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500">Acción</th>
        </tr></thead>
        <tbody>${rows}</tbody>
    </table>`;
}

async function toggleJustif(empId, date, tipo, justified, ws) {
    await fetch('/api/remuneracion/justificacion', {
        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF()},
        body: JSON.stringify({employee_id:empId, date, type:tipo, justified, reason:null})
    });
    abrirDetalle(empId, ws);
    cargarResumen();
}
function cerrarModal() { document.getElementById('modalOverlay').classList.add('hidden'); }

// ── CONFIG ───────────────────────────────────────────────────────
async function cargarConfig() {
    const [empRes, cfgRes] = await Promise.all([
        fetch('/api/remuneracion/empleados').then(r=>r.json()),
        fetch('/api/remuneracion/config').then(r=>r.json()),
    ]);
    allTiers   = cfgRes.goal_tiers ?? [];
    delTierIds = [];
    const tard = cfgRes.tardiness;
    if (tard) document.getElementById('cfgTardMin').value = tard.threshold_minutes ?? 10;

    // Rellenar bloques de tiers
    const slots = [
        {id:'grp-manana-weekly',  o:'group',      a:'ventas',            p:'weekly',  s:'manana'},
        {id:'grp-manana-monthly', o:'group',      a:'ventas',            p:'monthly', s:'manana'},
        {id:'grp-tarde-weekly',   o:'group',      a:'ventas',            p:'weekly',  s:'tarde'},
        {id:'grp-tarde-monthly',  o:'group',      a:'ventas',            p:'monthly', s:'tarde'},
        {id:'ind-ventas-weekly',  o:'individual', a:'ventas',            p:'weekly',  s:null},
        {id:'ind-ventas-monthly', o:'individual', a:'ventas',            p:'monthly', s:null},
        {id:'ind-servicio-weekly',o:'individual', a:'servicio_tecnico',  p:'weekly',  s:null},
        {id:'ind-servicio-monthly',o:'individual',a:'servicio_tecnico',  p:'monthly', s:null},
    ];
    slots.forEach(sl => {
        const t = allTiers.filter(t => t.owner_type===sl.o && t.area===sl.a && t.period_type===sl.p && (sl.s===null ? !t.shift : t.shift===sl.s));
        document.getElementById(sl.id).innerHTML = t.length ? t.map(tierRow).join('') : '';
    });

    renderEmpleadosConfig(empRes);
}

function tierRow(t) {
    return `<div class="tier-row" data-tid="${t.id??''}">
        <input type="text" value="${t.label??''}" placeholder="Nivel (A/B/C)" class="inp flex-1 text-xs py-1" data-f="label">
        <input type="number" value="${t.sales_goal??0}" placeholder="Meta S/" min="0" step="100" class="inp w-28 text-right text-xs py-1" data-f="goal">
        <input type="number" value="${t.bonus_amount??0}" placeholder="Bono S/" min="0" step="10" class="inp w-24 text-right text-xs py-1" data-f="bonus">
        <button onclick="removeTierRow(this,${t.id??'null'})" class="text-gray-300 hover:text-red-400 transition text-xl leading-none px-1">×</button>
    </div>`;
}

function addTier(containerId) {
    const el = document.getElementById(containerId);
    const div = document.createElement('div');
    div.innerHTML = tierRow({id:null, label:'', sales_goal:0, bonus_amount:0});
    el.appendChild(div.firstElementChild);
}

function removeTierRow(btn, tid) {
    if (tid) delTierIds.push(tid);
    btn.closest('.tier-row').remove();
}

function collectBlock(containerId, owner, area, period, shift) {
    return [...document.querySelectorAll(`#${containerId} .tier-row`)].map(row => ({
        id:           row.dataset.tid || null,
        owner_type:   owner, area, period_type: period, shift: shift||null,
        label:        row.querySelector('[data-f=label]').value,
        sales_goal:   parseFloat(row.querySelector('[data-f=goal]').value)||0,
        bonus_amount: parseFloat(row.querySelector('[data-f=bonus]').value)||0,
        sort_order:   0
    })).filter(t => t.label);
}

async function guardarGrupales() {
    const tiers = [
        ...collectBlock('grp-manana-weekly',  'group','ventas','weekly', 'manana'),
        ...collectBlock('grp-manana-monthly', 'group','ventas','monthly','manana'),
        ...collectBlock('grp-tarde-weekly',   'group','ventas','weekly', 'tarde'),
        ...collectBlock('grp-tarde-monthly',  'group','ventas','monthly','tarde'),
    ];
    await postMetas({tiers, delete_tier_ids: delTierIds.splice(0)});
    showMsg('msgGrp'); cargarConfig();
}

async function guardarIndividuales() {
    const tiers = [
        ...collectBlock('ind-ventas-weekly',   'individual','ventas','weekly', null),
        ...collectBlock('ind-ventas-monthly',  'individual','ventas','monthly',null),
        ...collectBlock('ind-servicio-weekly', 'individual','servicio_tecnico','weekly', null),
        ...collectBlock('ind-servicio-monthly','individual','servicio_tecnico','monthly',null),
    ];
    await postMetas({tiers, delete_tier_ids: delTierIds.splice(0)});
    showMsg('msgInd'); cargarConfig();
}

async function guardarSalarios() {
    const salarios = [], assignments = [];
    document.querySelectorAll('[data-emp]').forEach(inp => {
        const eid = parseInt(inp.dataset.emp), f = inp.dataset.field, v = inp.value;
        let e = salarios.find(s=>s.employee_id===eid);
        if (!e) { e={employee_id:eid,base_salary:0,extra_bonus:0,extra_bonus_reason:''}; salarios.push(e); }
        if (f==='salary')       e.base_salary       = parseFloat(v)||0;
        if (f==='extra_bonus')  e.extra_bonus        = parseFloat(v)||0;
        if (f==='extra_reason') e.extra_bonus_reason = v;
        if (f==='weekly_area' && v) assignments.push({employee_id:eid,period_type:'weekly',area:v});
    });
    await fetch('/api/remuneracion/salarios',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF()},body:JSON.stringify({salarios})});
    if (assignments.length) await postMetas({assignments});
    showMsg('msgSal');
}

async function guardarTardiness() {
    const mins = parseInt(document.getElementById('cfgTardMin').value)||10;
    await postMetas({tardiness:{threshold_minutes:mins}});
    showMsg('msgTard');
}

function postMetas(payload) {
    return fetch('/api/remuneracion/metas',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF()},body:JSON.stringify(payload)});
}

function showMsg(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    setTimeout(()=>el.classList.add('hidden'),3000);
}

function renderEmpleadosConfig(emps) {
    const areas = [{v:'ventas',l:'Ventas'},{v:'servicio_tecnico',l:'Serv. Técnico'}];
    const rows = emps.map(e => {
        const opts = areas.map(a=>`<option value="${a.v}" ${e.weekly_area===a.v?'selected':''}>${a.l}</option>`).join('');
        return `<tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                    ${AVT('w-8 h-8',e.photo)}
                    <div>
                        <p class="text-sm font-medium text-gray-800">${e.name}</p>
                        <p class="text-xs text-gray-400">${e.job||''}</p>
                    </div>
                </div>
            </td>
            <td class="px-4 py-3 text-right">
                <input type="number" min="0" step="10" value="${e.base_salary??0}" class="inp text-right w-28" data-emp="${e.id}" data-field="salary">
            </td>
            <td class="px-4 py-3 text-right">
                <input type="number" min="0" step="10" value="${e.extra_bonus??0}" class="inp text-right w-28" data-emp="${e.id}" data-field="extra_bonus">
            </td>
            <td class="px-4 py-3">
                <input type="text" value="${e.extra_bonus_reason??''}" placeholder="Ej: Encargado de turno" class="inp w-full" data-emp="${e.id}" data-field="extra_reason">
            </td>
            <td class="px-4 py-3 text-center">
                <select class="inp text-xs" data-emp="${e.id}" data-field="weekly_area">
                    <option value="">— sin asignar —</option>${opts}
                </select>
            </td>
        </tr>`;
    }).join('');
    document.getElementById('tbodyConfig').innerHTML = rows || '<tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">Sin colaboradores</td></tr>';
}

// ── INIT ─────────────────────────────────────────────────────────
document.getElementById('lblSemana').textContent = fmtSemana(getWeekStart(0));
cargarResumen();
</script>
