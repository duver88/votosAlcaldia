@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto text-center">
    <div class="card">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h2 class="text-3xl font-bold text-green-600 mb-4">Voto Registrado</h2>
        <p class="text-gray-600 mb-2">Su voto ha sido registrado exitosamente.</p>
        <p class="text-gray-600 mb-6">
            <span class="font-semibold">{{ $votedAt->format('d/m/Y') }}</span> a las <span class="font-semibold">{{ $votedAt->format('H:i:s') }}</span>
        </p>
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-6">
            Gracias por participar en el proceso electoral.
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-primary text-lg py-3 px-8">
                Cerrar Sesion
            </button>
        </form>
    </div>
</div>
@endsection
