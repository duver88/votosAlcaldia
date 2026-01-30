@extends('layouts.admin')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-4 mb-6 sm:mb-8">
        <a href="{{ route('admin.moderators.index') }}" class="w-10 h-10 rounded-xl flex items-center justify-center transition-all hover:shadow-md" style="background-color: #f3f4f6;">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-primary-700 mb-1">Agregar Moderador</h1>
            <p class="text-xs sm:text-sm text-gray-500">Usuario con acceso de solo lectura</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 sm:p-8">
        <form action="{{ route('admin.moderators.store') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Nombre Completo</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="input-field @error('name') border-red-500 @enderror"
                    placeholder="Nombre del moderador">
                @error('name')
                <p class="text-sm mt-1" style="color: #C20E1A;">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="input-field @error('email') border-red-500 @enderror"
                    placeholder="email@ejemplo.com">
                @error('email')
                <p class="text-sm mt-1" style="color: #C20E1A;">{{ $message }}</p>
                @enderror
            </div>

            <div class="p-4 rounded-xl" style="background-color: #f0f9ee; border: 1px solid #d1e7cc;">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 mt-0.5 shrink-0" style="color: #43883d;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <p class="text-sm font-semibold" style="color: #285F19;">Permisos del Moderador</p>
                        <ul class="text-xs mt-2 space-y-1" style="color: #43883d;">
                            <li>- Ver lista de votantes (sin modificar)</li>
                            <li>- Ver reporte de resultados</li>
                            <li>- No puede modificar candidatos, votantes ni configuracion</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="btn-primary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Crear Moderador
                </button>
                <a href="{{ route('admin.moderators.index') }}" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
