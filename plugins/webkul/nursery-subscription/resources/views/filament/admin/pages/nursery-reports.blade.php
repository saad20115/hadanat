<x-filament-panels::page>
    <div class="space-y-6">
        @livewire(\Webkul\NurserySubscription\Filament\Admin\Widgets\NurseryKpisWidget::class)

        @livewire(\Webkul\NurserySubscription\Filament\Admin\Widgets\ChildrenSubscriptionSummaryWidget::class)

        <div class="grid grid-cols-1 gap-6">
            @livewire(\Webkul\NurserySubscription\Filament\Admin\Widgets\ExpiringSubscriptionsTable::class)

            @livewire(\Webkul\NurserySubscription\Filament\Admin\Widgets\OutstandingBalancesTable::class)
        </div>
    </div>
</x-filament-panels::page>
