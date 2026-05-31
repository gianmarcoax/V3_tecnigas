<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-50">
        
        <!-- Wrapper principal que contiene Sidebar y Contenido usando x-data para Alpine -->
        <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden bg-gray-50">
            
            <!-- Sidebar Desktop y Mobile -->
            @include('layouts.sidebar')

            <!-- Contenedor Principal (Derecha) -->
            <div class="flex-1 flex flex-col overflow-hidden w-full relative">
                
                <!-- Navbar Superior -->
                @include('layouts.navigation')

                <!-- Cabecera de Página (Opcional, de Breeze) -->
                @isset($header)
                    <header class="bg-white border-b border-gray-100 shrink-0">
                        <div class="py-4 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Área desplazable de contenido -->
                <main class="flex-1 overflow-y-auto bg-gray-50">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
