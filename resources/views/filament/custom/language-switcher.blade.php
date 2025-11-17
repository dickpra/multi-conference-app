{{-- resources/views/filament/custom/language-switcher.blade.php --}}

@php
    // 1. Ambil locale yang sedang aktif
    $currentLocale = app()->getLocale();
    
    // 2. Ambil semua bahasa dari database
    $languages = \App\Models\Language::all();
    
    // 3. Cari objek bahasa yang sedang aktif untuk ditampilkan di tombol utama
    $activeLanguage = $languages->firstWhere('code', $currentLocale);
@endphp

<div x-data="{ open: false }" class="relative">
    
    {{-- TOMBOL UTAMA (TRIGGER) --}}
    <button
        x-on:click="open = ! open"
        type="button"
        class="flex items-center gap-x-2 rounded-lg px-3 py-2 text-sm font-semibold outline-none transition duration-75
               bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700
               border border-gray-200 dark:border-gray-700 shadow-sm"
    >
        {{-- Tampilkan bendera bahasa yang sedang aktif --}}
        @if($activeLanguage && $activeLanguage->code)
             {{-- Perhatikan: Pastikan kode di DB (id, us, gb) cocok dengan nama icon --}}
            <x-icon name="flag-language-{{ $activeLanguage->code }}" class="w-6 h-5 rounded-sm shadow-sm" />
        @else
            {{-- Fallback jika icon tidak ketemu --}}
            <span class="text-xs">🏳️</span>
        @endif

        <span>{{ strtoupper($currentLocale) }}</span>

        {{-- Panah Dropdown --}}
        <svg class="ms-auto h-4 w-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </button>

    {{-- PANEL DROPDOWN --}}
    <div
        x-show="open"
        x-on:click.away="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 z-50 mt-2 w-48 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none
               bg-white dark:bg-gray-800 overflow-hidden"
        style="display: none;"
    >
        <div class="py-1">
            @foreach ($languages as $language)
                {{-- GUNAKAN TAG <a> UNTUK LINK KE CONTROLLER --}}
                <a
                    href="{{ route('lang.switch', $language->code) }}"
                    class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm transition-colors
                           text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700
                           @if($currentLocale === $language->code) bg-gray-50 dark:bg-gray-700 font-semibold text-primary-600 @endif"
                >
                    {{-- Ikon Bendera di List --}}
                    @if($language->code)
                        <x-icon name="flag-language-{{ $language->code }}" class="w-6 h-5 rounded-sm shadow-sm" />
                    @endif

                    <span>{{ $language->name }}</span>

                    {{-- Kode Bahasa di Kanan --}}
                    <span class="ml-auto text-xs text-gray-400 font-medium">{{ strtoupper($language->code) }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>