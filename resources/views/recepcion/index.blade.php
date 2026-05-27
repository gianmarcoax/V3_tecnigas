<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"/>
  <title>Recepción de Productos – Tecnigass</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
          colors: {
            primario:  'var(--color-primario)',
            fondo:     'var(--color-fondo)',
            superficie:'var(--color-superficie)',
            texto:     'var(--color-texto)',
            borde:     'var(--color-borde)'
          },
          boxShadow: {
            card:    'var(--sombra-card)',
            elevada: 'var(--sombra-elevada)'
          },
          borderRadius: { xl: 'var(--radio)' }
        }
      }
    }
  </script>
  <script src="https://unpkg.com/lucide@latest"></script>

  <style>
    :root {
      --color-primario: #2563eb;
      --color-fondo: #f0f4f8;
      --color-superficie: #ffffff;
      --color-texto: #0f172a;
      --color-borde: #e2e8f0;
      --radio: 12px;
      --sombra-card: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
      --sombra-elevada: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
    }
    html.dark {
      --color-primario: #3b82f6;
      --color-fondo: #0f172a;
      --color-superficie: #1e293b;
      --color-texto: #f1f5f9;
      --color-borde: #334155;
      --sombra-card: 0 4px 6px -1px rgba(0,0,0,0.3);
      --sombra-elevada: 0 20px 25px -5px rgba(0,0,0,0.5);
    }
    body { background-color: var(--color-fondo); color: var(--color-texto); transition: background-color 0.3s ease, color 0.3s ease; }
    .glass-header { background: rgba(255,255,255,0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-bottom: 1px solid var(--color-borde); }
    html.dark .glass-header { background: rgba(30,41,59,0.7); }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--color-borde); border-radius: 6px; }
    @keyframes fadeInSlideUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    .animar-entrada { animation: fadeInSlideUp 0.3s cubic-bezier(0.4,0,0.2,1) forwards; }
    @keyframes shake { 0%,100% { transform:translateX(0); } 25% { transform:translateX(-4px); } 75% { transform:translateX(4px); } }
    .animar-shake { animation: shake 0.3s ease-in-out; }
    @keyframes pulseSuccess { 0% { transform:scale(1); } 50% { transform:scale(1.05); background-color:var(--color-primario); color:white; } 100% { transform:scale(1); } }
    .animar-pulso { animation: pulseSuccess 0.4s cubic-bezier(0.4,0,0.2,1); }
    .btn-trans { transition: all 200ms cubic-bezier(.4,0,.2,1); }
    input, select { background-color:var(--color-superficie); color:var(--color-texto); border-color:var(--color-borde); }
    input:focus, select:focus { outline:none; border-color:var(--color-primario); box-shadow:0 0 0 2px rgba(37,99,235,0.2); }
    html.dark input, html.dark select { border-color:var(--color-borde); background-color:#0f172a; }
  </style>
</head>
<body class="h-screen flex flex-col overflow-hidden font-sans">

<!-- HEADER -->
<header class="glass-header px-5 py-3 flex items-center justify-between shrink-0 z-20 relative">
  <div class="flex items-center gap-3">
    <a href="/dashboard" class="w-8 h-8 rounded-lg text-gray-500 hover:bg-[var(--color-borde)] flex items-center justify-center btn-trans">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div class="w-10 h-10 rounded-xl bg-primario text-white flex items-center justify-center shadow-elevada">
      <i data-lucide="package-open" stroke-width="2.5"></i>
    </div>
    <div>
      <h1 class="text-lg font-bold tracking-tight text-texto leading-tight">Tecnigass Recepción</h1>
      <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Odoo ERP · BarTender</p>
    </div>
  </div>

  <div class="hidden md:flex items-center gap-3 bg-superficie px-3 py-1.5 rounded-full border border-borde shadow-sm">
    <i data-lucide="map-pin" class="w-4 h-4 text-gray-500"></i>
    <select id="locationSelect" class="bg-transparent text-sm font-bold text-texto border-none outline-none appearance-none pr-6 cursor-pointer">
      <option value="">Cargando ubicaciones...</option>
    </select>
  </div>

  <div class="flex items-center gap-2">
    <button id="btnDarkMode" class="p-2 rounded-xl text-gray-500 hover:bg-borde hover:text-texto btn-trans">
      <i data-lucide="moon" class="w-5 h-5"></i>
    </button>
    <div class="hidden sm:flex items-center gap-2 bg-primario/10 text-primario px-4 py-1.5 rounded-full text-sm font-semibold">
      <i data-lucide="list" class="w-4 h-4"></i>
      <span id="totalCount" class="bg-primario text-white px-2 py-0.5 rounded-lg text-xs font-bold">0</span>
    </div>
  </div>
</header>

<div class="flex flex-1 overflow-hidden flex-col md:flex-row">

  <!-- LEFT PANEL: BÚSQUEDA -->
  <section class="flex-1 flex flex-col overflow-hidden border-b md:border-b-0 md:border-r border-borde z-10">
    <div class="p-4 bg-superficie/50 backdrop-blur-md border-b border-borde shrink-0 space-y-3">
      <div class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
          <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
          <input id="searchInput" type="text" class="w-full pl-9 pr-3 py-2 rounded-xl border-2 text-sm font-medium btn-trans" placeholder="Buscar por código o descripción..." autocomplete="off">
        </div>
        <div class="relative flex-1 max-w-[250px] min-w-[160px]">
          <i data-lucide="folder" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
          <input id="categInput" list="categList" class="w-full pl-9 pr-3 py-2 rounded-xl border-2 text-sm font-medium btn-trans bg-superficie" placeholder="Buscar categoría..." autocomplete="off">
          <datalist id="categList"></datalist>
        </div>
        <button id="btnSearch" class="flex items-center gap-2 bg-primario text-white hover:bg-blue-700 px-5 py-2.5 rounded-xl font-bold text-sm shadow-card btn-trans">
          <i data-lucide="search" class="w-4 h-4"></i> Buscar
        </button>
        <button id="btnRefresh" class="p-2.5 rounded-xl bg-superficie text-gray-600 hover:text-primario border border-borde shadow-sm btn-trans" title="Sincronizar con Odoo">
          <i data-lucide="refresh-cw" class="w-4 h-4"></i>
        </button>
        <button id="btnClear" class="p-2.5 rounded-xl bg-superficie text-gray-500 hover:text-red-500 border border-borde shadow-sm btn-trans" title="Limpiar formulario">
          <i data-lucide="eraser" class="w-4 h-4"></i>
        </button>
      </div>
      <div id="resultsMeta" class="text-xs font-semibold text-gray-500 px-1 h-4"></div>
    </div>

    <div class="flex-1 overflow-y-auto p-4 md:p-5" id="resultsArea">
      <div class="flex flex-col items-center justify-center h-full text-gray-400 animar-entrada">
        <i data-lucide="package-search" class="w-16 h-16 opacity-50 mb-3 text-borde"></i>
        <p class="font-semibold text-texto opacity-70">Busca un producto para empezar</p>
      </div>
    </div>
  </section>

  <!-- RIGHT PANEL: CARRITO -->
  <aside class="w-full md:w-[380px] shrink-0 flex flex-col bg-superficie/90 backdrop-blur-xl border-t md:border-t-0 shadow-[-4px_0_24px_rgba(0,0,0,0.02)] relative z-20">
    <div class="md:hidden p-3 border-b border-borde bg-superficie flex items-center gap-2">
      <i data-lucide="map-pin" class="w-4 h-4 text-gray-500"></i>
      <select id="mobileLocationSelect" class="bg-transparent text-sm font-bold text-texto border-none outline-none appearance-none flex-1">
        <option value="">Ubicación...</option>
      </select>
    </div>

    <div class="p-4 border-b border-borde flex items-center justify-between bg-superficie shrink-0">
      <div class="flex items-center gap-2 font-bold text-texto">
        <i data-lucide="clipboard-list" class="w-5 h-5 text-primario"></i>
        Lista de Recepción
      </div>
      <span id="cartCount" class="bg-fondo text-gray-500 px-3 py-1 rounded-full text-xs font-bold">0 líneas</span>
    </div>

    <div class="flex-1 overflow-y-auto p-3 space-y-3" id="cartList" style="max-height:45vh">
      <div class="flex flex-col items-center justify-center text-gray-400 py-10 animar-entrada">
        <i data-lucide="shopping-cart" class="w-12 h-12 opacity-30 mb-2"></i>
        <p class="text-sm font-medium">Lista vacía</p>
      </div>
    </div>

    <div class="p-4 bg-superficie border-t border-borde flex flex-col gap-3 shrink-0">
      <button id="btnReceiveOdoo" disabled class="flex items-center justify-center gap-2 w-full py-3.5 rounded-xl text-sm font-bold text-white bg-primario hover:bg-blue-700 disabled:opacity-50 shadow-elevada btn-trans">
        <i data-lucide="warehouse" class="w-5 h-5"></i> Recepcionar en Odoo
      </button>
      <button id="btnPrintExcel" disabled class="flex items-center justify-center gap-2 w-full py-3.5 rounded-xl text-sm font-bold text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 shadow-elevada btn-trans">
        <i data-lucide="printer" class="w-5 h-5"></i> Exportar Etiquetas
      </button>
      <button id="btnHistory" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-xs font-bold text-gray-500 hover:text-primario hover:bg-primario/10 border border-borde btn-trans">
        <i data-lucide="history" class="w-4 h-4"></i> Ver Historial de Recepciones
      </button>
      <button id="btnClearAll" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-xs font-bold text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 btn-trans">
        <i data-lucide="trash-2" class="w-4 h-4"></i> Vaciar Lista
      </button>
    </div>
  </aside>

</div>

<!-- MODAL HISTORIAL -->
<div id="historyOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-200">
  <div class="w-full max-w-3xl bg-superficie rounded-2xl shadow-elevada border border-borde flex flex-col max-h-[90vh] transform scale-95 transition-transform duration-200" id="historyPanel">
    <div class="p-5 border-b border-borde flex items-center justify-between shrink-0">
      <div>
        <h2 class="text-lg font-bold text-texto flex items-center gap-2"><i data-lucide="history" class="w-5 h-5 text-primario"></i> Historial de Recepciones</h2>
        <p class="text-xs text-gray-500 mt-0.5">Recepciones guardadas</p>
      </div>
      <button id="historyClose" class="p-2 rounded-xl text-gray-400 hover:text-texto hover:bg-borde btn-trans"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <div class="px-5 py-3 border-b border-borde bg-fondo/50 flex flex-wrap gap-2 shrink-0">
      <button data-hstate="" class="hist-filter-btn active-filter px-3 py-1 rounded-full text-xs font-bold border btn-trans bg-primario text-white border-primario">Todos</button>
      <button id="histRefresh" class="ml-auto p-1.5 rounded-lg text-gray-400 hover:text-primario btn-trans" title="Recargar"><i data-lucide="refresh-cw" class="w-4 h-4"></i></button>
    </div>
    <div class="flex-1 overflow-y-auto" id="historyList">
      <div class="flex flex-col items-center justify-center p-16 text-gray-400">
        <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-primario mb-3"></i>
        <p class="text-sm font-medium">Cargando historial...</p>
      </div>
    </div>
  </div>
</div>

<!-- MODAL DETALLE RECEPCIÓN -->
<div id="detailOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[70] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-200">
  <div class="w-full max-w-3xl bg-superficie rounded-2xl shadow-elevada border border-borde flex flex-col max-h-[92vh] transform scale-95 transition-transform duration-200" id="detailPanel">
    <div class="p-5 border-b border-borde flex items-start justify-between shrink-0">
      <div>
        <h2 id="detailTitle" class="text-lg font-bold text-texto">Detalle</h2>
        <p id="detailMeta" class="text-xs text-gray-500 mt-0.5"></p>
      </div>
      <button id="detailClose" class="p-2 rounded-xl text-gray-400 hover:text-texto hover:bg-borde btn-trans"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <div class="flex-1 overflow-y-auto p-4" id="detailBody">
      <div class="flex justify-center p-10"><i data-lucide="loader-2" class="w-8 h-8 animate-spin text-primario"></i></div>
    </div>
    <div class="p-4 border-t border-borde bg-fondo/50 flex justify-between items-center gap-3 shrink-0">
      <p id="detailFooterInfo" class="text-xs text-gray-500"></p>
      <div class="flex gap-3">
        <button id="detailClose2" class="px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:bg-borde btn-trans">Cerrar</button>
        <button id="btnDeleteRecepcion" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 shadow-card btn-trans hidden">
          <i data-lucide="trash-2" class="w-4 h-4"></i> Eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL PRODUCTOS (Simple) -->
<div id="modalOverlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center opacity-0 transition-opacity duration-200 p-4">
  <div id="modalSimple" class="hidden w-full max-w-md bg-superficie rounded-[var(--radio)] shadow-elevada overflow-hidden border border-borde transform scale-95 transition-transform duration-200">
    <div class="p-5 border-b border-borde bg-fondo/50 flex items-start justify-between">
      <div>
        <h3 id="modalSimpleTitle" class="font-bold text-texto text-lg leading-tight">Producto</h3>
        <p id="modalSimpleSub" class="text-xs font-medium text-gray-500 mt-1"></p>
      </div>
      <button class="modal-close text-gray-400 hover:text-texto p-1.5 rounded-lg hover:bg-borde btn-trans">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <div class="p-5 space-y-4">
      <div>
        <label class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">
          <i data-lucide="package-plus" class="w-4 h-4"></i> Cantidad
        </label>
        <input id="simpleQty" type="number" min="1" value="1" class="w-full p-3 rounded-xl border-2 text-center font-bold text-lg btn-trans">
      </div>
      <div>
        <label class="flex items-center gap-2 text-xs font-bold text-orange-500 uppercase tracking-widest mb-2">
          <i data-lucide="tag" class="w-4 h-4"></i> Tickets BarTender
        </label>
        <input id="simpleTickets" type="number" min="0" value="1" class="w-full p-3 rounded-xl border-2 border-orange-200 dark:border-orange-900/50 bg-orange-50 dark:bg-orange-900/20 text-orange-600 text-center font-bold text-lg btn-trans">
      </div>
      {{-- Costo oculto: se rellena automáticamente desde Odoo (standard_price), no editable --}}
      <input id="simpleCosto" type="hidden" value="0">
    </div>
    <div class="p-4 border-t border-borde bg-fondo/50 flex justify-end gap-3">
      <button class="modal-close px-5 py-2 rounded-xl text-sm font-bold text-gray-600 hover:bg-borde btn-trans">Cancelar</button>
      <button id="btnSimpleConfirm" class="flex items-center gap-2 px-6 py-2 rounded-xl text-sm font-bold text-white bg-primario hover:bg-blue-700 shadow-card btn-trans">
        <i data-lucide="check" class="w-4 h-4"></i> Añadir
      </button>
    </div>
  </div>
</div>

<!-- TOASTS -->
<div id="toastContainer" class="fixed bottom-6 left-1/2 -translate-x-1/2 flex flex-col gap-3 z-[100] pointer-events-none"></div>

<script>
// ═══════════════════════════════════════════════════════
// CONFIG — rutas Laravel
// ═══════════════════════════════════════════════════════
const ROUTES = {
  productos:   '/api/recepcion/productos',
  imagenes:    '/api/recepcion/imagenes',
  proveedores: '/api/recepcion/proveedores',
  ubicaciones: '/api/recepcion/ubicaciones',
  historial:   '/api/recepcion/historial',
  store:       '/api/recepcion',
  show:        (id) => `/api/recepcion/${id}`,
  destroy:     (id) => `/api/recepcion/${id}`,
};

const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ═══════════════════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════════════════
let receptionRows    = [];
let _currentProducts = [];
let _catMap          = {};
let _histDetail      = null;
let _ubicaciones     = [];

// ── Lazy image loader (eliminado — ahora se pre-carga antes de renderizar) ──
let _imgObserver = null;
let _imgTimer    = null;
function initImageObserver() {} // no-op, se conserva por compatibilidad

// ═══════════════════════════════════════════════════════
// THEME
// ═══════════════════════════════════════════════════════
function initTheme() {
  if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
  document.getElementById('btnDarkMode').addEventListener('click', () => {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
  });
}

// ═══════════════════════════════════════════════════════
// BOOTSTRAP
// ═══════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  lucide.createIcons();
  loadProductos();
  loadUbicaciones();
  loadCart();

  document.getElementById('locationSelect').addEventListener('change', (e) => {
    document.getElementById('mobileLocationSelect').value = e.target.value;
    saveCart();
  });
  document.getElementById('mobileLocationSelect').addEventListener('change', (e) => {
    document.getElementById('locationSelect').value = e.target.value;
    saveCart();
  });
});

