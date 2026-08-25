<x-filament-panels::page>
    <div class="space-y-8">
        <!-- 1. Top Section: Age Stages Table -->
        <div>
            {{ $this->table }}
        </div>

        <!-- 2. Bottom Section: Editable Academic Rules & Status Guide -->
        <div>
            @livewire(\Webkul\NurserySubscription\Filament\Admin\Widgets\AcademicRulesAndLegendWidget::class)
        </div>
    </div>
</x-filament-panels::page>
