<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"/>
  <title>Traslado Interno – Tecnigass</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={darkMode:'class',theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif']}}}}</script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    :root{--color-primario:#7c3aed;--color-fondo:#f0f4f8;--color-superficie:#ffffff;--color-texto:#0f172a;--color-borde:#e2e8f0}
    html.dark{--color-primario:#8b5cf6;--color-fondo:#0f172a;--color-superficie:#1e293b;--color-texto:#f1f5f9;--color-borde:#334155}
    body{background:var(--color-fondo);color:var(--color-texto);transition:background .3s,color .3s}
    .glass-header{background:rgba(255,255,255,.7);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-bottom:1px solid var(--color-borde)}
    html.dark .glass-header{background:rgba(30,41,59,.7)}
    ::-webkit-scrollbar{width:6px}::-webkit-scrollbar-track{background:transparent}::-webkit-scrollbar-thumb{background:var(--color-borde);border-radius:6px}
    @keyframes fadeInSlideUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
    .animar-entrada{animation:fadeInSlideUp .3s cubic-bezier(.4,0,.2,1) forwards}
    .btn-trans{transition:all 200ms cubic-bezier(.4,0,.2,1)}
    input,select{background-color:var(--color-superficie);color:var(--color-texto);border-color:var(--color-borde)}
    input:focus,select:focus{outline:none;border-color:var(--color-primario);box-shadow:0 0 0 2px rgba(124,58,237,0.2)}
    html.dark input,html.dark select{border-color:var(--color-borde);background-color:#0f172a}
  </style>
</head>
<body class="h-screen flex flex-col overflow-hidden font-sans">

<!-- HEADER -->
<header class="glass-header px-5 py-3 flex items-center justify-between shrink-0 z-20 relative">
  <div class="flex items-center gap-3">
    <a href="/dashboard" class="w-8 h-8 rounded-lg text-gray-500 hover:bg-[var(--color-borde)] flex items-center justify-center btn-trans">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-lg">
      <i data-lucide="arrow-right-left" class="w-5 h-5"></i>
    </div>
    <div>
      <h1 class="text-lg font-bold tracking-tight text-[var(--color-texto)] leading-tight">Tecnigass – Traslado</h1>
      <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Movimientos internos</p>
    </div>
  </div>

  <!-- Origen / Destino desktop -->
  <div class="hidden md:flex items-center gap-3 bg-[var(--color-superficie)] px-3 py-2 rounded-2xl border border-[var(--color-borde)] shadow-sm">
    <i data-lucide="map-pin" class="w-4 h-4 text-gray-400 shrink-0"></i>
    <div class="flex items-center gap-2 text-xs">
      <div class="flex flex-col gap-0.5">
        <span class="font-black text-[9px] uppercase text-gray-400 tracking-widest">Origen</span>
        <select id="locationSrcSelect" class="bg-transparent text-sm font-bold text-[var(--color-texto)] border-none outline-none appearance-none pr-5 cursor-pointer">
          <option value="">Cargando...</option>
        </select>
      </div>
      <i data-lucide="arrow-right" class="w-4 h-4 text-purple-500 shrink-0"></i>
      <div class="flex flex-col gap-0.5">
        <span class="font-black text-[9px] uppercase text-gray-400 tracking-widest">Destino</span>
        <select id="locationDestSelect" class="bg-transparent text-sm font-bold text-[var(--color-texto)] border-none outline-none appearance-none pr-5 cursor-pointer">
          <option value="">Cargando...</option>
        </select>
      </div>
    </div>
  </div>

  <div class="flex items-center gap-2">
    <button id="btnDarkMode" class="p-2 rounded-xl text-gray-500 hover:bg-[var(--color-borde)] hover:text-[var(--color-texto)] btn-trans">
      <i data-lucide="moon" class="w-5 h-5"></i>
    </button>
    <div class="hidden sm:flex items-center gap-2 bg-purple-100 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 px-4 py-1.5 rounded-full text-sm font-semibold">
      <i data-lucide="list" class="w-4 h-4"></i>
      <span id="totalCount" class="bg-purple-600 text-white px-2 py-0.5 rounded-lg text-xs font-bold">0</span>
    </div>
  </div>
</header>

<div class="flex flex-1 overflow-hidden flex-col md:flex-row">

  <!-- LEFT: BÚSQUEDA -->
  <section class="flex-1 flex flex-col overflow-hidden border-b md:border-b-0 md:border-r border-[var(--color-borde)] z-10">
    <div class="p-4 bg-[var(--color-superficie)]/50 backdrop-blur-md border-b border-[var(--color-borde)] shrink-0 space-y-3">
      <!-- Selectores móvil -->
      <div class="md:hidden grid grid-cols-2 gap-2">
        <div class="flex flex-col gap-1 bg-[var(--color-superficie)] border border-[var(--color-borde)] rounded-xl p-3">
          <span class="text-[9px] font-black uppercase text-gray-400 tracking-widest flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i> Origen</span>
          <select id="mSrcSelect" class="bg-transparent text-xs font-bold text-[var(--color-texto)] border-none outline-none cursor-pointer"><option value="">—</option></select>
        </div>
        <div class="flex flex-col gap-1 bg-[var(--color-superficie)] border border-purple-200 dark:border-purple-800 rounded-xl p-3">
          <span class="text-[9px] font-black uppercase text-purple-500 tracking-widest flex items-center gap-1"><i data-lucide="arrow-right" class="w-3 h-3"></i> Destino</span>
          <select id="mDestSelect" class="bg-transparent text-xs font-bold text-[var(--color-texto)] border-none outline-none cursor-pointer"><option value="">—</option></select>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
          <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
          <input id="searchInput" type="text" class="w-full pl-9 pr-3 py-2 rounded-xl border-2 text-sm font-medium btn-trans" placeholder="Buscar por código o nombre..." autocomplete="off">
        </div>
        <div class="relative flex-1 max-w-[220px] min-w-[140px]">
          <i data-lucide="folder" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
          <input id="categInput" list="categList" class="w-full pl-9 pr-3 py-2 rounded-xl border-2 text-sm font-medium btn-trans bg-[var(--color-superficie)]" placeholder="Categoría..." autocomplete="off">
          <datalist id="categList"></datalist>
        </div>
        <button id="btnSearch" class="flex items-center gap-2 bg-purple-600 text-white hover:bg-purple-700 px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm btn-trans">
          <i data-lucide="search" class="w-4 h-4"></i> Buscar
        </button>
        <button id="btnRefresh" class="p-2.5 rounded-xl bg-[var(--color-superficie)] text-gray-600 hover:text-purple-600 border border-[var(--color-borde)] shadow-sm btn-trans">
          <i data-lucide="refresh-cw" class="w-4 h-4"></i>
        </button>
        <button id="btnClear" class="p-2.5 rounded-xl bg-[var(--color-superficie)] text-gray-500 hover:text-red-500 border border-[var(--color-borde)] shadow-sm btn-trans">
          <i data-lucide="eraser" class="w-4 h-4"></i>
        </button>
      </div>
      <div id="resultsMeta" class="text-xs font-semibold text-gray-500 px-1 h-4"></div>
    </div>

    <div class="flex-1 overflow-y-auto p-4 md:p-5" id="resultsArea">
      <div class="flex flex-col items-center justify-center h-full text-gray-400 animar-entrada">
        <i data-lucide="arrow-right-left" class="w-16 h-16 opacity-40 mb-3 text-purple-300"></i>
        <p class="font-semibold text-[var(--color-texto)] opacity-70">Busca productos para trasladar</p>
      </div>
    </div>
  </section>

  <!-- RIGHT: CARRITO -->
  <aside class="w-full md:w-[380px] shrink-0 flex flex-col bg-[var(--color-superficie)]/90 backdrop-blur-xl border-t md:border-t-0 shadow-[-4px_0_24px_rgba(0,0,0,0.02)] relative z-20">
    <div class="p-4 border-b border-[var(--color-borde)] flex items-center justify-between bg-[var(--color-superficie)] shrink-0">
      <div class="flex items-center gap-2 font-bold text-[var(--color-texto)]">
        <i data-lucide="clipboard-list" class="w-5 h-5 text-purple-600"></i>
        Lista de Traslado
      </div>
      <span id="cartCount" class="bg-[var(--color-fondo)] text-gray-500 px-3 py-1 rounded-full text-xs font-bold">0 líneas</span>
    </div>

    <div class="flex-1 overflow-y-auto p-3 space-y-3" id="cartList" style="max-height:45vh">
      <div class="flex flex-col items-center justify-center text-gray-400 py-10 animar-entrada">
        <i data-lucide="package-x" class="w-12 h-12 opacity-30 mb-2"></i>
        <p class="text-sm font-medium">Lista vacía</p>
      </div>
    </div>

    <div class="p-4 bg-[var(--color-superficie)] border-t border-[var(--color-borde)] flex flex-col gap-3 shrink-0">
      <button id="btnTransfer" disabled class="flex items-center justify-center gap-2 w-full py-3.5 rounded-xl text-sm font-bold text-white bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg btn-trans">
        <i data-lucide="arrow-right-left" class="w-5 h-5"></i> Guardar Traslado
      </button>
      <button id="btnShowHistory" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-xs font-bold text-purple-400 hover:text-white hover:bg-purple-600 border border-purple-800 btn-trans">
        <i data-lucide="clock" class="w-4 h-4"></i> Ver Historial de Traslados
      </button>
      <button id="btnClearAll" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-xs font-bold text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 btn-trans">
        <i data-lucide="trash-2" class="w-4 h-4"></i> Vaciar Lista
      </button>
    </div>
  </aside>
</div>

<!-- MODAL SIMPLE -->
<div id="modalOverlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center opacity-0 transition-opacity duration-200 p-4">
  <div id="modalSimple" class="hidden w-full max-w-md bg-[var(--color-superficie)] rounded-2xl shadow-2xl overflow-hidden border border-[var(--color-borde)] transform scale-95 transition-transform duration-200">
    <div class="p-5 border-b border-[var(--color-borde)] bg-[var(--color-fondo)]/50 flex items-start justify-between">
      <div>
        <h3 id="modalSimpleTitle" class="font-bold text-[var(--color-texto)] text-lg leading-tight">Producto</h3>
        <p id="modalSimpleSub" class="text-xs font-medium text-gray-500 mt-1"></p>
      </div>
      <button id="modalClose" class="text-gray-400 hover:text-[var(--color-texto)] p-1.5 rounded-lg hover:bg-[var(--color-borde)] btn-trans">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <div class="p-5">
      <label class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">
        <i data-lucide="package" class="w-4 h-4"></i> Cantidad a trasladar
      </label>
      <input id="simpleQty" type="number" min="1" value="1" class="w-full p-3 rounded-xl border-2 text-center font-bold text-lg btn-trans">
    </div>
    <div class="p-4 border-t border-[var(--color-borde)] bg-[var(--color-fondo)]/50 flex justify-end gap-3">
      <button id="btnModalCancel" class="px-5 py-2 rounded-xl text-sm font-bold text-gray-600 hover:bg-[var(--color-borde)] btn-trans">Cancelar</button>
      <button id="btnSimpleConfirm" class="flex items-center gap-2 px-6 py-2 rounded-xl text-sm font-bold text-white bg-purple-600 hover:bg-purple-700 shadow-sm btn-trans">
        <i data-lucide="check" class="w-4 h-4"></i> Añadir
      </button>
    </div>
  </div>
</div>

<!-- MODAL HISTORIAL -->
<div id="historyModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
  <div class="bg-[var(--color-superficie)] rounded-2xl w-full max-w-2xl max-h-[85vh] flex flex-col border border-[var(--color-borde)] shadow-2xl">
    <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-borde)]">
      <div class="flex items-center gap-2 font-bold text-[var(--color-texto)]">
        <i data-lucide="clock" class="w-5 h-5 text-purple-400"></i>
        Historial de Traslados
      </div>
      <button id="historyClose" class="text-gray-400 hover:text-[var(--color-texto)] p-1">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <div id="historyList" class="flex-1 overflow-y-auto p-4 space-y-2"></div>
  </div>
</div>

<!-- MODAL DETALLE -->
<div id="histDetailModal" class="fixed inset-0 hidden items-center justify-center bg-black/70 backdrop-blur-sm p-4" style="z-index:60">
  <div class="bg-[var(--color-superficie)] rounded-2xl w-full max-w-xl max-h-[85vh] flex flex-col border border-[var(--color-borde)] shadow-2xl">
    <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-borde)]">
      <span id="histDetailTitle" class="font-bold text-[var(--color-texto)]"></span>
      <button id="histDetailClose" class="text-gray-400 hover:text-[var(--color-texto)] p-1">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <div id="histDetailBody" class="flex-1 overflow-y-auto p-4"></div>
    <div class="px-5 py-4 border-t border-[var(--color-borde)] flex gap-3">
      <button id="btnConfirmTraslado" class="flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold py-2 px-4 rounded-xl btn-trans hidden">
        <i data-lucide="check-circle" class="w-4 h-4"></i> Confirmar
      </button>
      <button id="btnDeleteTraslado" class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold py-2 px-4 rounded-xl btn-trans hidden">
        <i data-lucide="trash-2" class="w-4 h-4"></i> Eliminar
      </button>
      <button id="histDetailClose2" class="ml-auto px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:bg-[var(--color-borde)] btn-trans">Cerrar</button>
    </div>
  </div>
