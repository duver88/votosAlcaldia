<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="https://www.bucaramanga.gov.co/wp-content/uploads/2025/06/escudo-alcaldia.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" @resize.window="sidebarOpen = window.innerWidth >= 1024">
    <div class="flex min-h-screen">
        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <!-- Sidebar -->
        <aside class="w-72 min-h-screen fixed text-white flex flex-col shadow-2xl z-40"
               x-show="sidebarOpen"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               style="background: linear-gradient(180deg, #1a4a0e 0%, #285F19 40%, #2d6b1c 100%);">
            <!-- Logo Section -->
            <div class="p-5 lg:p-6 border-b border-white/10">
                <div class="flex items-center gap-3 lg:gap-4">
                    <div class="bg-white/10 rounded-xl p-2 backdrop-blur-sm">
                        <img src="https://www.bucaramanga.gov.co/wp-content/uploads/2025/06/escudo-alcaldia.png" alt="Alcaldia" class="h-10 lg:h-12 w-auto">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold tracking-wide" style="font-family: 'Oswald', sans-serif;">Panel Admin</h2>
                        <p class="text-xs text-green-300/80">Sistema de Votaciones</p>
                    </div>
                    <!-- Close button mobile -->
                    <button @click="sidebarOpen = false" class="lg:hidden w-8 h-8 rounded-full bg-white/15 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
            <!-- Admin Info -->
            <div class="px-5 lg:px-6 py-4 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center text-sm font-bold">
                        {{ strtoupper(substr(auth()->guard('admin')->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold">{{ auth()->guard('admin')->user()->name }}</p>
                        <p class="text-xs text-green-300/60">{{ auth()->guard('admin')->user()->isAdmin() ? 'Administrador' : 'Moderador' }}</p>
                    </div>
                </div>
            </div>
            <!-- Navigation -->
            <nav class="flex-1 px-3 lg:px-4 py-6 overflow-y-auto">
                <p class="text-xs uppercase tracking-widest text-green-300/40 font-bold px-4 mb-3">Menu Principal</p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-white/20 text-white shadow-lg' : 'text-green-100/80 hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            Dashboard
                        </a>
                    </li>
                    @if(auth()->guard('admin')->user()->isAdmin())
                    <li>
                        <a href="{{ route('admin.candidates.index') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.candidates.*') ? 'bg-white/20 text-white shadow-lg' : 'text-green-100/80 hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Candidatos
                        </a>
                    </li>
                    @endif
                    <li>
                        <a href="{{ route('admin.voters.index') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.voters.*') ? 'bg-white/20 text-white shadow-lg' : 'text-green-100/80 hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Votantes
                        </a>
                    </li>
                    @if(auth()->guard('admin')->user()->isAdmin())
                    <li>
                        <a href="{{ route('admin.settings') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.settings') ? 'bg-white/20 text-white shadow-lg' : 'text-green-100/80 hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Configuracion
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.moderators.index') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.moderators.*') ? 'bg-white/20 text-white shadow-lg' : 'text-green-100/80 hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Moderadores
                        </a>
                    </li>
                    @endif
                    <li>
                        <a href="{{ route('admin.report') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.report') ? 'bg-white/20 text-white shadow-lg' : 'text-green-100/80 hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Reporte
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.logs') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.logs') ? 'bg-white/20 text-white shadow-lg' : 'text-green-100/80 hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Logs
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- Logout -->
            <div class="p-4 border-t border-white/10">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 rounded-xl font-semibold text-sm transition-all duration-200 bg-white/10 hover:bg-red-600 text-white/80 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Cerrar Sesion
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 w-full lg:ml-72 transition-all duration-300">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-4 sticky top-0 z-20 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span class="hidden sm:inline">Alcaldia de Bucaramanga</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    @if(isset($settings) && $settings && $settings->isVotingOpen())
                    <span class="flex items-center gap-2 text-xs sm:text-sm font-semibold px-2 sm:px-3 py-1.5 rounded-full" style="background-color: #f0f9ee; color: #285F19;">
                        <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                        <span class="hidden sm:inline">Votacion activa</span>
                        <span class="sm:hidden">Activa</span>
                    </span>
                    @endif
                </div>
            </header>
            <!-- Page Content -->
            <div class="p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                <div class="flex items-center gap-3 bg-primary-50 border-l-4 border-primary-500 text-primary-800 px-4 sm:px-5 py-3 sm:py-4 rounded-r-lg mb-6 shadow-sm">
                    <svg class="w-5 h-5 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm sm:text-base">{{ session('success') }}</span>
                </div>
                @endif
                @if(session('error'))
                <div class="flex items-center gap-3 border-l-4 px-4 sm:px-5 py-3 sm:py-4 rounded-r-lg mb-6 shadow-sm" style="background-color: #fef2f2; border-color: #C20E1A; color: #991b1b;">
                    <svg class="w-5 h-5 shrink-0" style="color: #C20E1A;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm sm:text-base">{{ session('error') }}</span>
                </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