// ═══════════════════════════════════════════════════════
// CARGAR PRODUCTOS DESDE ODOO (vía Laravel)
// ═══════════════════════════════════════════════════════
async function loadProductos() {
  // Spinner mientras carga — puede tardar varios segundos con 4000+ productos
  const area = document.getElementById('resultsArea');
  area.innerHTML = `
    <div class="flex flex-col items-center justify-center h-full text-gray-400 py-16">
      <i data-lucide="loader-2" class="w-10 h-10 animate-spin text-primario mb-3"></i>
      <p class="font-semibold text-texto opacity-70">Sincronizando catálogo con Odoo...</p>
      <p class="text-xs mt-1 opacity-50">Stock en tiempo real · puede tardar unos segundos</p>
    </div>`;
  lucide.createIcons({ root: area });

  try {
    const r = await fetch(ROUTES.productos);
    const d = await r.json();
    if (!d.success) { toast(d.error || 'Error cargando productos', 'error', 'alert-circle'); return; }
    _currentProducts = d.products || [];

    // Categorías únicas para el datalist
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
          opt.value = name;
          dl.appendChild(opt);
        }
      }
    });

    // Restablecer área de resultados al estado inicial
    area.innerHTML = `
      <div class="flex flex-col items-center justify-center h-full text-gray-400 animar-entrada">
        <i data-lucide="package-search" class="w-16 h-16 opacity-50 mb-3 text-borde"></i>
        <p class="font-semibold text-texto opacity-70">Busca un producto para empezar</p>
        <p class="text-xs mt-1 opacity-40">${_currentProducts.length} productos disponibles</p>
      </div>`;
    lucide.createIcons({ root: area });

    toast(`${_currentProducts.length} productos cargados en tiempo real`, 'success', 'check-circle');
  } catch(e) {
    toast('Error conectando con Odoo', 'error', 'wifi-off');
  }
}