</div>

<!-- TOASTS -->
<div id="toastContainer" class="fixed bottom-6 left-1/2 -translate-x-1/2 flex flex-col gap-3 z-[100] pointer-events-none"></div>

<script>
// ═══════════════════════════════════════════════════════
// CONFIG
// ═══════════════════════════════════════════════════════
const ROUTES = {
  productos: '/api/traslado/productos',
  imagenes:  '/api/traslado/imagenes',
  almacenes: '/api/traslado/almacenes',
  historial: '/api/traslado/historial',
  store:     '/api/traslado',
  show:      (id) => `/api/traslado/${id}`,
  confirm:   (id) => `/api/traslado/${id}/confirm`,
  destroy:   (id) => `/api/traslado/${id}`,
};
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ═══════════════════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════════════════
let transferRows     = [];
let _currentProducts = [];
let _catMap          = {};
let _detailId        = null;

// ── Lazy image loader (eliminado — ahora se pre-carga antes de renderizar) ──
let _imgObserver = null;
let _imgTimer    = null;
function initImageObserver() {} // no-op, se conserva por compatibilidad

// ═══════════════════════════════════════════════════════
// INIT
// ═══════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
  lucide.createIcons();
  if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
  document.getElementById('btnDarkMode').addEventListener('click', () => {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
  });
  loadProductos();
  loadCart();
  ['locationSrcSelect','mSrcSelect'].forEach(id => document.getElementById(id).addEventListener('change', syncSelects));
  ['locationDestSelect','mDestSelect'].forEach(id => document.getElementById(id).addEventListener('change', syncSelects));
});

