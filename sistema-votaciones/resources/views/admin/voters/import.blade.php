@extends('layouts.admin')
@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl sm:text-3xl font-bold text-primary-700 mb-4 sm:mb-6">Importar Votantes</h1>
    <div class="card">
        <div class="bg-primary-50 border border-primary-200 text-primary-700 px-4 py-3 rounded mb-6">
            <p class="font-semibold mb-2">Formato del archivo CSV:</p>
            <ul class="list-disc list-inside text-sm">
                <li>Sin encabezados</li>
                <li>Formato: cedula,contrasena</li>
                <li>Ejemplo: 1234567890,pass123</li>
            </ul>
        </div>
        <form action="{{ route('admin.voters.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 mb-1">Archivo CSV</label>
                <input type="file" name="file" id="file" accept=".csv,.txt" required
                    class="w-full border border-gray-300 rounded-lg p-2 @error('file') border-red-500 @enderror">
                @error('file')
                <p class="text-sm mt-1" style="color: #C20E1A;">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-4">
                <a href="{{ route('admin.voters.index') }}" class="btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="btn-primary">
                    Importar Votantes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