// ═══════════════════════════════════════════════════════
// CARGAR UBICACIONES REALES DESDE ODOO
// ═══════════════════════════════════════════════════════
async function loadUbicaciones() {
  const selDesktop = document.getElementById('locationSelect');
  const selMobile  = document.getElementById('mobileLocationSelect');

  selDesktop.innerHTML = '<option value="">Cargando almacenes...</option>';
  selMobile.innerHTML  = '<option value="">Cargando almacenes...</option>';

  try {
    const r = await fetch(ROUTES.ubicaciones);
    const d = await r.json();

    if (!d.success || !d.ubicaciones.length) {
      selDesktop.innerHTML = '<option value="">— Sin ubicaciones —</option>';
      selMobile.innerHTML  = '<option value="">— Sin ubicaciones —</option>';
      return;
    }

    _ubicaciones = d.ubicaciones;

    const optsHtml = '<option value="">— Selecciona destino —</option>' +
      d.ubicaciones.map(u => {
        const label = u.complete_name || u.name;
        return `<option value="${u.id}">${esc(label)}</option>`;
      }).join('');

    selDesktop.innerHTML = optsHtml;
    selMobile.innerHTML  = optsHtml;

    try {
      const saved = JSON.parse(localStorage.getItem('tecnigas_recepcion') || '{}');
      if (saved.loc) {
        selDesktop.value = saved.loc;
        selMobile.value  = saved.loc;
      }
    } catch(_) {}

  } catch(e) {
    selDesktop.innerHTML = '<option value="">— Error al cargar —</option>';
    selMobile.innerHTML  = '<option value="">— Error al cargar —</option>';
    toast('No se pudieron cargar las ubicaciones de Odoo', 'error', 'map-pin-off');
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
  const q       = document.getElementById('searchInput').value.trim().toLowerCase();
  const catName = document.getElementById('categInput').value.trim();
  const catId   = _catMap[catName] || null;
  const meta    = document.getElementById('resultsMeta');
  const area    = document.getElementById('resultsArea');

  if (!q && !catName) {
    area.innerHTML = `
      <div class="flex flex-col items-center justify-center h-full text-gray-400 animar-entrada">
        <i data-lucide="package-search" class="w-16 h-16 opacity-50 mb-3 text-borde"></i>
        <p class="font-semibold text-texto opacity-70">Busca un producto para empezar</p>
        <p class="text-xs mt-1 opacity-40">${_currentProducts.length} productos disponibles</p>
      </div>`;
    lucide.createIcons({ root: area }); meta.textContent = ''; return;
  }

  const results = _currentProducts.filter(p => {
    const matchQ   = !q || (p.name||'').toLowerCase().includes(q) || (p.default_code||'').toLowerCase().includes(q);
    const matchCat = !catId || (p.categ_id && p.categ_id[0] === catId);
    return matchQ && matchCat;
  });

  meta.textContent = `${results.length} resultado(s)`;

  if (!results.length) {
    area.innerHTML = `<div class="flex flex-col items-center justify-center p-14 text-gray-400 animar-entrada"><i data-lucide="search-x" class="w-12 h-12 mb-2 opacity-50"></i><p class="font-bold">Sin resultados</p></div>`;
    lucide.createIcons(); return;
  }

  area.innerHTML = `
    <div class="flex flex-col items-center justify-center h-full text-gray-400 py-16">
      <i data-lucide="loader-2" class="w-10 h-10 animate-spin text-primario mb-3"></i>
      <p class="font-semibold text-texto opacity-70">Cargando ${results.length} producto(s)...</p>
      <p class="text-xs mt-1 opacity-50">Preparando imágenes</p>
    </div>`;
  lucide.createIcons({ root: area });

  const ids      = results.map(p => p.id);
  const imageMap = await fetchAllImages(ids);

  renderGrid(results, imageMap);
}

// ═══════════════════════════════════════════════════════
// RENDER GRID (con imágenes ya disponibles)
// ═══════════════════════════════════════════════════════
function renderGrid(products, imageMap = {}) {
  const area = document.getElementById('resultsArea');
  if (!products.length) {
    area.innerHTML = `<div class="flex flex-col items-center justify-center p-14 text-gray-400 animar-entrada"><i data-lucide="search-x" class="w-12 h-12 mb-2 opacity-50"></i><p class="font-bold">Sin resultados</p></div>`;
    lucide.createIcons(); return;
  }

  const html = products.map(p => {
    const code  = p.default_code || 'N/A';
    const price = `S/ ${parseFloat(p.list_price||0).toFixed(2)}`;
    const stock = parseFloat(p.qty_available||0).toFixed(0);
    const b64   = imageMap[p.id];
    const imgHtml = b64
      ? `<img src="data:image/png;base64,${b64}" class="w-full h-full object-contain p-2" alt="${esc(p.name)}">`
      : `<i data-lucide="package" class="w-10 h-10 text-borde"></i>`;

    return `
    <div class="bg-superficie rounded-xl border border-borde shadow-card hover:shadow-elevada hover:-translate-y-1 overflow-hidden flex flex-col cursor-pointer btn-trans animar-entrada" onclick="openSimpleModal(${p.id})" id="card-${p.id}">
      <div class="h-28 bg-white dark:bg-slate-800 flex items-center justify-center overflow-hidden">
        ${imgHtml}
      </div>
      <div class="p-3 flex-1 flex flex-col">
        <div class="text-[10px] font-black text-primario tracking-widest uppercase mb-1">${code}</div>
        <div class="text-xs font-bold text-texto leading-snug flex-1">${esc(p.name)}</div>
        <div class="flex gap-2 mt-2">
          <span class="flex items-center gap-1 text-[10px] font-bold bg-cyan-50 dark:bg-cyan-900/20 text-cyan-700 dark:text-cyan-300 px-1.5 py-0.5 rounded-md border border-cyan-100 dark:border-cyan-800">
            <i data-lucide="warehouse" class="w-3 h-3"></i> ${stock}
          </span>
        </div>
      </div>
      <div class="p-3 bg-fondo/50 border-t border-borde flex items-center justify-between">
        <span class="font-bold text-sm">${price}</span>
        <button class="bg-blue-100 hover:bg-primario text-blue-700 hover:text-white dark:bg-blue-900/30 dark:text-blue-300 text-xs font-bold py-1.5 px-3 rounded-full btn-trans flex items-center gap-1">
          <i data-lucide="plus" class="w-3 h-3"></i> Añadir
        </button>
      </div>
    </div>`;
  }).join('');

  area.innerHTML = `<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">${html}</div>`;
  lucide.createIcons();
  initImageObserver(); // arrancar lazy loader tras renderizar
}

// ═══════════════════════════════════════════════════════
// MODAL SIMPLE
// ═══════════════════════════════════════════════════════
function openSimpleModal(prodId) {
  const prod = _currentProducts.find(p => p.id === prodId);
  if (!prod) return;

  document.getElementById('modalSimpleTitle').textContent = prod.name;
  document.getElementById('modalSimpleSub').textContent   = `Ref: ${prod.default_code||'N/A'} · S/ ${parseFloat(prod.list_price||0).toFixed(2)}`;
  document.getElementById('simpleQty').value     = 1;
  document.getElementById('simpleTickets').value = 1;
  document.getElementById('simpleCosto').value   = parseFloat(prod.standard_price||0).toFixed(2);

  openModal('modalSimple');

  document.getElementById('btnSimpleConfirm').onclick = () => {
    const qty     = parseInt(document.getElementById('simpleQty').value) || 1;
    const tickets = parseInt(document.getElementById('simpleTickets').value) || 1;
    const costo   = parseFloat(document.getElementById('simpleCosto').value) || 0;
    addRow({
      uid:             `p-${prod.id}-${Date.now()}`,
      producto_id:     prod.id,
      producto_nombre: prod.name,
      default_code:    prod.default_code || '',
      list_price:      prod.list_price || 0,
      uom_id:          Array.isArray(prod.uom_id) ? prod.uom_id[0] : (prod.uom_id || 1),
      costo,
      qty,
      tickets,
    });
    closeModal();
    toast('Producto añadido', 'success', 'check-circle');
  };
}

// ═══════════════════════════════════════════════════════
// CART
// ═══════════════════════════════════════════════════════
function saveCart() {
  localStorage.setItem('tecnigas_recepcion', JSON.stringify({
    rows: receptionRows,
    loc:  document.getElementById('locationSelect').value || ''
  }));
}

function loadCart() {
  try {
    const raw = localStorage.getItem('tecnigas_recepcion');
    if (!raw) return;
    const data = JSON.parse(raw);
    if (data && Array.isArray(data.rows)) {
      receptionRows = data.rows;
      renderCart();
    }
  } catch(e) {}
}

function addRow(row)    { receptionRows.push(row); renderCart(); }
function removeRow(uid) { receptionRows = receptionRows.filter(r => r.uid !== uid); renderCart(); }

function updateRow(uid, field, val) {
  const row = receptionRows.find(r => r.uid === uid);
  if (row) {
    if (field === 'tickets') {
      row[field] = parseInt(val) || 0;
    } else {
      row[field] = parseFloat(val) || 0;
    }
    saveCart();
  }
}

function renderCart() {
  const count = receptionRows.length;
  document.getElementById('totalCount').textContent = count;
  document.getElementById('cartCount').textContent  = `${count} ítem(s)`;
  document.getElementById('btnReceiveOdoo').disabled = count === 0;
  document.getElementById('btnPrintExcel').disabled  = count === 0;
  saveCart();

  const list = document.getElementById('cartList');
  if (!count) {
    list.innerHTML = `
      <div class="flex flex-col items-center justify-center text-gray-400 py-10 animar-entrada">
        <div class="p-4 bg-fondo rounded-full mb-3"><i data-lucide="shopping-cart" class="w-8 h-8 opacity-50 text-borde"></i></div>
        <p class="text-sm font-bold text-texto opacity-70">Carrito Vacío</p>
      </div>`;
    lucide.createIcons(); return;
  }

  list.innerHTML = receptionRows.map(r => `
<div class="bg-superficie rounded-xl border border-l-4 border-l-primario border-borde shadow-sm mb-3 overflow-hidden animar-entrada group" id="row-${r.uid}">
  <div class="p-3 flex items-start gap-2">
    <div class="flex-1 min-w-0">
      <div class="text-xs font-bold text-texto truncate">${esc(r.producto_nombre)}</div>
      <div class="text-[10px] font-medium text-gray-400 mt-1">${r.default_code||'S/N'}</div>
    </div>
    <button class="text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 p-1.5 rounded-lg btn-trans shrink-0 opacity-0 group-hover:opacity-100 focus:opacity-100" onclick="removeRow('${r.uid}')">
      <i data-lucide="trash-2" class="w-4 h-4"></i>
    </button>
  </div>
  <div class="bg-fondo/50 border-t border-borde px-3 py-2 grid grid-cols-2 gap-2">
    <div>
      <label class="text-[9px] font-bold text-gray-500 uppercase tracking-widest block mb-1">Cant.</label>
      <input type="number" min="1" value="${r.qty}" onchange="updateRow('${r.uid}','qty',this.value)" class="w-full bg-superficie border border-borde rounded-lg px-2 py-1.5 text-xs font-bold text-center btn-trans outline-none focus:border-primario"/>
    </div>
    <div>
      <label class="text-[9px] font-bold text-orange-500 uppercase tracking-widest block mb-1">Tickets</label>
      <input type="number" min="0" value="${r.tickets}" onchange="updateRow('${r.uid}','tickets',this.value)" class="w-full bg-orange-50 dark:bg-orange-900/20 text-orange-600 border border-orange-200 rounded-lg px-2 py-1.5 text-xs font-bold text-center btn-trans outline-none focus:border-orange-500"/>
    </div>
  </div>
</div>`).join('');
  lucide.createIcons();
}

// ═══════════════════════════════════════════════════════
// RECEPCIONAR → crear picking en Odoo + guardar en BD
// ═══════════════════════════════════════════════════════
document.getElementById('btnReceiveOdoo').addEventListener('click', async () => {
  if (!receptionRows.length) return;

  // Validar que haya ubicación destino seleccionada
  const locationDestId = parseInt(document.getElementById('locationSelect').value) || 0;
  if (!locationDestId) {
    const locWrapper = document.querySelector('.glass-header .hidden.md\\:flex');
    if (locWrapper) {
      locWrapper.classList.add('ring-2', 'ring-red-400', 'animar-shake');
      setTimeout(() => locWrapper.classList.remove('ring-2','ring-red-400','animar-shake'), 600);
    }
    toast('Selecciona el almacén de destino primero', 'error', 'map-pin');
    return;
  }

  const btn = document.getElementById('btnReceiveOdoo');
  btn.disabled = true;
  btn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Recepcionando en Odoo...';
  lucide.createIcons({ root: btn });

  const subtotal = receptionRows.reduce((s, r) => s + (r.qty * r.costo), 0);
  const igv      = subtotal * 0.18;
  const total    = subtotal + igv;

  const payload = {
    fecha:            new Date().toISOString().split('T')[0],
    proveedor_nombre: 'Sin proveedor',
    location_dest_id: locationDestId,
    subtotal:         subtotal.toFixed(2),
    igv:              igv.toFixed(2),
    total:            total.toFixed(2),
    usuario:          '{{ auth()->user()->name ?? "web" }}',
    items: receptionRows.map(r => ({
      producto_id:      r.producto_id,
      producto_nombre:  r.producto_nombre,
      default_code:     r.default_code || '',
      cantidad:         r.qty,
      tickets:          r.tickets || r.qty,
      costo:            r.costo,
      list_price:       r.list_price || 0,
      subtotal:         (r.qty * r.costo).toFixed(4),
      uom_id:           r.uom_id || 1,
    }))
  };

  try {
    const res = await fetch(ROUTES.store, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify(payload)
    });
    const d = await res.json();
    if (!d.success) {
      toast(d.error || 'Error al recepcionar', 'error', 'x-circle');
      return;
    }
    const pickingMsg = d.odoo_picking_id
      ? `Picking Odoo #${d.odoo_picking_id} · Recepción Local #${d.recepcion.id}`
      : `Recepción #${d.recepcion.id} guardada (sin picking Odoo)`;
    toast(pickingMsg, 'success', 'check-circle');
    receptionRows = [];
    renderCart();
  } catch(e) {
    toast('Error de conexión', 'error', 'wifi-off');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i data-lucide="warehouse" class="w-5 h-5"></i> Recepcionar en Odoo';
    lucide.createIcons({ root: btn });
  }
});

