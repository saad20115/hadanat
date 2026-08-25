<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Webkul\NurserySubscription\Models\Child;
use Webkul\NurserySubscription\Models\Payment;
use Webkul\NurserySubscription\Models\Subscription;
use Webkul\Support\Services\CompanyContext;

class NurseryKpisWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $companyId = app(CompanyContext::class)->currentId() ?? Auth::user()?->default_company_id ?? 1;

        $totalChildren = Child::where('company_id', $companyId)->count();
        $activeSubs = Subscription::where('company_id', $companyId)->where('status', 'active')->count();
        $expiringSoon = Subscription::where('company_id', $companyId)->where('status', 'expiring_soon')->count();
        $totalCollected = (float) Payment::where('company_id', $companyId)->sum('amount');
        $totalOutstanding = (float) Subscription::where('company_id', $companyId)->where('remaining_amount', '>', 0)->sum('remaining_amount');
        $totalNet = (float) Subscription::where('company_id', $companyId)->sum('net_amount');

        $collectionRate = $totalNet > 0 ? round(($totalCollected / $totalNet) * 100, 1) : 100;

        return [
            Stat::make('إجمالي الأطفال المسجلين', (string) $totalChildren)
                ->description('إجمالي سجلات الأطفال في الحضانة')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('اشتراكات سارية نشطة', (string) $activeSubs)
                ->description('أطفال منتظمون حالياً')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('تنبيهات قرب الانتهاء', (string) $expiringSoon)
                ->description('تنتهي خلال 7 أيام أو أقل')
                ->descriptionIcon('heroicon-m-clock')
                ->color($expiringSoon > 0 ? 'warning' : 'gray'),

            Stat::make('إجمالي الإيرادات المحصلة', number_format($totalCollected, 2).' ر.س')
                ->description('المبالغ المستلمة فعلياً')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('مبالغ متبقية للتحصيل', number_format($totalOutstanding, 2).' ر.س')
                ->description('ذمم اشتراكات غير مسددة')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalOutstanding > 0 ? 'danger' : 'gray'),

            Stat::make('نسبة التحصيل المالي', $collectionRate.'%')
                ->description('نسبة المبالغ المسددة من الإجمالي')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($collectionRate >= 80 ? 'success' : 'warning'),
        ];
    }
}
