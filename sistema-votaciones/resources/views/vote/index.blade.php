@extends('layouts.app')
@section('content')
<div x-data="{ selectedCandidate: null, showModal: false }">
    <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Seleccione su Candidato</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
        @foreach($candidates as $candidate)
        <div class="card hover:shadow-xl transition-shadow cursor-pointer border-2 border-transparent hover:border-primary-500"
             data-candidate="{{ json_encode(['id' => $candidate->id, 'name' => $candidate->name]) }}"
             @click="selectedCandidate = JSON.parse($el.dataset.candidate); showModal = true">
            @if($candidate->photo)
            <img src="{{ Storage::url($candidate->photo) }}" alt="{{ $candidate->name }}" class="w-32 h-32 rounded-full mx-auto mb-4 object-cover">
            @else
            <div class="w-32 h-32 rounded-full mx-auto mb-4 bg-gray-200 flex items-center justify-center">
                <span class="text-4xl text-gray-400">{{ substr($candidate->name, 0, 1) }}</span>
            </div>
            @endif
            <h3 class="text-xl font-bold text-center text-gray-800">
                {{ $candidate->name }}
            </h3>
            <button class="w-full btn-primary mt-4">
                Votar
            </button>
        </div>
        @endforeach
    </div>
    <!-- Confirmation Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showModal = false">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl">
            <h3 class="text-2xl font-bold text-gray-800 mb-4 text-center">Confirmar Voto</h3>
            <p class="text-gray-600 text-center mb-6">
                Esta seguro que desea votar por:<br>
                <span class="font-bold text-xl text-primary-600" x-text="selectedCandidate?.name"></span>
            </p>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6 text-sm">
                <strong>Advertencia:</strong> Esta accion no se puede deshacer.
            </div>
            <form method="POST" action="{{ route('vote.store') }}" id="voteForm">
                @csrf
                <input type="hidden" name="candidate_id" x-bind:value="selectedCandidate?.id">
                <div class="flex gap-4">
                    <button type="button" @click="showModal = false" class="flex-1 btn-secondary">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 btn-primary">
                        Confirmar Voto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
