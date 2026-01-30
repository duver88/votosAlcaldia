<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="https://www.bucaramanga.gov.co/wp-content/uploads/2025/06/escudo-alcaldia.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-primary-500 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center gap-3">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <img src="https://www.bucaramanga.gov.co/wp-content/uploads/2025/06/escudo-alcaldia.png" alt="Alcaldia" class="h-8 sm:h-10 shrink-0">
                    <h1 class="text-base sm:text-xl font-bold truncate">Sistema de Votaciones</h1>
                </div>
                @auth
                <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                    <span class="text-xs sm:text-sm hidden sm:inline">Cedula: {{ auth()->user()->cedula }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-white text-primary-700 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg font-semibold hover:bg-gray-100 transition text-xs sm:text-sm">
                            Cerrar Sesion
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </header>
    <main class="container mx-auto px-4 py-6 sm:py-8">
        @if(session('success'))
        <div class="bg-primary-100 border border-primary-400 text-primary-800 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="border px-4 py-3 rounded mb-4" style="background-color: #fde8e8; border-color: #C20E1A; color: #C20E1A;">
            {{ session('error') }}
        </div>
        @endif
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
