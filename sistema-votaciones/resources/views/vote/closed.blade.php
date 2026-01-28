@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto text-center">
    <div class="card">
        <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6" style="background-color: #fde8e8;">
            <svg class="w-10 h-10" style="color: #C20E1A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-primary-700 mb-4">Votacion No Disponible</h2>
        <p class="text-gray-600 mb-6">
            La votacion no esta disponible en este momento. Por favor, intente mas tarde o contacte al administrador.
        </p>
        @auth
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-secondary">
                Cerrar Sesion
            </button>
        </form>
        @else
        <a href="{{ route('login') }}" class="btn-secondary inline-block">
            Volver al Login
        </a>
        @endauth
    </div>
</div>
@endsection
