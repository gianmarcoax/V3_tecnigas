<x-app-layout>


    <div class="py-8 px-6 max-w-7xl mx-auto">

        {{-- Bienvenida --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Bienvenido, {{ auth()->user()->name }}</h1>
                <p class="text-gray-500 mt-1">Panel de control</p>
            </div>
            <div class="text-right hidden md:block">
                <p class="text-sm font-medium text-gray-500 capitalize">{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
                <p class="text-xs text-gray-400">Rol: {{ ucfirst(auth()->user()->role) }}</p>
            </div>
        </div>

        {{-- KPIs Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            {{-- KPI 1: Ventas --}}
            <div class="bg-white rounded-2xl border-2 border-gray-200 p-6 flex items-center gap-4 hover:border-blue-300 hover:shadow-md transition group">
                <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Ventas de Hoy</p>
                    <p class="text-2xl font-bold text-gray-900 mt-0.5">S/ 4,250.00</p>
                    <p class="text-xs text-green-500 flex items-center gap-0.5 mt-1 font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        +12% vs ayer
                    </p>
                </div>
            </div>

            {{-- KPI 2: Personal --}}
            <div class="bg-white rounded-2xl border-2 border-gray-200 p-6 flex items-center gap-4 hover:border-emerald-300 hover:shadow-md transition group">
                <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Personal Activo</p>
                    <p class="text-2xl font-bold text-gray-900 mt-0.5">14 / 15</p>
                    <p class="text-xs text-orange-500 mt-1 font-medium">1 falta registrada hoy</p>
                </div>
            </div>

            {{-- KPI 3: Stock Crítico --}}
            <div class="bg-white rounded-2xl border-2 border-gray-200 p-6 flex items-center gap-4 hover:border-amber-300 hover:shadow-md transition group">
                <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Alertas de Stock</p>
                    <p class="text-2xl font-bold text-gray-900 mt-0.5">8 items</p>
                    <p class="text-xs text-red-500 mt-1 font-medium">Requieren reposición</p>
                </div>
            </div>

        </div>

        {{-- Panel Principal: Alertas + Accesos Rápidos --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- Panel Izquierdo: Alertas Recientes --}}
            <div class="bg-white rounded-2xl border-2 border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b-2 border-gray-100 flex justify-between items-center bg-gray-50/80">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <h3 class="font-semibold text-gray-800 text-sm">Alertas Recientes</h3>
                    </div>
                    <span class="text-xs bg-red-50 text-red-600 font-semibold px-2 py-1 rounded-full">3 nuevas</span>
                </div>
                <div class="divide-y divide-gray-50">

                    <div class="p-4 flex gap-3 hover:bg-gray-50/50 transition">
                        <div class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800">Stock Crítico en Tienda</p>
                            <p class="text-xs text-gray-500 mt-0.5">Válvula Premium (VAL-002): 0 unidades disponibles.</p>
                            <p class="text-xs text-gray-300 mt-1.5">Hace 15 min</p>
                        </div>
                    </div>

                    <div class="p-4 flex gap-3 hover:bg-gray-50/50 transition">
                        <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800">Tardanza Registrada</p>
                            <p class="text-xs text-gray-500 mt-0.5">Carlos P. marcó entrada a las 08:25 AM (Turno Mañana).</p>
                            <p class="text-xs text-gray-300 mt-1.5">Hace 2 horas</p>
                        </div>
                    </div>

                    <div class="p-4 flex gap-3 hover:bg-gray-50/50 transition">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800">Cierre de Caja Registrado</p>
                            <p class="text-xs text-gray-500 mt-0.5">Turno Tarde cerró caja correctamente: S/ 2,150.50.</p>
                            <p class="text-xs text-gray-300 mt-1.5">Ayer, 10:15 PM</p>
                        </div>
                    </div>

                    <div class="p-4 flex gap-3 hover:bg-gray-50/50 transition">
                        <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800">Calificación O&L Pendiente</p>
                            <p class="text-xs text-gray-500 mt-0.5">No se ha registrado la calificación del día de hoy.</p>
                            <p class="text-xs text-gray-300 mt-1.5">Hoy</p>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Panel Derecho: Accesos Rápidos --}}
            <div class="bg-white rounded-2xl border-2 border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b-2 border-gray-100 bg-gray-50/80">
                    <h3 class="font-semibold text-gray-800 text-sm">Accesos Directos</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Módulos frecuentes según tu rol</p>
                </div>
                <div class="p-5 grid grid-cols-2 gap-3">

                    @if(auth()->user()->isAdmin() || auth()->user()->isVendedor())
                    <a href="{{ route('ventas') }}" class="flex flex-col p-4 border border-gray-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 transition group">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 16v-4m8-4h-4M4 12H0"/></svg>
                        </div>
                        <h4 class="font-semibold text-gray-800 text-sm leading-tight">Ventas POS</h4>
                        <p class="text-xs text-gray-400 mt-1">Ranking y ticket promedio</p>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('asistencias') }}" class="flex flex-col p-4 border border-gray-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 transition group">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h4 class="font-semibold text-gray-800 text-sm leading-tight">Asistencias</h4>
                        <p class="text-xs text-gray-400 mt-1">Control de personal</p>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin() || auth()->user()->isAlmacen())
                    <a href="{{ route('recepcion') }}" class="flex flex-col p-4 border border-gray-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 transition group">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/></svg>
                        </div>
                        <h4 class="font-semibold text-gray-800 text-sm leading-tight">Nueva Recepción</h4>
                        <p class="text-xs text-gray-400 mt-1">Ingresar stock proveedor</p>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('remuneracion') }}" class="flex flex-col p-4 border border-gray-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 transition group">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM2 12c0-2.21 3.582-4 8-4s8 1.79 8 4-3.582 4-8 4-8-1.79-8-4z"/></svg>
                        </div>
                        <h4 class="font-semibold text-gray-800 text-sm leading-tight">Sueldos y Bonos</h4>
                        <p class="text-xs text-gray-400 mt-1">Nómina y metas</p>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin() || auth()->user()->isLimpieza())
                    <a href="{{ route('limpieza') }}" class="flex flex-col p-4 border border-gray-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 transition group">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        </div>
                        <h4 class="font-semibold text-gray-800 text-sm leading-tight">Orden y Limpieza</h4>
                        <p class="text-xs text-gray-400 mt-1">Calificar al personal</p>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin() || auth()->user()->isAlmacen())
                    <a href="{{ route('traslado') }}" class="flex flex-col p-4 border border-gray-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 transition group">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4M4 17h12m0 0l-4-4m4 4l-4 4"/></svg>
                        </div>
                        <h4 class="font-semibold text-gray-800 text-sm leading-tight">Traslado</h4>
                        <p class="text-xs text-gray-400 mt-1">Almacén a Tienda</p>
                    </a>
                    @endif

                </div>
            </div>

        </div>

    </div>

    {{-- Flash de acceso denegado --}}
    @if(session('error'))
    <div id="flash-error" class="fixed bottom-6 right-6 flex items-center gap-3 bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm z-50">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    <script>setTimeout(()=>document.getElementById('flash-error')?.remove(), 4000)</script>
    @endif

</x-app-layout>