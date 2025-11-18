@php
    $reviewers = $getRecord()->assignedReviewers;
@endphp

<div class="flex flex-col space-y-1">
    @forelse ($reviewers as $reviewer)
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-700">{{ $reviewer->name }}</span>
            @php
                $status = \App\Enums\ReviewStatus::tryFrom($reviewer->pivot->status);
                $color = match($status) {
                    \App\Enums\ReviewStatus::Completed => 'success',
                    default => 'gray',
                };
            @endphp
            <span @class([
                'fi-in-badge inline-flex items-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset',
                "text-{$color}-700 bg-{$color}-50 ring-{$color}-600/10",
            ])>
                {{ str($status->value)->title() }}
            </span>
        </div>
    @empty
        <span class="text-sm text-gray-400">{{ __('Belum ada') }}</span>
    @endforelse
</div>