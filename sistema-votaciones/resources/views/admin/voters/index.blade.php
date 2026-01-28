@extends('layouts.admin')
@section('content')
<div x-data="voterDetail()" x-cloak>
<!-- Voter Detail Modal -->
<div x-show="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[85vh] overflow-hidden">
        <div class="p-6 border-b border-gray-100" style="background: linear-gradient(135deg, #285F19, #43883d);">
            <div class="flex items-center justify-between">
                <div class="text-white">
                    <p class="text-xs uppercase tracking-wider text-white/60">Detalle del Votante</p>
                    <p class="text-2xl font-bold" style="font-family: 'Oswald', sans-serif;" x-text="voter.cedula"></p>
                </div>
                <button @click="showModal = false" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white hover:bg-white/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto" style="max-height: 60vh;">
            <!-- Status badges -->
            <div class="flex flex-wrap gap-2 mb-5">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold" :style="voter.is_blocked ? 'background-color: #fef2f2; color: #C20E1A' : 'background-color: #f0f9ee; color: #285F19'" x-text="voter.is_blocked ? 'Bloqueado' : 'Activo'"></span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold" :style="voter.has_voted ? 'background-color: #f0f9ee; color: #285F19' : 'background-color: #fef9c3; color: #92400e'" x-text="voter.has_voted ? 'Ya voto' : 'No ha votado'"></span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold" :style="voter.must_change_password ? 'background-color: #fef9c3; color: #92400e' : 'background-color: #f0f9ee; color: #285F19'" x-text="voter.must_change_password ? 'Clave pendiente' : 'Clave actualizada'"></span>
            </div>
            <!-- Timeline -->
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Linea de Tiempo</h4>
            <div class="space-y-1 mb-6">
                <div class="flex items-center gap-3 p-3 rounded-lg" style="background-color: #f9fafb;">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background-color: #dbeafe; color: #2563eb;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-700">Inicio de sesion</p>
                        <p class="text-xs text-gray-400" x-text="voter.login_at || 'No ha ingresado'"></p>
                    </div>
                </div>
                <template x-for="log in getPasswordChangeLogs()" :key="'pw-' + log.date">
                    <div class="flex items-center gap-3 p-3 rounded-lg" style="background-color: #f9fafb;">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background-color: #fef9c3; color: #d97706;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-700">Cambio de contrasena</p>
                            <p class="text-xs text-gray-400" x-text="log.date"></p>
                        </div>
                    </div>
                </template>
                <template x-if="getPasswordChangeLogs().length === 0">
                    <div class="flex items-center gap-3 p-3 rounded-lg" style="background-color: #f9fafb;">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background-color: #fef9c3; color: #d97706;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-700">Cambio de contrasena</p>
                            <p class="text-xs text-gray-400">No ha cambiado la contrasena</p>
                        </div>
                    </div>
                </template>
                <div class="flex items-center gap-3 p-3 rounded-lg" style="background-color: #f9fafb;">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" :style="voter.has_voted ? 'background-color: #f0f9ee; color: #43883d' : 'background-color: #f3f4f6; color: #9ca3af'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-700">Emision de voto</p>
                        <p class="text-xs text-gray-400" x-text="voter.voted_at || 'No ha votado'"></p>
                    </div>
                </div>
                <template x-for="log in getLogoutLogs()" :key="'lo-' + log.date">
                    <div class="flex items-center gap-3 p-3 rounded-lg" style="background-color: #f9fafb;">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background-color: #fef2f2; color: #C20E1A;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-700">Cierre de sesion</p>
                            <p class="text-xs text-gray-400" x-text="log.date"></p>
                        </div>
                    </div>
                </template>
                <template x-if="getLogoutLogs().length === 0">
                    <div class="flex items-center gap-3 p-3 rounded-lg" style="background-color: #f9fafb;">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background-color: #f3f4f6; color: #9ca3af;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-700">Cierre de sesion</p>
                            <p class="text-xs text-gray-400">No ha cerrado sesion</p>
                        </div>
                    </div>
                </template>
            </div>
            <!-- Full Activity Log -->
            <template x-if="voter.logs && voter.logs.length > 0">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Registro Completo de Actividad</h4>
                    <div class="space-y-1">
                        <template x-for="(log, i) in voter.logs" :key="'log-' + i">
                            <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-gray-50 text-sm">
                                <span class="text-xs text-gray-400 shrink-0 pt-0.5" x-text="log.date"></span>
                                <span class="text-gray-600" x-text="log.description"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 sm:mb-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-primary-700 mb-1">Votantes</h1>
        <p class="text-xs sm:text-sm text-gray-500">Gestion del censo electoral</p>
    </div>
    <div class="flex gap-2 sm:gap-3">
        <a href="{{ route('admin.voters.import') }}" class="btn-secondary flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            <span class="hidden sm:inline">Importar</span> CSV
        </a>
        <a href="{{ route('admin.voters.create') }}" class="btn-primary flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            <span class="hidden sm:inline">Agregar</span> Votante
        </a>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 sm:p-5 mb-4 sm:mb-6">
    <form action="{{ route('admin.voters.index') }}" method="GET" class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4 items-stretch sm:items-end">
        <div class="flex-1 min-w-0 sm:min-w-50">
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Buscar Cedula</label>
            <div class="relative">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Numero de cedula..." class="input-field pl-10">
            </div>
        </div>
        <div class="min-w-[180px]">
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Filtrar por</label>
            <select name="filter" class="input-field">
                <option value="">Todos</option>
                <option value="blocked" {{ request('filter') == 'blocked' ? 'selected' : '' }}>Bloqueados</option>
                <option value="voted" {{ request('filter') == 'voted' ? 'selected' : '' }}>Ya votaron</option>
                <option value="pending" {{ request('filter') == 'pending' ? 'selected' : '' }}>No han votado</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filtrar
            </button>
            <a href="{{ route('admin.voters.index') }}" class="btn-secondary">Limpiar</a>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr style="background-color: #f9fafb;">
                    <th class="text-left py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Cedula</th>
                    <th class="text-center py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Estado</th>
                    <th class="text-center py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Contrasena</th>
                    <th class="text-center py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Voto</th>
                    <th class="text-center py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Login</th>
                    <th class="text-center py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Hora Voto</th>
                    <th class="text-right py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($voters as $voter)
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                    <td class="py-4 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center text-xs font-bold" style="background-color: #f0f9ee; color: #285F19;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                            </div>
                            <span class="font-bold text-gray-800">{{ $voter->cedula }}</span>
                        </div>
                    </td>
                    <td class="py-4 px-5 text-center">
                        @if($voter->is_blocked)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold" style="background-color: #fef2f2; color: #C20E1A;">
                            <span class="w-2 h-2 rounded-full" style="background-color: #C20E1A;"></span>
                            Bloqueado
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold" style="background-color: #f0f9ee; color: #285F19;">
                            <span class="w-2 h-2 rounded-full" style="background-color: #43883d;"></span>
                            Activo
                        </span>
                        @endif
                    </td>
                    <td class="py-4 px-5 text-center">
                        @if($voter->must_change_password)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold" style="color: #d97706;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Pendiente
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs font-semibold" style="color: #43883d;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Completado
                        </span>
                        @endif
                    </td>
                    <td class="py-4 px-5 text-center">
                        @if($voter->has_voted)
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full" style="background-color: #f0f9ee;">
                            <svg class="w-4 h-4" style="color: #43883d;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </span>
                        @else
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100">
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </span>
                        @endif
                    </td>
                    <td class="py-4 px-5 text-center">
                        <span class="text-xs text-gray-500 font-medium">{{ $voter->login_at?->format('H:i:s') ?? '-' }}</span>
                    </td>
                    <td class="py-4 px-5 text-center">
                        <span class="text-xs text-gray-500 font-medium">{{ $voter->voted_at?->format('H:i:s') ?? '-' }}</span>
                    </td>
                    <td class="py-4 px-5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button @click="openDetail({{ $voter->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all hover:shadow-md" style="background-color: #dbeafe; color: #2563eb;">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                Ver
                            </button>
                            @if($voter->is_blocked)
                            <form action="{{ route('admin.voters.unblock', $voter) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all hover:shadow-md" style="background-color: #f0f9ee; color: #285F19;">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                                    Desbloquear
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('admin.voters.reset-password', $voter) }}" method="POST" class="inline" onsubmit="return confirm('Esta seguro de resetear la contrasena?')">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all hover:shadow-md" style="background-color: #fef9c3; color: #92400e;">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                    Reset
                                </button>
                            </form>
                            @if(!$voter->has_voted)
                            <form action="{{ route('admin.voters.destroy', $voter) }}" method="POST" class="inline" onsubmit="return confirm('Esta seguro de eliminar este votante?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all hover:shadow-md" style="background-color: #fef2f2; color: #C20E1A;">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Eliminar
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <p class="text-gray-400 font-medium">No hay votantes registrados</p>
                            <a href="{{ route('admin.voters.create') }}" class="btn-primary text-sm">Agregar primer votante</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($voters->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $voters->withQueryString()->links() }}
    </div>
    @endif
</div>
</div>
<style>[x-cloak] { display: none !important; }</style>
@endsection
@push('scripts')
<script>
function voterDetail() {
    return {
        showModal: false,
        loading: false,
        voter: {},
        async openDetail(id) {
            this.loading = true;
            this.showModal = true;
            try {
                const res = await fetch(`/admin/voters/${id}/detail`);
                this.voter = await res.json();
            } catch (e) {
                console.error('Error:', e);
            }
            this.loading = false;
        },
        getPasswordChangeLogs() {
            if (!this.voter.logs) return [];
            return this.voter.logs.filter(l => l.action === 'password_changed');
        },
        getLogoutLogs() {
            if (!this.voter.logs) return [];
            return this.voter.logs.filter(l => l.action === 'user_logout');
        }
    }
}
</script>
@endpush
