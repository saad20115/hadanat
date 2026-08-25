<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Widgets;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Webkul\NurserySubscription\Models\Subscription;
use Webkul\NurserySubscription\Services\PricingAndLifecycleService;

class OutstandingBalancesTable extends BaseWidget
{
    protected static ?string $heading = 'تقرير الذمم والمبالغ المتبقية للتحصيل';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Subscription::query()
                    ->where('remaining_amount', '>', 0)
                    ->whereIn('status', ['active', 'expiring_soon', 'new'])
                    ->with(['child', 'pricingPlan'])
                    ->orderByDesc('remaining_amount')
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
                TextColumn::make('net_amount')
                    ->label('الإجمالي')
                    ->money('SAR'),
                TextColumn::make('paid_amount')
                    ->label('المدفوع')
                    ->money('SAR')
                    ->color('success'),
                TextColumn::make('remaining_amount')
                    ->label('المتبقي')
                    ->money('SAR')
                    ->color('danger')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->actions([
                Action::make('add_payment')
                    ->label('تسجيل دفعة')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form([
                        TextInput::make('amount')
                            ->label('المبلغ المحصل')
                            ->numeric()
                            ->required()
                            ->default(fn (Subscription $record): string => (string) $record->remaining_amount)
                            ->maxValue(fn (Subscription $record): float => (float) $record->remaining_amount),
                        Select::make('payment_method')
                            ->label('طريقة الدفع')
                            ->options([
                                'cash' => '💵 نقدي (كاش)',
                                'card' => '💳 شبكة / بطاقة',
                                'bank_transfer' => '🏦 تحويل بنكي',
                            ])
                            ->required(),
                        TextInput::make('reference_number')
                            ->label('رقم المرجع / الإيصال')
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, Subscription $record): void {
                        app(PricingAndLifecycleService::class)->recordPayment(
                            $record,
                            (float) $data['amount'],
                            $data['payment_method'],
                            \Carbon\Carbon::now(),
                            $data['reference_number'] ?? null
                        );

                        Notification::make()
                            ->title('تم تسجيل الدفعة بنجاح')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('لا توجد مبالغ متبقية مستحقة')
            ->emptyStateDescription('جميع الاشتراكات مسددة بالكامل.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
