<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Widgets;

use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Webkul\NurserySubscription\Models\Subscription;
use Webkul\NurserySubscription\Services\PricingAndLifecycleService;

class ExpiringSubscriptionsTable extends BaseWidget
{
    protected static ?string $heading = 'اشتراكات تنتهي قريباً (تنبيهات المتابعة والتجديد)';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Subscription::query()
                    ->where('status', 'expiring_soon')
                    ->with(['child', 'pricingPlan'])
                    ->orderBy('end_date', 'asc')
            )
            ->columns([
                TextColumn::make('child.full_name')
                    ->label('اسم الطفل')
                    ->searchable(),
                TextColumn::make('child.guardian_name')
                    ->label('ولي الأمر'),
                TextColumn::make('child.guardian_phone')
                    ->label('رقم الجوال')
                    ->copyable(),
                TextColumn::make('pricingPlan.duration_label')
                    ->label('الباقة'),
                TextColumn::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('days_remaining')
                    ->label('متبقي')
                    ->suffix(' يوم')
                    ->color('warning'),
            ])
            ->actions([
                Action::make('renew')
                    ->label('تجديد')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تجديد الاشتراك')
                    ->modalDescription('هل تريد تجديد هذا الاشتراك بنفس الباقة؟')
                    ->action(function (Subscription $record) {
                        app(PricingAndLifecycleService::class)->processRenewal($record);
                    }),
            ])
            ->emptyStateHeading('لا توجد اشتراكات تنتهي قريباً')
            ->emptyStateDescription('جميع الاشتراكات سارية ولا يوجد ما ينتهي خلال 7 أيام.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