// ═══════════════════════════════════════════════════════
// EXPORTAR EXCEL PARA BARTENDER
// ═══════════════════════════════════════════════════════
document.getElementById('btnPrintExcel').addEventListener('click', async () => {
  if (!receptionRows.length) {
    toast('No hay productos en la lista', 'error', 'alert-circle');
    return;
  }

  const btn = document.getElementById('btnPrintExcel');
  btn.disabled = true;
  btn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Generando Excel...';
  lucide.createIcons({ root: btn });

  try {
    // Primero guardar la recepción para obtener IDs de items
    const locationDestId = parseInt(document.getElementById('locationSelect').value) || 0;
    if (!locationDestId) {
      toast('Selecciona el almacén de destino primero', 'error', 'map-pin');
      return;
    }

    const subtotal = receptionRows.reduce((s, r) => s + (r.qty * r.costo), 0);
    const igv      = subtotal * 0.18;
    const total    = subtotal + igv;

    const payload = {
      fecha:            new Date().toISOString().split('T')[0],
      proveedor_nombre: 'Sin proveedor',
      location_dest_id: locationDestId,
      subtotal:         subtotal.toFixed(2),
      igv:              igv.toFixed(2),
      total:            total.toFixed(2),
      usuario:          '{{ auth()->user()->name ?? "web" }}',
      items: receptionRows.map(r => ({
        producto_id:      r.producto_id,
        producto_nombre:  r.producto_nombre,
        default_code:     r.default_code || '',
        cantidad:         r.qty,
        tickets:          r.tickets || r.qty,
        costo:            r.costo,
        list_price:       r.list_price || 0,
        subtotal:         (r.qty * r.costo).toFixed(4),
        uom_id:           r.uom_id || 1,
      }))
    };

    // Guardar recepción
    const resStore = await fetch(ROUTES.store, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify(payload)
    });
    const dataStore = await resStore.json();

    if (!dataStore.success) {
      toast(dataStore.error || 'Error al guardar recepción', 'error', 'x-circle');
      return;
    }

    // Obtener IDs de los items
    const itemIds = dataStore.recepcion.items.map(i => i.id);

    // Exportar Excel — descarga directa sin popup (evita el bloqueador del navegador)
    const exportUrl = `/api/recepcion/export-bartender?ids=${itemIds.join(',')}`;
    const a = document.createElement('a');
    a.href = exportUrl;
    a.download = '';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

    toast(`Excel generado · Recepción #${dataStore.recepcion.id}`, 'success', 'file-spreadsheet');

    // NO limpiar carrito - el usuario debe poder recepcionar después en Odoo
    // receptionRows = [];
    // renderCart();

  } catch (e) {
    console.error(e);
    toast('Error al generar Excel', 'error', 'wifi-off');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i data-lucide="printer" class="w-5 h-5"></i> Exportar Etiquetas';
    lucide.createIcons({ root: btn });
  }
});

