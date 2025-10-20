@php
    $user = auth()->user();
    $currentPanelId = \Filament\Facades\Filament::getCurrentPanel()->getId();
    $otherPanels = [];

    // Cek apakah user punya akses ke panel Chair (dan tidak sedang di sana)
    if ($user->conferences()->where('role', \App\Enums\ConferenceRole::Chair)->exists() && $currentPanelId !== 'chair') {
        $firstConference = $user->conferences()->where('role', \App\Enums\ConferenceRole::Chair)->first();
        if($firstConference) {
            $otherPanels['Chair'] = \Filament\Facades\Filament::getPanel('chair')->getUrl($firstConference);
        }
    }

    // Cek apakah user punya akses ke panel Reviewer (dan tidak sedang di sana)
    if ($user->conferences()->where('role', \App\Enums\ConferenceRole::Reviewer)->exists() && $currentPanelId !== 'reviewer') {
        $otherPanels['Reviewer'] = \Filament\Facades\Filament::getPanel('reviewer')->getUrl();
    }

    // Cek apakah user punya akses ke panel Author (dan tidak sedang di sana)
    if ($currentPanelId !== 'author') {
         // Semua user non-admin dianggap punya akses ke panel Author
         if(!$user->is_super_admin) {
            $otherPanels['Author'] = \Filament\Facades\Filament::getPanel('author')->getUrl();
         }
    }
@endphp

{{-- Hanya tampilkan dropdown jika ada panel lain yang bisa diakses --}}
@if (count($otherPanels) > 0)
    <x-filament::dropdown placement="bottom-end">
        <x-slot name="trigger">
            <button
                type="button"
                class="fi-btn fi-btn-size-md fi-btn-color-gray fi-icon-btn group flex h-9 w-9 items-center justify-center rounded-lg outline-none transition-colors duration-75 hover:bg-gray-50 focus:bg-gray-50"
            >
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M15.75 2.75a.75.75 0 00-1.5 0V4.5h-3V2.75a.75.75 0 00-1.5 0V4.5h-3V2.75a.75.75 0 00-1.5 0V4.5A2.25 2.25 0 002.25 6.75v10.5A2.25 2.25 0 004.5 19.5h15A2.25 2.25 0 0021.75 17.25V6.75A2.25 2.25 0 0019.5 4.5h-3V2.75zM4.5 18V8.25h15V18a.75.75 0 01-.75.75H5.25a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                </svg>
            </button>
        </x-slot>

        <x-filament::dropdown.list>
            @foreach ($otherPanels as $name => $url)
                <x-filament::dropdown.list.item
                    :href="$url"
                    tag="a"
                >
                    Masuk sebagai {{ $name }}
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
@endif