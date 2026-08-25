<x-filament-panels::page>
    <form wire:submit="calculateCost" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-3">
            <x-filament::actions
                :actions="$this->getFormActions()"
                :full-width="false"
            />
        </div>
    </form>
</x-filament-panels::page>