document.getElementById('btnClearAll').addEventListener('click', () => { receptionRows = []; renderCart(); });

// ═══════════════════════════════════════════════════════
// HISTORIAL
// ═══════════════════════════════════════════════════════
document.getElementById('btnHistory').addEventListener('click', () => {
  openOverlay('historyOverlay', 'historyPanel');
  loadHistory();
});
document.getElementById('historyClose').addEventListener('click', () => closeOverlay('historyOverlay', 'historyPanel'));
document.getElementById('histRefresh').addEventListener('click', loadHistory);
document.getElementById('historyOverlay').addEventListener('mousedown', e => { if (e.target.id === 'historyOverlay') closeOverlay('historyOverlay', 'historyPanel'); });

async function loadHistory() {
  const list = document.getElementById('historyList');
  list.innerHTML = `<div class="flex flex-col items-center justify-center p-16 text-gray-400"><i data-lucide="loader-2" class="w-8 h-8 animate-spin text-primario mb-3"></i><p class="text-sm">Cargando...</p></div>`;
  lucide.createIcons({ root: list });

  try {
    const r = await fetch(ROUTES.historial);
    const d = await r.json();
    if (!d.success) { list.innerHTML = `<p class="text-center p-10 text-red-500 font-bold">${esc(d.error)}</p>`; return; }

    const picks = d.recepciones || [];
    if (!picks.length) {
      list.innerHTML = `<div class="flex flex-col items-center p-16 text-gray-400"><i data-lucide="inbox" class="w-12 h-12 opacity-40 mb-3"></i><p class="font-bold">Sin recepciones</p></div>`;
      lucide.createIcons({ root: list }); return;
    }

    list.innerHTML = picks.map(p => {
      const hasOdoo = p.odoo_picking_id;
      return `
    <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-fondo/70 cursor-pointer border-b border-borde group btn-trans" onclick="openRecepcionDetail(${p.id})">
      <div class="w-9 h-9 rounded-xl bg-primario/10 text-primario flex items-center justify-center shrink-0">
        <i data-lucide="${hasOdoo ? 'package-check' : 'package'}" class="w-4 h-4"></i>
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
          <span class="text-sm font-black text-texto">Recepción #${p.id}</span>
          ${hasOdoo
            ? `<span class="text-[10px] font-black px-2 py-0.5 rounded-full border bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800">✔ Odoo #${p.odoo_picking_id}</span>`
            : `<span class="text-[10px] font-black px-2 py-0.5 rounded-full border bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800">⚠ Solo local</span>`
          }
        </div>
        <div class="flex items-center gap-2 mt-0.5">
          <span class="text-[11px] text-gray-400">${p.fecha}</span>
          <span class="text-[10px] text-gray-500">${esc(p.proveedor_nombre)}</span>
          <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-gray-500 dark:text-gray-400 px-1.5 py-0.5 rounded font-bold">S/ ${parseFloat(p.total).toFixed(2)}</span>
        </div>
      </div>
      <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400 group-hover:text-primario btn-trans shrink-0"></i>
    </div>`;
    }).join('');
    lucide.createIcons({ root: list });
  } catch(e) {
    list.innerHTML = `<p class="text-center p-10 text-red-500">Error: ${esc(e.message)}</p>`;
  }
}

// ═══════════════════════════════════════════════════════
// DETALLE RECEPCIÓN
// ═══════════════════════════════════════════════════════
async function openRecepcionDetail(id) {
  openOverlay('detailOverlay', 'detailPanel');
  document.getElementById('detailTitle').textContent = 'Cargando...';
  document.getElementById('detailMeta').textContent  = '';
  document.getElementById('detailBody').innerHTML    = `<div class="flex justify-center p-10"><i data-lucide="loader-2" class="w-8 h-8 animate-spin text-primario"></i></div>`;
  lucide.createIcons({ root: document.getElementById('detailBody') });

  try {
    const r = await fetch(ROUTES.show(id));
    const d = await r.json();
    if (!d.success) {
      document.getElementById('detailBody').innerHTML = `<p class="text-red-500 p-8 text-center font-bold">${esc(d.error)}</p>`; return;
    }
    const rec = d.recepcion;
    _histDetail = rec;

    document.getElementById('detailTitle').textContent = `Recepción #${rec.id}${rec.odoo_picking_id ? ' · Odoo #'+rec.odoo_picking_id : ''}`;
    document.getElementById('detailMeta').textContent  = `${rec.fecha} · ${rec.proveedor_nombre}${rec.usuario ? ' · por '+rec.usuario : ''}`;
    document.getElementById('detailFooterInfo').textContent = `${rec.items.length} producto(s) · S/ ${parseFloat(rec.total).toFixed(2)}`;
    document.getElementById('btnDeleteRecepcion').classList.remove('hidden');

    const items = rec.items || [];
    document.getElementById('detailBody').innerHTML = `
    <table class="w-full text-xs">
      <thead class="sticky top-0 bg-superficie z-10">
        <tr class="border-b-2 border-borde">
          <th class="text-left py-2 px-3 font-black text-gray-400 uppercase tracking-widest">Producto</th>
          <th class="text-center py-2 px-3 font-black text-gray-400 uppercase tracking-widest w-20">Cant.</th>
          <th class="text-right py-2 px-3 font-black text-gray-400 uppercase tracking-widest">Costo</th>
          <th class="text-right py-2 px-3 font-black text-gray-400 uppercase tracking-widest">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        ${items.map(l => `
        <tr class="border-b border-borde hover:bg-fondo/50">
          <td class="py-2 px-3 font-medium text-texto leading-tight max-w-[200px]"><div class="truncate">${esc(l.producto_nombre)}</div></td>
          <td class="py-2 px-3 text-center font-bold">${parseFloat(l.cantidad).toFixed(2)}</td>
          <td class="py-2 px-3 text-right font-bold">S/ ${parseFloat(l.costo).toFixed(2)}</td>
          <td class="py-2 px-3 text-right font-bold text-primario">S/ ${parseFloat(l.subtotal).toFixed(2)}</td>
        </tr>`).join('')}
      </tbody>
    </table>`;
    lucide.createIcons({ root: document.getElementById('detailBody') });
  } catch(e) {
    document.getElementById('detailBody').innerHTML = `<p class="text-red-500 p-8 text-center">${esc(e.message)}</p>`;
  }
}

document.getElementById('detailClose').addEventListener('click',  () => closeOverlay('detailOverlay', 'detailPanel'));
document.getElementById('detailClose2').addEventListener('click', () => closeOverlay('detailOverlay', 'detailPanel'));
document.getElementById('detailOverlay').addEventListener('mousedown', e => { if (e.target.id === 'detailOverlay') closeOverlay('detailOverlay', 'detailPanel'); });

document.getElementById('btnDeleteRecepcion').addEventListener('click', async () => {
  if (!_histDetail || !confirm('¿Eliminar esta recepción?')) return;
  const res = await fetch(ROUTES.destroy(_histDetail.id), {
    method: 'DELETE',
    headers: { 'X-CSRF-TOKEN': CSRF }
  });
  const d = await res.json();
  if (d.success) { toast('Recepción eliminada', 'success', 'check-circle'); closeOverlay('detailOverlay', 'detailPanel'); loadHistory(); }
  else toast(d.error, 'error', 'x-circle');
});

// ═══════════════════════════════════════════════════════
// MODAL HELPERS
// ═══════════════════════════════════════════════════════
function openModal(id) {
  const over = document.getElementById('modalOverlay');
  const modal = document.getElementById(id);
  over.classList.remove('hidden'); over.classList.add('flex');
  modal.classList.remove('hidden');
  requestAnimationFrame(() => { over.classList.remove('opacity-0'); modal.classList.remove('scale-95'); });
}
function closeModal() {
  const over = document.getElementById('modalOverlay');
  over.classList.add('opacity-0');
  document.querySelectorAll('#modalOverlay > div').forEach(m => m.classList.add('scale-95'));
  setTimeout(() => { over.classList.add('hidden'); over.classList.remove('flex'); document.querySelectorAll('#modalOverlay > div').forEach(m => m.classList.add('hidden')); }, 200);
}
function openOverlay(overlayId, panelId) {
  const ov = document.getElementById(overlayId);
  ov.classList.remove('hidden'); ov.classList.add('flex');
  document.getElementById(panelId).classList.remove('scale-95');
  requestAnimationFrame(() => ov.classList.remove('opacity-0'));
  lucide.createIcons({ root: ov });
}
function closeOverlay(overlayId, panelId) {
  const ov = document.getElementById(overlayId);
  ov.classList.add('opacity-0');
  document.getElementById(panelId).classList.add('scale-95');
  setTimeout(() => { ov.classList.add('hidden'); ov.classList.remove('flex'); }, 200);
}

document.querySelectorAll('.modal-close').forEach(btn => btn.addEventListener('click', closeModal));
document.getElementById('modalOverlay').addEventListener('mousedown', e => { if (e.target.id === 'modalOverlay') closeModal(); });

// ═══════════════════════════════════════════════════════
// EVENTOS BÚSQUEDA
// ═══════════════════════════════════════════════════════
document.getElementById('btnSearch').addEventListener('click', doSearch);
document.getElementById('searchInput').addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });
document.getElementById('categInput').addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });
document.getElementById('btnClear').addEventListener('click', () => {
  document.getElementById('searchInput').value = '';
  document.getElementById('categInput').value  = '';
  document.getElementById('resultsMeta').textContent = '';
  document.getElementById('resultsArea').innerHTML = `
    <div class="flex flex-col items-center justify-center h-full text-gray-400 animar-entrada">
      <i data-lucide="package-search" class="w-16 h-16 opacity-50 mb-3 text-borde"></i>
      <p class="font-semibold text-texto opacity-70">Busca un producto para empezar</p>
    </div>`;
  lucide.createIcons();
});
document.getElementById('btnRefresh').addEventListener('click', () => { loadProductos(); });

document.addEventListener('keydown', e => {
  if (e.key !== 'Escape') return;
  closeModal();
  closeOverlay('detailOverlay', 'detailPanel');
  closeOverlay('historyOverlay', 'historyPanel');
});

// ═══════════════════════════════════════════════════════
// TOAST & ESC
// ═══════════════════════════════════════════════════════
function toast(msg, type = 'info', icon = 'info') {
  const c  = document.getElementById('toastContainer');
  const el = document.createElement('div');
  const bg = type === 'success' ? 'bg-green-600 border-green-500' : type === 'error' ? 'bg-red-600 border-red-500' : 'bg-slate-800 border-slate-700';
  el.className = `flex items-center gap-2 px-4 py-3 rounded-full text-white text-sm font-bold shadow-elevada animar-entrada border ${bg} backdrop-blur-md bg-opacity-95`;
  el.innerHTML = `<i data-lucide="${icon}" class="w-4 h-4"></i> ${esc(msg)}`;
  c.appendChild(el);
  lucide.createIcons({ root: el });
  setTimeout(() => { el.style.opacity = '0'; el.style.transform = 'translateY(10px)'; el.style.transition = 'all 0.3s'; setTimeout(() => el.remove(), 300); }, 3000);
}
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>
</body>
</html>
