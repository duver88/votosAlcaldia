@extends('layouts.admin')
@section('content')
<div class="max-w-2xl" x-data="settingsPage()" x-init="init()">
    <div class="flex items-center justify-between mb-4 sm:mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-primary-700">Configuracion de Votacion</h1>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-2 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Hora Colombia</p>
            <p class="text-xl font-bold text-primary-700" x-text="currentColombiaTime"></p>
            <p class="text-xs text-gray-500" x-text="currentColombiaDate"></p>
        </div>
    </div>
    <div class="card mb-6">
        <h2 class="text-xl font-semibold mb-4">Estado Actual</h2>
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                @if($settings->is_active)
                <span class="inline-flex items-center px-4 py-2 bg-primary-100 text-primary-800 rounded-full font-bold">
                    <span class="w-3 h-3 bg-primary-500 rounded-full mr-2 animate-pulse"></span>
                    VOTACION ABIERTA
                </span>
                @else
                <span class="inline-flex items-center px-4 py-2 rounded-full font-bold" style="background-color: #fde8e8; color: #C20E1A;">
                    <span class="w-3 h-3 rounded-full mr-2" style="background-color: #C20E1A;"></span>
                    VOTACION CERRADA
                </span>
                @endif
            </div>
            <form action="{{ route('admin.settings.toggle') }}" method="POST">
                @csrf
                @if($settings->is_active)
                <button type="submit" class="btn-danger">Cerrar Votacion</button>
                @else
                <button type="submit" class="btn-primary">Abrir Votacion</button>
                @endif
            </form>
        </div>

        <!-- Countdown Timer -->
        <div x-show="countdown" class="mt-4 p-4 rounded-lg" :class="countdownType === 'open' ? 'bg-primary-50 border border-primary-200' : 'bg-amber-50 border border-amber-200'">
            <p class="text-sm font-medium" :class="countdownType === 'open' ? 'text-primary-700' : 'text-amber-700'">
                <span x-text="countdownLabel"></span>: <span x-text="countdown" class="font-bold"></span>
            </p>
        </div>
    </div>
    <div class="card mb-6">
        <h2 class="text-xl font-semibold mb-4">Programacion Automatica</h2>
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="start_datetime" class="block text-sm font-medium text-gray-700 mb-1">Fecha y Hora de Apertura</label>
                <input type="datetime-local" name="start_datetime" id="start_datetime"
                    value="{{ $settings->start_datetime?->format('Y-m-d\TH:i') }}"
                    class="input-field @error('start_datetime') border-red-500 @enderror">
                @error('start_datetime')
                <p class="text-sm mt-1" style="color: #C20E1A;">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="end_datetime" class="block text-sm font-medium text-gray-700 mb-1">Fecha y Hora de Cierre</label>
                <input type="datetime-local" name="end_datetime" id="end_datetime"
                    value="{{ $settings->end_datetime?->format('Y-m-d\TH:i') }}"
                    class="input-field @error('end_datetime') border-red-500 @enderror">
                @error('end_datetime')
                <p class="text-sm mt-1" style="color: #C20E1A;">{{ $message }}</p>
                @enderror
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">
                    La votacion se abrira y cerrara automaticamente segun las fechas programadas. La pagina se actualizara automaticamente cuando cambie el estado.
                </p>
            </div>
            <button type="submit" class="btn-primary">
                Guardar Configuracion
            </button>
        </form>
    </div>

    <!-- Reset del Sistema (Desplegable) -->
    <div class="card border-2" style="border-color: #C20E1A;">
        <button @click="showDangerZone = !showDangerZone" class="w-full flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #fde8e8;">
                    <svg class="w-5 h-5" style="color: #C20E1A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <h2 class="text-xl font-semibold" style="color: #C20E1A;">Zona de Peligro</h2>
                    <p class="text-xs text-gray-500">Acciones irreversibles</p>
                </div>
            </div>
            <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': showDangerZone }" style="color: #C20E1A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <div x-show="showDangerZone" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="mt-4">
            <div class="p-4 rounded-xl mb-4" style="background-color: #fef2f2; border: 1px solid #fecaca;">
                <p class="text-sm" style="color: #991b1b;">
                    <strong>Advertencia:</strong> El reset del sistema eliminara permanentemente todos los votantes, candidatos, votos y registros de actividad. Solo se mantendran los administradores. Esta accion NO se puede deshacer.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('admin.settings.export-backup') }}" class="btn-secondary flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Descargar Backup (CSV)
                </a>
                <button @click="showResetModal = true" class="btn-danger flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Resetear Sistema
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmacion Reset -->
    <div x-show="showResetModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div x-show="showResetModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 @click="showResetModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Content -->
            <div x-show="showResetModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10" style="background-color: #fde8e8;">
                            <svg class="h-6 w-6" style="color: #C20E1A;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                Confirmar Reset del Sistema
                            </h3>
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 mb-4">
                                    Esta a punto de eliminar <strong>permanentemente</strong>:
                                </p>
                                <ul class="text-sm text-gray-600 space-y-1 mb-4">
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4" style="color: #C20E1A;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        Todos los votantes
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4" style="color: #C20E1A;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        Todos los candidatos
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4" style="color: #C20E1A;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        Todos los votos
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4" style="color: #C20E1A;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        Historial de actividad
                                    </li>
                                </ul>
                                <div class="p-3 rounded-lg mb-4" style="background-color: #fef3c7;">
                                    <p class="text-xs font-medium" style="color: #92400e;">
                                        <strong>Recomendacion:</strong> Descargue el backup antes de continuar.
                                    </p>
                                </div>
                                <form action="{{ route('admin.settings.reset') }}" method="POST" id="resetForm">
                                    @csrf
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Escriba <strong class="text-red-600">RESETEAR</strong> para confirmar:
                                    </label>
                                    <input type="text" name="confirm_text" required autocomplete="off"
                                           class="input-field" placeholder="Escriba RESETEAR"
                                           pattern="RESETEAR" title="Debe escribir RESETEAR exactamente">
                                    @error('confirm_text')
                                    <p class="text-sm mt-1" style="color: #C20E1A;">{{ $message }}</p>
                                    @enderror
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button type="submit" form="resetForm" class="w-full sm:w-auto btn-danger">
                        Confirmar Reset
                    </button>
                    <a href="{{ route('admin.settings.export-backup') }}" class="w-full sm:w-auto btn-secondary text-center mt-2 sm:mt-0">
                        Descargar Backup
                    </a>
                    <button type="button" @click="showResetModal = false" class="w-full sm:w-auto mt-2 sm:mt-0 px-4 py-2 bg-white text-gray-700 rounded-lg border border-gray-300 hover:bg-gray-50 font-medium text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function settingsPage() {
    return {
        showResetModal: false,
        showDangerZone: false,
        countdown: null,
        countdownLabel: '',
        countdownType: '',
        currentColombiaTime: '',
        currentColombiaDate: '',
        // Timestamps de las fechas (ya en hora Colombia desde el servidor)
        startTime: @json($settings->start_datetime?->timestamp),
        endTime: @json($settings->end_datetime?->timestamp),
        isActive: @json($settings->is_active),

        init() {
            this.updateColombiaTime();
            this.updateCountdown();
            setInterval(() => {
                this.updateColombiaTime();
                this.updateCountdown();
            }, 1000);
        },

        // Obtener hora de Colombia usando Intl API
        getColombiaTime() {
            return new Date(new Date().toLocaleString('en-US', { timeZone: 'America/Bogota' }));
        },

        getColombiaTimestamp() {
            return Math.floor(this.getColombiaTime().getTime() / 1000);
        },

        updateColombiaTime() {
            const now = this.getColombiaTime();
            const hours = String(now.getHours()).padStart(2, '0');
            const mins = String(now.getMinutes()).padStart(2, '0');
            const secs = String(now.getSeconds()).padStart(2, '0');
            this.currentColombiaTime = `${hours}:${mins}:${secs}`;

            const days = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
            const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            this.currentColombiaDate = `${days[now.getDay()]} ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
        },

        updateCountdown() {
            const now = this.getColombiaTimestamp();

            // Si esta cerrada y hay fecha de apertura en el futuro
            if (!this.isActive && this.startTime && this.startTime > now) {
                this.countdownType = 'open';
                this.countdownLabel = 'Se abrira en';
                this.countdown = this.formatTime(this.startTime - now);

                // Auto-refresh cuando llegue la hora
                if (this.startTime - now <= 1) {
                    setTimeout(() => location.reload(), 1500);
                }
                return;
            }

            // Si esta abierta y hay fecha de cierre en el futuro
            if (this.isActive && this.endTime && this.endTime > now) {
                this.countdownType = 'close';
                this.countdownLabel = 'Se cerrara en';
                this.countdown = this.formatTime(this.endTime - now);

                // Auto-refresh cuando llegue la hora
                if (this.endTime - now <= 1) {
                    setTimeout(() => location.reload(), 1500);
                }
                return;
            }

            // Si esta cerrada pero deberia estar abierta (apertura paso, cierre no)
            if (!this.isActive && this.startTime && this.startTime <= now && (!this.endTime || this.endTime > now)) {
                location.reload();
                return;
            }

            // Si esta abierta pero deberia estar cerrada
            if (this.isActive && this.endTime && this.endTime <= now) {
                location.reload();
                return;
            }

            this.countdown = null;
        },

        formatTime(seconds) {
            if (seconds < 0) return '0s';

            const days = Math.floor(seconds / 86400);
            const hours = Math.floor((seconds % 86400) / 3600);
            const mins = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            let parts = [];
            if (days > 0) parts.push(days + 'd');
            if (hours > 0) parts.push(hours + 'h');
            if (mins > 0) parts.push(mins + 'm');
            parts.push(secs + 's');

            return parts.join(' ');
        }
    }
}
</script>
@endpush
@endsection
