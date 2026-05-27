<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Tecnigas
        </h2>
    </x-slot>

    <div class="py-10 px-6 max-w-7xl mx-auto">

        {{-- Bienvenida --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Bienvenido, {{ auth()->user()->name }}</h1>
            <p class="text-gray-500 mt-1">Sistema de gestión interno — Tecnigas</p>
        </div>

        {{-- Grid de módulos --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- Catálogo --}}
            <a href="#"
                class="group block bg-white rounded-2xl shadow hover:shadow-lg transition p-6 border border-gray-100 hover:border-blue-300">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-100 text-blue-600 rounded-xl p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 text-lg group-hover:text-blue-600 transition">Catálogo
                        </h3>
                        <p class="text-gray-400 text-sm">Productos, fotos y precios</p>
                    </div>
                </div>
            </a>

            {{-- Ventas --}}
            <a href="{{ route('ventas') }}"
                class="group block bg-white rounded-2xl shadow hover:shadow-lg transition p-6 border border-gray-100 hover:border-green-300">
                <div class="flex items-center gap-4">
                    <div class="bg-green-100 text-green-600 rounded-xl p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 16v-4m8-4h-4M4 12H0" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 text-lg group-hover:text-green-600 transition">Ventas
                        </h3>
                        <p class="text-gray-400 text-sm">Ranking POS y reportes</p>
                    </div>
                </div>
            </a>

            {{-- Asistencias --}}
            <a href="{{ route('asistencias') }}"
                class="group block bg-white rounded-2xl shadow hover:shadow-lg transition p-6 border border-gray-100 hover:border-teal-300">
                <div class="flex items-center gap-4">
                    <div class="bg-teal-100 text-teal-600 rounded-xl p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87a4 4 0 100-8 4 4 0 000 8zm6-10a4 4 0 10-8 0 4 4 0 008 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 text-lg group-hover:text-teal-600 transition">Asistencias
                        </h3>
                        <p class="text-gray-400 text-sm">En vivo y registro semanal</p>
                    </div>
                </div>
            </a>

            {{-- Stock --}}
            <a href="#"
                class="group block bg-white rounded-2xl shadow hover:shadow-lg transition p-6 border border-gray-100 hover:border-sky-300">
                <div class="flex items-center gap-4">
                    <div class="bg-sky-100 text-sky-600 rounded-xl p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 text-lg group-hover:text-sky-600 transition">Stock</h3>
                        <p class="text-gray-400 text-sm">Inventario Tienda y Almacén</p>
                    </div>
                </div>
            </a>

            {{-- Remuneración --}}
            <a href="{{ route('remuneracion') }}"
                class="group block bg-white rounded-2xl shadow hover:shadow-lg transition p-6 border border-gray-100 hover:border-violet-300">
                <div class="flex items-center gap-4">
                    <div class="bg-violet-100 text-violet-600 rounded-xl p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a4 4 0 00-8 0v2M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 text-lg group-hover:text-violet-600 transition">
                            Remuneración</h3>
                        <p class="text-gray-400 text-sm">Salarios, bonos y metas</p>
                    </div>
                </div>
            </a>

            {{-- Recepción --}}
            <a href="{{ route('recepcion') }}"
                class="group block bg-white rounded-2xl shadow hover:shadow-lg transition p-6 border border-gray-100 hover:border-orange-300">
                <div class="flex items-center gap-4">
                    <div class="bg-orange-100 text-orange-600 rounded-xl p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 text-lg group-hover:text-orange-600 transition">Recepción
                        </h3>
                        <p class="text-gray-400 text-sm">Mercancía de proveedores</p>
                    </div>
                </div>
            </a>

            {{-- Traslado --}}
            <a href="{{ route('traslado') }}"
                class="group block bg-white rounded-2xl shadow hover:shadow-lg transition p-6 border border-gray-100 hover:border-yellow-300">
                <div class="flex items-center gap-4">
                    <div class="bg-yellow-100 text-yellow-600 rounded-xl p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4M4 17h12m0 0l-4-4m4 4l-4 4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 text-lg group-hover:text-yellow-600 transition">Traslado
                        </h3>
                        <p class="text-gray-400 text-sm">Entre Almacén y Tienda</p>
                    </div>
                </div>
            </a>

            {{-- Productos --}}
            <a href="#"
                class="group block bg-white rounded-2xl shadow hover:shadow-lg transition p-6 border border-gray-100 hover:border-rose-300">
                <div class="flex items-center gap-4">
                    <div class="bg-rose-100 text-rose-600 rounded-xl p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 text-lg group-hover:text-rose-600 transition">Productos
                        </h3>
                        <p class="text-gray-400 text-sm">Crear y editar en Odoo</p>
                    </div>
                </div>
            </a>

        </div>
    </div>
</x-app-layout>