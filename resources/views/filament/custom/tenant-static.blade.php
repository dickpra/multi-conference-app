@php $tenant = \Filament\Facades\Filament::getTenant(); @endphp
@if ($tenant)
  <div class="px-4 py-3">
    <div class="flex items-center gap-3">
      <div class="h-10 w-10 rounded-xl bg-gray-900 text-white flex items-center justify-center text-sm font-semibold">
        {{ strtoupper(substr($tenant->name, 0, 2)) }}
      </div>
      <div class="min-w-0">
        <div class="text-sm font-semibold leading-tight truncate">
          {{ $tenant->name }}
        </div>
        @if($tenant->location)
          <div class="text-xs text-gray-500 truncate">
            {{ $tenant->location }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endif
