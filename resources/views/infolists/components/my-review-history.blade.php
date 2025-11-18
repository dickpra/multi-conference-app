@php
    // Ambil record utama (submission)
    $submission = $getRecord();
    // Ambil HANYA review milik user yang sedang login untuk submission ini
    $myReviews = $submission->reviews()->where('user_id', auth()->id())->latest()->get();
@endphp

<div class="fi-in-repeatable">
    @forelse ($myReviews as $review)
        <div class="fi-in-repeatable-item rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5">
            <div class="fi-in-entry-wrp p-6">
                <div class="grid grid-cols-3 gap-6">
                    {{-- Nama Reviewer (selalu nama sendiri) --}}
                    <div class="fi-in-text text-sm">
                        <span class="font-medium text-gray-500">{{ __('Peninjau') }}:</span>
                        <span class="text-gray-950">{{ $review->reviewer->name }}</span>
                    </div>
                    {{-- Rekomendasi --}}
                    <div class="fi-in-text text-sm">
                        <span class="fi-in-badge inline-flex items-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-success-700 bg-success-50 ring-success-600/10">
                            {{ $review->recommendation->name }}
                        </span>
                    </div>
                    {{-- Tanggal Ulasan --}}
                    <div class="fi-in-text text-sm">
                        <span class="font-medium text-gray-500">{{ __('Tanggal') }}:</span>
                        <span class="text-gray-950">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    {{-- Komentar --}}
                    <div class="fi-in-text text-sm col-span-full">
                        <span class="font-medium text-gray-500">{{ __('Komentar') }}:</span>
                        <div class="prose max-w-none text-gray-950">{!! $review->comments !!}</div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">{{ __('Anda belum memberikan ulasan untuk makalah ini.') }}</p>
    @endforelse
</div>