@props(['blocks' => []])

@if(is_array($blocks) && count($blocks) > 0)
    <div class="space-y-8">
        @foreach($blocks as $block)
            @php
                $type = $block['type'];
                $data = $block['data'];
            @endphp

            {{-- BLOK: HEADING --}}
            @if($type === 'heading')
                @if($data['level'] === 'h2')
                    <h2 class="text-3xl font-bold text-gray-900 mt-8 mb-4">{{ $data['content'] }}</h2>
                @elseif($data['level'] === 'h3')
                    <h3 class="text-2xl font-semibold text-gray-800 mt-6 mb-3">{{ $data['content'] }}</h3>
                @else
                    <h4 class="text-xl font-semibold text-gray-700 mt-4 mb-2">{{ $data['content'] }}</h4>
                @endif

            {{-- BLOK: PARAGRAF --}}
            @elseif($type === 'paragraph')
                <div class="prose max-w-none text-gray-600 leading-relaxed">
                    {!! $data['content'] !!}
                </div>

            {{-- BLOK: GAMBAR --}}
            @elseif($type === 'image')
                <figure class="my-6">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($data['url']) }}" 
                         alt="{{ $data['alt'] ?? 'Image' }}" 
                         class="rounded-lg shadow-md w-full object-cover max-h-[500px]">
                    @if(!empty($data['alt']))
                        <figcaption class="text-center text-sm text-gray-500 mt-2">{{ $data['alt'] }}</figcaption>
                    @endif
                </figure>

            {{-- BLOK: PDF DOCUMENT --}}
            @elseif($type === 'pdf_document')
                <div class="my-6 w-full border rounded-lg overflow-hidden shadow-sm" style="height: {{ $data['height'] ?? '800px' }}">
                    <iframe src="{{ \Illuminate\Support\Facades\Storage::url($data['url']) }}" width="100%" height="100%" class="border-none">
                    </iframe>
                </div>

            {{-- BLOK: SMART EMBED (Logika Cerdas) --}}
            @elseif($type === 'smart_embed')
                @php
                    $url = $data['url'];
                    $embedUrl = $url; // Default fallback

                    // 1. Youtube Logic
                    if (str_contains($url, 'youtube.com/watch?v=')) {
                        $videoId = explode('v=', parse_url($url, PHP_URL_QUERY))[0];
                        $embedUrl = "https://www.youtube.com/embed/{$videoId}";
                    } elseif (str_contains($url, 'youtu.be/')) {
                        $videoId = basename(parse_url($url, PHP_URL_PATH));
                        $embedUrl = "https://www.youtube.com/embed/{$videoId}";
                    }
                    
                    // 2. Google Drive Logic (Preview)
                    elseif (str_contains($url, 'drive.google.com') && str_contains($url, '/view')) {
                        $embedUrl = str_replace('/view', '/preview', $url);
                    }

                    // 3. Canva Logic (Biasanya sudah embed friendly, tapi pastikan view)
                    elseif (str_contains($url, 'canva.com') && !str_contains($url, 'embed')) {
                        // Canva butuh link khusus embed, tapi jika user paste link view, 
                        // biasanya iframe masih bisa handle atau butuh penyesuaian khusus.
                        // Untuk amannya kita biarkan URL asli atau tambahkan 'view?embed'
                        if(!str_ends_with($url, 'view?embed')) {
                             $embedUrl = $url . '?embed';
                        }
                    }
                @endphp

                <div class="my-6 relative w-full aspect-video rounded-lg overflow-hidden shadow-lg bg-gray-100">
                    <iframe 
                        src="{{ $embedUrl }}" 
                        class="absolute top-0 left-0 w-full h-full" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
            @endif
        @endforeach
    </div>
@endif