<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <x-filament-panels::form.actions
            class="mt-6"
            :actions="$this->getFormActions()"
        />
    </form>
</x-filament-panels::page>