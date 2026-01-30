@extends('layouts.admin')
@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 sm:mb-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-primary-700 mb-1">Moderadores</h1>
        <p class="text-xs sm:text-sm text-gray-500">Usuarios con acceso de solo lectura</p>
    </div>
    <a href="{{ route('admin.moderators.create') }}" class="btn-primary flex items-center gap-2 text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        Agregar Moderador
    </a>
</div>

<div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr style="background-color: #f9fafb;">
                    <th class="text-left py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Nombre</th>
                    <th class="text-left py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Email</th>
                    <th class="text-center py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Creado</th>
                    <th class="text-right py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($moderators as $moderator)
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                    <td class="py-4 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background: linear-gradient(135deg, #6b7280, #9ca3af);">
                                {{ strtoupper(substr($moderator->name, 0, 2)) }}
                            </div>
                            <span class="font-semibold text-gray-800">{{ $moderator->name }}</span>
                        </div>
                    </td>
                    <td class="py-4 px-5 text-gray-600">{{ $moderator->email }}</td>
                    <td class="py-4 px-5 text-center text-sm text-gray-500">{{ $moderator->created_at->format('d/m/Y') }}</td>
                    <td class="py-4 px-5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <form action="{{ route('admin.moderators.reset-password', $moderator) }}" method="POST" class="inline" onsubmit="return confirm('Resetear contrasena?')">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all hover:shadow-md" style="background-color: #fef9c3; color: #92400e;">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                    Reset
                                </button>
                            </form>
                            <form action="{{ route('admin.moderators.destroy', $moderator) }}" method="POST" class="inline" onsubmit="return confirm('Eliminar moderador?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all hover:shadow-md" style="background-color: #fef2f2; color: #C20E1A;">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <p class="text-gray-400 font-medium">No hay moderadores registrados</p>
                            <a href="{{ route('admin.moderators.create') }}" class="btn-primary text-sm">Agregar primer moderador</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($moderators->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $moderators->links() }}
    </div>
    @endif
</div>
@endsection
