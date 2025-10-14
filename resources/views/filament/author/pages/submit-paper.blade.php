<x-filament-panels::page>

    {{-- Tampilkan error validasi --}}
    @if ($errors->any())
        <div class="p-4 mb-6 text-sm text-danger-800 rounded-lg bg-danger-50" role="alert">
            <span class="font-medium">Terdapat kesalahan validasi!</span>
            <ul class="mt-1.5 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- INFO KONFERENSI --}}
    <div class="p-6 bg-white rounded-xl shadow-sm mb-8 border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800">{{ $this->conference->name }}</h2>
        <p class="mt-2 text-gray-600">{{ $this->conference->theme }}</p>
        <div class="mt-4 prose max-w-none text-gray-500 text-sm">
            {!! $this->conference->description !!}
        </div>
    </div>

    {{-- FORM SUBMISSION --}}
    <form wire:submit.prevent="submit" enctype="multipart/form-data">
        {{ $this->form }}

        <x-filament-panels::form.actions
            class="mt-6"
            :actions="$this->getFormActions()"
        />
    </form>
</x-filament-panels::page>
