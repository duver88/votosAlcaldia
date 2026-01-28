<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesion - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-primary-700 via-primary-500 to-primary-300 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
        <div class="text-center mb-6 sm:mb-8">
            <img src="https://www.bucaramanga.gov.co/wp-content/uploads/2025/06/escudo-alcaldia.png" alt="Alcaldia de Bucaramanga" class="h-16 sm:h-24 mx-auto mb-3 sm:mb-4">
            <h1 class="text-2xl font-bold text-primary-700">Sistema de Votaciones</h1>
            <p class="text-gray-500 mt-1 text-sm">Alcaldia de Bucaramanga</p>
        </div>
        @if(session('error'))
        <div class="border text-sm px-4 py-3 rounded mb-4" style="background-color: #fde8e8; border-color: #C20E1A; color: #C20E1A;">
            {{ session('error') }}
        </div>
        @endif
        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf
            <div>
                <label for="identificacion" class="block text-sm font-medium text-gray-700 mb-1">Cedula o Email</label>
                <input type="text" name="identificacion" id="identificacion" value="{{ old('identificacion') }}" required autofocus
                    class="input-field text-lg @error('identificacion') border-red-500 @enderror"
                    placeholder="Ingrese cedula o email">
                @error('identificacion')
                <p class="text-sm mt-1" style="color: #C20E1A;">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contrasena</label>
                <input type="password" name="password" id="password" required
                    class="input-field text-lg @error('password') border-red-500 @enderror"
                    placeholder="Ingrese su contrasena">
                @error('password')
                <p class="text-sm mt-1" style="color: #C20E1A;">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full btn-primary text-lg py-3">
                Ingresar
            </button>
        </form>
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>Ingrese su numero de cedula</p>
        </div>
    </div>
</body>
</html>
