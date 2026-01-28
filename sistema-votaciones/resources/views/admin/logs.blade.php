@extends('layouts.admin')
@section('content')
@php
    function getLogConfig($action) {
        return match($action) {
            'admin_login' => ['label' => 'Inicio Admin', 'bg' => 'linear-gradient(135deg, #ede9fe, #ddd6fe)', 'color' => '#6d28d9', 'iconBg' => '#7c3aed', 'icon' => 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            'admin_logout' => ['label' => 'Cierre Admin', 'bg' => 'linear-gradient(135deg, #faf5ff, #ede9fe)', 'color' => '#7c3aed', 'iconBg' => '#a78bfa', 'icon' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1'],
            'user_login', 'login' => ['label' => 'Inicio Sesion', 'bg' => 'linear-gradient(135deg, #ecfdf5, #d1fae5)', 'color' => '#065f46', 'iconBg' => '#10b981', 'icon' => 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1'],
            'user_logout', 'logout' => ['label' => 'Cierre Sesion', 'bg' => 'linear-gradient(135deg, #f9fafb, #f3f4f6)', 'color' => '#4b5563', 'iconBg' => '#9ca3af', 'icon' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1'],
            'vote' => ['label' => 'Voto Emitido', 'bg' => 'linear-gradient(135deg, #eff6ff, #dbeafe)', 'color' => '#1e40af', 'iconBg' => '#3b82f6', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            'login_failed' => ['label' => 'Login Fallido', 'bg' => 'linear-gradient(135deg, #fef2f2, #fde8e8)', 'color' => '#991b1b', 'iconBg' => '#ef4444', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z'],
            'account_blocked' => ['label' => 'Cuenta Bloqueada', 'bg' => 'linear-gradient(135deg, #fef2f2, #fde8e8)', 'color' => '#991b1b', 'iconBg' => '#dc2626', 'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
            'password_changed' => ['label' => 'Cambio Clave', 'bg' => 'linear-gradient(135deg, #fffbeb, #fef3c7)', 'color' => '#92400e', 'iconBg' => '#f59e0b', 'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'],
            'voting_toggled' => ['label' => 'Estado Votacion', 'bg' => 'linear-gradient(135deg, #f0f9ee, #dcfce7)', 'color' => '#166534', 'iconBg' => '#43883d', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
            'settings_updated' => ['label' => 'Config Actualizada', 'bg' => 'linear-gradient(135deg, #eff6ff, #dbeafe)', 'color' => '#1e40af', 'iconBg' => '#2563eb', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
            default => ['label' => $action, 'bg' => 'linear-gradient(135deg, #f9fafb, #f3f4f6)', 'color' => '#6b7280', 'iconBg' => '#9ca3af', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        };
    }
@endphp

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 sm:mb-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-primary-700 mb-1">Actividad del Sistema</h1>
        <p class="text-xs sm:text-sm text-gray-500">Historial completo de acciones y eventos</p>
    </div>
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 px-5 py-3 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #43883d, #285F19);">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Registros</p>
            <p class="text-2xl font-bold text-gray-800">{{ $logs->total() }}</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
        <span class="text-sm font-semibold text-gray-600">Filtros</span>
    </div>
    <form action="{{ route('admin.logs') }}" method="GET" class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4 items-stretch sm:items-end">
        <div class="flex-1 min-w-0">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Desde</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-field">
        </div>
        <div class="flex-1 min-w-0">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Hasta</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="input-field">
        </div>
        <div class="flex-1 min-w-0">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tipo</label>
            <select name="action" class="input-field">
                <option value="">Todas las acciones</option>
                <option value="user_login" {{ request('action') == 'user_login' ? 'selected' : '' }}>Inicio Sesion</option>
                <option value="user_logout" {{ request('action') == 'user_logout' ? 'selected' : '' }}>Cierre Sesion</option>
                <option value="admin_login" {{ request('action') == 'admin_login' ? 'selected' : '' }}>Inicio Admin</option>
                <option value="admin_logout" {{ request('action') == 'admin_logout' ? 'selected' : '' }}>Cierre Admin</option>
                <option value="vote" {{ request('action') == 'vote' ? 'selected' : '' }}>Voto</option>
                <option value="login_failed" {{ request('action') == 'login_failed' ? 'selected' : '' }}>Login Fallido</option>
                <option value="password_changed" {{ request('action') == 'password_changed' ? 'selected' : '' }}>Cambio Clave</option>
                <option value="voting_toggled" {{ request('action') == 'voting_toggled' ? 'selected' : '' }}>Estado Votacion</option>
                <option value="settings_updated" {{ request('action') == 'settings_updated' ? 'selected' : '' }}>Config Actualizada</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Buscar
            </button>
            <a href="{{ route('admin.logs') }}" class="btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Desktop Timeline Table -->
<div class="hidden sm:block bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr style="background: linear-gradient(135deg, #f8fafc, #f1f5f9);">
                    <th class="text-left py-4 px-5 font-bold text-xs uppercase tracking-wider text-gray-500 w-40">Fecha</th>
                    <th class="text-left py-4 px-5 font-bold text-xs uppercase tracking-wider text-gray-500 w-36">Usuario</th>
                    <th class="text-left py-4 px-5 font-bold text-xs uppercase tracking-wider text-gray-500 w-44">Evento</th>
                    <th class="text-left py-4 px-5 font-bold text-xs uppercase tracking-wider text-gray-500">Detalle</th>
                    <th class="text-right py-4 px-5 font-bold text-xs uppercase tracking-wider text-gray-500 w-28">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                @php $cfg = getLogConfig($log->action); @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50/70 transition-all duration-150 group">
                    <td class="py-4 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $cfg['iconBg'] }};"></div>
                            <div>
                                <div class="text-sm font-semibold text-gray-800">{{ $log->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-400 font-mono">{{ $log->created_at->format('H:i:s') }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-5">
                        @if($log->user_cedula)
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs text-white shadow-sm" style="background: linear-gradient(135deg, {{ $cfg['iconBg'] }}, {{ $cfg['color'] }});">
                                {{ strtoupper(substr($log->user_cedula, 0, 2)) }}
                            </div>
                            <span class="font-semibold text-sm text-gray-700">{{ $log->user_cedula }}</span>
                        </div>
                        @else
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-gray-100">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <span class="text-sm text-gray-400 italic">Sistema</span>
                        </div>
                        @endif
                    </td>
                    <td class="py-4 px-5">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm" style="background: {{ $cfg['bg'] }}; color: {{ $cfg['color'] }};">
                            <div class="w-5 h-5 rounded-md flex items-center justify-center shadow-sm" style="background-color: {{ $cfg['iconBg'] }};">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $cfg['icon'] }}"></path></svg>
                            </div>
                            {{ $cfg['label'] }}
                        </div>
                    </td>
                    <td class="py-4 px-5">
                        <span class="text-sm text-gray-600 group-hover:text-gray-800 transition-colors">{{ $log->description }}</span>
                    </td>
                    <td class="py-4 px-5 text-right">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-50 text-xs text-gray-400 font-mono">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                            {{ $log->ip_address }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-20 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-50 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <p class="text-gray-500 font-semibold text-base">Sin registros</p>
                        <p class="text-gray-400 text-sm mt-1">Los eventos apareceran aqui cuando haya actividad</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $logs->withQueryString()->links() }}
    </div>
    @endif
</div>

<!-- Mobile Cards -->
<div class="sm:hidden space-y-3">
    @forelse($logs as $log)
    @php $cfg = getLogConfig($log->action); @endphp
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm" style="background: {{ $cfg['bg'] }}; color: {{ $cfg['color'] }};">
                <div class="w-5 h-5 rounded-md flex items-center justify-center shadow-sm" style="background-color: {{ $cfg['iconBg'] }};">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $cfg['icon'] }}"></path></svg>
                </div>
                {{ $cfg['label'] }}
            </div>
            <span class="text-xs text-gray-400 font-mono">{{ $log->created_at->format('H:i:s') }}</span>
        </div>
        <p class="text-sm text-gray-700 mb-3 leading-relaxed">{{ $log->description }}</p>
        <div class="flex items-center justify-between pt-3 border-t border-gray-50">
            <div class="flex items-center gap-2">
                @if($log->user_cedula)
                <div class="w-6 h-6 rounded-md flex items-center justify-center text-white font-bold text-[10px]" style="background: linear-gradient(135deg, {{ $cfg['iconBg'] }}, {{ $cfg['color'] }});">
                    {{ strtoupper(substr($log->user_cedula, 0, 2)) }}
                </div>
                <span class="text-xs font-medium text-gray-600">{{ $log->user_cedula }}</span>
                @else
                <span class="text-xs text-gray-400 italic">Sistema</span>
                @endif
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-400">
                <span>{{ $log->created_at->format('d/m/Y') }}</span>
                <span class="font-mono bg-gray-50 px-1.5 py-0.5 rounded">{{ $log->ip_address }}</span>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 py-16 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-50 flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </div>
        <p class="text-gray-500 font-semibold">Sin registros</p>
        <p class="text-gray-400 text-sm mt-1">Los eventos apareceran aqui cuando haya actividad</p>
    </div>
    @endforelse

    @if($logs->hasPages())
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 px-5 py-4">
        {{ $logs->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