function syncSelects() {
  const src = document.getElementById('locationSrcSelect').value || document.getElementById('mSrcSelect').value;
  const dst = document.getElementById('locationDestSelect').value || document.getElementById('mDestSelect').value;
  ['locationSrcSelect','mSrcSelect'].forEach(id => document.getElementById(id).value = src);
  ['locationDestSelect','mDestSelect'].forEach(id => document.getElementById(id).value = dst);
  saveCart();
}

// ═══════════════════════════════════════════════════════
// CARGAR PRODUCTOS + ALMACENES
// ═══════════════════════════════════════════════════════
async function loadProductos() {
  try {
    const [rp, ra] = await Promise.all([fetch(ROUTES.productos), fetch(ROUTES.almacenes)]);
    const dp = await rp.json();
    const da = await ra.json();

    _currentProducts = dp.products || [];

    // Categorías para datalist
    const dl = document.getElementById('categList');
    dl.innerHTML = '';
    const cats = new Set();
    _currentProducts.forEach(p => {
      if (p.categ_id && Array.isArray(p.categ_id)) {
        const name = p.categ_id[1];
        if (!cats.has(name)) {
          cats.add(name);
          _catMap[name] = p.categ_id[0];
          const opt = document.createElement('option');
          opt.value = name; dl.appendChild(opt);
        }
      }
    });

    // Almacenes en selects
    const almacenes = da.almacenes || [];
    const saved = JSON.parse(localStorage.getItem('tecnigas_traslado') || '{}');
    ['locationSrcSelect','mSrcSelect','locationDestSelect','mDestSelect'].forEach(id => {
      document.getElementById(id).innerHTML = '<option value="">— Seleccionar —</option>';
    });
    almacenes.forEach(a => {
      const label = a.complete_name || a.name;
      ['locationSrcSelect','mSrcSelect'].forEach(id => {
        const opt = document.createElement('option');
        opt.value = a.id; opt.textContent = label;
        if (saved.src && a.id == saved.src) opt.selected = true;
        document.getElementById(id).appendChild(opt);
      });
      ['locationDestSelect','mDestSelect'].forEach(id => {
        const opt = document.createElement('option');
        opt.value = a.id; opt.textContent = label;
        if (saved.dst && a.id == saved.dst) opt.selected = true;
        document.getElementById(id).appendChild(opt);
      });
    });
    toast(`${_currentProducts.length} productos cargados`, 'success', 'check-circle');
  } catch(e) {
    toast('Error conectando con Odoo', 'error', 'wifi-off');
  }
}

