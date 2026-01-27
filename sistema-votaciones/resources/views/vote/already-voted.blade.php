@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto text-center">
    <div class="card">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Usted ya emitio su voto</h2>
        <p class="text-gray-600 mb-6">
            Su voto fue registrado el <span class="font-semibold">{{ $votedAt->format('d/m/Y') }}</span> a las <span class="font-semibold">{{ $votedAt->format('H:i:s') }}</span>
        </p>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-primary">
                Cerrar Sesion
            </button>
        </form>
    </div>
</div>
@endsection
