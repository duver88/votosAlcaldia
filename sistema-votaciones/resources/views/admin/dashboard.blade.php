@extends('layouts.admin')
@section('content')
<div x-data="dashboardData()" x-init="init()">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Dashboard</h1>
    <!-- Colombia Time Clock -->
    <div class="card mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-600">Hora Colombia</h3>
                <p class="text-4xl font-bold text-primary-600" x-text="currentTime"></p>
                <p class="text-gray-500" x-text="currentDate"></p>
            </div>
            <div class="text-right">
                @if($settings && $settings->isVotingOpen())
                <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full font-bold">
                    <span class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                    VOTACION ABIERTA
                </span>
                @else
                <span class="inline-flex items-center px-4 py-2 bg-red-100 text-red-800 rounded-full font-bold">
                    <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                    VOTACION CERRADA
                </span>
                @endif
            </div>
        </div>
    </div>
    <!-- Voting Schedule -->
    <div class="card mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-sm text-gray-500">Apertura:</p>
                <p class="font-semibold">{{ $settings?->start_datetime?->format('d/m/Y H:i') ?? 'No configurada' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Cierre:</p>
                <p class="font-semibold">{{ $settings?->end_datetime?->format('d/m/Y H:i') ?? 'No configurado' }}</p>
            </div>
            <form action="{{ route('admin.settings.toggle') }}" method="POST">
                @csrf
                @if($settings && $settings->is_active)
                <button type="submit" class="btn-danger">Cerrar Votacion</button>
                @else
                <button type="submit" class="btn-primary">Abrir Votacion</button>
                @endif
            </form>
        </div>
    </div>
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card bg-blue-50 border-l-4 border-blue-500">
            <p class="text-sm text-blue-600 font-semibold">Total Votantes</p>
            <p class="text-3xl font-bold text-blue-800" x-text="stats.totalVoters">{{ $totalVoters }}</p>
        </div>
        <div class="card bg-green-50 border-l-4 border-green-500">
            <p class="text-sm text-green-600 font-semibold">Votos Emitidos</p>
            <p class="text-3xl font-bold text-green-800" x-text="stats.totalVotes">{{ $totalVotes }}</p>
        </div>
        <div class="card bg-yellow-50 border-l-4 border-yellow-500">
            <p class="text-sm text-yellow-600 font-semibold">Pendientes</p>
            <p class="text-3xl font-bold text-yellow-800" x-text="stats.pendingVoters">{{ $pendingVoters }}</p>
        </div>
        <div class="card bg-purple-50 border-l-4 border-purple-500">
            <p class="text-sm text-purple-600 font-semibold">Participacion</p>
            <p class="text-3xl font-bold text-purple-800"><span x-text="stats.participation">{{ $participation }}</span>%</p>
        </div>
    </div>
    <!-- Results Chart -->
    <div class="card mb-8">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Resultados en Tiempo Real</h3>
        <div class="h-80">
            <canvas id="resultsChart"></canvas>
        </div>
    </div>
    <!-- Results Table -->
    <div class="card">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Tabla de Resultados</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">Pos</th>
                        <th class="text-left py-3 px-4">Candidato</th>
                        <th class="text-right py-3 px-4">Votos</th>
                        <th class="text-right py-3 px-4">Porcentaje</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(candidate, index) in stats.candidates" :key="candidate.id">
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4 font-bold" x-text="index + 1"></td>
                            <td class="py-3 px-4">
                                <span x-text="candidate.name"></span>
                            </td>
                            <td class="py-3 px-4 text-right font-semibold" x-text="candidate.votes_count"></td>
                            <td class="py-3 px-4 text-right">
                                <span x-text="stats.totalVotes > 0 ? ((candidate.votes_count / stats.totalVotes) * 100).toFixed(2) : '0.00'"></span>%
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function dashboardData() {
    return {
        currentTime: '',
        currentDate: '',
        chart: null,
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
            this.initChart();
            this.fetchStats();
            setInterval(() => this.fetchStats(), 5000);
            if (window.Echo) {
                window.Echo.channel('voting-results')
                    .listen('.vote.cast', (e) => {
                        this.stats.candidates = e.candidates;
                        this.stats.totalVotes++;
                        this.stats.pendingVoters--;
                        this.stats.participation = ((this.stats.totalVotes / this.stats.totalVoters) * 100).toFixed(2);
                        this.updateChart();
                    });
            }
        },
        updateClock() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('es-CO', { timeZone: 'America/Bogota' });
            this.currentDate = now.toLocaleDateString('es-CO', { timeZone: 'America/Bogota', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        },
        initChart() {
            const canvas = document.getElementById('resultsChart');
            // Destroy existing chart if it exists
            if (Chart.getChart(canvas)) {
                Chart.getChart(canvas).destroy();
            }
            const ctx = canvas.getContext('2d');
            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.stats.candidates.map(c => c.name),
                    datasets: [{
                        label: 'Votos',
                        data: this.stats.candidates.map(c => c.votes_count),
                        backgroundColor: '#22c55e',
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        },
        updateChart() {
            if (this.chart) {
                this.chart.data.labels = this.stats.candidates.map(c => c.name);
                this.chart.data.datasets[0].data = this.stats.candidates.map(c => c.votes_count);
                this.chart.update();
            }
        },
        async fetchStats() {
            try {
                const response = await fetch('{{ route("admin.dashboard.stats") }}');
                const data = await response.json();
                this.stats = data;
                this.updateChart();
            } catch (e) {
                console.error('Error fetching stats:', e);
            }
        }
    }
}
</script>
@endpush
