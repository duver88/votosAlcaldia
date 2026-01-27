@extends('layouts.admin')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Candidatos</h1>
    <a href="{{ route('admin.candidates.create') }}" class="btn-primary">
        Agregar Candidato
    </a>
</div>
<div class="card">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-3 px-4">Orden</th>
                    <th class="text-left py-3 px-4">Foto</th>
                    <th class="text-left py-3 px-4">Nombre</th>
                    <th class="text-center py-3 px-4">Votos</th>
                    <th class="text-center py-3 px-4">Estado</th>
                    <th class="text-right py-3 px-4">Acciones</th>
                </tr>
            </thead>
            <tbody id="sortable-candidates">
                @forelse($candidates as $candidate)
                <tr class="border-b hover:bg-gray-50" data-id="{{ $candidate->id }}">
                    <td class="py-3 px-4">
                        <span class="cursor-move text-gray-400">{{ $candidate->position + 1 }}</span>
                    </td>
                    <td class="py-3 px-4">
                        @if($candidate->photo)
                        <img src="{{ Storage::url($candidate->photo) }}" alt="{{ $candidate->name }}" class="w-12 h-12 rounded-full object-cover">
                        @else
                        <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-500">{{ substr($candidate->name, 0, 1) }}</span>
                        </div>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <span class="font-semibold">{{ $candidate->name }}</span>
                        @if($candidate->is_blank_vote)
                        <span class="ml-2 px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">Voto en Blanco</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center font-bold">{{ $candidate->votes_count }}</td>
                    <td class="py-3 px-4 text-center">
                        <form action="{{ route('admin.candidates.toggle', $candidate) }}" method="POST" class="inline">
                            @csrf
                            @if($candidate->is_active)
                            <button type="submit" class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm hover:bg-green-200">
                                Activo
                            </button>
                            @else
                            <button type="submit" class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm hover:bg-red-200">
                                Inactivo
                            </button>
                            @endif
                        </form>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <a href="{{ route('admin.candidates.edit', $candidate) }}" class="text-blue-600 hover:text-blue-800 mr-3">
                            Editar
                        </a>
                        @if(!$candidate->hasVotes())
                        <form action="{{ route('admin.candidates.destroy', $candidate) }}" method="POST" class="inline" onsubmit="return confirm('Esta seguro de eliminar este candidato?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">
                                Eliminar
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-500">
                        No hay candidatos registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
