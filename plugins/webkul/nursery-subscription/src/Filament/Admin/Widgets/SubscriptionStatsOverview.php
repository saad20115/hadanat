<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Webkul\NurserySubscription\Models\Child;
use Webkul\NurserySubscription\Models\Subscription;

class SubscriptionStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي الأطفال', Child::count())
                ->icon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('اشتراكات فعّالة', Subscription::where('status', 'active')->count())
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('تنتهي قريباً', Subscription::where('status', 'expiring_soon')->count())
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('منتهية', Subscription::where('status', 'expired')->count())
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('مبالغ متبقية', number_format((float) Subscription::where('remaining_amount', '>', 0)->sum('remaining_amount'), 2).' ر.س')
                ->icon('heroicon-o-banknotes')
                ->color('danger'),
        ];
    }
}
