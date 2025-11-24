<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sustaincon — Conference & Paper Hub</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />

    <script src="https://unpkg.com/feather-icons"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>

    <style>
        body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
        .conference-card { transition: transform .25s ease, box-shadow .25s ease; }
        .conference-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -8px rgba(0,0,0,.12), 0 8px 10px -6px rgba(0,0,0,.08); }
        .animated-bg { animation: gradientShift 15s ease infinite; background-size: 200% 200%; }
        @keyframes gradientShift { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
        /* Fallback if line-clamp plugin is not active */
        .clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="bg-gray-50">
    <div id="vanta-bg" class="fixed inset-0 -z-10"></div>

    <nav class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center" aria-label="Sustaincon Homepage">
                        <i data-feather="book-open" class="text-indigo-600"></i>
                        <span class="ml-2 text-xl font-extrabold text-gray-900">Sustaincon</span>
                    </a>
                    <div class="hidden sm:flex sm:space-x-8 sm:ml-8">
                        <a href="#upcoming" class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Active Conferences</a>
                        <a href="#archive" class="border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Archive</a>
                        <a href="{{ route('filament.author.pages.dashboard') }}" class="border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Submit Paper</a>
                    </div>
                </div>
                <div class="hidden sm:flex items-center space-x-3">
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-md text-indigo-600 text-sm font-semibold border border-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Register
                    </a>
                    <a href="{{ route('login') }}" class="bg-indigo-600 px-4 py-2 rounded-md text-white text-sm font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Login
                    </a>
                </div>

                <button id="btn-mobile" class="sm:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" aria-controls="mobile-menu" aria-expanded="false">
                    <i data-feather="menu"></i>
                </button>
            </div>
        </div>
        <div id="mobile-menu" class="sm:hidden hidden border-t border-gray-200 bg-white/90 backdrop-blur">
            <div class="space-y-1 px-2 pt-2 pb-3">
                <a href="#upcoming" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 bg-gray-100">Active Conferences</a>
                <a href="#archive" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50">Archive</a>
                <a href="{{ route('filament.author.pages.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50">Submit Paper</a>
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-semibold text-indigo-700 hover:bg-indigo-50">Login</a>
            </div>
        </div>
    </nav>

    <header class="relative">
        <div class="bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 animated-bg text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight">
                    {{ $settings->hero_title ?? 'Temukan & Ikuti Konferensi Akademik' }}
                </h1>
                <p class="mt-6 max-w-3xl mx-auto text-lg sm:text-xl text-indigo-100">{{ $settings->hero_description ?? 'Jelajahi konferensi aktif, arsip acara sebelumnya, dan kirimkan makalah terbaik Anda.' }}</p>
                <div class="mt-10 flex justify-center gap-4">
                    <a href="#upcoming" class="bg-white text-indigo-700 px-6 sm:px-8 py-3 rounded-md text-base sm:text-lg font-semibold hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white">Explore Conferences</a>
                    <a href="{{ route('filament.author.pages.dashboard') }}" class="border-2 border-white text-white px-6 sm:px-8 py-3 rounded-md text-base sm:text-lg font-semibold hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white">Submit Paper</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        {{-- @php
            $now = now();
            // Partition data: active (end_date hasn't passed) vs past
            [$activeConfs, $pastConfs] = $conferences->partition(function ($c) use ($now) {
                return \Carbon\Carbon::parse($c->end_date)->isAfter($now);
            });
        @endphp --}}

        <section id="upcoming" class="py-16 bg-white/80 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-900">Active Conferences</h2>
                    <p class="mt-3 max-w-2xl mx-auto text-gray-600">Upcoming or currently ongoing conferences</p>
                </div>

                <div class="mt-10 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @forelse ($activeConfs as $conference)
                        @php
                            $start = \Carbon\Carbon::parse($conference->start_date);
                            $end   = \Carbon\Carbon::parse($conference->end_date);
                            $isUpcoming = $start->isFuture();
                            $statusText = $isUpcoming ? 'Upcoming' : 'Ongoing';
                            $statusClass = $isUpcoming ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800';
                            $logo = $conference->logo
                                ? Illuminate\Support\Facades\Storage::url($conference->logo)
                                : 'https://via.placeholder.com/800x400.png/EBF2FF/1F2937?text=' . urlencode($conference->name);
                        @endphp

                        <article class="conference-card bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="relative h-44 sm:h-48">
                                <img src="{{ $logo }}" alt="Poster {{ $conference->name }}" class="w-full h-full object-cover" loading="lazy" />
                                <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">{{ $statusText }}</span>
                            </div>
                            <div class="p-6">
                                <h3 class="text-lg font-bold text-gray-900 truncate" title="{{ $conference->name }}">{{ $conference->name }}</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    <i data-feather="calendar" class="inline-block -mt-1 mr-1 h-4 w-4 text-gray-400"></i>
                                    {{ $start->translatedFormat('d M Y') }} — {{ $end->translatedFormat('d M Y') }}
                                </p>
                                @if($conference->location)
                                <p class="mt-1 text-sm text-gray-500">
                                    <i data-feather="map-pin" class="inline-block -mt-1 mr-1 h-4 w-4 text-gray-400"></i>
                                    {{ $conference->location }}
                                </p>
                                @endif

                                @if($conference->theme)
                                <p class="mt-3 text-gray-600 h-12 clamp-2">{{ $conference->theme }}</p>
                                @endif

                                <div class="mt-5 flex items-center justify-between">
                                    @if(!empty($conference->submission_deadline))
                                        <span class="text-xs text-gray-500 flex items-center"><i data-feather=\"clock\" class="mr-1 h-4 w-4 text-gray-400"></i> Deadline: {{ \Carbon\Carbon::parse($conference->submission_deadline)->translatedFormat('d M Y') }}</span>
                                    @else
                                        <span></span>
                                    @endif
                                    <a href="{{ route('conference.show', $conference) }}" class="inline-flex items-center text-indigo-600 font-semibold hover:text-indigo-800">View Details <i data-feather="arrow-right" class="ml-1 h-4 w-4"></i></a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <p class="col-span-full text-center text-gray-500">There are no active conferences currently.</p>
                    @endforelse
                </div>

                @if($activeConfs->isNotEmpty())
                <div class="mt-12 text-center">
                    <a href="#archive" class="bg-indigo-600 px-6 py-3 rounded-md text-white font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">View Conference Archive</a>
                </div>
                @endif
            </div>
        </section>

        <section id="archive" class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-900">Conference Archive</h2>
                    <p class="mt-3 max-w-2xl mx-auto text-gray-600">Browse through past conferences.</p>
                </div>

                <form method="GET" action="{{ url('/') }}#archive" class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                        <div class="sm:divide-y sm:divide-gray-200">
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-1 md:grid-cols-3 sm:gap-4 sm:px-6">
                                <div class="md:col-span-2">
                                    <label for="search" class="block text-sm font-medium text-gray-700">Search by keyword</label>
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <div class="relative flex-grow focus-within:z-10">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i data-feather="search" class="text-gray-400 h-5 w-5"></i></div>
                                            <input type="text" name="search" id="search" class="block w-full rounded-none rounded-l-md pl-10 sm:text-sm border-gray-300" placeholder="Title, theme, location..." value="{{ request('search') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 md:mt-0">
                                    <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                                    <select id="year" name="year" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md">
                                        <option value="">All Years</option>
                                        @foreach($years as $year)
                                            <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="py-4 sm:py-5 sm:px-6 bg-gray-50 flex justify-end">
                                <button type="submit" class="bg-indigo-600 px-4 py-2 rounded-md text-white text-sm font-semibold hover:bg-indigo-700">Apply Filter</button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-md">
                    <ul class="divide-y divide-gray-200">
                        @forelse ($pastConfs as $conference)
                            <li class="archive-item" data-name="{{ strtolower($conference->name) }}" data-location="{{ strtolower($conference->location) }}">
                                <a href="{{ route('conference.show', $conference) }}" class="block px-4 py-4 hover:bg-gray-50 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-semibold text-indigo-600 truncate">{{ $conference->name }}</p>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Completed</span>
                                    </div>
                                    <div class="mt-2 sm:flex sm:justify-between">
                                        <p class="flex items-center text-sm text-gray-500"><i data-feather="calendar" class="mr-1.5 h-5 w-5 text-gray-400"></i>{{ \Carbon\Carbon::parse($conference->start_date)->translatedFormat('d M Y') }}</p>
                                        @if($conference->book_of_abstracts_path)
                                        <div class="mt-2 sm:mt-0 flex items-center text-sm text-gray-500"><i data-feather="download" class="mr-1.5 h-5 w-5 text-gray-400"></i><p>Proceedings available</p></div>
                                        @endif
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="px-4 py-6 text-center text-gray-500">No archives match your filter.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="mt-8">
                    {{ $pastConfs->withQueryString()->links() }}
                </div>

            </div>
        </section>
        <section class="border-t border-gray-200">

            @if($settings?->about_me)
    <section id="about" class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-xs font-semibold text-indigo-700 uppercase tracking-wide">
                    <i data-feather="info" class="w-3 h-3 mr-1.5"></i>
                    About Sustaincon
                </span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900">
                    {{ __('Built for sustainable, impactful conferences') }}
                </h2>
                <p class="mt-3 text-base sm:text-lg text-gray-600">
                    {{ __('Use Sustaincon as your hub to discover, manage, and publish research in sustainability-focused events.') }}
                </p>
            </div>

            <div class="mt-10 grid lg:grid-cols-3 gap-8 items-start">
                <div class="lg:col-span-1">
                    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 h-full flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-indigo-900 mb-2">
                                {{ __('Why Sustaincon?') }}
                            </h3>
                            <p class="text-sm text-indigo-900/80">
                                {{ __('A curated environment that puts sustainability, accessibility, and academic rigor at the center of every event.') }}
                            </p>
                        </div>
                        <ul class="mt-6 space-y-2 text-sm text-indigo-900/80">
                            <li class="flex items-center">
                                <span class="inline-flex h-5 w-5 rounded-full bg-emerald-500/10 text-emerald-600 items-center justify-center mr-2">
                                    <i data-feather="check" class="w-3 h-3"></i>
                                </span>
                                {{ __('Track conferences over time') }}
                            </li>
                            <li class="flex items-center">
                                <span class="inline-flex h-5 w-5 rounded-full bg-emerald-500/10 text-emerald-600 items-center justify-center mr-2">
                                    <i data-feather="check" class="w-3 h-3"></i>
                                </span>
                                {{ __('Highlight sustainability themes') }}
                            </li>
                            <li class="flex items-center">
                                <span class="inline-flex h-5 w-5 rounded-full bg-emerald-500/10 text-emerald-600 items-center justify-center mr-2">
                                    <i data-feather="check" class="w-3 h-3"></i>
                                </span>
                                {{ __('Connect authors, reviewers, and organizers') }}
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
                        <div class="space-y-4 prose max-w-none">
                            <x-content-blocks :blocks="$settings->about_me" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif


            @if($settings?->guidebook)
    <section id="guidebook" class="py-16 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-xs font-semibold text-emerald-700 uppercase tracking-wide">
                    <i data-feather="book-open" class="w-3 h-3 mr-1.5"></i>
                    Guidebook & Resources
                </span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900">
                    {{ __('Navigate your submission with confidence') }}
                </h2>
                <p class="mt-3 text-base sm:text-lg text-gray-600">
                    {{ __('All key information, templates, and instructions curated in one place.') }}
                </p>
            </div>

            <div class="mt-10 grid md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
                        <x-content-blocks :blocks="$settings->guidebook" />
                    </div>
                </div>

                <div class="md:col-span-1">
                    <div class="h-full bg-gradient-to-br from-indigo-600 to-purple-600 text-white rounded-2xl p-6 flex flex-col justify-between shadow-lg">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">
                                {{ __('Need a quick start?') }}
                            </h3>
                            <p class="text-sm text-indigo-100">
                                {{ __('Check if your conference provides templates, formatting rules, and ethical guidelines directly in this guidebook section.') }}
                            </p>
                        </div>
                        <div class="mt-6 space-y-2 text-sm text-indigo-100">
                            <div class="flex items-center">
                                <i data-feather="file-text" class="w-4 h-4 mr-2"></i>
                                {{ __('Download templates from each conference page') }}
                            </div>
                            <div class="flex items-center">
                                <i data-feather="shield" class="w-4 h-4 mr-2"></i>
                                {{ __('Follow ethical and review processes') }}
                            </div>
                            <div class="flex items-center">
                                <i data-feather="help-circle" class="w-4 h-4 mr-2"></i>
                                {{ __('Reach out to organizers if something is unclear') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif

@if(!empty($settings->credit))
    <section id="credit"
             class="py-12 bg-gradient-to-r from-gray-50 via-white to-gray-50 border-t border-gray-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header kecil di atas konten --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-xs font-semibold text-indigo-700 tracking-wide uppercase">
                        <i data-feather="heart" class="w-3 h-3 mr-1.5"></i>
                        Credits & Partners
                    </span>
                    <h2 class="mt-3 text-2xl sm:text-3xl font-bold text-gray-900">
                        {{ __('Recognizing the people behind Sustaincon') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 max-w-xl">
                        {{ __('Organizing institutions, sponsors, and supporting teams who make these conferences possible.') }}
                    </p>
                </div>

                <div class="hidden sm:flex items-center gap-2 text-xs text-gray-500">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white shadow-sm border border-gray-200">
                        <i data-feather="users" class="w-4 h-4 text-indigo-600"></i>
                    </span>
                    <span>{{ __('Updated by conference admins through the Sustaincon dashboard.') }}</span>
                </div>
            </div>

            {{-- Card utama berisi konten dari Filament Builder --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm px-5 sm:px-8 py-6 sm:py-8">
                <div class="prose prose-sm sm:prose-base max-w-none text-gray-700">
                    <x-content-blocks :blocks="$settings->credit" />
                </div>
            </div>
        </div>
    </section>
@endif

        </section>

        <section class="bg-indigo-700">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8 lg:py-20">
                <div class="lg:grid lg:grid-cols-2 lg:gap-8 lg:items-center">
                    <div>
                        <h2 class="text-3xl font-extrabold text-white sm:text-4xl">Ready to publish your research?</h2>
                        <p class="mt-3 max-w-3xl text-lg leading-6 text-indigo-200">Submit your paper to upcoming conferences and share your findings with the global academic community.</p>
                        <div class="mt-8 sm:flex">
                            <div class="rounded-md shadow">
                                <a href="{{ route('filament.author.pages.dashboard') }}" class="flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50">Submit Paper</a>
                            </div>
                            <div class="mt-3 sm:mt-0 sm:ml-3">
                                <a href="{{ route('filament.author.pages.dashboard') }}#guidelines" class="flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-800/60 hover:bg-indigo-800/70">View submission guidelines</a>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 lg:mt-0">
                        <div class="bg-white/10 backdrop-blur-sm p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-white">Example Submission Deadlines</h3>
                            <ul class="mt-4 space-y-4">
                                @foreach($activeConfs->take(3) as $c)
                                @php
                                    // Use submission_deadline if available, otherwise fallback to end_date, then start_date
                                    $deadlineCandidate = $c->submission_deadline ?? $c->end_date ?? $c->start_date;

                                    try {
                                        $dl = $deadlineCandidate
                                            ? \Carbon\Carbon::parse($deadlineCandidate)->translatedFormat('d M Y')
                                            : 'To be determined';
                                    } catch (\Throwable $e) {
                                        $dl = 'To be determined';
                                    }
                                @endphp
                                <li class="flex items-start">
                                    <i data-feather="clock" class="flex-shrink-0 h-5 w-5 text-indigo-200"></i>
                                    <p class="ml-3 text-sm text-indigo-200">
                                        <span class="font-medium text-white">{{ $c->name }}</span> — {{ $dl }}
                                    </p>
                                </li>
                            @endforeach
                            @if($activeConfs->isEmpty())
                                <li class="text-indigo-200 text-sm">No active deadlines currently.</li>
                            @endif

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
        @if(!empty($settings?->contact_email))
            <li class="flex items-center space-x-3">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-gray-800">
                    <i data-feather="mail" class="text-gray-300 h-4 w-4"></i>
                </span>
                <div class="flex flex-col">
                    <span class="text-xs uppercase tracking-wide text-gray-400">Email</span>
                    <a href="mailto:{{ $settings->contact_email }}" class="text-sm text-gray-300 hover:text-white">
                        {{ $settings->contact_email }}
                    </a>
                </div>
            </li>
        @endif

        @if(!empty($settings?->contact_phone))
            <li class="flex items-center space-x-3">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-gray-800">
                    <i data-feather="phone" class="text-gray-300 h-4 w-4"></i>
                </span>
                <div class="flex flex-col">
                    <span class="text-xs uppercase tracking-wide text-gray-400">Phone / WhatsApp</span>
                    <a href="tel:{{ $settings->contact_phone }}" class="text-sm text-gray-300 hover:text-white">
                        {{ $settings->contact_phone }}
                    </a>
                </div>
            </li>
        @endif
    </ul>
</div>

            </div>
            <div class="mt-12 border-t border-gray-800 pt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <p class="text-base text-gray-400">&copy; {{ date('Y') }} Sustaincon. All rights reserved.</p>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-400 hover:text-white"><i data-feather="twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white"><i data-feather="linkedin"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white"><i data-feather="github"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Vanta background (made safe if it fails)
        try {
            VANTA.NET({
                el: "#vanta-bg",
                mouseControls: true,
                touchControls: true,
                gyroControls: false,
                minHeight: 200.00,
                minWidth: 200.00,
                scale: 1.00,
                scaleMobile: 1.00,
                color: 0x6366f1,
                backgroundColor: 0xf9fafb,
                points: 10.00,
                maxDistance: 22.00,
                spacing: 18.00
            });
        } catch (e) {
            console.error('Vanta.js failed to load:', e);
        }

        // Feather icons
        feather.replace();

        // Mobile menu toggle
        const btn = document.getElementById('btn-mobile');
        const menu = document.getElementById('mobile-menu');
        if (btn && menu) {
            btn.addEventListener('click', () => {
                const hidden = menu.classList.toggle('hidden');
                btn.setAttribute('aria-expanded', String(!hidden));
            });
        }

        // Client-side filter archive by keyword (name/location)
        const searchInput = document.getElementById('archive-search');
        const applyBtn = document.getElementById('btn-apply-search');
        const list = document.getElementById('archive-list');
        function filterArchive() {
            const q = (searchInput?.value || '').trim().toLowerCase();
            list?.querySelectorAll('.archive-item').forEach(li => {
                const name = li.getAttribute('data-name') || '';
                const loc = li.getAttribute('data-location') || '';
                const match = !q || name.includes(q) || loc.includes(q);
                li.style.display = match ? '' : 'none';
            });
        }
        searchInput?.addEventListener('keyup', (e) => { if (e.key === 'Enter') filterArchive(); });
        applyBtn?.addEventListener('click', filterArchive);
    </script>
</body>
</html>