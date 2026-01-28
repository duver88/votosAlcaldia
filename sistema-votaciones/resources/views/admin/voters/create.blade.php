@extends('layouts.admin')
@section('content')
<div class="max-w-2xl">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Agregar Votante</h1>
    <div class="card">
        <form action="{{ route('admin.voters.store') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="cedula" class="block text-sm font-medium text-gray-700 mb-1">Cedula</label>
                <input type="text" name="cedula" id="cedula" value="{{ old('cedula') }}" required
                    maxlength="20" pattern="[0-9]+" title="La cedula solo debe contener numeros"
                    class="input-field @error('cedula') border-red-500 @enderror">
                @error('cedula')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contrasena Inicial</label>
                <input type="text" name="password" id="password" value="{{ old('password') }}" required
                    minlength="8" title="La contrasena debe tener al menos 8 caracteres"
                    class="input-field @error('password') border-red-500 @enderror">
                <p class="text-sm text-gray-500 mt-1">El votante debera cambiar esta contrasena en su primer inicio de sesion.</p>
                @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-4">
                <a href="{{ route('admin.voters.index') }}" class="btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="btn-primary">
                    Guardar Votante
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
