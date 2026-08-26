<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Webkul\NurserySubscription\Filament\Admin\Widgets\ChildrenSubscriptionSummaryWidget;
use Webkul\NurserySubscription\Filament\Admin\Widgets\ExpiringSubscriptionsTable;
use Webkul\NurserySubscription\Filament\Admin\Widgets\NurseryKpisWidget;
use Webkul\NurserySubscription\Filament\Admin\Widgets\OutstandingBalancesTable;
use Webkul\Support\Enums\NavigationGroup;

class NurseryReports extends Page
{
    protected static ?string $slug = 'nursery/reports';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?int $navigationSort = 5;

    protected string $view = 'nursery-subscription::filament.admin.pages.nursery-reports';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasRole('super_admin')
            || $user->hasRole('Super_admin')
            || $user->is_default
            || $user->can('app_nursery')
            || $user->can('page_nursery_subscription_nursery_reports');
    }

    public static function getNavigationLabel(): string
    {
        return 'التقارير والمؤشرات';
    }

    public static function getNavigationGroup(): string|\UnitEnum
    {
        return NavigationGroup::Nursery;
    }

    public function getTitle(): string
    {
        return 'لوحة تقارير الحضانة ومؤشرات الأداء';
    }

    public function getSubheading(): ?string
    {
        return 'ملخص شامل لحركة اشتراكات الأطفال، الإيرادات والتحصيل المالي، وتنبيهات التجديد';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            NurseryKpisWidget::class,
            ChildrenSubscriptionSummaryWidget::class,
            ExpiringSubscriptionsTable::class,
            OutstandingBalancesTable::class,
        ];
    }
}
