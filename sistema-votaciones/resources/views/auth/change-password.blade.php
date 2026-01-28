@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto">
    <div class="card">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Cambiar Contrasena</h2>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
            <p class="font-semibold">Debe cambiar su contrasena antes de continuar.</p>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <p class="text-sm text-gray-600 font-semibold mb-2">Requisitos de la contrasena:</p>
            <ul class="text-sm text-gray-600 list-disc list-inside">
                <li>Minimo 8 caracteres</li>
                <li>Al menos una letra mayuscula</li>
                <li>Al menos un numero</li>
            </ul>
        </div>
        <form method="POST" action="{{ route('change-password') }}" class="space-y-6">
            @csrf
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nueva Contrasena</label>
                <input type="password" name="password" id="password" required
                    minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}"
                    title="Minimo 8 caracteres, al menos una mayuscula, una minuscula y un numero"
                    class="input-field @error('password') border-red-500 @enderror">
                @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contrasena</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="input-field">
            </div>
            <button type="submit" class="w-full btn-primary">
                Cambiar Contrasena
            </button>
        </form>
    </div>
</div>
@endsection
