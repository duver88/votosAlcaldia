@extends('layouts.admin')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Votantes</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.voters.import') }}" class="btn-secondary">
            Importar CSV
        </a>
        <a href="{{ route('admin.voters.create') }}" class="btn-primary">
            Agregar Votante
        </a>
    </div>
</div>
<!-- Filters -->
<div class="card mb-6">
    <form action="{{ route('admin.voters.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Cedula</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar..." class="input-field">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Filtrar</label>
            <select name="filter" class="input-field">
                <option value="">Todos</option>
                <option value="blocked" {{ request('filter') == 'blocked' ? 'selected' : '' }}>Bloqueados</option>
                <option value="voted" {{ request('filter') == 'voted' ? 'selected' : '' }}>Ya votaron</option>
                <option value="pending" {{ request('filter') == 'pending' ? 'selected' : '' }}>No han votado</option>
            </select>
        </div>
        <button type="submit" class="btn-primary">Filtrar</button>
        <a href="{{ route('admin.voters.index') }}" class="btn-secondary">Limpiar</a>
    </form>
</div>
<div class="card">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-3 px-4">Cedula</th>
                    <th class="text-center py-3 px-4">Estado</th>
                    <th class="text-center py-3 px-4">Cambio Contrasena</th>
                    <th class="text-center py-3 px-4">Voto</th>
                    <th class="text-center py-3 px-4">Login</th>
                    <th class="text-center py-3 px-4">Hora Voto</th>
                    <th class="text-right py-3 px-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($voters as $voter)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4 font-semibold">{{ $voter->cedula }}</td>
                    <td class="py-3 px-4 text-center">
                        @if($voter->is_blocked)
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-sm">Bloqueado</span>
                        @else
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm">Activo</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center">
                        @if($voter->must_change_password)
                        <span class="text-yellow-600">Pendiente</span>
                        @else
                        <span class="text-green-600">Completado</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center">
                        @if($voter->has_voted)
                        <span class="text-green-600 font-semibold">Si</span>
                        @else
                        <span class="text-gray-400">No</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center text-sm text-gray-600">
                        {{ $voter->login_at?->format('H:i:s') ?? '-' }}
                    </td>
                    <td class="py-3 px-4 text-center text-sm text-gray-600">
                        {{ $voter->voted_at?->format('H:i:s') ?? '-' }}
                    </td>
                    <td class="py-3 px-4 text-right">
                        @if($voter->is_blocked)
                        <form action="{{ route('admin.voters.unblock', $voter) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-blue-600 hover:text-blue-800 mr-2">
                                Desbloquear
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('admin.voters.reset-password', $voter) }}" method="POST" class="inline" onsubmit="return confirm('Esta seguro de resetear la contrasena?')">
                            @csrf
                            <button type="submit" class="text-yellow-600 hover:text-yellow-800 mr-2">
                                Reset Pass
                            </button>
                        </form>
                        @if(!$voter->has_voted)
                        <form action="{{ route('admin.voters.destroy', $voter) }}" method="POST" class="inline" onsubmit="return confirm('Esta seguro de eliminar este votante?')">
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
                    <td colspan="7" class="py-8 text-center text-gray-500">
                        No hay votantes registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $voters->withQueryString()->links() }}
    </div>
</div>
@endsection
