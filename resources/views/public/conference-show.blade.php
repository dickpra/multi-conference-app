<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $conference->name }} — Conferex</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />

    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .prose h1, .prose h2, .prose h3 { color: #1f2937; }
        .prose a { color: #4f46e5; }
    </style>
</head>
<body class="bg-gray-50">

    <nav class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center" aria-label="Conferex Homepage">
                        <i data-feather="book-open" class="text-indigo-600"></i>
                        <span class="ml-2 text-xl font-extrabold text-gray-900">Conferex</span>
                    </a>
                </div>
                <div class="flex">
                    <a href="{{ route('filament.author.pages.dashboard') }}" class="bg-indigo-600 px-4 py-2 rounded-md text-white text-sm font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Login / Submit Paper
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <header class="relative py-20 bg-gray-800 bg-cover bg-center text-white" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ $conference->logo ? Illuminate\Support\Facades\Storage::url($conference->logo) : '' }}');">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">{{ $conference->name }}</h1>
            <p class="mt-4 text-lg sm:text-xl text-gray-300">{{ $conference->theme }}</p>
        </div>
    </header>

    <main class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 bg-white p-6 sm:p-8 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-gray-900 border-b pb-4 mb-4">Description & Call for Papers</h2>
                
                {{-- <div class="flex flex-wrap gap-4 mb-6">
                    @if($conference->paper_template_path)
                        <a href="{{ Illuminate\Support\Facades\Storage::url($conference->paper_template_path) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-semibold rounded-lg shadow hover:bg-green-700 transition">
                            <i data-feather="download" class="mr-2 h-5 w-5"></i>Download Template
                        </a>
                    @endif

                    @if(\Carbon\Carbon::parse($conference->end_date)->isAfter(now()))
                        <a href="{{ route('filament.author.pages.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700 transition">
                            <i data-feather="send" class="mr-2 h-5 w-5"></i>Submit Paper Now
                        </a>
                    @endif
                </div> --}}

                <div class="prose max-w-none text-gray-700">
                    {!! $conference->description !!}
                </div>
            </div>

            <aside class="lg:col-span-1 space-y-8">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Key Information</h3>
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex items-start">
                            <i data-feather="calendar" class="mt-1 mr-3 h-5 w-5 text-indigo-600 flex-shrink-0"></i>
                            <div>
                                <span class="font-semibold">Date:</span><br>
                                {{ \Carbon\Carbon::parse($conference->start_date)->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($conference->end_date)->translatedFormat('d M Y') }}
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i data-feather="map-pin" class="mt-1 mr-3 h-5 w-5 text-indigo-600 flex-shrink-0"></i>
                            <div>
                                <span class="font-semibold">Location:</span><br>
                                {{ $conference->location }}
                            </div>
                        </li>
                        @if($conference->isbn_issn)
                        <li class="flex items-start">
                            <i data-feather="hash" class="mt-1 mr-3 h-5 w-5 text-indigo-600 flex-shrink-0"></i>
                            <div>
                                <span class="font-semibold">ISBN/ISSN:</span><br>
                                {{ $conference->isbn_issn }}
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    @if($conference->book_of_abstracts_path)
                        <a href="{{ Illuminate\Support\Facades\Storage::url($conference->book_of_abstracts_path) }}" target="_blank"
                            class="mb-6 w-full inline-flex items-center justify-center px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 transition">
                            <i data-feather="book-open" class="mr-2 h-5 w-5"></i>Download Proceedings
                        </a>
                    @endif

                    <h3 class="text-xl font-bold text-gray-900 mb-4">Important Dates</h3>
                    <ul class="space-y-4">
                        @forelse($conference->schedules->sortBy('date') as $schedule)
                            <li class="flex items-start">
                                <div class="flex-shrink-0 h-5 w-5 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mr-3 mt-1">
                                    <i data-feather="check" class="h-4 w-4"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $schedule->title }}</p>
                                    <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($schedule->date)->translatedFormat('d F Y') }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="text-gray-500 text-sm">Schedule is not yet available.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Conference Actions</h3>
                    <div class="flex flex-col gap-3">
                        @if($conference->paper_template_path)
                            <a href="{{ Illuminate\Support\Facades\Storage::url($conference->paper_template_path) }}" target="_blank"
                                class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white font-semibold rounded-lg shadow hover:bg-green-700 transition">
                                <i data-feather="download" class="mr-2 h-5 w-5"></i>Download Template
                            </a>
                        @endif

                        @if(\Carbon\Carbon::parse($conference->end_date)->isAfter(now()))
                            <a href="{{ route('filament.author.pages.dashboard') }}"
                                class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700 transition">
                                <i data-feather="send" class="mr-2 h-5 w-5"></i>Submit Paper Now
                            </a>
                        @endif
                    </div>
                </div>
            </aside>

        </div>
    </main>

    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Conferences</h3>
                    <ul class="mt-4 space-y-4">
                        <li><a href="#upcoming" class="text-base text-gray-300 hover:text-white">Active Conferences</a></li>
                        <li><a href="#archive" class="text-base text-gray-300 hover:text-white">Conference Archive</a></li>
                        <li><a href="{{ route('filament.author.pages.dashboard') }}" class="text-base text-gray-300 hover:text-white">Submit Paper</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">About</h3>
                    <ul class="mt-4 space-y-4">
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Our Mission</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Editorial Board</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Partners</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Resources</h3>
                    <ul class="mt-4 space-y-4">
                        <li><a href="{{ route('filament.author.pages.dashboard') }}#guidelines" class="text-base text-gray-300 hover:text-white">Submission Guidelines</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Review Process</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Publication Ethics</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Connect</h3>
                    <ul class="mt-4 space-y-4">
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Contact</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Twitter</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">LinkedIn</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 border-t border-gray-800 pt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <p class="text-base text-gray-400">&copy; {{ date('Y') }} Conferex. All rights reserved.</p>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-400 hover:text-white"><i data-feather="twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white"><i data-feather="linkedin"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white"><i data-feather="github"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        feather.replace();
    </script>
</body>
</html>