// ═══════════════════════════════════════════════════════
// FETCH DE IMÁGENES (paralelo, batches de 50)
// ═══════════════════════════════════════════════════════
async function fetchAllImages(ids) {
  const imageMap = {};
  if (!ids.length) return imageMap;

  const batches = [];
  for (let i = 0; i < ids.length; i += 50) batches.push(ids.slice(i, i + 50));

  await Promise.all(batches.map(async batch => {
    try {
      const r = await fetch(`${ROUTES.imagenes}?ids=${batch.join(',')}`);
      const d = await r.json();
      if (d.success) Object.assign(imageMap, d.images);
    } catch(e) { /* silencioso: el producto aparece sin imagen */ }
  }));

  return imageMap;
}

// ═══════════════════════════════════════════════════════
// BÚSQUEDA + RENDER — esperar imágenes antes de mostrar
// ═══════════════════════════════════════════════════════
async function doSearch() {
  const q      = document.getElementById('searchInput').value.trim().toLowerCase();
  const catName= document.getElementById('categInput').value.trim();
  const catId  = _catMap[catName] || null;
  const meta   = document.getElementById('resultsMeta');
  const area   = document.getElementById('resultsArea');

  if (!q && !catName) {
    area.innerHTML = `
      <div class="flex flex-col items-center justify-center h-full text-gray-400 animar-entrada">
        <i data-lucide="arrow-right-left" class="w-16 h-16 opacity-40 mb-3 text-purple-300"></i>
        <p class="font-semibold opacity-70">Busca productos para trasladar</p>
      </div>`;
    lucide.createIcons(); meta.textContent = ''; return;
  }

  const results = _currentProducts.filter(p => {
    const mq  = !q || (p.name||'').toLowerCase().includes(q) || (p.default_code||'').toLowerCase().includes(q);
    const mc  = !catId || (p.categ_id && p.categ_id[0] === catId);
    return mq && mc;
  });

  meta.textContent = `${results.length} resultado(s)`;

  if (!results.length) {
    area.innerHTML = `<div class="flex flex-col items-center justify-center p-14 text-gray-400 animar-entrada"><i data-lucide="search-x" class="w-12 h-12 mb-2 opacity-50"></i><p class="font-bold">Sin resultados</p></div>`;
    lucide.createIcons(); return;
  }

  area.innerHTML = `
    <div class="flex flex-col items-center justify-center h-full text-gray-400 py-16">
      <i data-lucide="loader-2" class="w-10 h-10 animate-spin text-purple-600 mb-3"></i>
      <p class="font-semibold text-[var(--color-texto)] opacity-70">Cargando ${results.length} producto(s)...</p>
      <p class="text-xs mt-1 opacity-50">Preparando imágenes</p>
    </div>`;
  lucide.createIcons({ root: area });

  const ids      = results.map(p => p.id);
  const imageMap = await fetchAllImages(ids);

  renderGrid(results, imageMap);
}

