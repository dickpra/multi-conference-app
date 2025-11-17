@php
    $user = auth()->user();
    $currentPanel = \Filament\Facades\Filament::getCurrentPanel()->getId();
    $menuItems = [];

    // --- SKENARIO 1: SEDANG DI PANEL CHAIR ---
    // Tujuannya pasti ingin keluar ke panel Author/Reviewer (guard 'web')
    if ($currentPanel === 'chair') {
        // Gunakan rute 'switch.general' yang menuju ke PanelSwitchController
        $menuItems['Kembali ke Dashboard Utama'] = route('switch.general');
    }

    // --- SKENARIO 2: SEDANG DI PANEL AUTHOR ATAU REVIEWER (guard 'web') ---
    elseif ($currentPanel === 'author' || $currentPanel === 'reviewer') {
        
        // A. Cek apakah user punya akses Chair (guard 'chair')
        $chairConference = $user->conferences()->where('role', \App\Enums\ConferenceRole::Chair)->first();
        
        if ($chairConference) {
            // Gunakan rute 'switch.chair' yang menuju ke PanelSwitchController
            $menuItems['Masuk sebagai Chair'] = route('switch.chair', ['conference' => $chairConference->id]);
        }

        // B. Tukar Peran Author <-> Reviewer (Sesama Guard 'web')
        // Ini tidak perlu controller, bisa link langsung
        if ($currentPanel === 'author' && $user->conferences()->where('role', \App\Enums\ConferenceRole::Reviewer)->exists()) {
            $menuItems['Masuk sebagai Reviewer'] = \Filament\Facades\Filament::getPanel('reviewer')->getUrl();
        }
        
        if ($currentPanel === 'reviewer') {
            $menuItems['Masuk sebagai Author'] = \Filament\Facades\Filament::getPanel('author')->getUrl();
        }
    }
@endphp

{{-- Hanya render dropdown jika ada menu yang tersedia --}}
@if (count($menuItems) > 0)
    <x-filament::dropdown placement="bottom-end">
        <x-slot name="trigger">
            <button
                type="button"
                class="fi-btn fi-btn-size-md fi-btn-color-gray fi-icon-btn group flex h-9 w-9 items-center justify-center rounded-lg outline-none transition-colors duration-75 hover:bg-gray-50 focus:bg-gray-50"
                title="Ganti Panel / Peran"
            >
                <!-- Ikon Panah Bolak-balik (Switch) -->
                <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
            </button>
        </x-slot>

        <x-filament::dropdown.list>
            @foreach ($menuItems as $label => $url)
                <x-filament::dropdown.list.item
                    :href="$url"
                    tag="a"
                    icon="heroicon-m-arrow-right-end-on-rectangle"
                >
                    {{ $label }}
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
@endif