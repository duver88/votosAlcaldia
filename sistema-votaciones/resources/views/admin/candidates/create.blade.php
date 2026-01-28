@extends('layouts.admin')
@section('content')
<div class="max-w-2xl">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Agregar Candidato</h1>
    <div class="card">
        <form action="{{ route('admin.candidates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required maxlength="255"
                    class="input-field @error('name') border-red-500 @enderror">
                @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">Foto (opcional)</label>
                <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/jpg,image/gif"
                    class="w-full border border-gray-300 rounded-lg p-2 @error('photo') border-red-500 @enderror">
                @error('photo')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-4">
                <a href="{{ route('admin.candidates.index') }}" class="btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="btn-primary">
                    Guardar Candidato
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
