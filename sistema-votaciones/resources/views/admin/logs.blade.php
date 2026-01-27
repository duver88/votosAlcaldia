@extends('layouts.admin')
@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">Logs de Actividad</h1>
<!-- Filters -->
<div class="card mb-6">
    <form action="{{ route('admin.logs') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-field">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="input-field">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Accion</label>
            <input type="text" name="action" value="{{ request('action') }}" placeholder="login, vote, etc." class="input-field">
        </div>
        <button type="submit" class="btn-primary">Filtrar</button>
        <a href="{{ route('admin.logs') }}" class="btn-secondary">Limpiar</a>
    </form>
</div>
<div class="card">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-3 px-4">Fecha/Hora</th>
                    <th class="text-left py-3 px-4">Usuario/Admin</th>
                    <th class="text-left py-3 px-4">Accion</th>
                    <th class="text-left py-3 px-4">Descripcion</th>
                    <th class="text-left py-3 px-4">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4 text-sm text-gray-600">
                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="py-3 px-4">
                        @if($log->admin)
                        <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm">
                            Admin: {{ $log->admin->name }}
                        </span>
                        @elseif($log->user_cedula)
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                            Cedula: {{ $log->user_cedula }}
                        </span>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <span class="font-semibold">{{ $log->action }}</span>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-600">
                        {{ $log->description ?? '-' }}
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-500">
                        {{ $log->ip_address }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-gray-500">
                        No hay registros de actividad.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $logs->withQueryString()->links() }}
    </div>
</div>
@endsection
