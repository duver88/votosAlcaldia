@extends('layouts.admin')
@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 sm:mb-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-primary-700 mb-1">Candidatos</h1>
        <p class="text-xs sm:text-sm text-gray-500">Gestion de candidatos del proceso electoral</p>
    </div>
    <a href="{{ route('admin.candidates.create') }}" class="btn-primary flex items-center gap-2 text-sm">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        Agregar Candidato
    </a>
</div>

<!-- Stats mini -->
<div class="grid grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background-color: #f0f9ee;">
            <svg class="w-5 h-5" style="color: #43883d;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Total</p>
            <p class="text-2xl font-bold text-gray-800">{{ $candidates->count() }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background-color: #f0f9ee;">
            <svg class="w-5 h-5" style="color: #43883d;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Activos</p>
            <p class="text-2xl font-bold" style="color: #285F19;">{{ $candidates->where('is_active', true)->count() }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background-color: #fef2f2;">
            <svg class="w-5 h-5" style="color: #C20E1A;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Inactivos</p>
            <p class="text-2xl font-bold" style="color: #C20E1A;">{{ $candidates->where('is_active', false)->count() }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr style="background-color: #f9fafb;">
                    <th class="text-left py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Orden</th>
                    <th class="text-left py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Candidato</th>
                    <th class="text-center py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Votos</th>
                    <th class="text-center py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Estado</th>
                    <th class="text-right py-4 px-5 text-xs font-bold uppercase tracking-wider text-gray-400">Acciones</th>
                </tr>
            </thead>
            <tbody id="sortable-candidates">
                @forelse($candidates as $candidate)
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors" data-id="{{ $candidate->id }}">
                    <td class="py-4 px-5">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-500 text-sm font-bold cursor-move">
                            {{ $candidate->position + 1 }}
                        </span>
                    </td>
                    <td class="py-4 px-5">
                        <div class="flex items-center gap-4">
                            @if($candidate->photo)
                            <img src="{{ Storage::url($candidate->photo) }}" alt="{{ $candidate->name }}" class="w-12 h-12 rounded-lg object-cover shadow-sm" style="border: 2px solid #e5e7eb;">
                            @else
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center shadow-sm" style="background: linear-gradient(135deg, #43883d, #93C01F); border: 2px solid #e5e7eb;">
                                <span class="text-white font-bold text-lg">{{ strtoupper(substr($candidate->name, 0, 1)) }}</span>
                            </div>
                            @endif
                            <div>
                                <p class="font-semibold text-gray-800">{{ $candidate->name }}</p>
                                @if($candidate->is_blank_vote)
                                <span class="inline-flex items-center gap-1 text-xs text-gray-500 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Voto en Blanco
                                </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-5 text-center">
                        <span class="inline-flex items-center justify-center min-w-[2.5rem] px-3 py-1.5 rounded-lg font-bold text-sm" style="background-color: #f0f9ee; color: #285F19;">
                            {{ $candidate->votes_count }}
                        </span>
                    </td>
                    <td class="py-4 px-5 text-center">
                        <form action="{{ route('admin.candidates.toggle', $candidate) }}" method="POST" class="inline">
                            @csrf
                            @if($candidate->is_active)
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition-all hover:shadow-md" style="background-color: #f0f9ee; color: #285F19;">
                                <span class="w-2 h-2 rounded-full" style="background-color: #43883d;"></span>
                                Activo
                            </button>
                            @else
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition-all hover:shadow-md" style="background-color: #fef2f2; color: #C20E1A;">
                                <span class="w-2 h-2 rounded-full" style="background-color: #C20E1A;"></span>
                                Inactivo
                            </button>
                            @endif
                        </form>
                    </td>
                    <td class="py-4 px-5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.candidates.edit', $candidate) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all hover:shadow-md" style="background-color: #f0f9ee; color: #285F19;">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Editar
                            </a>
                            @if(!$candidate->hasVotes())
                            <form action="{{ route('admin.candidates.destroy', $candidate) }}" method="POST" class="inline" onsubmit="return confirm('Esta seguro de eliminar este candidato?')">
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
                    <td colspan="5" class="py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <p class="text-gray-400 font-medium">No hay candidatos registrados</p>
                            <a href="{{ route('admin.candidates.create') }}" class="btn-primary text-sm">Agregar primer candidato</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
