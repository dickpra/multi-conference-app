<x-filament-panels::page>
    {{-- Tampilkan info konferensi di atas tabel --}}
    <div class="p-6 bg-white rounded-xl shadow-sm mb-6 border border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">{{ $this->conference->name }}</h2>
        <p class="mt-1 text-gray-600">{{ $this->conference->theme }}</p>
    </div>

    {{ $this->table }}
</x-filament-panels::page>