// ═══════════════════════════════════════════════════════
// GRID (con imágenes ya disponibles)
// ═══════════════════════════════════════════════════════
function renderGrid(products, imageMap = {}) {
  const area = document.getElementById('resultsArea');
  if (!products.length) {
    area.innerHTML = `<div class="flex flex-col items-center justify-center p-14 text-gray-400 animar-entrada"><i data-lucide="search-x" class="w-12 h-12 mb-2 opacity-50"></i><p class="font-bold">Sin resultados</p></div>`;
    lucide.createIcons(); return;
  }
  const html = products.map(p => {
    const code  = p.default_code || 'N/A';
    const stock = parseFloat(p.qty_available||0).toFixed(0);
    const b64   = imageMap[p.id];
    const imgHtml = b64
      ? `<img src="data:image/png;base64,${b64}" class="w-full h-full object-contain p-2" alt="${esc(p.name)}">`
      : `<i data-lucide="package" class="w-10 h-10 text-[var(--color-borde)]"></i>`;

    return `
    <div class="bg-[var(--color-superficie)] rounded-xl border border-[var(--color-borde)] shadow-sm hover:shadow-lg hover:-translate-y-1 overflow-hidden flex flex-col cursor-pointer btn-trans animar-entrada" onclick="openSimpleModal(${p.id})" id="card-${p.id}">
      <div class="h-28 bg-white dark:bg-slate-800 flex items-center justify-center overflow-hidden">
        ${imgHtml}
      </div>
      <div class="p-3 flex-1 flex flex-col">
        <div class="text-[10px] font-black text-purple-600 dark:text-purple-400 tracking-widest uppercase mb-1">${code}</div>
        <div class="text-xs font-bold text-[var(--color-texto)] leading-snug flex-1">${esc(p.name)}</div>
        <div class="flex gap-2 mt-2">
          <span class="flex items-center gap-1 text-[10px] font-bold bg-cyan-50 dark:bg-cyan-900/20 text-cyan-700 dark:text-cyan-300 px-1.5 py-0.5 rounded-md border border-cyan-100 dark:border-cyan-800">
            <i data-lucide="warehouse" class="w-3 h-3"></i> ${stock}
          </span>
        </div>
      </div>
      <div class="p-3 bg-[var(--color-fondo)]/50 border-t border-[var(--color-borde)] flex items-center justify-between">
        <span class="text-xs text-gray-400">${esc(Array.isArray(p.uom_id) ? p.uom_id[1] : '')}</span>
        <button class="bg-purple-100 hover:bg-purple-600 text-purple-700 hover:text-white dark:bg-purple-900/30 dark:text-purple-300 text-xs font-bold py-1.5 px-3 rounded-full btn-trans flex items-center gap-1">
          <i data-lucide="plus" class="w-3 h-3"></i> Añadir
        </button>
      </div>
    </div>`;
  }).join('');
  area.innerHTML = `<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">${html}</div>`;
  lucide.createIcons();
}

// ═══════════════════════════════════════════════════════
// MODAL SIMPLE
// ═══════════════════════════════════════════════════════
function openSimpleModal(prodId) {
  const prod = _currentProducts.find(p => p.id === prodId);
  if (!prod) return;
  document.getElementById('modalSimpleTitle').textContent = prod.name;
  document.getElementById('modalSimpleSub').textContent   = `Ref: ${prod.default_code||'N/A'}`;
  document.getElementById('simpleQty').value = 1;

  const ov = document.getElementById('modalOverlay');
  const mo = document.getElementById('modalSimple');
  ov.classList.remove('hidden'); ov.classList.add('flex');
  mo.classList.remove('hidden');
  requestAnimationFrame(() => { ov.classList.remove('opacity-0'); mo.classList.remove('scale-95'); });

  document.getElementById('btnSimpleConfirm').onclick = () => {
    const qty = parseInt(document.getElementById('simpleQty').value) || 1;
    addRow({ uid:`p-${prod.id}-${Date.now()}`, producto_id:prod.id, producto_nombre:prod.name,
      default_code:prod.default_code||'', unidad: Array.isArray(prod.uom_id)?prod.uom_id[1]:'', qty });
    closeSimpleModal();
    toast('Añadido a lista', 'success', 'check-circle');
  };
}

