@php
    $reviewsByRound = $getRecord()->reviews()->orderBy('round')->get()->groupBy('round');
@endphp

@foreach ($reviewsByRound as $round => $reviews)
    <div class="fi-in-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 mb-6">
        <div class="fi-in-section-header p-4">
            <h3 class="text-base font-semibold text-gray-950">
                Review History (Round {{ $round }})
            </h3>
        </div>
        <div class="fi-in-section-content p-6">
            @foreach ($reviews as $review)
                <div class="text-sm mb-4 border-b pb-4">
                    <div class="flex justify-between items-center">
                        <p class="font-bold">{{ $review->reviewer->name }}</p>
                        <span class="fi-in-badge inline-flex items-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-primary-700 bg-primary-50 ring-primary-600/10">
                            {{ $review->recommendation->name }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500">{{ $review->created_at->format('d M Y, H:i') }}</p>
                    <div class="prose max-w-none text-gray-700 mt-2">{!! $review->comments !!}</div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach