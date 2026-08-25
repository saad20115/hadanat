<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Webkul\NurserySubscription\Enums\PaymentMethod;
use Webkul\NurserySubscription\Filament\Admin\Clusters\NurseryManagement;
use Webkul\NurserySubscription\Filament\Admin\Resources\PaymentResource\Pages;
use Webkul\NurserySubscription\Models\Payment;

use Webkul\Support\Enums\NavigationGroup;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $slug = 'nursery/payments';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 6;

    public static function getModelLabel(): string
    {
        return 'دفعة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'المدفوعات';
    }

    public static function getNavigationLabel(): string
    {
        return 'المدفوعات';
    }

    public static function getNavigationGroup(): string|\UnitEnum
    {
        return NavigationGroup::Nursery;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل سند القبض والدفعة المالية')
                    ->description('اختر الاشتراك المراد سداده وحدد المبلغ وطريقة التحصيل')
                    ->columns(2)
                    ->schema([
                        Select::make('subscription_id')
                            ->label('اشتراك الطفل المراد سداده')
                            ->placeholder('اختر اشتراك الطفل...')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('subscription', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "👶 {$record->child->full_name} | 📋 {$record->pricingPlan->duration_label} (المتبقي: " . number_format((float)$record->remaining_amount, 2) . " ر.س)")
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $sub = \Webkul\NurserySubscription\Models\Subscription::find($state);
                                    if ($sub && $sub->remaining_amount > 0) {
                                        $set('amount', $sub->remaining_amount);
                                    }
                                }
                            }),

                        TextInput::make('amount')
                            ->label('المبلغ المحصل')
                            ->placeholder('0.00')
                            ->required()
                            ->numeric()
                            ->prefix('ر.س')
                            ->helperText('يمكنك إدخال كامل المبلغ المتبقي أو دفعة جزئية'),

                        ToggleButtons::make('payment_method')
                            ->label('طريقة الدفع والتحصيل')
                            ->options([
                                'cash' => '💵 نقدي (كاش)',
                                'card' => '💳 شبكة / بطاقة مدى وائتمان',
                                'bank_transfer' => '🏦 تحويل بنكي',
                            ])
                            ->colors([
                                'cash' => 'success',
                                'card' => 'primary',
                                'bank_transfer' => 'info',
                            ])
                            ->inline()
                            ->default('cash')
                            ->required(),

                        DatePicker::make('payment_date')
                            ->label('تاريخ التحصيل')
                            ->required()
                            ->default(now()),

                        TextInput::make('reference_number')
                            ->label('رقم الإيصال / الحوالة / العملية')
                            ->placeholder('مثال: TXN-982341')
                            ->nullable(),

                        Textarea::make('notes')
                            ->label('ملاحظات إضافية')
                            ->placeholder('أي تفاصيل حول عملية الدفع')
                            ->columnSpanFull()
                            ->rows(2)
                            ->nullable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subscription.child.full_name')
                    ->label('الطفل')
                    ->searchable(),
                TextColumn::make('subscription.pricingPlan.duration_label')
                    ->label('الباقة'),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('SAR')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge(),
                TextColumn::make('payment_date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('reference_number')
                    ->label('المرجع'),
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('payment_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
        ];
    }
}
