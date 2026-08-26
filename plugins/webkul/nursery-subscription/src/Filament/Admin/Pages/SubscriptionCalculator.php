<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Webkul\NurserySubscription\Enums\DurationType;
use Webkul\NurserySubscription\Models\PricingPlan;
use Webkul\NurserySubscription\Traits\HandlesNurseryAuthorization;
use Webkul\Support\Enums\NavigationGroup;

class SubscriptionCalculator extends Page implements HasForms
{
    use HandlesNurseryAuthorization, InteractsWithForms;

    protected static ?string $slug = 'nursery/calculator';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static ?int $navigationSort = 3;

    protected string $view = 'nursery-subscription::filament.admin.pages.subscription-calculator';

    public ?array $data = [];

    public ?array $calculatedResult = null;

    public function mount(): void
    {
        $this->form->fill([
            'stage'                => 'nursery',
            'duration_type'        => 'monthly',
            'hours'                => 4,
            'start_date'           => Carbon::today()->format('Y-m-d'),
            'has_sibling_discount' => false,
            'include_tshirt'       => false,
            'paid_amount'          => null,
            'custom_price'         => null,
        ]);

        $this->calculatedResult = null;
    }

    public static function getNavigationLabel(): string
    {
        return 'حاسبة الاشتراكات';
    }

    public static function getNavigationGroup(): string|\UnitEnum
    {
        return NavigationGroup::Nursery;
    }

    public function getTitle(): string
    {
        return 'حاسبة الاشتراكات';
    }

