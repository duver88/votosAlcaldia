@extends('layouts.admin')
@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl sm:text-3xl font-bold text-primary-700 mb-4 sm:mb-6">Editar Candidato</h1>
    <div class="card">
        <form action="{{ route('admin.candidates.update', $candidate) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" name="name" id="name" value="{{ old('name', $candidate->name) }}" required maxlength="255"
                    class="input-field @error('name') border-red-500 @enderror">
                @error('name')
                <p class="text-sm mt-1" style="color: #C20E1A;">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">Foto</label>
                @if($candidate->photo)
                <div class="mb-2">
                    <img src="{{ Storage::url($candidate->photo) }}" alt="{{ $candidate->name }}" class="w-24 h-24 rounded-lg object-cover border-4 border-primary-200">
                </div>
                @endif
                <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/jpg,image/gif"
                    class="w-full border border-gray-300 rounded-lg p-2 @error('photo') border-red-500 @enderror">
                <p class="text-sm text-gray-500 mt-1">Dejar vacio para mantener la foto actual</p>
                @error('photo')
                <p class="text-sm mt-1" style="color: #C20E1A;">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-4">
                <a href="{{ route('admin.candidates.index') }}" class="btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="btn-primary">
                    Actualizar Candidato
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
