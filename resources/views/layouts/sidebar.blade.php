{{--
    Sidebar — Tecnigas
    Color base: #2a3f54 (navy steel blue)
    Palette analógica:
      - Active bg:   #1e3347 (más oscuro, tono profundo)
      - Hover bg:    rgba(255,255,255,0.06)
      - Active pill: #3b82f6 (azul eléctrico — contraste vivo sobre el navy)
      - Category text: rgba(255,255,255,0.4)
      - Icon active: #93c5fd (azul claro)
--}}

<!-- Overlay móvil -->
<div x-show="sidebarOpen"
     class="fixed inset-0 z-40 lg:hidden"
     style="background:rgba(0,0,0,0.45);"
     x-transition:enter="transition-opacity ease-linear duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false">
</div>

<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto"
       style="background:#2a3f54; border-right:1px solid rgba(255,255,255,0.07);">

    <!-- Logo / Marca -->
    <div class="flex items-center gap-3 h-16 px-5 shrink-0" style="border-bottom:1px solid rgba(255,255,255,0.08);">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#3b82f6;">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <a href="{{ route('dashboard') }}" class="text-lg font-bold tracking-tight text-white">Tecnigas</a>
    </div>

    <!-- Navegación -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group"
           style="{{ request()->routeIs('dashboard') ? 'background:rgba(59,130,246,0.18); color:white;' : 'color:rgba(255,255,255,0.65);' }}"
           onmouseover="if(!this.style.background||this.style.background.indexOf('59,130')===-1) this.style.background='rgba(255,255,255,0.06)'; this.style.color='white';"
           onmouseout="if(!this.style.background||this.style.background.indexOf('59,130')===-1) { this.style.background=''; this.style.color='rgba(255,255,255,0.65)'; }">
            <svg class="w-4.5 h-4.5 w-5 h-5 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-blue-400' : '' }}"
                 style="color: {{ request()->routeIs('dashboard') ? '#93c5fd' : 'rgba(255,255,255,0.4)' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
            @if(request()->routeIs('dashboard'))
                <span class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-400"></span>
            @endif
        </a>

        @if(auth()->user()->isAdmin() || auth()->user()->isAdministrador())
        <!-- Categoría: Gestión POS -->
        <div class="pt-5 pb-1.5 px-3">
            <p class="text-xs font-semibold uppercase tracking-widest" style="color:rgba(255,255,255,0.3);">Gestión POS</p>
        </div>

        @php $isVentas = request()->routeIs('ventas'); @endphp
        <a href="{{ route('ventas') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
           style="{{ $isVentas ? 'background:rgba(59,130,246,0.18); color:white;' : 'color:rgba(255,255,255,0.65);' }}"
           onmouseover="if(!this.style.background||this.style.background.indexOf('59,130')===-1) this.style.background='rgba(255,255,255,0.06)'; this.style.color='white';"
           onmouseout="if(!this.style.background||this.style.background.indexOf('59,130')===-1) { this.style.background=''; this.style.color='rgba(255,255,255,0.65)'; }">
            <svg class="w-5 h-5 flex-shrink-0" style="color: {{ $isVentas ? '#93c5fd' : 'rgba(255,255,255,0.4)' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 16v-4m8-4h-4M4 12H0"/>
            </svg>
            Ventas POS
            @if($isVentas) <span class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-400"></span> @endif
        </a>

        <a href="#"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
           style="color:rgba(255,255,255,0.65);"
           onmouseover="this.style.background='rgba(255,255,255,0.06)'; this.style.color='white';"
           onmouseout="this.style.background=''; this.style.color='rgba(255,255,255,0.65)';">
            <svg class="w-5 h-5 flex-shrink-0" style="color:rgba(255,255,255,0.4)"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            Catálogo
        </a>
        @endif

        @if(auth()->user()->isAdmin() || auth()->user()->isAdministrador() || auth()->user()->isAlmacen() || auth()->user()->isVendedor())
        <!-- Categoría: Inventario -->
        <div class="pt-5 pb-1.5 px-3">
            <p class="text-xs font-semibold uppercase tracking-widest" style="color:rgba(255,255,255,0.3);">Inventario</p>
        </div>

        @php $isStock = request()->routeIs('stock'); @endphp
        <a href="{{ route('stock') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
           style="{{ $isStock ? 'background:rgba(59,130,246,0.18); color:white;' : 'color:rgba(255,255,255,0.65);' }}"
           onmouseover="if(!this.style.background||this.style.background.indexOf('59,130')===-1) this.style.background='rgba(255,255,255,0.06)'; this.style.color='white';"
           onmouseout="if(!this.style.background||this.style.background.indexOf('59,130')===-1) { this.style.background=''; this.style.color='rgba(255,255,255,0.65)'; }">
            <svg class="w-5 h-5 flex-shrink-0" style="color: {{ $isStock ? '#93c5fd' : 'rgba(255,255,255,0.4)' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4"/>
            </svg>
            Consultar Stock
            @if($isStock) <span class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-400"></span> @endif
        </a>
        @endif

        @if(auth()->user()->isAdmin() || auth()->user()->isAdministrador() || auth()->user()->isAlmacen())
        @php $isRecepcion = request()->routeIs('recepcion'); @endphp
        <a href="{{ route('recepcion') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
           style="{{ $isRecepcion ? 'background:rgba(59,130,246,0.18); color:white;' : 'color:rgba(255,255,255,0.65);' }}"
           onmouseover="if(!this.style.background||this.style.background.indexOf('59,130')===-1) this.style.background='rgba(255,255,255,0.06)'; this.style.color='white';"
           onmouseout="if(!this.style.background||this.style.background.indexOf('59,130')===-1) { this.style.background=''; this.style.color='rgba(255,255,255,0.65)'; }">
            <svg class="w-5 h-5 flex-shrink-0" style="color: {{ $isRecepcion ? '#93c5fd' : 'rgba(255,255,255,0.4)' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/>
            </svg>
            Recepción
            @if($isRecepcion) <span class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-400"></span> @endif
        </a>

        @php $isTraslado = request()->routeIs('traslado'); @endphp
        <a href="{{ route('traslado') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
           style="{{ $isTraslado ? 'background:rgba(59,130,246,0.18); color:white;' : 'color:rgba(255,255,255,0.65);' }}"
           onmouseover="if(!this.style.background||this.style.background.indexOf('59,130')===-1) this.style.background='rgba(255,255,255,0.06)'; this.style.color='white';"
           onmouseout="if(!this.style.background||this.style.background.indexOf('59,130')===-1) { this.style.background=''; this.style.color='rgba(255,255,255,0.65)'; }">
            <svg class="w-5 h-5 flex-shrink-0" style="color: {{ $isTraslado ? '#93c5fd' : 'rgba(255,255,255,0.4)' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4M4 17h12m0 0l-4-4m4 4l-4 4"/>
            </svg>
            Traslado
            @if($isTraslado) <span class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-400"></span> @endif
        </a>
        @endif

        <!-- Categoría: Recursos Humanos -->
        @if(auth()->user()->isAdmin() || auth()->user()->isAdministrador() || auth()->user()->isLimpieza())
        <div class="pt-5 pb-1.5 px-3">
            <p class="text-xs font-semibold uppercase tracking-widest" style="color:rgba(255,255,255,0.3);">Recursos Humanos</p>
        </div>
        @endif

        @if(auth()->user()->isAdmin() || auth()->user()->isAdministrador())
        @php $isAsistencias = request()->routeIs('asistencias'); @endphp
        <a href="{{ route('asistencias') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
           style="{{ $isAsistencias ? 'background:rgba(59,130,246,0.18); color:white;' : 'color:rgba(255,255,255,0.65);' }}"
           onmouseover="if(!this.style.background||this.style.background.indexOf('59,130')===-1) this.style.background='rgba(255,255,255,0.06)'; this.style.color='white';"
           onmouseout="if(!this.style.background||this.style.background.indexOf('59,130')===-1) { this.style.background=''; this.style.color='rgba(255,255,255,0.65)'; }">
            <svg class="w-5 h-5 flex-shrink-0" style="color: {{ $isAsistencias ? '#93c5fd' : 'rgba(255,255,255,0.4)' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Asistencias
            @if($isAsistencias) <span class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-400"></span> @endif
        </a>

        @php $isRemuneracion = request()->routeIs('remuneracion'); @endphp
        <a href="{{ route('remuneracion') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
           style="{{ $isRemuneracion ? 'background:rgba(59,130,246,0.18); color:white;' : 'color:rgba(255,255,255,0.65);' }}"
           onmouseover="if(!this.style.background||this.style.background.indexOf('59,130')===-1) this.style.background='rgba(255,255,255,0.06)'; this.style.color='white';"
           onmouseout="if(!this.style.background||this.style.background.indexOf('59,130')===-1) { this.style.background=''; this.style.color='rgba(255,255,255,0.65)'; }">
            <svg class="w-5 h-5 flex-shrink-0" style="color: {{ $isRemuneracion ? '#93c5fd' : 'rgba(255,255,255,0.4)' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM2 12c0-2.21 3.582-4 8-4s8 1.79 8 4-3.582 4-8 4-8-1.79-8-4z"/>
            </svg>
            Remuneración
            @if($isRemuneracion) <span class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-400"></span> @endif
        </a>
        @endif

        @if(auth()->user()->isAdmin() || auth()->user()->isAdministrador() || auth()->user()->isLimpieza())
        @php $isLimpieza = request()->routeIs('limpieza'); @endphp
        <a href="{{ route('limpieza') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
           style="{{ $isLimpieza ? 'background:rgba(59,130,246,0.18); color:white;' : 'color:rgba(255,255,255,0.65);' }}"
           onmouseover="if(!this.style.background||this.style.background.indexOf('59,130')===-1) this.style.background='rgba(255,255,255,0.06)'; this.style.color='white';"
           onmouseout="if(!this.style.background||this.style.background.indexOf('59,130')===-1) { this.style.background=''; this.style.color='rgba(255,255,255,0.65)'; }">
            <svg class="w-5 h-5 flex-shrink-0" style="color: {{ $isLimpieza ? '#93c5fd' : 'rgba(255,255,255,0.4)' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            Orden y Limpieza
            @if($isLimpieza) <span class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-400"></span> @endif
        </a>
        @endif

        @if(auth()->user()->isAdmin())
        <!-- Categoría: Sistema -->
        <div class="pt-5 pb-1.5 px-3">
            <p class="text-xs font-semibold uppercase tracking-widest" style="color:rgba(255,255,255,0.3);">Sistema</p>
        </div>

        @php $isConfig = request()->routeIs('config.index'); @endphp
        <a href="{{ route('config.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
           style="{{ $isConfig ? 'background:rgba(59,130,246,0.18); color:white;' : 'color:rgba(255,255,255,0.65);' }}"
           onmouseover="if(!this.style.background||this.style.background.indexOf('59,130')===-1) this.style.background='rgba(255,255,255,0.06)'; this.style.color='white';"
           onmouseout="if(!this.style.background||this.style.background.indexOf('59,130')===-1) { this.style.background=''; this.style.color='rgba(255,255,255,0.65)'; }">
            <svg class="w-5 h-5 flex-shrink-0" style="color: {{ $isConfig ? '#93c5fd' : 'rgba(255,255,255,0.4)' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Configuración
            @if($isConfig) <span class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-400"></span> @endif
        </a>
        @endif

    </nav>

    <!-- Perfil usuario al pie -->
    <div class="px-4 py-3 shrink-0" style="border-top:1px solid rgba(255,255,255,0.08);">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0"
                 style="background:rgba(59,130,246,0.25); color:#93c5fd;">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate leading-tight">{{ auth()->user()->name }}</p>
                <p class="text-xs truncate leading-tight" style="color:rgba(255,255,255,0.4);">{{ ucfirst(auth()->user()->role) }}</p>
            </div>
        </div>
    </div>
</aside>