function closeSimpleModal() {
  const ov = document.getElementById('modalOverlay');
  ov.classList.add('opacity-0');
  document.getElementById('modalSimple').classList.add('scale-95');
  setTimeout(() => { ov.classList.add('hidden'); ov.classList.remove('flex'); document.getElementById('modalSimple').classList.add('hidden'); }, 200);
}

document.getElementById('modalClose').addEventListener('click', closeSimpleModal);
document.getElementById('btnModalCancel').addEventListener('click', closeSimpleModal);
document.getElementById('modalOverlay').addEventListener('mousedown', e => { if (e.target.id === 'modalOverlay') closeSimpleModal(); });

// ═══════════════════════════════════════════════════════
// CART
// ═══════════════════════════════════════════════════════
function saveCart() {
  localStorage.setItem('tecnigas_traslado', JSON.stringify({
    rows: transferRows,
    src:  document.getElementById('locationSrcSelect').value,
    dst:  document.getElementById('locationDestSelect').value,
  }));
}

function loadCart() {
  try {
    const data = JSON.parse(localStorage.getItem('tecnigas_traslado') || '{}');
    if (data.rows) { transferRows = data.rows; renderCart(); }
  } catch(e) {}
}

function addRow(row)    { transferRows.push(row); renderCart(); }
function removeRow(uid) { transferRows = transferRows.filter(r => r.uid !== uid); renderCart(); }

function updateRow(uid, val) {
  const row = transferRows.find(r => r.uid === uid);
  if (row) { row.qty = parseInt(val) || 0; saveCart(); }
}

function renderCart() {
  const count = transferRows.length;
  document.getElementById('totalCount').textContent = count;
  document.getElementById('cartCount').textContent  = `${count} ítem(s)`;
  document.getElementById('btnTransfer').disabled   = count === 0;
  saveCart();

  const list = document.getElementById('cartList');
  if (!count) {
    list.innerHTML = `<div class="flex flex-col items-center justify-center text-gray-400 py-10 animar-entrada"><i data-lucide="package-x" class="w-12 h-12 opacity-30 mb-2"></i><p class="text-sm font-bold opacity-70">Lista vacía</p></div>`;
    lucide.createIcons(); return;
  }
  list.innerHTML = transferRows.map(r => `
<div class="bg-[var(--color-superficie)] rounded-xl border border-l-4 border-l-purple-500 border-[var(--color-borde)] shadow-sm mb-2 overflow-hidden animar-entrada group">
  <div class="p-3 flex items-start gap-2">
    <div class="flex-1 min-w-0">
      <div class="text-xs font-bold text-[var(--color-texto)] truncate">${esc(r.producto_nombre)}</div>
      <div class="text-[10px] text-gray-400 mt-0.5">${r.default_code||'S/N'}</div>
    </div>
    <button class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg btn-trans opacity-0 group-hover:opacity-100" onclick="removeRow('${r.uid}')">
      <i data-lucide="trash-2" class="w-4 h-4"></i>
    </button>
  </div>
  <div class="bg-[var(--color-fondo)]/50 border-t border-[var(--color-borde)] px-3 py-2">
    <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1 mb-1"><i data-lucide="package" class="w-3 h-3"></i> Cantidad</label>
    <input type="number" min="1" value="${r.qty}" onchange="updateRow('${r.uid}',this.value)" class="w-full bg-[var(--color-superficie)] border border-[var(--color-borde)] rounded-lg px-2 py-1.5 text-xs font-bold text-center btn-trans outline-none focus:border-purple-500"/>
  </div>
</div>`).join('');
  lucide.createIcons();
}

