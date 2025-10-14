<x-filament-panels::page>
    {{-- Infolist untuk detail makalah --}}
    {{ $this->submissionInfolist }}

    {{-- Infolist baru untuk riwayat ulasan --}}
    <div class="mt-8">
        {{ $this->reviewHistoryInfolist }}
    </div>
</x-filament-panels::page>