<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: true }">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 text-white min-h-screen fixed" x-show="sidebarOpen">
            <div class="p-4 border-b border-gray-700">
                <h2 class="text-xl font-bold">Panel Admin</h2>
                <p class="text-sm text-gray-400">{{ auth()->guard('admin')->user()->name }}</p>
            </div>
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-primary-600' : '' }}">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.candidates.index') }}" class="block px-4 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.candidates.*') ? 'bg-primary-600' : '' }}">
                            Candidatos
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.voters.index') }}" class="block px-4 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.voters.*') ? 'bg-primary-600' : '' }}">
                            Votantes
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings') }}" class="block px-4 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.settings') ? 'bg-primary-600' : '' }}">
                            Configuracion
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.logs') }}" class="block px-4 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.logs') ? 'bg-primary-600' : '' }}">
                            Logs
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-700">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded font-semibold transition">
                        Cerrar Sesion
                    </button>
                </form>
            </div>
        </aside>
        <!-- Main Content -->
        <main class="flex-1 ml-64 p-8">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden mb-4 p-2 bg-gray-200 rounded">
                Menu
            </button>
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
            @endif
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
