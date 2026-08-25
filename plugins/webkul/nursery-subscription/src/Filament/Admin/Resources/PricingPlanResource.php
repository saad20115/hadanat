<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Webkul\NurserySubscription\Enums\AgeStage;
use Webkul\NurserySubscription\Enums\DurationType;
use Webkul\NurserySubscription\Filament\Admin\Clusters\NurseryManagement;
use Webkul\NurserySubscription\Filament\Admin\Resources\PricingPlanResource\Pages;
use Webkul\NurserySubscription\Models\PricingPlan;

use Webkul\Support\Enums\NavigationGroup;

class PricingPlanResource extends Resource
{
    protected static ?string $model = PricingPlan::class;
    protected static ?string $slug = 'nursery/pricing-plans';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return 'باقة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الباقات والأسعار';
    }

    public static function getNavigationLabel(): string
    {
        return 'الباقات والأسعار';
    }

    public static function getNavigationGroup(): string|\UnitEnum
    {
        return NavigationGroup::Nursery;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('1. تصنيف المرحلة ونوع الباقة')
                    ->description('حدد الفئة العمرية ونوع ومدة الاشتراك')
                    ->columns(2)
                    ->schema([
                        ToggleButtons::make('age_stage')
                            ->label('الفئة العمرية (المرحلة)')
                            ->options([
                                'infant' => '🍼 الرضع (6 - 18 شهراً)',
                                'toddler' => '🧒 البراعم (18 - 36 شهراً)',
                                'kg' => '🎒 رياض الأطفال (3 - 6 سنوات)',
                            ])
                            ->colors([
                                'infant' => 'info',
                                'toddler' => 'warning',
                                'kg' => 'success',
                            ])
                            ->inline()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                $labels = [
                                    'infant' => 'الرضع',
                                    'toddler' => 'البراعم',
                                    'kg' => 'رياض الأطفال',
                                ];
                                if ($state && isset($labels[$state])) {
                                    $set('stage_label', $labels[$state]);
                                }
                            }),

                        TextInput::make('stage_label')
                            ->label('تسمية المرحلة بالعربية')
                            ->placeholder('مثال: الرضع، البراعم، روضة أولى')
                            ->required(),

                        Select::make('duration_type')
                            ->label('نوع ومدة الباقة')
                            ->placeholder('اختر نوع المدة...')
                            ->options([
                                'hourly' => '⏱️ بالساعة (Hourly)',
                                'daily' => '📅 يومي (Daily)',
                                'weekly' => '🗓️ أسبوعي (Weekly)',
                                'monthly' => '📆 شهري (Monthly)',
                                'term' => '🎓 فصل دراسي / ترم (Term)',
                                'yearly' => '🏫 سنة دراسية كاملة (Yearly)',
                                'visit_package' => '🎫 باقة زيارات (Visit Package)',
                            ])
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive(),

                        TextInput::make('duration_label')
                            ->label('وصف الباقة المعروض لولي الأمر')
                            ->placeholder('مثال: شهر كامل (4 ساعات يومياً) أو الترم الأول')
                            ->required(),
                    ]),

                Section::make('2. تفاصيل الساعات والتسعير')
                    ->columns(2)
                    ->schema([
                        ToggleButtons::make('hours_per_day')
                            ->label('عدد الساعات اليومية')
                            ->options([
                                4 => '4 ساعات ⏱️',
                                6 => '6 ساعات 🕒',
                                8 => '8 ساعات 🏢',
                            ])
                            ->inline()
                            ->nullable(),

                        TextInput::make('price')
                            ->label('سعر الباقة (شامل ضريبة القيمة المضافة 15%)')
                            ->placeholder('0.00')
                            ->required()
                            ->numeric()
                            ->prefix('ر.س'),

                        TextInput::make('duration_value')
                            ->label('قيمة المدة الرقمية (أشهر / أسابيع / ساعات)')
                            ->helperText('مثال: 1 للشهر الواحد، 3 لـ 3 أشهر، 4.25 للترم الأول')
                            ->numeric()
                            ->nullable(),

                        TextInput::make('visits_count')
                            ->label('عدد الزيارات المتاحة (لباقات الزيارات فقط)')
                            ->placeholder('مثال: 10 أو 12 زيارة')
                            ->numeric()
                            ->nullable(),

                        TextInput::make('visits_period_months')
                            ->label('صلاحية باقة الزيارات (بالشهور)')
                            ->placeholder('مثال: شهر واحد أو شهرين')
                            ->numeric()
                            ->nullable(),

                        TextInput::make('sort_order')
                            ->label('ترتيب الظهور في القائمة')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('مفعّلة ومتاحة للاشتراك حالياً')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('stage_label')
                    ->label('الفئة')
                    ->sortable(),
                TextColumn::make('duration_type')
                    ->label('نوع المدة')
                    ->badge(),
                TextColumn::make('duration_label')
                    ->label('المدة'),
                TextColumn::make('hours_per_day')
                    ->label('ساعات')
                    ->suffix(' ساعات'),
                TextColumn::make('price')
                    ->label('السعر')
                    ->money('SAR')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('مفعّل')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('age_stage')
                    ->options(AgeStage::class)
                    ->label('الفئة العمرية'),
                SelectFilter::make('duration_type')
                    ->options(DurationType::class)
                    ->label('نوع المدة'),
                TernaryFilter::make('is_active')
                    ->label('مفعّل'),
            ])
            ->groups([
                \Filament\Tables\Grouping\Group::make('age_stage')
                    ->label('الفئة العمرية')
                    ->getTitleFromRecordUsing(fn ($record) => $record->stage_label),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('toggle_active')
                    ->label('تفعيل/تعطيل')
                    ->action(function (Collection $records) {
                        $records->each(function ($record) {
                            $record->update(['is_active' => ! $record->is_active]);
                        });
                    }),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPricingPlans::route('/'),
            'create' => Pages\CreatePricingPlan::route('/create'),
            'edit' => Pages\EditPricingPlan::route('/{record}/edit'),
        ];
    }
}
