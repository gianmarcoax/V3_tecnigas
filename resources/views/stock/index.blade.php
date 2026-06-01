<x-app-layout>
    {{-- CSS personalizado para micro-animaciones, glassmorphism y transiciones suaves --}}
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .active-category-pill {
            background: #2a3f54 !important;
            color: #ffffff !important;
        }
        .active-category-pill span {
            background: #3b82f6 !important;
            color: #ffffff !important;
        }
        .product-image-container {
            position: relative;
            background: #f1f5f9;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px -8px rgba(0,0,0,0.08);
            border-color: #cbd5e1;
        }
        .copy-tooltip {
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.2s, visibility 0.2s;
        }
        .copy-trigger:hover .copy-tooltip {
            visibility: visible;
            opacity: 1;
        }
        .grid-item-fade {
            animation: fadeIn 0.4s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.97); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>

    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto" x-data="{ sidebarOpen: false }">

        {{-- Cabecera y Títulos --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#2a3f54] text-white flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4"/>
                        </svg>
                    </div>
                    Consulta de Stock
                </h1>
                <p class="text-gray-500 mt-1">Busca y visualiza la disponibilidad de mercancía en tiempo real en Tienda y Almacén</p>
            </div>
            
            {{-- Acciones de Cabecera (Sincronización) --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-xs text-gray-400">Última Sincronización</p>
                    <p class="text-sm font-semibold text-gray-600" id="sync-time-indicator">Cargando...</p>
                </div>
                <button onclick="refrescarStock()" class="flex items-center gap-2 bg-[#2a3f54] hover:bg-[#1e2f3f] text-white px-4 py-2.5 rounded-xl text-sm font-semibold shadow-sm transition group" id="btn-sync">
                    <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="icon-sync">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17"/>
                    </svg>
                    <span>Actualizar Stock</span>
                </button>
            </div>
        </div>

        {{-- Banner de Contingencia / Offline --}}
        <div class="hidden mb-6 bg-amber-50 border-2 border-amber-200 rounded-2xl p-4 flex gap-3 shadow-sm animate-pulse" id="offline-banner">
            <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-amber-800 text-sm">Modo de Contingencia Local</h4>
                <p class="text-xs text-amber-600 mt-0.5">Odoo no está accesible temporalmente. Se muestran existencias locales simuladas para garantizar la operatividad de ventas.</p>
            </div>
        </div>

        {{-- Layout Principal --}}
        <div>
            {{-- Catálogo y Buscador --}}
            <div class="space-y-6">

                {{-- Panel Superior: Buscador y Filtros Rápidos --}}
                <div class="glass-card rounded-2xl p-5 shadow-sm space-y-4">
                    
                    {{-- Fila del Buscador, Categoría y Selector de Vista --}}
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" id="search-input" oninput="onSearchInput(this.value)" placeholder="Buscar por nombre, código de barras o referencia..." class="w-full pl-11 pr-10 py-3 rounded-xl border border-gray-200 focus:border-[#2a3f54] focus:ring focus:ring-[#2a3f54]/20 text-sm transition placeholder-gray-400">
                            <button onclick="clearSearch()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 hidden" id="btn-clear-search">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        
                        {{-- Filtro de Categoría (Datalist) --}}
                        <div class="relative flex-1 max-w-[300px] min-w-[200px]">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"></path>
                                </svg>
                            </div>
                            <input id="categInput" list="categList" onchange="onCategorySelect(this.value)" class="w-full pl-11 pr-3 py-3 rounded-xl border border-gray-200 focus:border-[#2a3f54] focus:ring focus:ring-[#2a3f54]/20 text-sm transition placeholder-gray-400 bg-white" placeholder="Buscar categoría..." autocomplete="off">
                            <datalist id="categList"></datalist>
                        </div>

                        {{-- Selector de Vista (Grid/List) --}}
                        <div class="flex bg-gray-100 p-1 rounded-xl shrink-0 self-start sm:self-auto gap-0.5">
                            <button onclick="changeView('grid')" class="p-2.5 rounded-lg transition" id="btn-view-grid">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                            </button>
                            <button onclick="changeView('list')" class="p-2.5 rounded-lg transition" id="btn-view-list">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Filtros rápidos en píldoras --}}
                    <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3 text-xs">
                        <span class="text-gray-400 uppercase font-bold tracking-wider mr-2 shrink-0">Filtrar por:</span>
                        <button onclick="changeFilter('todos')" class="px-3.5 py-1.5 rounded-full font-semibold transition bg-gray-100 text-gray-600 hover:bg-gray-200" id="filter-todos">Todos</button>
                        <button onclick="changeFilter('con_stock')" class="px-3.5 py-1.5 rounded-full font-semibold transition bg-gray-100 text-gray-600 hover:bg-gray-200" id="filter-con_stock">Con Stock</button>
                        <button onclick="changeFilter('critico')" class="px-3.5 py-1.5 rounded-full font-semibold transition bg-gray-100 text-gray-600 hover:bg-gray-200" id="filter-critico">Stock Crítico</button>
                        <button onclick="changeFilter('sin_stock')" class="px-3.5 py-1.5 rounded-full font-semibold transition bg-gray-100 text-gray-600 hover:bg-gray-200" id="filter-sin_stock">Sin Stock</button>
                    </div>
                </div>

                {{-- Listado de Productos --}}
                <div id="products-catalog-container">
                    {{-- Skeletons de Carga --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="products-skeleton">
                        <template x-for="i in 6" :key="i">
                            <div class="bg-white rounded-2xl border-2 border-gray-200 p-4 space-y-4 animate-pulse">
                                <div class="w-full h-40 bg-gray-200 rounded-xl"></div>
                                <div class="h-5 bg-gray-200 rounded w-3/4"></div>
                                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                <div class="grid grid-cols-2 gap-3 pt-2">
                                    <div class="h-10 bg-gray-200 rounded-xl"></div>
                                    <div class="h-10 bg-gray-200 rounded-xl"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Catálogo en Grid (Cargado Asíncronamente) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden" id="products-grid"></div>

                    {{-- Catálogo en Lista (Cargado Asíncronamente) --}}
                    <div class="flex flex-col gap-4 hidden" id="products-list"></div>

                    {{-- Empty State (Sin Resultados) --}}
                    <div class="hidden glass-card rounded-2xl p-12 text-center" id="empty-state">
                        <div class="w-16 h-16 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-800 text-lg">No se encontraron productos</h3>
                        <p class="text-sm text-gray-500 mt-1">Prueba a modificar los filtros o escribir un término de búsqueda diferente.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Script JavaScript Principal - SPA Optimizada (3 endpoints) --}}
    <script>
        // ─── Estado Global (3 capas separadas) ────────────────────────────────
        let catalogoCompleto   = [];   // datos estáticos del catálogo (nombre, precio, código…)
        let originalCategories = [];   // lista de categorías
        let stockActual        = {};   // { product_id: { almacen_qty, tienda_qty, qty_available, locations } }
        let imagenesCache      = {};   // { product_id: base64 | null }
        let resultadosFiltrados = [];  // productos filtrados actualmente visibles
        const PAGE_SIZE = 100;         // máx cards en el DOM al mismo tiempo

        let activeCategory  = null;
        let searchQuery     = '';
        let activeFilter    = 'todos'; // todos, con_stock, critico, sin_stock
        let currentViewMode = localStorage.getItem('stock_view_mode') || 'grid';
        let debounceTimer;

        // Rol del usuario actual para restricciones de negocio
        const userRole  = "{{ auth()->user()->role }}";
        const isAdmin   = {{ auth()->user()->isAdmin()   ? 'true' : 'false' }};
        const isAlmacen = {{ auth()->user()->isAlmacen() ? 'true' : 'false' }};

        document.addEventListener('DOMContentLoaded', () => {
            setViewStyle(currentViewMode);
            inicializar();
        });

        // Buscador con debounce (elimina lag en 4000+ productos)
        document.getElementById('search-input').addEventListener('input', (e) => {
            searchQuery = e.target.value;
            const btnClear = document.getElementById('btn-clear-search');
            btnClear.classList.toggle('hidden', !searchQuery.trim());
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => aplicarFiltroYRender(), 300);
        });

        // =========================================================
        // INICIALIZACIÓN — CARGA EN 3 FASES
        // =========================================================
        async function inicializar() {
            showLoading(true);

            // Fase 1: catálogo estático (caché 24h — muy rápido en visitas siguientes)
            try {
                const resCat = await fetch('/api/stock/catalogo');
                const dataCat = await resCat.json();

                if (!dataCat.success) {
                    showToast('error', 'Error al cargar catálogo.');
                    showLoading(false);
                    return;
                }

                catalogoCompleto   = dataCat.products   || [];
                originalCategories = dataCat.categories || [];
            } catch (e) {
                showToast('error', 'Sin conexión al servidor.');
                showLoading(false);
                return;
            }

            // Fase 2: cantidades de stock (caché 3 min)
            await actualizarCantidades(false);

            // Renderizar con stock ya integrado
            renderCategoriesList();
            aplicarFiltroYRender();
            showLoading(false);

            // Fase 3: imágenes en background (no bloquea la UI)
            cargarImagenesBackground();
        }

        /**
         * Descarga solo cantidades desde /api/stock/cantidades
         * Si forceRefresh=true, invalida caché servidor (3 min).
         */
        async function actualizarCantidades(forceRefresh = false) {
            try {
                const res  = await fetch(`/api/stock/cantidades${forceRefresh ? '?refresh=true' : ''}`);
                const data = await res.json();
                if (data.success) {
                    stockActual = data.stock || {};
                    document.getElementById('sync-time-indicator').innerText = data.cached_at || '--:--';
                    const offlineBanner = document.getElementById('offline-banner');
                    offlineBanner.classList.toggle('hidden', !data.offline);
                }
            } catch (e) {
                console.warn('No se pudo actualizar stock:', e);
            }
        }

        /**
         * Botón "Actualizar Stock": solo recarga cantidades (sin re-bajar catálogo ni imágenes).
         * Actualiza valores en las cards ya visibles sin re-renderizar todo.
         */
        async function refrescarStock() {
            const btn  = document.getElementById('btn-sync');
            const icon = document.getElementById('icon-sync');
            btn.disabled = true;
            icon.classList.add('animate-spin');

            await actualizarCantidades(true);
            // Actualizar qtys en DOM sin re-render completo
            actualizarQtysEnDOM();

            btn.disabled = false;
            icon.classList.remove('animate-spin');
            showToast('success', 'Stock actualizado correctamente.');
        }

        /**
         * Actualiza solo los valores de cantidad en las cards ya renderizadas.
         * Evita re-renderizar 100 cards cuando solo cambian los números.
         */
        function actualizarQtysEnDOM() {
            document.querySelectorAll('.producto-card[data-product-id]').forEach(card => {
                const id    = parseInt(card.dataset.productId);
                const stock = stockActual[id];
                if (!stock) return;

                const almacenEl = card.querySelector('[data-qty="almacen"]');
                const tiendaEl  = card.querySelector('[data-qty="tienda"]');
                const totalEl   = card.querySelector('[data-qty="total"]');
                if (almacenEl) almacenEl.textContent = stock.almacen_qty  + ' und';
                if (tiendaEl)  tiendaEl.textContent  = stock.tienda_qty   + ' und';
                if (totalEl)   totalEl.textContent   = stock.qty_available + ' und';
            });
        }

        function showLoading(isLoading) {
            const skeleton = document.getElementById('products-skeleton');
            const grid     = document.getElementById('products-grid');
            const list     = document.getElementById('products-list');
            const empty    = document.getElementById('empty-state');

            if (isLoading) {
                skeleton.classList.remove('hidden');
                grid.classList.add('hidden');
                list.classList.add('hidden');
                empty.classList.add('hidden');
            } else {
                skeleton.classList.add('hidden');
                setViewStyle(currentViewMode);
            }
        }

        // =========================================================
        // RENDERS DE COMPONENTES
        // =========================================================

        function renderCategoriesList() {
            const datalist = document.getElementById('categList');
            if (!datalist) return;
            datalist.innerHTML = '<option value="Todas"></option>';

            const countsMap = {};
            catalogoCompleto.forEach(p => {
                countsMap[p.categ_id] = (countsMap[p.categ_id] || 0) + 1;
            });

            originalCategories.forEach(cat => {
                const count = countsMap[cat.id] || 0;
                if (count === 0) return;

                const opt = document.createElement('option');
                opt.value = cat.name;
                datalist.appendChild(opt);
            });
        }

        /**
         * Aplica todos los filtros activos sobre catalogoCompleto y renderiza.
         * Limita el DOM a PAGE_SIZE cards para no saturar el navegador.
         */
        function aplicarFiltroYRender() {
            const q = searchQuery.trim().toLowerCase();

            let filtered = catalogoCompleto.filter(p => {
                // Filtro de categoría
                if (activeCategory !== null && p.categ_id !== activeCategory) return false;

                // Leer stock del mapa separado
                const stock = stockActual[p.id] || { almacen_qty: 0, tienda_qty: 0, qty_available: 0 };

                // Filtros rápidos
                if (activeFilter === 'con_stock' && stock.qty_available <= 0) return false;
                if (activeFilter === 'sin_stock' && stock.qty_available > 0)  return false;
                if (activeFilter === 'critico') {
                    const isCritico = stock.almacen_qty <= 0 || stock.tienda_qty <= 0 || stock.qty_available <= 5;
                    if (!isCritico) return false;
                }

                // Búsqueda (4 niveles de score)
                if (q) {
                    const score = matchProductScore(p, q);
                    if (score === 0) return false;
                    p._search_score = score;
                } else {
                    p._search_score = 0;
                }

                return true;
            });

            // Ordenar
            if (q) {
                filtered.sort((a, b) => b._search_score - a._search_score);
            } else {
                filtered.sort((a, b) => a.name.localeCompare(b.name));
            }

            resultadosFiltrados = filtered;

            const gridContainer = document.getElementById('products-grid');
            const listContainer = document.getElementById('products-list');
            const emptyState    = document.getElementById('empty-state');

            gridContainer.innerHTML = '';
            listContainer.innerHTML = '';

            if (filtered.length === 0) {
                gridContainer.classList.add('hidden');
                listContainer.classList.add('hidden');
                emptyState.classList.remove('hidden');
                actualizarContador(0, 0);
                return;
            }

            emptyState.classList.add('hidden');

            // Renderizar solo PAGE_SIZE cards (no saturar DOM con 4000+ nodos)
            const pagina = filtered.slice(0, PAGE_SIZE);
            pagina.forEach(p => {
                gridContainer.appendChild(createGridCard(p));
                listContainer.appendChild(createListRow(p));
            });

            actualizarContador(pagina.length, filtered.length);
            setViewStyle(currentViewMode);
        }

        // Helper: muestra "Mostrando X de Y" en el header
        function actualizarContador(visible, total) {
            let el = document.getElementById('contador-resultados');
            if (!el) {
                el = document.createElement('span');
                el.id = 'contador-resultados';
                el.className = 'text-xs text-gray-400 ml-2';
                const h1 = document.querySelector('h1');
                if (h1) h1.appendChild(el);
            }
            if (total > visible) {
                el.textContent = `— Mostrando ${visible} de ${total} productos`;
            } else {
                el.textContent = total > 0 ? `— ${total} productos` : '';
            }
        }

        // =========================================================
        // CREADORES DE COMPONENTES DOM DE PRODUCTOS
        // =========================================================

        function createGridCard(p) {
            const stock = stockActual[p.id] || { almacen_qty: 0, tienda_qty: 0, qty_available: 0, locations: [] };
            const img   = imagenesCache[p.id];

            const card = document.createElement('div');
            card.className = 'bg-white rounded-2xl border-2 border-gray-200 overflow-hidden flex flex-col product-card transition-all duration-300 grid-item-fade relative producto-card';
            card.dataset.productId = p.id;

            const tagCritico = (stock.almacen_qty <= 0 || stock.tienda_qty <= 0 || stock.qty_available <= 5)
                ? `<span class="absolute top-3 left-3 bg-red-500 text-white text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full z-10 animate-pulse">Crítico</span>`
                : '';

            const showCostInfo = (isAdmin || isAlmacen)
                ? `<p class="text-xs text-gray-400 mt-0.5">Costo: S/ ${p.standard_price.toFixed(2)}</p>`
                : '';

            const imgContent = img
                ? `<img src="data:image/png;base64,${img}" class="w-full h-full object-contain" alt="${p.name}">`
                : `<div class="spinner absolute w-6 h-6 border-3 border-gray-300 border-t-[#2a3f54] rounded-full animate-spin"></div>
                   <img data-product-id="${p.id}" class="w-full h-full object-contain opacity-0 transition-opacity duration-300 lazy-stock-img" src="" alt="${p.name}">`;

            const qtyColorTotal  = stock.qty_available > 5 ? 'text-gray-800' : (stock.qty_available > 0 ? 'text-orange-500' : 'text-red-500');
            const qtyColorAlmacen = stock.almacen_qty > 0 ? 'text-slate-800' : 'text-red-500';
            const qtyColorTienda  = stock.tienda_qty  > 0 ? 'text-blue-700'  : 'text-red-500';
            const warnTienda = stock.tienda_qty <= 0
                ? `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`
                : '';

            card.innerHTML = `
                ${tagCritico}
                <div class="product-image-container h-44 shrink-0">${imgContent}</div>
                <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-full uppercase tracking-wide inline-block">${p.categ_name}</span>
                        <h4 class="font-bold text-gray-800 text-sm leading-snug line-clamp-2" title="${p.name}">${p.name}</h4>
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            ${p.default_code ? createCopyableBadge('Ref', p.default_code) : ''}
                            ${p.barcode ? createCopyableBadge('EAN', p.barcode) : ''}
                        </div>
                    </div>
                    <div class="border-t border-gray-100 pt-3">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-xs text-gray-400 font-semibold">Precio Venta</p>
                                <p class="text-lg font-bold text-[#2a3f54]">S/ ${p.list_price.toFixed(2)}</p>
                                ${showCostInfo}
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-400 font-semibold">Total Stock</p>
                                <p class="text-lg font-bold ${qtyColorTotal}" data-qty="total">${stock.qty_available} und</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-3 text-xs pt-1.5 border-t border-dashed border-gray-100">
                            <div class="bg-slate-50 border border-slate-200/60 p-2 rounded-xl flex flex-col justify-center">
                                <span class="text-gray-400 font-semibold text-[10px] uppercase">Almacén</span>
                                <span class="text-sm font-bold ${qtyColorAlmacen}" data-qty="almacen">${stock.almacen_qty} und</span>
                            </div>
                            <div class="bg-blue-50/50 border border-blue-200/40 p-2 rounded-xl flex flex-col justify-center">
                                <span class="text-gray-400 font-semibold text-[10px] uppercase">Tienda POS</span>
                                <span class="text-sm font-bold flex items-center gap-0.5 ${qtyColorTienda}" data-qty="tienda">${warnTienda}${stock.tienda_qty} und</span>
                            </div>
                        </div>
                        <button onclick="toggleDetails(this, ${p.id})" class="w-full text-center text-xs text-gray-500 hover:text-[#2a3f54] font-semibold mt-3 pt-2 border-t border-gray-50 flex items-center justify-center gap-1">
                            <span>Ver ubicaciones detalladas</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="hidden mt-3 p-3 bg-gray-50 rounded-xl text-xs space-y-1.5 border border-gray-100" id="details-${p.id}">
                            <p class="font-bold text-gray-700 border-b border-gray-200 pb-1 flex justify-between"><span>Ubicaciones Odoo</span><span>Cantidad</span></p>
                            ${renderDetailLocations(stock.locations)}
                        </div>
                    </div>
                </div>
            `;
            return card;
        }

        function createListRow(p) {
            const stock = stockActual[p.id] || { almacen_qty: 0, tienda_qty: 0, qty_available: 0, locations: [] };
            const img   = imagenesCache[p.id];

            const row = document.createElement('div');
            row.className = 'bg-white rounded-2xl border-2 border-gray-200 overflow-hidden flex flex-col md:flex-row items-stretch md:items-center p-4 gap-4 product-card transition-all duration-300 relative grid-item-fade producto-card';
            row.dataset.productId = p.id;

            const showCostInfo = (isAdmin || isAlmacen)
                ? `<span class="text-xs text-gray-400 block">Costo: S/ ${p.standard_price.toFixed(2)}</span>`
                : '';

            const imgContent = img
                ? `<img src="data:image/png;base64,${img}" class="w-full h-full object-contain" alt="${p.name}">`
                : `<div class="spinner absolute w-5 h-5 border-2 border-gray-300 border-t-[#2a3f54] rounded-full animate-spin"></div>
                   <img data-product-id="${p.id}" class="w-full h-full object-contain opacity-0 transition-opacity duration-300 lazy-stock-img" src="" alt="${p.name}">`;

            const qtyColorTotal   = stock.qty_available > 5 ? 'text-gray-800' : (stock.qty_available > 0 ? 'text-orange-500' : 'text-red-500');
            const qtyColorAlmacen = stock.almacen_qty > 0 ? 'text-slate-800' : 'text-red-500';
            const qtyColorTienda  = stock.tienda_qty  > 0 ? 'text-blue-700'  : 'text-red-500';

            row.innerHTML = `
                <div class="product-image-container w-24 h-24 rounded-xl border border-gray-100 shrink-0 self-center">${imgContent}</div>
                <div class="flex-1 min-w-0 space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[9px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full uppercase tracking-wide shrink-0">${p.categ_name}</span>
                        <div class="flex gap-1.5">
                            ${p.default_code ? createCopyableBadge('Ref', p.default_code) : ''}
                            ${p.barcode ? createCopyableBadge('EAN', p.barcode) : ''}
                        </div>
                    </div>
                    <h4 class="font-bold text-gray-800 text-sm leading-snug truncate" title="${p.name}">${p.name}</h4>
                    <div class="flex gap-3 text-xs text-gray-500 pt-1">
                        <span>Almacén: <strong class="${qtyColorAlmacen}" data-qty="almacen">${stock.almacen_qty} und</strong></span>
                        <span>Tienda: <strong class="${qtyColorTienda}" data-qty="tienda">${stock.tienda_qty} und</strong></span>
                    </div>
                </div>
                <div class="flex items-center justify-between md:justify-end gap-6 md:gap-8 pt-3 md:pt-0 border-t md:border-t-0 border-gray-100 shrink-0">
                    <div class="text-left md:text-right">
                        <span class="text-xs text-gray-400 font-semibold block leading-none">Precio Venta</span>
                        <strong class="text-lg font-bold text-[#2a3f54] block mt-1">S/ ${p.list_price.toFixed(2)}</strong>
                        ${showCostInfo}
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-gray-400 font-semibold block leading-none">Total Stock</span>
                        <strong class="text-lg font-bold block mt-1 ${qtyColorTotal}" data-qty="total">${stock.qty_available} und</strong>
                        <button onclick="toggleDetails(this, ${p.id}, true)" class="text-xs text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-0.5 justify-end mt-1">
                            <span>Ubicaciones</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                </div>
                <div class="hidden w-full md:absolute md:top-full md:left-0 md:z-10 mt-2 p-3 bg-gray-50 rounded-xl text-xs space-y-1.5 border border-gray-100 shadow-md" id="details-list-${p.id}">
                    <p class="font-bold text-gray-700 border-b border-gray-200 pb-1 flex justify-between"><span>Ubicaciones de Stock Odoo 18</span><span>Cantidad Física</span></p>
                    ${renderDetailLocations(stock.locations)}
                </div>
            `;
            return row;
        }

        // Dibuja la lista detallada de quants de ubicación
        function renderDetailLocations(locations) {
            if (!locations || locations.length === 0) {
                return `<p class="text-gray-400 italic text-[11px]">No hay stock registrado en ubicaciones internas.</p>`;
            }
            return locations.map(loc => `
                <div class="flex justify-between items-center text-gray-600 py-0.5">
                    <span>${loc.name}</span>
                    <strong class="font-semibold text-slate-800">${loc.qty} und</strong>
                </div>
            `).join('');
        }

        // Genera un badge copiable al portapapeles con feedback visual
        function createCopyableBadge(label, text) {
            return `
                <button onclick="copyToClipboard('${text}', this)" class="copy-trigger relative flex items-center gap-1 bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200 hover:text-gray-700 px-2 py-0.5 rounded-lg text-[10px] font-semibold transition">
                    <span class="uppercase text-[9px] text-gray-400">${label}:</span>
                    <span>${text}</span>
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    
                    {{-- Tooltip emergente --}}
                    <span class="copy-tooltip absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1.5 bg-[#2a3f54] text-white text-[9px] font-bold py-1 px-2.5 rounded-md shadow-lg whitespace-nowrap z-30">Copiar</span>
                </button>
            `;
        }

        // =========================================================
        // ACCIONES DE FILTRO E INTERFACES
        // =========================================================

        function onCategorySelect(val) {
            if (!val || val === 'Todas' || val === 'All') {
                activeCategory = null;
            } else {
                const cat = originalCategories.find(c => c.name === val);
                activeCategory = cat ? cat.id : null;
            }
            aplicarFiltroYRender();
        }

        function changeFilter(filterName) {
            activeFilter = filterName;
            const filters = ['todos', 'con_stock', 'critico', 'sin_stock'];
            filters.forEach(f => {
                const btn = document.getElementById('filter-' + f);
                if (f === filterName) {
                    btn.className = 'px-3.5 py-1.5 rounded-full font-bold transition bg-[#2a3f54] text-white shadow-sm';
                } else {
                    btn.className = 'px-3.5 py-1.5 rounded-full font-semibold transition bg-gray-100 text-gray-600 hover:bg-gray-200';
                }
            });
            aplicarFiltroYRender();
        }

        function onSearchInput(val) {
            // Fallback para el atributo oninput= en el HTML (el listener con debounce es el principal)
            searchQuery = val;
            const btnClear = document.getElementById('btn-clear-search');
            btnClear.classList.toggle('hidden', !val.trim());
        }

        function clearSearch() {
            const input = document.getElementById('search-input');
            input.value = '';
            searchQuery  = '';
            document.getElementById('btn-clear-search').classList.add('hidden');
            clearTimeout(debounceTimer);
            aplicarFiltroYRender();
        }

        function changeView(mode) {
            currentViewMode = mode;
            localStorage.setItem('stock_view_mode', mode);
            setViewStyle(mode);
        }

        function setViewStyle(mode) {
            const btnGrid = document.getElementById('btn-view-grid');
            const btnList = document.getElementById('btn-view-list');
            const gridContainer = document.getElementById('products-grid');
            const listContainer = document.getElementById('products-list');

            if (mode === 'grid') {
                btnGrid.className = 'p-2.5 rounded-lg transition bg-[#2a3f54] text-white shadow-sm';
                btnList.className = 'p-2.5 rounded-lg transition text-gray-500 hover:bg-gray-200';
                
                if (catalogoCompleto.length > 0) {
                    gridContainer.classList.remove('hidden');
                    listContainer.classList.add('hidden');
                }
            } else {
                btnList.className = 'p-2.5 rounded-lg transition bg-[#2a3f54] text-white shadow-sm';
                btnGrid.className = 'p-2.5 rounded-lg transition text-gray-500 hover:bg-gray-200';
                
                if (catalogoCompleto.length > 0) {
                    listContainer.classList.remove('hidden');
                    gridContainer.classList.add('hidden');
                }
            }
        }

        // Expande o colapsa el panel de ubicaciones detalladas
        function toggleDetails(btn, pId, isList = false) {
            const panelId = isList ? 'details-list-' + pId : 'details-' + pId;
            const panel = document.getElementById(panelId);
            const svg = btn.querySelector('svg');

            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                svg.style.transform = 'rotate(180deg)';
            } else {
                panel.classList.add('hidden');
                svg.style.transform = 'rotate(0deg)';
            }
        }

        // Copia texto al portapapeles y da feedback visual en el tooltip
        function copyToClipboard(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const tooltip = btn.querySelector('.copy-tooltip');
                tooltip.innerText = 'Copiado!';
                tooltip.style.background = '#10b981'; // color verde esmeralda
                setTimeout(() => {
                    tooltip.innerText = 'Copiar';
                    tooltip.style.background = '#2a3f54';
                }, 2000);
            }).catch(err => {
                showToast('error', 'No se pudo copiar.');
            });
        }

        // =========================================================
        // ALGORITMO DE BÚSQUEDA POR SIMILITUD DE 4 NIVELES
        // =========================================================
        function matchProductScore(product, query) {
            if (!query) return 0;
            const q = query.toLowerCase().trim();
            const name = (product.name || '').toLowerCase();
            const code = (product.default_code || '').toLowerCase();
            const barcode = (product.barcode || '').toLowerCase();
            const cat = (product.categ_name || '').toLowerCase();

            // Nivel 1: Coincidencia exacta con Código de barras o Referencia
            if (code === q || barcode === q) return 100;

            // Nivel 2: Prefijos (Empieza con) en código o nombre
            if (code.startsWith(q)) return 80;
            if (name.startsWith(q)) return 70;

            // Nivel 3: Términos divididos por palabras (Todos presentes en desorden)
            const terms = q.split(/\s+/);
            const matchesAllTerms = terms.every(term => 
                name.includes(term) || code.includes(term) || barcode.includes(term) || cat.includes(term)
            );
            if (matchesAllTerms) {
                // Dar peso adicional si los términos coinciden al principio del nombre
                return name.indexOf(terms[0]) === 0 ? 60 : 50;
            }

            // Nivel 4: Subcadena parcial fuzzy (Mínimo 3 caracteres en buscador)
            if (q.length >= 3) {
                if (name.includes(q) || code.includes(q)) return 30;
            }

            return 0; // Sin coincidencias
        }

        // =========================================================
        // CARGA DE IMÁGENES EN BACKGROUND (no bloquea la UI)
        // =========================================================
        async function cargarImagenesBackground() {
            // Solo pedir imágenes que no están en caché local
            const idsQueNecesitan = catalogoCompleto
                .filter(p => imagenesCache[p.id] === undefined)
                .map(p => p.id);

            // Procesar en batches de 50
            for (let i = 0; i < idsQueNecesitan.length; i += 50) {
                const batch = idsQueNecesitan.slice(i, i + 50);
                try {
                    const res  = await fetch(`/api/stock/imagenes?ids=${batch.join(',')}`);
                    const data = await res.json();
                    if (data.success && data.images) {
                        Object.entries(data.images).forEach(([id, img]) => {
                            const numId = parseInt(id);
                            imagenesCache[numId] = img || null;

                            // Actualizar placeholders ya visibles en el DOM
                            document.querySelectorAll(`.lazy-stock-img[data-product-id="${id}"]`).forEach(imgEl => {
                                const spinner = imgEl.parentElement.querySelector('.spinner');
                                if (img) {
                                    imgEl.src = 'data:image/png;base64,' + img;
                                    imgEl.classList.remove('opacity-0');
                                } else {
                                    imgEl.style.display = 'none';
                                    const ph = document.createElement('div');
                                    ph.className = 'w-10 h-10 text-gray-300';
                                    ph.innerHTML = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>`;
                                    imgEl.parentElement.appendChild(ph);
                                }
                                spinner?.remove();
                            });
                        });
                    }
                } catch (e) {
                    console.warn(`Error cargando imágenes batch ${i}:`, e);
                }
                // Pausa entre batches para no saturar el servidor
                await new Promise(r => setTimeout(r, 200));
            }
        }

        // =========================================================
        // NOTIFICACIONES TOAST (PREMIUM & LIMPIAS)
        // =========================================================
        function showToast(type, message) {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-6 right-6 flex items-center gap-3 text-white px-5 py-3.5 rounded-xl shadow-xl text-sm z-50 transition-all transform translate-y-2 opacity-0 duration-300 ${type === 'error' ? 'bg-red-600' : 'bg-[#2a3f54]'}`;
            
            const icon = type === 'error' 
                ? '<svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                : '<svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

            toast.innerHTML = `${icon} <span>${message}</span>`;
            document.body.appendChild(toast);

            // Animar entrada
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 50);

            // Salida y remoción
            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    </script>
</x-app-layout>
