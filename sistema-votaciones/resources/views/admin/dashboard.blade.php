@extends('layouts.admin')
@section('content')
<div x-data="dashboardData()" x-init="init()">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 sm:mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-primary-700 mb-1">Dashboard</h1>
            <p class="text-xs sm:text-sm text-gray-500">Monitoreo en tiempo real del proceso electoral</p>
        </div>
        <div>
            @if($settings && $settings->isVotingOpen())
            <span class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-full font-bold text-xs sm:text-sm shadow-md" style="background: linear-gradient(135deg, #43883d, #93C01F); color: white;">
                <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 bg-white rounded-full animate-pulse"></span>
                VOTACION ABIERTA
            </span>
            @else
            <span class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-full font-bold text-xs sm:text-sm shadow-md" style="background: linear-gradient(135deg, #C20E1A, #e04550); color: white;">
                <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 bg-white/60 rounded-full"></span>
                VOTACION CERRADA
            </span>
            @endif
        </div>
    </div>

    <!-- Clock & Schedule Bar -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 sm:p-5 mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #43883d, #285F19);">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xl sm:text-2xl font-bold text-gray-800" x-text="currentTime"></p>
                    <p class="text-xs text-gray-400 capitalize" x-text="currentDate"></p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-4 sm:gap-8 w-full sm:w-auto">
                <div class="text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-wider">Apertura</p>
                    <p class="font-bold text-xs sm:text-sm text-gray-700">{{ $settings?->start_datetime?->format('d/m/Y H:i') ?? 'No configurada' }}</p>
                </div>
                <div class="w-px h-8 bg-gray-200 hidden sm:block"></div>
                <div class="text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-wider">Cierre</p>
                    <p class="font-bold text-xs sm:text-sm text-gray-700">{{ $settings?->end_datetime?->format('d/m/Y H:i') ?? 'No configurado' }}</p>
                </div>
                <form action="{{ route('admin.settings.toggle') }}" method="POST" class="ml-auto sm:ml-0">
                    @csrf
                    @if($settings && $settings->is_active)
                    <button type="submit" class="btn-danger text-xs sm:text-sm px-4 sm:px-5 py-2">Cerrar Votacion</button>
                    @else
                    <button type="submit" class="btn-primary text-xs sm:text-sm px-4 sm:px-5 py-2">Abrir Votacion</button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-8">
        <!-- Total Votantes -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 rounded-full opacity-5" style="background-color: #43883d; transform: translate(30%, -30%);"></div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Total Votantes</p>
                    <p class="text-2xl sm:text-4xl font-bold text-gray-800" x-text="stats.totalVoters">{{ $totalVoters }}</p>
                    <p class="text-xs text-gray-400 mt-1">Registrados en el sistema</p>
                </div>
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: #f0f9ee;">
                    <svg class="w-6 h-6" style="color: #43883d;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </div>
        <!-- Votos Emitidos -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 rounded-full opacity-5" style="background-color: #93C01F; transform: translate(30%, -30%);"></div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Votos Emitidos</p>
                    <p class="text-2xl sm:text-4xl font-bold" style="color: #285F19;" x-text="stats.totalVotes">{{ $totalVotes }}</p>
                    <p class="text-xs text-gray-400 mt-1">Votos registrados</p>
                </div>
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: #f4fce3;">
                    <svg class="w-6 h-6" style="color: #93C01F;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
        <!-- Pendientes -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 rounded-full opacity-5" style="background-color: #f8dc0b; transform: translate(30%, -30%);"></div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Abstinencia</p>
                    <p class="text-2xl sm:text-4xl font-bold" style="color: #92400e;" x-text="stats.pendingVoters">{{ $pendingVoters }}</p>
                    <p class="text-xs text-gray-400 mt-1">No han ejercido su voto</p>
                </div>
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: #fef9c3;">
                    <svg class="w-6 h-6" style="color: #d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
        <!-- Participacion -->
        <div class="rounded-2xl shadow-md p-6 relative overflow-hidden text-white" style="background: linear-gradient(135deg, #43883d 0%, #285F19 100%);">
            <div class="absolute top-0 right-0 w-32 h-32 rounded-full bg-white/5" style="transform: translate(30%, -30%);"></div>
            <div class="flex items-start justify-between relative">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-white/60 mb-2">Participacion</p>
                    <p class="text-2xl sm:text-4xl font-bold"><span x-text="stats.participation">{{ $participation }}</span>%</p>
                    <p class="text-xs text-white/50 mt-1">Del total de votantes</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
            <!-- Mini progress bar -->
            <div class="mt-4 w-full bg-white/15 rounded-full h-2">
                <div class="h-full rounded-full bg-white/60 transition-all duration-500" :style="'width: ' + stats.participation + '%'"></div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
        <!-- Bar Chart -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #f0f9ee;">
                        <svg class="w-5 h-5" style="color: #43883d;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Votos por Candidato</h3>
                        <p class="text-xs text-gray-400">Resultados en tiempo real</p>
                    </div>
                </div>
                <span class="flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full" style="background-color: #f0f9ee; color: #43883d;">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></span>
                    EN VIVO
                </span>
            </div>
            <div class="h-60 sm:h-80">
                <canvas id="barChart"></canvas>
            </div>
        </div>
        <!-- Doughnut Chart -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #f4fce3;">
                        <svg class="w-5 h-5" style="color: #93C01F;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Distribucion de Votos</h3>
                        <p class="text-xs text-gray-400">Porcentaje por candidato</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold" style="color: #43883d;" x-text="stats.totalVotes"></p>
                    <p class="text-xs text-gray-400">votos totales</p>
                </div>
            </div>
            <div class="h-60 sm:h-80 flex items-center justify-center">
                <canvas id="doughnutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2: Horizontal Bar Full Width -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 sm:p-6 mb-4 sm:mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #f0f9ee;">
                    <svg class="w-5 h-5" style="color: #285F19;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Ranking de Candidatos</h3>
                    <p class="text-xs text-gray-400">Ordenado por cantidad de votos</p>
                </div>
            </div>
        </div>
        <div class="h-60 sm:h-80">
            <canvas id="horizontalChart"></canvas>
        </div>
    </div>

    <!-- Bottom Row: Participation + Results Table -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-4 sm:mb-6">
        <!-- Participation Gauge -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 flex flex-col items-center">
            <div class="flex items-center gap-3 self-start mb-6">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #f0f9ee;">
                    <svg class="w-5 h-5" style="color: #43883d;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Participacion</h3>
            </div>
            <div class="relative w-52 h-52">
                <canvas id="participationChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-4xl font-bold" style="color: #285F19;" x-text="stats.participation + '%'"></span>
                    <span class="text-xs text-gray-400 mt-1">participacion</span>
                </div>
            </div>
            <div class="flex justify-around w-full mt-6 pt-4 border-t border-gray-100">
                <div class="text-center">
                    <p class="text-xl font-bold" style="color: #43883d;" x-text="stats.totalVotes"></p>
                    <p class="text-xs text-gray-400">Votaron</p>
                </div>
                <div class="w-px bg-gray-100"></div>
                <div class="text-center">
                    <p class="text-xl font-bold" style="color: #d97706;" x-text="stats.pendingVoters"></p>
                    <p class="text-xs text-gray-400">Abstinencia</p>
                </div>
            </div>
        </div>
        <!-- Results Table -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 lg:col-span-2">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #f0f9ee;">
                    <svg class="w-5 h-5" style="color: #43883d;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Tabla de Resultados</h3>
                    <p class="text-xs text-gray-400">Detalle por candidato</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2" style="border-color: #e5e7eb;">
                            <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider text-gray-400">Pos</th>
                            <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider text-gray-400">Candidato</th>
                            <th class="text-right py-3 px-4 text-xs font-bold uppercase tracking-wider text-gray-400">Votos</th>
                            <th class="text-right py-3 px-4 text-xs font-bold uppercase tracking-wider text-gray-400">%</th>
                            <th class="py-3 px-4 text-xs font-bold uppercase tracking-wider text-gray-400 w-44">Progreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(candidate, index) in stats.candidates" :key="candidate.id">
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                <td class="py-3 px-4">
                                    <template x-if="index === 0">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold text-white shadow-sm" style="background: linear-gradient(135deg, #43883d, #93C01F);">1</span>
                                    </template>
                                    <template x-if="index === 1">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold text-white shadow-sm" style="background: linear-gradient(135deg, #6b7280, #9ca3af);">2</span>
                                    </template>
                                    <template x-if="index === 2">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold text-white shadow-sm" style="background: linear-gradient(135deg, #92400e, #d97706);">3</span>
                                    </template>
                                    <template x-if="index > 2">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold bg-gray-100 text-gray-500" x-text="index + 1"></span>
                                    </template>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-gray-800" x-text="candidate.name"></span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <span class="font-bold text-lg text-gray-800" x-text="candidate.votes_count"></span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <span class="font-bold text-sm" style="color: #43883d;" x-text="(stats.totalVotes > 0 ? ((candidate.votes_count / stats.totalVotes) * 100).toFixed(1) : '0.0') + '%'"></span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-700 ease-out"
                                             style="background: linear-gradient(90deg, #43883d, #93C01F);"
                                             :style="'width: ' + (stats.totalVotes > 0 ? ((candidate.votes_count / stats.totalVotes) * 100) : 0) + '%'">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Analytics Section: Winner + Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-4 sm:mb-6">
        <!-- Winner Card -->
        <div class="rounded-2xl shadow-lg overflow-hidden" style="background: linear-gradient(135deg, #285F19 0%, #43883d 50%, #93C01F 100%);">
            <div class="p-6 text-white">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-6 h-6 text-yellow-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white/70">Candidato Lider</h3>
                    <svg class="w-6 h-6 text-yellow-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <template x-if="stats.candidates.length > 0 && stats.totalVotes > 0">
                    <div>
                        <p class="text-3xl font-bold mb-1" style="font-family: 'Oswald', sans-serif;" x-text="stats.candidates[0]?.name"></p>
                        <div class="flex items-baseline gap-3 mb-4">
                            <span class="text-5xl font-bold" x-text="stats.candidates[0]?.votes_count"></span>
                            <span class="text-lg text-white/70">votos</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white/15 rounded-xl p-3 text-center backdrop-blur-sm">
                                <p class="text-2xl font-bold" x-text="(stats.totalVotes > 0 ? ((stats.candidates[0]?.votes_count / stats.totalVotes) * 100).toFixed(1) : '0') + '%'"></p>
                                <p class="text-xs text-white/60">del total</p>
                            </div>
                            <div class="bg-white/15 rounded-xl p-3 text-center backdrop-blur-sm">
                                <p class="text-2xl font-bold" x-text="stats.candidates.length > 1 ? '+' + (stats.candidates[0]?.votes_count - stats.candidates[1]?.votes_count) : stats.candidates[0]?.votes_count"></p>
                                <p class="text-xs text-white/60">ventaja</p>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="stats.candidates.length === 0 || stats.totalVotes === 0">
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-white/30 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        <p class="text-white/50">Sin votos registrados</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Detailed Analytics -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 lg:col-span-2">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #f0f9ee;">
                    <svg class="w-5 h-5" style="color: #43883d;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Resumen Estadistico</h3>
                    <p class="text-xs text-gray-400">Analisis detallado del proceso electoral</p>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <!-- Promedio votos -->
                <div class="rounded-xl p-4 text-center" style="background-color: #f0f9ee;">
                    <p class="text-2xl font-bold" style="color: #285F19;" x-text="stats.candidates.length > 0 ? (stats.totalVotes / stats.candidates.length).toFixed(1) : '0'"></p>
                    <p class="text-xs text-gray-500 mt-1">Promedio votos/candidato</p>
                </div>
                <!-- Total candidatos -->
                <div class="rounded-xl p-4 text-center" style="background-color: #f4fce3;">
                    <p class="text-2xl font-bold" style="color: #43883d;" x-text="stats.candidates.length"></p>
                    <p class="text-xs text-gray-500 mt-1">Candidatos activos</p>
                </div>
                <!-- Margen victoria -->
                <div class="rounded-xl p-4 text-center" style="background-color: #fef9c3;">
                    <p class="text-2xl font-bold" style="color: #92400e;">
                        <span x-text="stats.candidates.length > 1 && stats.totalVotes > 0 ? ((Math.abs(stats.candidates[0]?.votes_count - stats.candidates[1]?.votes_count) / stats.totalVotes) * 100).toFixed(1) + '%' : '-'"></span>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Margen de victoria</p>
                </div>
                <!-- Competitividad -->
                <div class="rounded-xl p-4 text-center" style="background-color: #f0f9ee;">
                    <p class="text-2xl font-bold" style="color: #285F19;">
                        <span x-text="getCompetitiveness()"></span>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Competitividad</p>
                </div>
            </div>

            <!-- Candidate comparison bars -->
            <div class="space-y-3">
                <template x-for="(candidate, index) in stats.candidates" :key="'analytics-' + candidate.id">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2 w-24 sm:w-36 shrink-0">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold text-white" :style="index === 0 ? 'background: #43883d' : (index === 1 ? 'background: #6b7280' : (index === 2 ? 'background: #d97706' : 'background: #d1d5db; color: #6b7280'))" x-text="index + 1"></span>
                            <span class="text-sm font-semibold text-gray-700 truncate" x-text="candidate.name"></span>
                        </div>
                        <div class="flex-1 h-8 bg-gray-100 rounded-lg overflow-hidden relative">
                            <div class="h-full rounded-lg transition-all duration-700 flex items-center justify-end pr-2"
                                 :style="'width: ' + (stats.totalVotes > 0 ? Math.max(((candidate.votes_count / stats.totalVotes) * 100), candidate.votes_count > 0 ? 8 : 0) : 0) + '%; background: ' + (index === 0 ? 'linear-gradient(90deg, #43883d, #93C01F)' : (index === 1 ? 'linear-gradient(90deg, #6b7280, #9ca3af)' : (index === 2 ? 'linear-gradient(90deg, #92400e, #d97706)' : 'linear-gradient(90deg, #d1d5db, #e5e7eb)')))">
                                <span class="text-xs font-bold text-white drop-shadow-sm" x-show="candidate.votes_count > 0" x-text="candidate.votes_count"></span>
                            </div>
                        </div>
                        <span class="text-sm font-bold w-14 text-right" style="color: #43883d;" x-text="(stats.totalVotes > 0 ? ((candidate.votes_count / stats.totalVotes) * 100).toFixed(1) : '0.0') + '%'"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Electoral Insights -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #f0f9ee;">
                <svg class="w-5 h-5" style="color: #43883d;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Indicadores Electorales</h3>
                <p class="text-xs text-gray-400">Metricas clave del proceso</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Legitimidad -->
            <div class="border border-gray-100 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-3 h-3 rounded-full" :style="'background-color: ' + (stats.participation >= 50 ? '#43883d' : (stats.participation >= 30 ? '#d97706' : '#C20E1A'))"></div>
                    <h4 class="text-sm font-bold text-gray-700">Legitimidad Electoral</h4>
                </div>
                <p class="text-3xl font-bold mb-1" :style="'color: ' + (stats.participation >= 50 ? '#285F19' : (stats.participation >= 30 ? '#92400e' : '#C20E1A'))" x-text="stats.participation + '%'"></p>
                <p class="text-xs text-gray-400" x-text="stats.participation >= 50 ? 'Participacion suficiente - Eleccion valida' : (stats.participation >= 30 ? 'Participacion moderada' : 'Participacion baja - Revisar convocatoria')"></p>
            </div>
            <!-- Concentracion -->
            <div class="border border-gray-100 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-3 h-3 rounded-full" :style="'background-color: ' + getConcentrationColor()"></div>
                    <h4 class="text-sm font-bold text-gray-700">Concentracion de Voto</h4>
                </div>
                <p class="text-3xl font-bold mb-1" :style="'color: ' + getConcentrationColor()" x-text="getConcentration() + '%'"></p>
                <p class="text-xs text-gray-400" x-text="getConcentrationLabel()"></p>
            </div>
            <!-- Estado -->
            <div class="border border-gray-100 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-3 h-3 rounded-full" :style="'background-color: ' + getResultStatusColor()"></div>
                    <h4 class="text-sm font-bold text-gray-700">Estado del Resultado</h4>
                </div>
                <p class="text-lg font-bold mb-1" :style="'color: ' + getResultStatusColor()" x-text="getResultStatus()"></p>
                <p class="text-xs text-gray-400" x-text="getResultStatusDetail()"></p>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
const chartColors = ['#43883d', '#93C01F', '#C7D300', '#285F19', '#56a94b', '#3a7635', '#7ecb72', '#b3e1ab'];

function dashboardData() {
    return {
        currentTime: '',
        currentDate: '',
        barChart: null,
        doughnutChart: null,
        horizontalChart: null,
        participationChart: null,
        stats: {
            totalVoters: {{ $totalVoters }},
            totalVotes: {{ $totalVotes }},
            pendingVoters: {{ $pendingVoters }},
            participation: {{ $participation }},
            candidates: @json($candidatesJson)
        },
        init() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
            this.$nextTick(() => this.initCharts());
            this.fetchStats();
            setInterval(() => this.fetchStats(), 5000);
            if (window.Echo) {
                window.Echo.channel('voting-results')
                    .listen('.vote.cast', (e) => {
                        this.stats.candidates = e.candidates;
                        this.stats.totalVotes++;
                        this.stats.pendingVoters--;
                        this.stats.participation = ((this.stats.totalVotes / this.stats.totalVoters) * 100).toFixed(2);
                        this.updateCharts();
                    });
            }
        },
        updateClock() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('es-CO', { timeZone: 'America/Bogota' });
            this.currentDate = now.toLocaleDateString('es-CO', { timeZone: 'America/Bogota', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        },
        initCharts() {
            const names = this.stats.candidates.map(c => c.name);
            const votes = this.stats.candidates.map(c => c.votes_count);
            const colors = this.stats.candidates.map((_, i) => chartColors[i % chartColors.length]);

            const tooltipStyle = {
                backgroundColor: '#1e293b',
                titleFont: { family: 'Oswald', size: 14, weight: '500' },
                bodyFont: { family: 'Ubuntu', size: 13 },
                padding: 14,
                cornerRadius: 10,
                displayColors: true,
                boxPadding: 6,
            };

            // Bar Chart
            const barCanvas = document.getElementById('barChart');
            if (Chart.getChart(barCanvas)) Chart.getChart(barCanvas).destroy();
            this.barChart = new Chart(barCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: names,
                    datasets: [{
                        label: 'Votos',
                        data: votes,
                        backgroundColor: (ctx) => {
                            const gradient = ctx.chart.ctx.createLinearGradient(0, ctx.chart.chartArea?.bottom || 0, 0, ctx.chart.chartArea?.top || 0);
                            const color = colors[ctx.dataIndex] || '#43883d';
                            gradient.addColorStop(0, color + '40');
                            gradient.addColorStop(1, color + 'DD');
                            return gradient;
                        },
                        borderColor: colors,
                        borderWidth: 2,
                        borderRadius: 10,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 600, easing: 'easeOutQuart' },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, font: { family: 'Ubuntu', size: 12 }, color: '#9ca3af' },
                            grid: { color: '#f3f4f6', drawBorder: false }
                        },
                        x: {
                            ticks: { font: { family: 'Oswald', size: 13, weight: '500' }, color: '#374151' },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { ...tooltipStyle, callbacks: { label: (ctx) => ` ${ctx.parsed.y} votos` } }
                    }
                },
                plugins: [{
                    id: 'barLabels',
                    afterDatasetsDraw(chart) {
                        const { ctx } = chart;
                        chart.data.datasets[0].data.forEach((value, index) => {
                            if (value === 0) return;
                            const meta = chart.getDatasetMeta(0);
                            const bar = meta.data[index];
                            ctx.save();
                            ctx.fillStyle = '#285F19';
                            ctx.font = "bold 13px 'Oswald', sans-serif";
                            ctx.textAlign = 'center';
                            ctx.fillText(value, bar.x, bar.y - 8);
                            ctx.restore();
                        });
                    }
                }]
            });

            // Doughnut Chart
            const doughnutCanvas = document.getElementById('doughnutChart');
            if (Chart.getChart(doughnutCanvas)) Chart.getChart(doughnutCanvas).destroy();
            this.doughnutChart = new Chart(doughnutCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: names,
                    datasets: [{
                        data: votes,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 4,
                        hoverOffset: 12,
                        hoverBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    animation: { duration: 800, easing: 'easeOutQuart', animateRotate: true },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'rectRounded',
                                font: { family: 'Ubuntu', size: 12, weight: '500' },
                                color: '#374151'
                            }
                        },
                        tooltip: {
                            ...tooltipStyle,
                            callbacks: {
                                label: (ctx) => {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : '0.0';
                                    return ` ${ctx.label}: ${ctx.parsed} votos (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Horizontal Bar Chart
            const sorted = [...this.stats.candidates].sort((a, b) => b.votes_count - a.votes_count);
            const hCanvas = document.getElementById('horizontalChart');
            if (Chart.getChart(hCanvas)) Chart.getChart(hCanvas).destroy();
            this.horizontalChart = new Chart(hCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: sorted.map(c => c.name),
                    datasets: [{
                        label: 'Votos',
                        data: sorted.map(c => c.votes_count),
                        backgroundColor: (ctx) => {
                            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, ctx.chart.chartArea?.right || 300, 0);
                            gradient.addColorStop(0, '#43883dDD');
                            gradient.addColorStop(1, '#93C01FCC');
                            return gradient;
                        },
                        borderRadius: 8,
                        borderSkipped: false,
                        barThickness: 30,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 600, easing: 'easeOutQuart' },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, font: { family: 'Ubuntu', size: 12 }, color: '#9ca3af' },
                            grid: { color: '#f9fafb', drawBorder: false }
                        },
                        y: {
                            ticks: { font: { family: 'Oswald', size: 14, weight: '500' }, color: '#374151' },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { ...tooltipStyle, callbacks: { label: (ctx) => ` ${ctx.parsed.x} votos` } }
                    }
                }
            });

            // Participation Doughnut
            const partCanvas = document.getElementById('participationChart');
            if (Chart.getChart(partCanvas)) Chart.getChart(partCanvas).destroy();
            this.participationChart = new Chart(partCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Votaron', 'Abstinencia'],
                    datasets: [{
                        data: [this.stats.totalVotes, this.stats.pendingVoters],
                        backgroundColor: ['#43883d', '#f3f4f6'],
                        borderWidth: 0,
                        hoverOffset: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '78%',
                    animation: { duration: 800, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            ...tooltipStyle,
                            callbacks: {
                                label: (ctx) => ` ${ctx.label}: ${ctx.parsed}`
                            }
                        }
                    }
                }
            });
        },
        updateCharts() {
            const names = this.stats.candidates.map(c => c.name);
            const votes = this.stats.candidates.map(c => c.votes_count);

            if (this.barChart) {
                this.barChart.data.labels = names;
                this.barChart.data.datasets[0].data = votes;
                this.barChart.update('active');
            }
            if (this.doughnutChart) {
                this.doughnutChart.data.labels = names;
                this.doughnutChart.data.datasets[0].data = votes;
                this.doughnutChart.update('active');
            }
            if (this.horizontalChart) {
                const sorted = [...this.stats.candidates].sort((a, b) => b.votes_count - a.votes_count);
                this.horizontalChart.data.labels = sorted.map(c => c.name);
                this.horizontalChart.data.datasets[0].data = sorted.map(c => c.votes_count);
                this.horizontalChart.update('active');
            }
            if (this.participationChart) {
                this.participationChart.data.datasets[0].data = [this.stats.totalVotes, this.stats.pendingVoters];
                this.participationChart.update('active');
            }
        },
        getCompetitiveness() {
            if (this.stats.candidates.length < 2 || this.stats.totalVotes === 0) return '-';
            const first = this.stats.candidates[0]?.votes_count || 0;
            const second = this.stats.candidates[1]?.votes_count || 0;
            const diff = first - second;
            const pct = (diff / this.stats.totalVotes) * 100;
            if (pct <= 5) return 'Muy reñida';
            if (pct <= 15) return 'Competida';
            if (pct <= 30) return 'Moderada';
            return 'Amplia';
        },
        getConcentration() {
            if (this.stats.totalVotes === 0 || this.stats.candidates.length === 0) return '0.0';
            const first = this.stats.candidates[0]?.votes_count || 0;
            return ((first / this.stats.totalVotes) * 100).toFixed(1);
        },
        getConcentrationColor() {
            const c = parseFloat(this.getConcentration());
            if (c >= 60) return '#285F19';
            if (c >= 40) return '#d97706';
            return '#6b7280';
        },
        getConcentrationLabel() {
            const c = parseFloat(this.getConcentration());
            if (c >= 60) return 'Mayoria clara del lider';
            if (c >= 40) return 'Voto fragmentado - sin mayoria absoluta';
            return 'Voto muy distribuido entre candidatos';
        },
        getResultStatus() {
            if (this.stats.totalVotes === 0) return 'Sin resultados';
            if (this.stats.candidates.length < 2) return 'Candidato unico';
            const first = this.stats.candidates[0]?.votes_count || 0;
            const second = this.stats.candidates[1]?.votes_count || 0;
            if (first === second) return 'Empate';
            const margin = ((first - second) / this.stats.totalVotes) * 100;
            if (margin <= 2) return 'Diferencia minima';
            if (margin <= 10) return 'Victoria estrecha';
            return 'Victoria definida';
        },
        getResultStatusColor() {
            const status = this.getResultStatus();
            if (status === 'Victoria definida') return '#285F19';
            if (status === 'Victoria estrecha') return '#43883d';
            if (status === 'Empate' || status === 'Diferencia minima') return '#C20E1A';
            return '#6b7280';
        },
        getResultStatusDetail() {
            if (this.stats.totalVotes === 0) return 'Esperando votos para generar resultados';
            if (this.stats.candidates.length < 2) return 'Solo hay un candidato registrado';
            const first = this.stats.candidates[0]?.votes_count || 0;
            const second = this.stats.candidates[1]?.votes_count || 0;
            const diff = first - second;
            if (diff === 0) return 'Los dos primeros candidatos tienen la misma cantidad de votos';
            return `Diferencia de ${diff} voto${diff !== 1 ? 's' : ''} entre 1ro y 2do lugar`;
        },
        async fetchStats() {
            try {
                const response = await fetch('{{ route("admin.dashboard.stats") }}');
                const data = await response.json();
                this.stats = data;
                this.updateCharts();
            } catch (e) {
                console.error('Error fetching stats:', e);
            }
        }
    }
}
</script>
@endpush
