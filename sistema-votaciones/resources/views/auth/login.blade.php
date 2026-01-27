<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesion - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-primary-500 to-primary-700 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Sistema de Votaciones</h1>
            <p class="text-gray-600 mt-2">Ingrese sus credenciales</p>
        </div>
        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
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
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contrasena</label>
                <input type="password" name="password" id="password" required
                    class="input-field text-lg @error('password') border-red-500 @enderror"
                    placeholder="Ingrese su contrasena">
                @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full btn-primary text-lg py-3">
                Ingresar
            </button>
        </form>
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>Votantes: ingrese su numero de cedula</p>
            <p>Administradores: ingrese su email</p>
        </div>
    </div>
</body>
</html>
