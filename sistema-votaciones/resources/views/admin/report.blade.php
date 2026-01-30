@extends('layouts.admin')

@push('styles')
<style>
    @media print {
        /* OCULTAR COMPLETAMENTE el sidebar */
        aside {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            position: absolute !important;
            left: -9999px !important;
        }

        /* Ocultar header, overlay y botones */
        header, nav, .sidebar, .mobile-menu-btn, .btn-primary, .btn-secondary, .print-btn {
            display: none !important;
        }

        /* Reset body */
        body {
            background: white !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Reset HTML */
        html {
            overflow: visible !important;
        }

        /* Contenedor principal sin margen del sidebar */
        .flex.min-h-screen {
            display: block !important;
        }

        /* Main content ocupa todo el ancho */
        main {
            margin: 0 !important;
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            position: static !important;
        }

        /* Contenido de la pagina */
        main > div {
            padding: 0 !important;
        }

        /* Ocultar overlay del mobile menu */
        .fixed.inset-0 {
            display: none !important;
        }

        /* Show print header */
        .print-header {
            display: block !important;
        }

        /* Card styling for print */
        .candidate-card {
            break-inside: avoid;
            page-break-inside: avoid;
            border: 1px solid #ddd !important;
            margin-bottom: 15px !important;
            box-shadow: none !important;
        }

        /* Ensure images print */
        img {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Progress bar print colors */
        .progress-bar-fill {
            background: #43883d !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Stats grid for print */
        .stats-grid {
            display: flex !important;
            justify-content: space-between !important;
            gap: 20px !important;
        }

        .stats-grid > div {
            flex: 1 !important;
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }

        /* Page margins */
        @page {
            margin: 1.5cm;
            size: A4;
        }

        /* Hide participation badge duplicate and screen header */
        .participation-badge-screen, .screen-header {
            display: none !important;
        }

        /* Ocultar alertas de session */
        .border-l-4 {
            display: none !important;
        }
    }

    /* Hide print header on screen */
    .print-header {
        display: none;
    }
</style>
@endpush

@section('content')
<!-- Print Header (only visible when printing) -->
<div class="print-header" style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #43883d;">
    <img src="https://www.bucaramanga.gov.co/wp-content/uploads/2025/06/escudo-alcaldia.png" alt="Alcaldía de Bucaramanga" style="height: 80px; margin-bottom: 10px;">
    <h1 style="font-size: 24px; font-weight: bold; color: #285F19; margin: 10px 0 5px;">REPORTE DE RESULTADOS</h1>
    <p style="font-size: 14px; color: #58585a;">Sistema de Votaciones - Alcaldía de Bucaramanga</p>
    <p style="font-size: 12px; color: #888; margin-top: 5px;">Generado el: {{ now()->format('d/m/Y H:i') }}</p>
</div>

<div>
    <!-- Page Header (hidden on print) -->
    <div class="screen-header flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 sm:mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-primary-700 mb-1">Reporte de Resultados</h1>
            <p class="text-xs sm:text-sm text-gray-500">Resultados oficiales del proceso electoral</p>
        </div>
        <div class="flex items-center gap-3 participation-badge-screen">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm shadow-md transition-all hover:shadow-lg print-btn" style="background-color: #285F19; color: white;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimir
            </button>
            <span class="text-sm text-gray-500">Participacion:</span>
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm shadow-md" style="background: linear-gradient(135deg, #43883d, #93C01F); color: white;">
                {{ $participation }}%
            </span>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="stats-grid grid grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-4 text-center">
            <p class="text-3xl font-bold text-primary-700">{{ number_format($totalVoters) }}</p>
            <p class="text-xs text-gray-500 mt-1">Votantes Registrados</p>
        </div>
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-4 text-center">
            <p class="text-3xl font-bold" style="color: #285F19;">{{ number_format($totalVotes) }}</p>
            <p class="text-xs text-gray-500 mt-1">Votos Emitidos</p>
        </div>
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-4 text-center">
            <p class="text-3xl font-bold" style="color: #93C01F;">{{ $participation }}%</p>
            <p class="text-xs text-gray-500 mt-1">Participacion</p>
        </div>
    </div>

    <!-- Candidate Cards -->
    <div class="space-y-4">
        @foreach($candidates as $index => $candidate)
        <div class="candidate-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow">
            <div class="flex items-stretch">
                <!-- Left: Photo -->
                <div class="w-28 sm:w-36 shrink-0 bg-gray-100 relative">
                    @if($candidate->photo)
                        <img src="{{ asset('storage/' . $candidate->photo) }}"
                             alt="{{ $candidate->name }}"
                             class="w-full h-full object-cover absolute inset-0">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    @endif
                    @if($candidate->is_blank_vote)
                        <div class="absolute inset-0 flex items-center justify-center bg-gray-100">
                            <div class="text-center px-2">
                                <p class="font-bold text-gray-600 text-sm">VOTO EN</p>
                                <p class="font-bold text-gray-600 text-sm">BLANCO</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right: Info -->
                <div class="flex-1 p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <!-- Name and votes -->
                        <div class="flex-1">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 uppercase tracking-wide" style="font-family: 'Oswald', sans-serif;">
                                {{ $candidate->name }}
                            </h2>
                            <p class="text-2xl sm:text-3xl font-bold mt-2" style="color: #43883d;">
                                {{ number_format($candidate->votes_count) }}
                                <span class="text-base font-normal text-gray-500">Votos</span>
                            </p>
                        </div>

                        <!-- Percentage -->
                        <div class="text-right">
                            <p class="text-3xl sm:text-4xl font-bold" style="color: #285F19;">
                                {{ $candidate->percentage }}%
                            </p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-4">
                        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                            <div class="progress-bar-fill h-full rounded-full transition-all duration-700"
                                 style="width: {{ $candidate->percentage }}%; background: linear-gradient(90deg, #43883d, #93C01F);">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($candidates->isEmpty())
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-12 text-center">
        <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <p class="text-gray-500 text-lg">No hay candidatos activos</p>
    </div>
    @endif
</div>
@endsection
