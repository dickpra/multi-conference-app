@php
    $record = $getRecord();
@endphp

<div class="flex flex-col space-y-3">
    {{-- Selalu tampilkan link untuk file asli --}}
    <div>
        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
            {{ __('Versi Awal:') }}
        </span>
        <a href="{{ Illuminate\Support\Facades\Storage::url($record->full_paper_path) }}" 
           class="inline-flex items-center gap-x-1 text-sm text-primary-600 hover:underline"
           target="_blank">
            <span>{{ __('Unduh File') }}</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-4.5 4.5v.75A2.25 2.25 0 0113.5 18h-2.25a2.25 2.25 0 01-2.25-2.25v-1.5m1.5-9l-3-3m0 0l-3 3m3-3v12"></path></svg>
        </a>
    </div>

    {{-- Tampilkan link untuk file revisi HANYA JIKA ADA --}}
    @if($record->revised_paper_path)
        <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ __('Versi Revisi:') }}
            </span>
            <a href="{{ Illuminate\Support\Facades\Storage::url($record->revised_paper_path) }}" 
               class="inline-flex items-center gap-x-1 text-sm text-amber-600 hover:underline"
               target="_blank">
                <span>{{ __('Unduh File Revisi') }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-4.5 4.5v.75A2.25 2.25 0 0113.5 18h-2.25a2.25 2.25 0 01-2.25-2.25v-1.5m1.5-9l-3-3m0 0l-3 3m3-3v12"></path></svg>
            </a>
        </div>
    @endif
</div>