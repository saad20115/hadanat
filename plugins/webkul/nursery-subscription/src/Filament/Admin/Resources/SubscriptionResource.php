<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Webkul\NurserySubscription\Enums\PaymentMethod;
use Webkul\NurserySubscription\Filament\Admin\Resources\SubscriptionResource\Pages;
use Webkul\NurserySubscription\Models\Child;
use Webkul\NurserySubscription\Models\PricingPlan;
use Webkul\NurserySubscription\Models\Subscription;
use Webkul\NurserySubscription\Services\PricingAndLifecycleService;
use Webkul\NurserySubscription\Traits\HandlesNurseryAuthorization;
use Webkul\Support\Enums\NavigationGroup;

class SubscriptionResource extends Resource
{
    use HandlesNurseryAuthorization;

    protected static ?string $model = Subscription::class;

    protected static ?string $slug = 'nursery/subscriptions';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'اشتراك';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الاشتراكات';
    }

    public static function getNavigationLabel(): string
    {
        return 'الاشتراكات';
    }

    public static function getNavigationGroup(): string|\UnitEnum
    {
        return NavigationGroup::Nursery;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('1. بيانات الطفل والقسم المستحق')
                    ->description('اختر الطفل المسجل مسبقاً أو اضغط على (+) لإضافة طفل جديد فوراً')
                    ->columns(2)
                    ->schema([
                        Select::make('child_id')
                            ->label('الطفل المسجل')
                            ->placeholder('اضغط لاختيار الطفل المسجل...')
                            ->options(function () {
                                return Child::all()->mapWithKeys(function ($child) {
                                    return [
                                        $child->id => "👶 {$child->full_name} ({$child->age_label} - ولي الأمر: {$child->guardian_name})",
                                    ];
                                });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('full_name')
                                    ->label('اسم الطفل الكامل')
                                    ->required(),
                                DatePicker::make('birth_date')
                                    ->label('تاريخ الميلاد')
                                    ->required(),
                                TextInput::make('guardian_name')
                                    ->label('اسم ولي الأمر')
                                    ->required(),
                                TextInput::make('guardian_phone')
                                    ->label('رقم جوال ولي الأمر')
                                    ->tel()
                                    ->required(),
                                Toggle::make('has_siblings')
                                    ->label('لديه إخوة مسجلين'),
                            ])
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $set('pricing_plan_id', null);
                                }
                            }),

                        Placeholder::make('child_card')
                            ->label('معلومات الطفل والمرحلة')
                            ->content(function ($get) {
                                $childId = $get('child_id');
                                if (! $childId) {
                                    return 'يرجى اختيار الطفل لعرض مرحلته العمرية والخصومات';
                                }
                                $child = Child::find($childId);
                                if (! $child) {
                                    return '-';
                                }
                                $sibling = $child->has_siblings ? '✅ مؤهل لخصم الإخوة (5%)' : '❌ بدون خصم إخوة';

                                return "👶 {$child->full_name} | 🎂 العمر: {$child->age_label} | 🏫 القسم: {$child->age_stage_label} | 👨‍👩‍👧 {$sibling}";
                            }),
                    ]),

                Section::make('2. اختيار الباقة وتحديد الفترة')
                    ->description('حدد الباقة المطلوبة وسيقوم النظام باحتساب تاريخ الانتهاء الدقيق آلياً')
                    ->columns(2)
                    ->schema([
                        Select::make('pricing_plan_id')
                            ->label('باقة الاشتراك والمدة')
                            ->placeholder('اختر باقة الاشتراك المناسبة...')
                            ->options(function ($get) {
                                $childId = $get('child_id');
                                $child = $childId ? Child::find($childId) : null;
                                $query = PricingPlan::active();
                                if ($child && $child->age_stage) {
                                    $query->forAgeStage($child->age_stage);
                                }

                                return $query->get()->mapWithKeys(function ($plan) {
                                    $hours = $plan->hours_per_day ? " ({$plan->hours_per_day} ساعات/يوم)" : '';

                                    return [
                                        $plan->id => "⭐ [{$plan->stage_label}] {$plan->duration_label}{$hours} — ".number_format((float) $plan->price, 2).' ر.س',
                                    ];
                                });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive(),

                        DatePicker::make('start_date')
                            ->label('تاريخ بداية الاشتراك')
                            ->required()
                            ->default(now())
                            ->reactive(),

                        Placeholder::make('end_date_preview')
                            ->label('تاريخ نهاية الاشتراك (محسوب آلياً)')
                            ->content(function ($get) {
                                $planId = $get('pricing_plan_id');
                                $startDate = $get('start_date');
                                if ($planId && $startDate) {
                                    $plan = PricingPlan::find($planId);
                                    if ($plan) {
                                        $service = app(PricingAndLifecycleService::class);
                                        $endDate = $service->calculateEndDate($plan, Carbon::parse($startDate));

                                        return '📅 '.$endDate->format('Y-m-d')." (المدة: {$plan->duration_label})";
                                    }
                                }

                                return 'يتم الحساب تلقائياً فور اختيار الباقة والبداية';
                            }),
                    ]),

                Section::make('3. التسعير والخصومات والإضافات')
                    ->columns(2)
                    ->schema([
                        Toggle::make('includes_tshirt')
                            ->label('إضافة تيشيرت وزي الحضانة الرسمي (+75.00 ر.س)')
                            ->reactive(),

                        Placeholder::make('pricing_breakdown')
                            ->label('تفاصيل الحساب المالي')
                            ->columnSpanFull()
                            ->content(function ($get) {
                                $planId = $get('pricing_plan_id');
                                $childId = $get('child_id');
                                $includeTshirt = (bool) $get('includes_tshirt');
                                if ($planId && $childId) {
                                    $plan = PricingPlan::find($planId);
                                    $child = Child::find($childId);
                                    if ($plan && $child) {
                                        $service = app(PricingAndLifecycleService::class);
                                        $p = $service->calculateNetAmount($plan, $child, $includeTshirt);

                                        return sprintf(
                                            '💰 السعر الأساسي: %s ر.س  |  🎁 خصم الإخوة (5%%): -%s ر.س  |  👕 التيشيرت: +%s ر.س  ===>  💳 الإجمالي الصافي المطلوب: %s ر.س',
                                            number_format($p['base_price'], 2),
                                            number_format($p['discount_amount'], 2),
                                            number_format($p['tshirt_amount'], 2),
                                            number_format($p['net_amount'], 2)
                                        );
                                    }
                                }

                                return 'اختر الطفل والباقة لحساب التكلفة الإجمالية والخصومات';
                            }),
                    ]),

                Section::make('4. تسجيل الدفعة الأولى (اختياري)')
                    ->description('يمكنك تسجيل المبلغ المسدد الآن أو تركه فارغاً في حال تأجيل الدفع')
                    ->columns(2)
                    ->schema([
                        TextInput::make('initial_payment')
                            ->label('المبلغ المدفوع مقدماً')
                            ->numeric()
                            ->prefix('ر.س')
                            ->placeholder('مثال: 500 أو كامل المبلغ')
                            ->helperText('اترك فارغاً في حال لم يدفع ولي الأمر بعد')
                            ->reactive(),

                        ToggleButtons::make('payment_method')
                            ->label('طريقة الدفع والتحصيل')
                            ->options([
                                'cash'          => '💵 نقدي (كاش)',
                                'card'          => '💳 بطاقة / مدى',
                                'bank_transfer' => '🏦 تحويل بنكي',
                            ])
                            ->colors([
                                'cash'          => 'success',
                                'card'          => 'primary',
                                'bank_transfer' => 'info',
                            ])
                            ->inline()
                            ->default('cash')
                            ->visible(fn ($get) => filled($get('initial_payment'))),
                    ]),

                Section::make('5. ملاحظات وشروط إضافية')
                    ->schema([
                        Textarea::make('notes')
                            ->label('ملاحظات على الاشتراك')
                            ->placeholder('أي شروط خاصة أو اتفاقات مع ولي الأمر')
                            ->rows(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('child.full_name')
                    ->label('الطفل')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pricingPlan.duration_label')
                    ->label('الباقة'),
                TextColumn::make('start_date')
                    ->label('بداية')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('نهاية')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('net_amount')
                    ->label('الإجمالي')
                    ->money('SAR'),
                TextColumn::make('paid_amount')
                    ->label('المدفوع')
                    ->formatStateUsing(fn ($record) => $record->remaining_amount <= 0 ? 'كامل ('.number_format((float) $record->paid_amount, 2).' ر.س)' : number_format((float) $record->paid_amount, 2).' ر.س')
                    ->color('success'),
                TextColumn::make('remaining_amount')
                    ->label('المتبقي')
                    ->formatStateUsing(fn ($state) => (float) $state <= 0 ? 'كامل (0.00)' : number_format((float) $state, 2).' ر.س')
                    ->badge(fn ($record) => (float) $record->remaining_amount <= 0)
                    ->color(fn ($record) => (float) $record->remaining_amount > 0 ? 'danger' : 'success'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('child_id')
                    ->relationship('child', 'full_name')
                    ->label('الطفل'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('renew')
                    ->label('تجديد')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status->value, ['active', 'expiring_soon', 'expired']))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $service = app(PricingAndLifecycleService::class);
                        $service->processRenewal($record);
                    }),
                Action::make('record_payment')
                    ->label('دفعة')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->visible(fn ($record) => $record->remaining_amount > 0)
                    ->form([
                        TextInput::make('amount')
                            ->label('المبلغ')
                            ->required()
                            ->numeric(),
                        Select::make('payment_method')
                            ->label('طريقة الدفع')
                            ->options(PaymentMethod::class)
                            ->required(),
                        TextInput::make('reference_number')
                            ->label('رقم المرجع')
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->nullable(),
                    ])
                    ->action(function ($record, array $data) {
                        $service = app(PricingAndLifecycleService::class);
                        $method = $data['payment_method'] instanceof PaymentMethod
                            ? $data['payment_method']->value
                            : (string) $data['payment_method'];

                        $service->recordPayment(
                            $record,
                            (float) $data['amount'],
                            $method,
                            Carbon::now(),
                            $data['reference_number'] ?? null,
                            $data['notes'] ?? null
                        );
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit'   => Pages\EditSubscription::route('/{record}/edit'),
            'view'   => Pages\ViewSubscription::route('/{record}'),
        ];
    }
}