// ═══════════════════════════════════════════════════════
// GUARDAR TRASLADO → Laravel
// ═══════════════════════════════════════════════════════
document.getElementById('btnTransfer').addEventListener('click', async () => {
  const srcId  = document.getElementById('locationSrcSelect').value;
  const dstId  = document.getElementById('locationDestSelect').value;
  const srcTxt = document.getElementById('locationSrcSelect').options[document.getElementById('locationSrcSelect').selectedIndex]?.text || '';
  const dstTxt = document.getElementById('locationDestSelect').options[document.getElementById('locationDestSelect').selectedIndex]?.text || '';

  if (!srcId) { toast('Selecciona ubicación de ORIGEN', 'error', 'alert-triangle'); return; }
  if (!dstId) { toast('Selecciona ubicación de DESTINO', 'error', 'alert-triangle'); return; }
  if (srcId === dstId) { toast('Origen y destino deben ser distintos', 'error', 'alert-triangle'); return; }
  if (!transferRows.length) return;

  const btn = document.getElementById('btnTransfer');
  btn.disabled = true;
  btn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Guardando...';
  lucide.createIcons({ root: btn });

  const payload = {
    fecha:                  new Date().toISOString().split('T')[0],
    almacen_origen_id:      parseInt(srcId),
    almacen_origen_nombre:  srcTxt,
    almacen_destino_id:     parseInt(dstId),
    almacen_destino_nombre: dstTxt,
    usuario:                '{{ auth()->user()->name ?? "sistema" }}',
    items: transferRows.map(r => ({
      producto_id:      r.producto_id,
      producto_nombre:  r.producto_nombre,
      cantidad:         r.qty,
      unidad:           r.unidad || '',
    }))
  };

  try {
    const res = await fetch(ROUTES.store, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify(payload)
    });
    const d = await res.json();
    if (!d.success) { toast(d.error || 'Error al guardar', 'error', 'x-circle'); return; }
    const picking = d.traslado.odoo_picking_id;
    toast(
      picking
        ? `✔ Traslado #${d.traslado.id} · Picking Odoo #${picking}`
        : `Traslado #${d.traslado.id} guardado`,
      'success', 'check-circle'
    );
    transferRows = []; renderCart();
  } catch(e) {
    toast('Error de conexión', 'error', 'wifi-off');
  } finally {
    btn.disabled = transferRows.length === 0;
    btn.innerHTML = '<i data-lucide="arrow-right-left" class="w-5 h-5"></i> Guardar Traslado';
    lucide.createIcons({ root: btn });
  }
});

document.getElementById('btnClearAll').addEventListener('click', () => { transferRows = []; renderCart(); });

// ═══════════════════════════════════════════════════════
// HISTORIAL
// ═══════════════════════════════════════════════════════
document.getElementById('btnShowHistory').addEventListener('click', openHistoryModal);
document.getElementById('historyClose').addEventListener('click', () => {
  document.getElementById('historyModal').classList.add('hidden');
  document.getElementById('historyModal').classList.remove('flex');
});
document.getElementById('histDetailClose').addEventListener('click',  closeDetailModal);
document.getElementById('histDetailClose2').addEventListener('click', closeDetailModal);

function closeDetailModal() {
  document.getElementById('histDetailModal').classList.add('hidden');
  document.getElementById('histDetailModal').classList.remove('flex');
}

async function openHistoryModal() {
  document.getElementById('historyModal').classList.remove('hidden');
  document.getElementById('historyModal').classList.add('flex');
  lucide.createIcons({ root: document.getElementById('historyModal') });
  await loadHistory();
}

async function loadHistory() {
  const list = document.getElementById('historyList');
  list.innerHTML = `<div class="text-center text-gray-400 py-8 text-sm">Cargando...</div>`;
  try {
    const r  = await fetch(ROUTES.historial);
    const d  = await r.json();
    const ts = d.traslados || [];
    if (!ts.length) {
      list.innerHTML = `<div class="text-center text-gray-400 py-8 text-sm">Sin traslados encontrados</div>`; return;
    }
    const estadoColor = { pendiente:'bg-yellow-500/20 text-yellow-400', confirmado:'bg-green-500/20 text-green-400', cancelado:'bg-red-500/20 text-red-400' };
    list.innerHTML = ts.map(t => {
      const sc = estadoColor[t.estado] || 'bg-gray-500/20 text-gray-400';
      return `
      <div class="flex items-center justify-between p-3 bg-[var(--color-fondo)] rounded-xl border border-[var(--color-borde)] hover:border-purple-500 btn-trans cursor-pointer" onclick="openTrasladoDetail(${t.id})">
        <div class="flex flex-col gap-0.5">
          <div class="font-bold text-sm text-[var(--color-texto)]">Traslado #${t.id}</div>
          <div class="text-[10px] text-gray-400 flex items-center gap-1">
            <i data-lucide="move-right" class="w-3 h-3"></i>
            ${esc(t.almacen_origen_nombre)} → ${esc(t.almacen_destino_nombre)}
          </div>
          <div class="text-[10px] text-gray-500">${t.fecha} · ${(t.items||[]).length} línea(s)</div>
        </div>
        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${sc}">${t.estado}</span>
      </div>`;
    }).join('');
    lucide.createIcons({ root: list });
  } catch(e) {
    list.innerHTML = `<div class="text-red-400 text-sm p-4">Error: ${e.message}</div>`;
  }
}

