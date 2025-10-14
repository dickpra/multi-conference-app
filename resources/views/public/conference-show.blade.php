<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Judul halaman dinamis sesuai nama konferensi --}}
    <title>{{ $conference->name }}</title>

    {{-- Tailwind CSS CDN & Font --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">

    {{-- Header / Banner --}}
    <header class="bg-blue-800 text-white shadow-lg">
        <div class="container mx-auto px-6 py-12 text-center">
            <h1 class="text-4xl font-bold mb-2">{{ $conference->name }}</h1>
            <p class="text-xl text-blue-200">{{ $conference->theme }}</p>
            <div class="mt-4 text-lg text-blue-100">
                <span>{{ \Carbon\Carbon::parse($conference->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($conference->end_date)->format('d M Y') }}</span>
                <span class="mx-2">|</span>
                <span>{{ $conference->location }}</span>
            </div>
        </div>
    </header>

    {{-- Konten Utama --}}
    <main class="container mx-auto px-6 py-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">

            {{-- Kolom Kiri: Deskripsi --}}
            <div class="md:col-span-2">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b-2 border-blue-600 pb-2">Deskripsi & Call for Paper</h2>
                <div class="prose max-w-none text-gray-700">
                    {{-- Tampilkan HTML dari RichEditor --}}
                    {!! $conference->description !!}
                </div>
                @if($conference->paper_template_path)
                    <div class="mb-6">
                        <a href="{{ Illuminate\Support\Facades\Storage::url($conference->paper_template_path) }}" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-semibold rounded-lg shadow hover:bg-green-700 transition"
                        target="_blank">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Unduh Template Paper
                        </a>
                    </div>
                @endif
            </div>

            {{-- Kolom Kanan: Jadwal --}}
            <div class="md:col-span-1">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Jadwal Penting</h3>
                    <ul class="space-y-3">
                        @forelse($conference->schedules->sortBy('date') as $schedule)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <div>
                                    <p class="font-semibold text-gray-700">{{ $schedule->title }}</p>
                                    <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($schedule->date)->format('d F Y') }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="text-gray-500 text-sm">Jadwal belum tersedia.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </main>

</body>
</html>