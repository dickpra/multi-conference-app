<x-filament-widgets::widget>
    <x-filament::section>
        <h2 class="text-lg font-bold text-gray-900 mb-4">Makalah Diterima</h2>

        @if($acceptedSubmissions->isNotEmpty())
            <div class="space-y-4">
                @foreach($acceptedSubmissions as $submission)
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-800">{{ $submission->title }}</p>
                            <p class="text-sm text-gray-500">{{ $submission->conference->name }}</p>
                        </div>
                        <a href="{{ Illuminate\Support\Facades\Storage::url($submission->loa_path) }}" 
                           target="_blank"
                           class="inline-flex items-center px-3 py-2 bg-success-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-success-700 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Unduh LoA
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">Belum ada makalah yang diterima.</p>
        @endif

    </x-filament::section>
</x-filament-widgets::widget>