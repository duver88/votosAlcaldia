@extends('layouts.admin')
@section('content')
<div class="max-w-2xl">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Configuracion de Votacion</h1>
    <div class="card mb-6">
        <h2 class="text-xl font-semibold mb-4">Estado Actual</h2>
        <div class="flex items-center justify-between">
            <div>
                @if($settings->is_active)
                <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full font-bold">
                    <span class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                    VOTACION ABIERTA
                </span>
                @else
                <span class="inline-flex items-center px-4 py-2 bg-red-100 text-red-800 rounded-full font-bold">
                    <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                    VOTACION CERRADA
                </span>
                @endif
            </div>
            <form action="{{ route('admin.settings.toggle') }}" method="POST">
                @csrf
                @if($settings->is_active)
                <button type="submit" class="btn-danger">Cerrar Votacion</button>
                @else
                <button type="submit" class="btn-primary">Abrir Votacion</button>
                @endif
            </form>
        </div>
    </div>
    <div class="card">
        <h2 class="text-xl font-semibold mb-4">Programacion Automatica</h2>
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="start_datetime" class="block text-sm font-medium text-gray-700 mb-1">Fecha y Hora de Apertura</label>
                <input type="datetime-local" name="start_datetime" id="start_datetime"
                    value="{{ $settings->start_datetime?->format('Y-m-d\TH:i') }}"
                    class="input-field @error('start_datetime') border-red-500 @enderror">
                @error('start_datetime')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="end_datetime" class="block text-sm font-medium text-gray-700 mb-1">Fecha y Hora de Cierre</label>
                <input type="datetime-local" name="end_datetime" id="end_datetime"
                    value="{{ $settings->end_datetime?->format('Y-m-d\TH:i') }}"
                    class="input-field @error('end_datetime') border-red-500 @enderror">
                @error('end_datetime')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">
                    La votacion se abrira/cerrara automaticamente segun las fechas configuradas, siempre que se haya ejecutado el comando de verificacion programado.
                </p>
            </div>
            <button type="submit" class="btn-primary">
                Guardar Configuracion
            </button>
        </form>
    </div>
</div>
@endsection