async function openTrasladoDetail(id) {
  _detailId = id;
  document.getElementById('histDetailTitle').textContent = `Traslado #${id}`;
  document.getElementById('histDetailBody').innerHTML = `<div class="text-center text-gray-400 py-8 text-sm">Cargando...</div>`;
  document.getElementById('histDetailModal').classList.remove('hidden');
  document.getElementById('histDetailModal').classList.add('flex');

  try {
    const r = await fetch(ROUTES.show(id));
    const d = await r.json();
    if (!d.success) { document.getElementById('histDetailBody').innerHTML = `<p class="text-red-400 p-4">${esc(d.error)}</p>`; return; }
    const t = d.traslado;

    const btnConf = document.getElementById('btnConfirmTraslado');
    const btnDel  = document.getElementById('btnDeleteTraslado');
    btnConf.classList.toggle('hidden', t.estado !== 'pendiente');
    btnDel.classList.remove('hidden');

    document.getElementById('histDetailBody').innerHTML = `
    <div class="mb-3 text-xs text-gray-500">
      <span class="font-bold">${esc(t.almacen_origen_nombre)}</span>
      → <span class="font-bold">${esc(t.almacen_destino_nombre)}</span>
      · ${t.fecha}
    </div>
    <div class="space-y-2">
      ${(t.items||[]).map(l => `
      <div class="flex items-center justify-between p-3 bg-[var(--color-fondo)] rounded-xl border border-[var(--color-borde)]">
        <div>
          <div class="font-bold text-sm text-[var(--color-texto)]">${esc(l.producto_nombre)}</div>
          <div class="text-[10px] text-gray-400">${esc(l.unidad||'')}</div>
        </div>
        <div class="text-lg font-black text-purple-400">${parseFloat(l.cantidad).toFixed(0)}</div>
      </div>`).join('')}
    </div>`;
    lucide.createIcons({ root: document.getElementById('histDetailBody') });
  } catch(e) {
    document.getElementById('histDetailBody').innerHTML = `<p class="text-red-400 p-4">${e.message}</p>`;
  }
}

document.getElementById('btnConfirmTraslado').addEventListener('click', async () => {
  if (!_detailId || !confirm('¿Confirmar este traslado?')) return;
  const res = await fetch(ROUTES.confirm(_detailId), { method:'POST', headers:{'X-CSRF-TOKEN':CSRF} });
  const d   = await res.json();
  if (d.success) { toast('Traslado confirmado', 'success', 'check-circle'); closeDetailModal(); loadHistory(); }
  else toast(d.error, 'error', 'x-circle');
});

document.getElementById('btnDeleteTraslado').addEventListener('click', async () => {
  if (!_detailId || !confirm('¿Eliminar este traslado?')) return;
  const res = await fetch(ROUTES.destroy(_detailId), { method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF} });
  const d   = await res.json();
  if (d.success) { toast('Traslado eliminado', 'success', 'check-circle'); closeDetailModal(); loadHistory(); }
  else toast(d.error, 'error', 'x-circle');
});

// ═══════════════════════════════════════════════════════
// EVENTOS BÚSQUEDA
// ═══════════════════════════════════════════════════════
document.getElementById('btnSearch').addEventListener('click', doSearch);
['searchInput','categInput'].forEach(id => document.getElementById(id).addEventListener('keydown', e => { if (e.key==='Enter') doSearch(); }));
document.getElementById('btnClear').addEventListener('click', () => {
  document.getElementById('searchInput').value = '';
  document.getElementById('categInput').value  = '';
  document.getElementById('resultsMeta').textContent = '';
  document.getElementById('resultsArea').innerHTML = `
    <div class="flex flex-col items-center justify-center h-full text-gray-400 animar-entrada">
      <i data-lucide="arrow-right-left" class="w-16 h-16 opacity-40 mb-3 text-purple-300"></i>
      <p class="font-semibold opacity-70">Busca productos para trasladar</p>
    </div>`;
  lucide.createIcons();
});
document.getElementById('btnRefresh').addEventListener('click', () => { loadProductos(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeSimpleModal(); closeDetailModal(); } });

// ═══════════════════════════════════════════════════════
// TOAST & ESC
// ═══════════════════════════════════════════════════════
function toast(msg, type='info', icon='info') {
  const c  = document.getElementById('toastContainer');
  const el = document.createElement('div');
  const bg = type==='success'?'bg-green-600 border-green-500':type==='error'?'bg-red-600 border-red-500':'bg-slate-800 border-slate-700';
  el.className = `flex items-center gap-2 px-4 py-3 rounded-full text-white text-sm font-bold shadow-lg animar-entrada border ${bg} backdrop-blur-md`;
  el.innerHTML = `<i data-lucide="${icon}" class="w-4 h-4"></i> ${esc(msg)}`;
  c.appendChild(el); lucide.createIcons({ root: el });
  setTimeout(() => { el.style.opacity='0'; el.style.transform='translateY(10px)'; el.style.transition='all .3s'; setTimeout(()=>el.remove(),300); }, 3000);
}
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>
</body>
</html>