    public function getSubheading(): ?string
    {
        return 'أداة مساعدة لحساب التكلفة الإجمالية والتواريخ الرسمية للاشتراكات والخصومات';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 12])
                    ->schema([
                        // Left: Inputs Section (7 Cols)
                        Section::make('مدخلات الاشتراك')
                            ->description('حدد الباقة وفترة الاشتراك ثم اضغط على زر احسب التكلفة')
                            ->icon('heroicon-o-pencil-square')
                            ->columnSpan(['default' => 12, 'lg' => 7])
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('stage')
                                        ->label('القسم / الفئة العمرية')
                                        ->options([
                                            'nursery' => '🍼 الحضانة (0-3 سنوات)',
                                            'kg1'     => '🎒 KG1 (3-4 سنوات)',
                                            'kg2'     => '🎒 KG2 (4-5 سنوات)',
                                            'kg3'     => '🎒 KG3 (5-6 سنوات)',
                                        ])
                                        ->default('nursery')
                                        ->native(false)
                                        ->required(),

                                    Select::make('duration_type')
                                        ->label('نوع ومدة الاشتراك')
                                        ->options([
                                            'monthly'       => '📆 شهر كامل',
                                            'term1'         => '🎓 الفصل الدراسي الأول (30/08 - 07/01)',
                                            'term2'         => '🎓 الفصل الدراسي الثاني (17/01 - 01/07)',
                                            'yearly'        => '🏫 سنة دراسية كاملة (30/08 - 01/07)',
                                            'weekly'        => '🗓️ أسبوعي (7 أيام)',
                                            'daily'         => '📅 يوم كامل',
                                            'hourly'        => '⏱️ بالساعة',
                                            'visit_package' => '🎫 باقة زيارات',
                                            'custom'        => '✨ باقة أو مدة مخصصة جديدة',
                                        ])
                                        ->default('monthly')
                                        ->native(false)
                                        ->live()
                                        ->required(),
                                ]),

                                Grid::make(2)->schema([
                                    Select::make('hours')
                                        ->label('عدد الساعات اليومية')
                                        ->options([
                                            4 => '4 ساعات يومياً',
                                            6 => '6 ساعات يومياً',
                                            8 => '8 ساعات (دوام كامل)',
                                        ])
                                        ->default(4)
                                        ->native(false)
                                        ->required(),

                                    DatePicker::make('start_date')
                                        ->label('تاريخ بداية الاشتراك (ميلادي)')
                                        ->default(Carbon::today()->format('Y-m-d'))
                                        ->required(),
                                ]),

                                TextInput::make('custom_price')
                                    ->label('السعر الأساسي المخصص (شامل الضريبة)')
                                    ->numeric()
                                    ->prefix('ر.س')
                                    ->placeholder('0.00')
                                    ->helperText('أدخل السعر للباقات الجديدة أو المدد المخصصة غير الموجودة بالقائمة')
                                    ->visible(fn ($get) => $get('duration_type') === 'custom'),

                                Grid::make(2)->schema([
                                    Toggle::make('has_sibling_discount')
                                        ->label('خصم الإخوة (5%)')
                                        ->helperText('يطبق على الاشتراكات الشهرية والفصول والسنوية')
                                        ->inline(false),

                                    Toggle::make('include_tshirt')
                                        ->label('زي الحضانة الرسمي (+75 ر.س)')
                                        ->helperText('إضافة تيشيرت الحضانة المعتمد')
                                        ->inline(false),
                                ]),

                                TextInput::make('paid_amount')
                                    ->label('الدفعة المقدمة المدفوعة (اختياري)')
                                    ->numeric()
                                    ->prefix('ر.س')
                                    ->placeholder('0.00 ر.س')
                                    ->helperText('اترك فارغاً أو اكتب المبلغ لمعرفة المتبقي للسداد'),
                            ]),

                        // Right: Results Section (5 Cols)
                        Section::make('التكلفة والنتيجة المالية النهائية')
                            ->description('تظهر تفاصيل الحساب هنا بعد الضغط على زر احسب التكلفة')
                            ->icon('heroicon-o-calculator')
                            ->columnSpan(['default' => 12, 'lg' => 5])
                            ->schema([
                                Placeholder::make('calculation_result_box')
                                    ->label('')
                                    ->content(function (): HtmlString {
                                        if (! $this->calculatedResult) {
                                            return new HtmlString("
                                                <div class='flex flex-col items-center justify-center p-8 text-center bg-gray-50 dark:bg-gray-800/50 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500'>
                                                    <svg class='w-12 h-12 mb-3 text-gray-300 dark:text-gray-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'></path>
                                                    </svg>
                                                    <span class='text-sm font-bold text-gray-700 dark:text-gray-300 mb-1'>النتيجة المالية فارغة حالياً</span>
                                                    <span class='text-xs text-gray-500'>يرجى تحديد خيارات الاشتراك والضغط على زر <strong class='text-primary-600 dark:text-primary-400'>احسب التكلفة الآن</strong></span>
                                                </div>
                                            ");
                                        }

                                        $calc = $this->calculatedResult;

                                        $discountHtml = $calc['discount_amount'] > 0
                                            ? "<span class='font-mono font-bold text-sm text-emerald-600 dark:text-emerald-400'>-".number_format($calc['discount_amount'], 2).' ر.س</span>'
                                            : "<span class='text-gray-400 text-xs'>0.00 ر.س (غير مطبق)</span>";

                                        $tshirtHtml = $calc['tshirt_amount'] > 0
                                            ? "<span class='font-mono font-bold text-sm text-primary-600 dark:text-primary-400'>+".number_format($calc['tshirt_amount'], 2).' ر.س</span>'
                                            : "<span class='text-gray-400 text-xs'>0.00 ر.س (غير مضاف)</span>";

                                        $remainingHtml = '';
                                        if ($calc['paid_amount'] > 0) {
                                            if ($calc['remaining_amount'] <= 0) {
                                                $remainingHtml = "
                                                    <div class='flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700 text-xs'>
                                                        <span class='text-gray-500'>حالة السداد:</span>
                                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300'>كامل</span>
                                                    </div>
                                                ";
                                            } else {
                                                $remainingHtml = "
                                                    <div class='flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700 text-xs'>
                                                        <span class='text-gray-500'>الدفعة المدفوعة:</span>
                                                        <span class='font-mono font-bold text-emerald-600'>".number_format($calc['paid_amount'], 2)." ر.س</span>
                                                    </div>
                                                    <div class='flex justify-between items-center text-xs'>
                                                        <span class='font-bold text-gray-700 dark:text-gray-300'>المتبقي للسداد:</span>
                                                        <span class='font-mono font-bold text-sm text-rose-600 dark:text-rose-400'>".number_format($calc['remaining_amount'], 2).' ر.س</span>
                                                    </div>
                                                ';
                                            }
                                        }

                                        return new HtmlString("
                                            <div class='space-y-4'>
                                                <div class='flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 text-xs'>
                                                    <div>
                                                        <span class='text-gray-500 block text-[10px]'>فترة الاشتراك المعتمدة (ميلادي)</span>
                                                        <span class='font-mono font-bold text-primary-600 dark:text-primary-400 text-sm'>
                                                            {$calc['start_date']} ➔ {$calc['end_date']}
                                                        </span>
                                                    </div>
                                                    <span class='px-2 py-1 bg-white dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700 font-semibold text-gray-700 dark:text-gray-300'>
                                                        {$calc['total_days']} يوماً
                                                    </span>
                                                </div>

                                                <div class='space-y-2 text-xs'>
                                                    <div class='flex justify-between items-center'>
                                                        <span class='text-gray-500'>السعر الأساسي:</span>
                                                        <span class='font-mono font-bold text-gray-900 dark:text-white'>".number_format($calc['base_price'], 2)." ر.س</span>
                                                    </div>

                                                    <div class='flex justify-between items-center'>
                                                        <span class='text-gray-500'>خصم الإخوة (5%):</span>
                                                        {$discountHtml}
                                                    </div>

                                                    <div class='flex justify-between items-center'>
                                                        <span class='text-gray-500'>زي الحضانة الرسمي:</span>
                                                        {$tshirtHtml}
                                                    </div>

                                                    <div class='p-3.5 bg-primary-50 dark:bg-primary-950/50 rounded-xl border-2 border-primary-500 flex items-baseline justify-between mt-3'>
                                                        <div>
                                                            <div class='text-xs font-bold text-primary-900 dark:text-primary-200'>
                                                                الصافي الإجمالي المطلوب:
                                                            </div>
                                                            <div class='text-[10px] text-gray-500 mt-0.5'>
                                                                شامل ضريبة 15% (".number_format($calc['vat_amount'], 2)." ر.س)
                                                            </div>
                                                        </div>
                                                        <div class='text-2xl font-black font-mono text-primary-700 dark:text-primary-300'>
                                                            ".number_format($calc['net_amount'], 2)." <span class='text-xs font-normal'>ر.س</span>
                                                        </div>
                                                    </div>

                                                    {$remainingHtml}
                                                </div>
                                            </div>
                                        ");
                                    }),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function calculateCost(): void
    {
        $formData = $this->form->getState();

        $startDateStr = $formData['start_date'] ?: Carbon::today()->format('Y-m-d');
        $start = Carbon::parse($startDateStr);
        $year = (int) $start->format('Y');

        $durationType = $formData['duration_type'] ?: 'monthly';
        $hours = (int) ($formData['hours'] ?: 4);
        $hasSiblingDiscount = (bool) ($formData['has_sibling_discount'] ?? false);
        $includeTshirt = (bool) ($formData['include_tshirt'] ?? false);
        $paidAmount = ! empty($formData['paid_amount']) ? (float) $formData['paid_amount'] : 0.00;
        $customPrice = ! empty($formData['custom_price']) ? (float) $formData['custom_price'] : null;

        $endDate = $start->copy();
        $isEligibleDiscount = false;

        switch ($durationType) {
            case 'hourly':
            case 'daily':
                $endDate = $start->copy();
                break;
            case 'weekly':
                $endDate = $start->copy()->addDays(6);
                break;
            case 'monthly':
                $endDate = $start->copy()->addMonth()->subDay();
                $isEligibleDiscount = true;
                break;
            case 'term1':
                $startMonth = (int) $start->format('n');
                $endYear = ($startMonth >= 8) ? $year + 1 : $year;
                $endDate = Carbon::create($endYear, 1, 7, 0, 0, 0);
                $isEligibleDiscount = true;
                break;
            case 'term2':
                $endDate = Carbon::create($year, 7, 1, 0, 0, 0);
                $isEligibleDiscount = true;
                break;
            case 'yearly':
                $startMonth = (int) $start->format('n');
                $endYear = ($startMonth >= 7) ? $year + 1 : $year;
                $endDate = Carbon::create($endYear, 7, 1, 0, 0, 0);
                $isEligibleDiscount = true;
                break;
            case 'visit_package':
                $endDate = $start->copy()->addMonth()->subDay();
                break;
            case 'custom':
                $endDate = $start->copy()->addMonth()->subDay();
                $isEligibleDiscount = true;
                break;
        }

        $totalDays = $start->diffInDays($endDate) + 1;

        if ($durationType === 'custom' && $customPrice !== null) {
            $basePrice = $customPrice;
        } else {
            $planQuery = PricingPlan::active();
            if ($durationType === 'term1') {
                $planQuery->where('duration_type', DurationType::TERM->value)->where('duration_label', 'like', '%الأول%');
            } elseif ($durationType === 'term2') {
                $planQuery->where('duration_type', DurationType::TERM->value)->where('duration_label', 'like', '%الثاني%');
            } elseif ($durationType === 'yearly') {
                $planQuery->where('duration_type', DurationType::YEARLY->value);
            } else {
                $planQuery->where('duration_type', $durationType);
            }

            if (in_array($durationType, ['hourly', 'daily', 'weekly', 'monthly', 'term1', 'term2', 'yearly'])) {
                $planQuery->where('hours_per_day', $hours);
            }

            $plan = $planQuery->first();

            if ($plan) {
                $basePrice = (float) $plan->price;
            } else {
                $basePrice = match ($durationType) {
                    'hourly'        => 60.00,
                    'daily'         => 140.00,
                    'weekly'        => $hours == 4 ? 490.00 : 690.00,
                    'monthly'       => $hours == 4 ? 1955.00 : ($hours == 6 ? 2185.00 : 2415.00),
                    'term1'         => $hours == 4 ? 7330.00 : ($hours == 6 ? 8310.00 : 9285.00),
                    'term2'         => $hours == 4 ? 8855.00 : ($hours == 6 ? 10120.00 : 11385.00),
                    'yearly'        => $hours == 4 ? 15200.00 : ($hours == 6 ? 17500.00 : 19800.00),
                    'visit_package' => 1280.00,
                    default         => 2000.00,
                };
            }
        }

        $discountAmount = ($hasSiblingDiscount && $isEligibleDiscount) ? round($basePrice * 0.05, 2) : 0.00;
        $tshirtAmount = $includeTshirt ? 75.00 : 0.00;
        $netAmount = max(0, $basePrice - $discountAmount + $tshirtAmount);
        $vatAmount = round($netAmount * (15 / 115), 2);
        $paid = min($netAmount, max(0, $paidAmount));
        $remaining = max(0, $netAmount - $paid);

        $this->calculatedResult = [
            'start_date'       => $start->format('Y-m-d'),
            'end_date'         => $endDate->format('Y-m-d'),
            'total_days'       => $totalDays,
            'base_price'       => $basePrice,
            'discount_amount'  => $discountAmount,
            'tshirt_amount'    => $tshirtAmount,
            'net_amount'       => $netAmount,
            'vat_amount'       => $vatAmount,
            'paid_amount'      => $paid,
            'remaining_amount' => $remaining,
        ];

        Notification::make()
            ->title('تم حساب التكلفة بنجاح')
            ->body('الصافي المطلوب: '.number_format($netAmount, 2).' ر.س')
            ->success()
            ->send();
    }

    public function resetCalculation(): void
    {
        $this->calculatedResult = null;

        Notification::make()
            ->title('تمت إعادة التعيين')
            ->info()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('calculateCost')
                ->label('احسب التكلفة الآن')
                ->icon('heroicon-o-calculator')
                ->color('primary')
                ->size('lg')
                ->action('calculateCost'),

            Action::make('resetCalculation')
                ->label('إعادة تعيين')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action('resetCalculation'),
        ];
    }
}
