@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto text-center">
    <div class="card">
        <div class="w-24 h-24 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h2 class="text-3xl font-bold text-primary-500 mb-4">Voto Registrado</h2>
        <p class="text-gray-600 mb-2">Su voto ha sido registrado exitosamente.</p>
        <p class="text-gray-600 mb-6">
            <span class="font-semibold">{{ $votedAt->format('d/m/Y') }}</span> a las <span class="font-semibold">{{ $votedAt->format('H:i:s') }}</span>
        </p>
        <div class="bg-primary-50 border border-primary-200 text-primary-700 px-4 py-3 rounded mb-6">
            Gracias por participar en las elecciones de los representantes de los empleados en la comision de personal del municipio de Bucaramanga.
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-primary text-lg py-3 px-8">
                Aceptar
            </button>
        </form>
    </div>
</div>
@endsection
