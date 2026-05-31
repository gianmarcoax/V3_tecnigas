<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tecnigas ERP — Sistema de Gestión Empresarial</title>
    <meta name="description" content="Sistema de gestión empresarial Tecnigas. Consulte stock en tiempo real, gestione ventas, inventario y recursos humanos.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #f8fafc; color: #1e293b; }

        /* ─── HEADER / NAV ─── */
        .erp-nav {
            background: #2a3f54;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 0 2rem;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .nav-logo {
            display: flex; align-items: center; gap: 10px;
        }
        .nav-logo-icon {
            width: 32px; height: 32px; border-radius: 8px;
            background: #3b82f6;
            display: flex; align-items: center; justify-content: center;
        }
        .nav-logo-text { font-weight: 800; font-size: 1.1rem; color: white; letter-spacing: -.02em; }
        .nav-logo-sub { font-size: .65rem; font-weight: 500; color: rgba(255,255,255,.4); letter-spacing: .1em; text-transform: uppercase; }
        .nav-actions { display: flex; align-items: center; gap: .75rem; }
        .btn-login {
            display: flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,.15);
            color: white; font-size: .8rem; font-weight: 600;
            padding: .4rem 1rem; border-radius: 8px; cursor: pointer;
            text-decoration: none; transition: all .15s;
        }
        .btn-login:hover { background: rgba(255,255,255,.18); }
        .btn-dashboard {
            display: flex; align-items: center; gap: 6px;
            background: #3b82f6; border: 1px solid #2563eb;
            color: white; font-size: .8rem; font-weight: 600;
            padding: .4rem 1rem; border-radius: 8px; cursor: pointer;
            text-decoration: none; transition: all .15s;
        }
        .btn-dashboard:hover { background: #2563eb; }
        .system-time {
            font-size: .72rem; color: rgba(255,255,255,.45);
            font-weight: 500; letter-spacing: .03em;
        }

        /* ─── HERO STRIP ─── */
        .hero-strip {
            background: linear-gradient(135deg, #2a3f54 0%, #1e3347 50%, #162b3e 100%);
            padding: 3.5rem 2rem 4.5rem;
            position: relative; overflow: hidden;
        }
        .hero-strip::before {
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(circle at 20% 50%, rgba(59,130,246,.12) 0%, transparent 50%),
                              radial-gradient(circle at 80% 20%, rgba(99,102,241,.1) 0%, transparent 40%);
        }
        .hero-strip::after {
            content: '';
            position: absolute; bottom: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(59,130,246,.4), transparent);
        }
        .hero-inner {
            max-width: 1100px; margin: 0 auto; position: relative;
            display: flex; align-items: center; justify-content: space-between; gap: 2rem;
            flex-wrap: wrap;
        }
        .hero-text h1 {
            font-size: 2rem; font-weight: 900; color: white;
            letter-spacing: -.03em; line-height: 1.2;
        }
        .hero-text h1 span { color: #60a5fa; }
        .hero-text p {
            font-size: .9rem; color: rgba(255,255,255,.55); margin-top: .6rem;
            max-width: 480px; line-height: 1.6;
        }
        .hero-badges { display: flex; gap: .5rem; margin-top: 1.2rem; flex-wrap: wrap; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
            border-radius: 999px; padding: .3rem .8rem;
            font-size: .7rem; font-weight: 600; color: rgba(255,255,255,.65);
            letter-spacing: .04em;
        }
        .hero-badge-dot { width: 6px; height: 6px; border-radius: 50%; background: #34d399; }
        .hero-stat {
            background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);
            border-radius: 14px; padding: 1.25rem 1.75rem; text-align: center;
            min-width: 120px;
        }
        .hero-stat-num { font-size: 1.6rem; font-weight: 900; color: white; }
        .hero-stat-lbl { font-size: .68rem; color: rgba(255,255,255,.4); font-weight: 500; text-transform: uppercase; letter-spacing: .07em; margin-top: 2px; }
        .hero-stats { display: flex; gap: .75rem; flex-wrap: wrap; }

        /* ─── MAIN CONTENT ─── */
        .erp-body { max-width: 1100px; margin: 0 auto; padding: 2rem 2rem 4rem; }

        /* ─── STOCK PANEL ─── */
        .stock-panel {
            background: white; border-radius: 1.25rem;
            border: 2px solid #e2e8f0;
            margin-bottom: 2rem;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(42,63,84,.08);
        }
        .stock-panel-header {
            background: linear-gradient(135deg, #2a3f54 0%, #1e3347 100%);
            padding: 1.25rem 1.75rem;
            display: flex; align-items: center; justify-between; gap: 1rem;
            flex-wrap: wrap;
        }
        .stock-panel-title {
            display: flex; align-items: center; gap: .75rem;
        }
        .stock-icon-wrap {
            width: 40px; height: 40px; border-radius: 10px;
            background: rgba(59,130,246,.25); border: 1px solid rgba(59,130,246,.35);
            display: flex; align-items: center; justify-content: center;
        }
        .stock-panel-title h2 { font-size: 1rem; font-weight: 700; color: white; }
        .stock-panel-title p { font-size: .72rem; color: rgba(255,255,255,.5); margin-top: 1px; }
        .stock-badge {
            background: rgba(52,211,153,.15); border: 1px solid rgba(52,211,153,.3);
            color: #34d399; font-size: .68rem; font-weight: 700;
            padding: .25rem .65rem; border-radius: 999px; letter-spacing: .05em;
        }

        .stock-search-area { padding: 1.5rem 1.75rem; }
        .search-row { display: flex; gap: .75rem; flex-wrap: wrap; }
        .search-input-wrap { flex: 1; min-width: 240px; position: relative; }
        .search-input-wrap svg {
            position: absolute; left: .9rem; top: 50%; transform: translateY(-50%);
            width: 18px; height: 18px; color: #94a3b8; pointer-events: none;
        }
        .search-input {
            width: 100%; height: 44px; padding: 0 1rem 0 2.75rem;
            border: 1.5px solid #e2e8f0; border-radius: 10px;
            font-size: .88rem; color: #1e293b; background: #f8fafc;
            outline: none; transition: all .15s; font-family: 'Inter', sans-serif;
        }
        .search-input:focus { border-color: #3b82f6; background: white; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
        .search-input::placeholder { color: #94a3b8; }

        .search-select {
            height: 44px; padding: 0 2.5rem 0 .9rem;
            border: 1.5px solid #e2e8f0; border-radius: 10px;
            font-size: .85rem; color: #475569; background: #f8fafc;
            outline: none; cursor: pointer; font-family: 'Inter', sans-serif;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right .7rem center; background-size: 14px;
            transition: border .15s;
        }
        .search-select:focus { border-color: #3b82f6; background-color: white; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }

        .btn-search {
            height: 44px; padding: 0 1.5rem;
            background: #2a3f54; border: none; border-radius: 10px;
            color: white; font-size: .85rem; font-weight: 600;
            cursor: pointer; display: flex; align-items: center; gap: 6px;
            transition: all .15s; white-space: nowrap;
        }
        .btn-search:hover { background: #1e3347; transform: translateY(-1px); }
        .btn-search:active { transform: translateY(0); }

        /* Results Table */
        .results-area { padding: 0 1.75rem 1.75rem; }
        .results-info {
            display: flex; align-items: center; justify-content: space-between;
            padding: .75rem 0; border-bottom: 1px solid #f1f5f9; margin-bottom: .75rem;
        }
        .results-count { font-size: .78rem; color: #64748b; font-weight: 500; }
        .results-tag { font-size: .72rem; color: #3b82f6; font-weight: 600; background: #eff6ff; padding: .2rem .65rem; border-radius: 999px; }

        .stock-table { width: 100%; border-collapse: collapse; min-width: 600px; }
        .stock-table th {
            background: #f8fafc; padding: .6rem .9rem;
            font-size: .68rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; color: #64748b;
            text-align: left; border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .stock-table td {
            padding: .7rem .9rem; font-size: .82rem; color: #334155;
            border-bottom: 1px solid #f8fafc; vertical-align: middle;
        }
        .stock-table tbody tr:last-child td { border-bottom: none; }
        .stock-table tbody tr:hover td { background: #fafbff; }

        .item-code { font-family: monospace; font-size: .75rem; color: #94a3b8; }
        .item-name { font-weight: 600; color: #0f172a; }
        .item-desc { font-size: .72rem; color: #94a3b8; margin-top: 1px; }
        .stock-pill {
            display: inline-flex; align-items: center; gap: 4px;
            padding: .25rem .65rem; border-radius: 999px;
            font-size: .72rem; font-weight: 700;
        }
        .stock-ok  { background: #dcfce7; color: #166534; }
        .stock-low { background: #fef9c3; color: #854d0e; }
        .stock-out { background: #fee2e2; color: #991b1b; }
        .price-cell { font-weight: 700; color: #1e293b; }

        .empty-state {
            text-align: center; padding: 3rem 1rem;
            color: #94a3b8;
        }
        .empty-state svg { width: 48px; height: 48px; margin: 0 auto 1rem; opacity: .4; }
        .empty-state p { font-size: .85rem; }

        .loading-row td {
            text-align: center; padding: 2rem;
            color: #94a3b8; font-size: .83rem;
        }

        /* ─── MODULES GRID ─── */
        .section-title {
            font-size: .7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .1em; color: #94a3b8; margin-bottom: 1rem;
        }
        .modules-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }
        .module-card {
            background: white; border-radius: 1rem; border: 1.5px solid #e2e8f0;
            padding: 1.25rem; display: flex; flex-direction: column; gap: .75rem;
            transition: all .2s; text-decoration: none;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .module-card:hover { border-color: #bfdbfe; box-shadow: 0 4px 16px rgba(42,63,84,.1); transform: translateY(-2px); }
        .module-card.locked { opacity: .55; cursor: not-allowed; filter: grayscale(.3); }
        .module-card.locked:hover { transform: none; box-shadow: none; border-color: #e2e8f0; }
        .module-icon {
            width: 42px; height: 42px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .module-card h3 { font-size: .88rem; font-weight: 700; color: #0f172a; }
        .module-card p { font-size: .72rem; color: #64748b; line-height: 1.5; }
        .module-tag {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: .65rem; font-weight: 600; letter-spacing: .04em;
            padding: .2rem .55rem; border-radius: 999px;
        }
        .tag-admin { background: #eef2ff; color: #4338ca; }
        .tag-almacen { background: #fff7ed; color: #c2410c; }
        .tag-all { background: #f0fdf4; color: #15803d; }
        .tag-soon { background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; }

        .scroll-wrap { overflow-x: auto; }

        /* Spinner */
        .spinner-sm {
            width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.3);
            border-top-color: white; border-radius: 50%; animation: spin .6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ─── FOOTER ─── */
        .erp-footer {
            background: #2a3f54; border-top: 1px solid rgba(255,255,255,.07);
            text-align: center; padding: 1.25rem;
            font-size: .72rem; color: rgba(255,255,255,.3); letter-spacing: .04em;
        }
    </style>
</head>
<body>

    {{-- ══ NAV BAR ══ --}}
    <nav class="erp-nav">
        <div class="nav-logo">
            <div class="nav-logo-icon">
                <svg class="w-4 h-4 text-white" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <div class="nav-logo-text">Tecnigas</div>
                <div class="nav-logo-sub">ERP — Sistema de Gestión</div>
            </div>
        </div>
        <div class="nav-actions">
            <span class="system-time" id="navTime">{{ now()->timezone('America/Lima')->format('H:i') }} — Lima, PE</span>
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-dashboard">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
            @else
                @if (Route::has('login'))
                <a href="{{ route('login') }}" class="btn-login">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Acceso al Sistema
                </a>
                @endif
            @endauth
        </div>
    </nav>

    {{-- ══ HERO STRIP ══ --}}
    <div class="hero-strip">
        <div class="hero-inner">
            <div class="hero-text">
                <h1>Sistema de Gestión<br><span>Empresarial Tecnigas</span></h1>
                <p>Plataforma integrada de control de ventas, inventario, asistencias, remuneración y calidad operativa.</p>
                <div class="hero-badges">
                    <span class="hero-badge"><span class="hero-badge-dot"></span>EN LÍNEA</span>
                    <span class="hero-badge">📍 Lima, Perú</span>
                    <span class="hero-badge">🔒 Acceso por roles</span>
                    <span class="hero-badge">⚡ Tiempo real</span>
                </div>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-num">7</div>
                    <div class="hero-stat-lbl">Módulos</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-num">4</div>
                    <div class="hero-stat-lbl">Roles</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-num" id="heroYear">{{ date('Y') }}</div>
                    <div class="hero-stat-lbl">Versión</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ BODY ══ --}}
    <div class="erp-body">

        {{-- ── PANEL CONSULTA DE STOCK (PÚBLICO) ── --}}
        <div class="stock-panel" id="stockPanel">
            <div class="stock-panel-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">
                <div class="stock-panel-title">
                    <div class="stock-icon-wrap">
                        <svg style="width:20px;height:20px;color:#60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4M8 13h8"/>
                        </svg>
                    </div>
                    <div>
                        <h2>Consulta de Stock</h2>
                        <p>Búsqueda de productos disponibles en almacén — acceso público</p>
                    </div>
                </div>
                <span class="stock-badge">⚡ ACCESO LIBRE</span>
            </div>

            <div class="stock-search-area">
                <div class="search-row">
                    <div class="search-input-wrap">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" id="stockSearch" class="search-input"
                               placeholder="Buscar por código, nombre o descripción..."
                               onkeydown="if(event.key==='Enter') buscarStock()">
                    </div>
                    <select id="stockWarehouse" class="search-select">
                        <option value="">Todos los almacenes</option>
                        <option value="1">Almacén Principal</option>
                        <option value="2">Tienda</option>
                    </select>
                    <button class="btn-search" id="btnBuscar" onclick="buscarStock()">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Buscar
                    </button>
                </div>
            </div>

            <div class="results-area" id="resultsArea" style="display:none;">
                <div class="results-info">
                    <span class="results-count" id="resultsCount">0 resultados</span>
                    <span class="results-tag" id="resultsQuery"></span>
                </div>
                <div class="scroll-wrap">
                    <table class="stock-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Almacén</th>
                                <th>Disponible</th>
                                <th>Precio Ref.</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="stockTableBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="empty-state" id="emptyState">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p>Ingrese un término para buscar productos en el inventario</p>
            </div>
        </div>

        {{-- ── MÓDULOS DEL SISTEMA ── --}}
        <div style="margin-top: 2.5rem;">
            <p class="section-title">Módulos del sistema — requieren autenticación</p>
            <div class="modules-grid">

                {{-- Dashboard --}}
                <a href="{{ url('/dashboard') }}" class="module-card {{ !auth()->check() ? 'locked' : '' }}" {{ !auth()->check() ? 'onclick="return false;"' : '' }}>
                    <div class="module-icon" style="background:#eff6ff;">
                        <svg style="width:22px;height:22px;color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Dashboard General</h3>
                        <p>Resumen ejecutivo con KPIs, alertas y métricas del negocio en tiempo real.</p>
                    </div>
                    <span class="module-tag tag-admin">🔒 Admin</span>
                </a>

                {{-- Ventas POS --}}
                <a href="{{ url('/ventas') }}" class="module-card {{ !auth()->check() ? 'locked' : '' }}" {{ !auth()->check() ? 'onclick="return false;"' : '' }}>
                    <div class="module-icon" style="background:#f0fdf4;">
                        <svg style="width:22px;height:22px;color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Ventas POS</h3>
                        <p>Ranking y análisis de ventas por período, empleado y categoría.</p>
                    </div>
                    <span class="module-tag tag-admin">🔒 Admin</span>
                </a>

                {{-- Asistencias --}}
                <a href="{{ url('/asistencias') }}" class="module-card {{ !auth()->check() ? 'locked' : '' }}" {{ !auth()->check() ? 'onclick="return false;"' : '' }}>
                    <div class="module-icon" style="background:#ecfdf5;">
                        <svg style="width:22px;height:22px;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Asistencias</h3>
                        <p>Control de entradas, salidas, tardanzas y gestión de horarios del personal.</p>
                    </div>
                    <span class="module-tag tag-admin">🔒 Admin</span>
                </a>

                {{-- Bono Semanal --}}
                <a href="{{ url('/remuneracion') }}" class="module-card {{ !auth()->check() ? 'locked' : '' }}" {{ !auth()->check() ? 'onclick="return false;"' : '' }}>
                    <div class="module-icon" style="background:#fefce8;">
                        <svg style="width:22px;height:22px;color:#ca8a04;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Bono Semanal</h3>
                        <p>Cálculo de bonos, gestión de faltas, tardanzas justificadas y exportación.</p>
                    </div>
                    <span class="module-tag tag-admin">🔒 Admin</span>
                </a>

                {{-- Recepción --}}
                <a href="{{ url('/recepcion') }}" class="module-card {{ !auth()->check() ? 'locked' : '' }}" {{ !auth()->check() ? 'onclick="return false;"' : '' }}>
                    <div class="module-icon" style="background:#fff7ed;">
                        <svg style="width:22px;height:22px;color:#ea580c;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Recepción</h3>
                        <p>Ingreso y registro de productos en almacén, control de entradas.</p>
                    </div>
                    <span class="module-tag tag-almacen">📦 Almacén</span>
                </a>

                {{-- Traslado --}}
                <a href="{{ url('/traslado') }}" class="module-card {{ !auth()->check() ? 'locked' : '' }}" {{ !auth()->check() ? 'onclick="return false;"' : '' }}>
                    <div class="module-icon" style="background:#fdf4ff;">
                        <svg style="width:22px;height:22px;color:#9333ea;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4M4 17h12m0 0l-4-4m4 4l-4 4"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Traslado</h3>
                        <p>Movimiento de mercancía entre almacenes y puntos de venta.</p>
                    </div>
                    <span class="module-tag tag-almacen">📦 Almacén</span>
                </a>

                {{-- Orden y Limpieza --}}
                <a href="{{ url('/limpieza') }}" class="module-card {{ !auth()->check() ? 'locked' : '' }}" {{ !auth()->check() ? 'onclick="return false;"' : '' }}>
                    <div class="module-icon" style="background:#f0fdf4;">
                        <svg style="width:22px;height:22px;color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Orden y Limpieza</h3>
                        <p>Calificación semanal del personal de tienda y control de calidad.</p>
                    </div>
                    <span class="module-tag tag-all">✨ Limpieza</span>
                </a>

                {{-- Configuración --}}
                <a href="{{ url('/config') }}" class="module-card {{ !auth()->check() ? 'locked' : '' }}" {{ !auth()->check() ? 'onclick="return false;"' : '' }}>
                    <div class="module-icon" style="background:#eef2ff;">
                        <svg style="width:22px;height:22px;color:#4f46e5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Configuración</h3>
                        <p>Gestión de usuarios, roles y parámetros del sistema.</p>
                    </div>
                    <span class="module-tag tag-admin">🔒 Admin</span>
                </a>

            </div>
        </div>

        @guest
        <div style="margin-top:2rem; background:#eff6ff; border:1.5px solid #bfdbfe; border-radius:1rem; padding:1.25rem 1.5rem; display:flex; align-items:center; gap:1rem;">
            <svg style="width:22px;height:22px;color:#3b82f6;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p style="font-size:.83rem;color:#1e40af;flex:1;">
                Los módulos del sistema requieren autenticación. Si tienes credenciales, usa el botón <strong>"Acceso al Sistema"</strong> en la barra superior.
            </p>
            <a href="{{ route('login') }}" style="background:#3b82f6;color:white;font-size:.78rem;font-weight:600;padding:.45rem 1.1rem;border-radius:8px;text-decoration:none;white-space:nowrap;flex-shrink:0;">
                Iniciar Sesión
            </a>
        </div>
        @endguest

    </div>

    {{-- ══ FOOTER ══ --}}
    <footer class="erp-footer">
        Tecnigas ERP &nbsp;·&nbsp; Sistema de Gestión Empresarial &nbsp;·&nbsp; Lima, Perú &nbsp;·&nbsp; &copy; {{ date('Y') }}
    </footer>

    <script>
    // ── Reloj en tiempo real ──
    function updateClock() {
        const now = new Date();
        const opts = { hour:'2-digit', minute:'2-digit', timeZone:'America/Lima', hour12: false };
        const time = new Intl.DateTimeFormat('es-PE', opts).format(now);
        const el = document.getElementById('navTime');
        if (el) el.textContent = time + ' — Lima, PE';
    }
    updateClock();
    setInterval(updateClock, 30000);

    // ── Consulta de Stock ──
    let currentSearch = '';

    async function buscarStock() {
        const q = document.getElementById('stockSearch').value.trim();
        const warehouse = document.getElementById('stockWarehouse').value;
        if (!q) return;

        currentSearch = q;
        const btn = document.getElementById('btnBuscar');
        btn.innerHTML = '<div class="spinner-sm"></div> Buscando...';
        btn.disabled = true;

        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('resultsArea').style.display = 'none';

        try {
            const params = new URLSearchParams({ q });
            if (warehouse) params.append('warehouse', warehouse);
            const res = await fetch('/api/stock/buscar?' + params.toString());
            const data = await res.json();
            renderResults(data, q);
        } catch(e) {
            renderError();
        } finally {
            btn.innerHTML = '<svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg> Buscar';
            btn.disabled = false;
        }
    }

    function renderResults(data, query) {
        const items = data.items || data.productos || data || [];
        const tbody = document.getElementById('stockTableBody');
        const area  = document.getElementById('resultsArea');
        const empty = document.getElementById('emptyState');

        if (!Array.isArray(items) || items.length === 0) {
            empty.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>No se encontraron productos con "<strong>${escHtml(query)}</strong>"</p>`;
            empty.style.display = 'block';
            area.style.display = 'none';
            return;
        }

        document.getElementById('resultsCount').textContent = items.length + ' resultado' + (items.length !== 1 ? 's' : '');
        document.getElementById('resultsQuery').textContent = '"' + query + '"';

        tbody.innerHTML = items.map(item => {
            const qty   = parseFloat(item.stock ?? item.quantity ?? item.qty ?? 0);
            const price = item.price ?? item.precio ?? null;
            const { cls, label } = stockStatus(qty);
            const code  = item.code ?? item.codigo ?? item.sku ?? '—';
            const name  = item.name ?? item.nombre ?? item.product_name ?? '—';
            const desc  = item.description ?? item.descripcion ?? '';
            const wh    = item.warehouse ?? item.almacen ?? '—';

            return `<tr>
                <td><span class="item-code">${escHtml(String(code))}</span></td>
                <td>
                    <div class="item-name">${escHtml(String(name))}</div>
                    ${desc ? `<div class="item-desc">${escHtml(String(desc).slice(0,60))}${String(desc).length > 60 ? '…' : ''}</div>` : ''}
                </td>
                <td style="font-size:.78rem;color:#64748b;">${escHtml(String(wh))}</td>
                <td style="font-weight:700;color:#0f172a;">${qty.toLocaleString('es-PE', {minimumFractionDigits:0, maximumFractionDigits:2})}</td>
                <td class="price-cell">${price !== null ? 'S/ ' + parseFloat(price).toFixed(2) : '<span style="color:#cbd5e1;">—</span>'}</td>
                <td><span class="stock-pill ${cls}">${label}</span></td>
            </tr>`;
        }).join('');

        area.style.display = 'block';
        empty.style.display = 'none';
    }

    function renderError() {
        const empty = document.getElementById('emptyState');
        empty.innerHTML = `
            <svg style="color:#fca5a5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p style="color:#ef4444;">Error de conexión. Verifique que el sistema esté disponible.</p>`;
        empty.style.display = 'block';
        document.getElementById('resultsArea').style.display = 'none';
    }

    function stockStatus(qty) {
        if (qty <= 0) return { cls: 'stock-out', label: 'Sin stock' };
        if (qty <= 5) return { cls: 'stock-low', label: 'Stock bajo' };
        return { cls: 'stock-ok', label: 'Disponible' };
    }

    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    </script>

</body>
</html>
