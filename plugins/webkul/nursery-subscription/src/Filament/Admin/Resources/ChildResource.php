<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources;

use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Webkul\NurserySubscription\Filament\Admin\Resources\ChildResource\Pages;
use Webkul\NurserySubscription\Models\AgeStageRule;
use Webkul\NurserySubscription\Models\Child;
use Webkul\Support\Enums\NavigationGroup;

class ChildResource extends Resource
{
    protected static ?string $model = Child::class;

    protected static ?string $slug = 'nursery/children';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $recordTitleAttribute = 'full_name';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'طفل';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الأطفال';
    }

    public static function getNavigationLabel(): string
    {
        return 'الأطفال';
    }

    public static function getNavigationGroup(): string|\UnitEnum
    {
        return NavigationGroup::Nursery;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الطفل الأساسية')
                    ->description('أدخل الاسم وتاريخ الميلاد لتحديد فئة وقسم الطفل تلقائياً')
                    ->columns(2)
                    ->schema([
                        TextInput::make('full_name')
                            ->label('اسم الطفل الكامل')
                            ->placeholder('مثال: ريان أحمد الغامدي')
                            ->required()
                            ->maxLength(255),

                        ToggleButtons::make('gender')
                            ->label('الجنس')
                            ->options([
                                'male'   => 'ذكر 👦',
                                'female' => 'أنثى 👧',
                            ])
                            ->colors([
                                'male'   => 'info',
                                'female' => 'danger',
                            ])
                            ->inline()
                            ->default('male')
                            ->required(),

                        DatePicker::make('birth_date')
                            ->label('تاريخ الميلاد')
                            ->required()
                            ->maxDate(now())
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $birth = Carbon::parse($state);
                                    $months = (int) $birth->diffInMonths(Carbon::now());
                                    $years = (int) $birth->diffInYears(Carbon::now());
                                    $remMonths = $months % 12;
                                    $ageText = ($years > 0 ? "{$years} سنة " : '').($remMonths > 0 ? "و {$remMonths} أشهر" : "{$months} شهراً");
                                    $rule = AgeStageRule::where('is_active', true)
                                        ->where('min_age_months', '<=', $months)
                                        ->where('max_age_months', '>', $months)
                                        ->first();
                                    $stageName = $rule ? $rule->name : ($months < 18 ? 'الرضع' : ($months < 36 ? 'البراعم' : 'رياض الأطفال'));
                                    $set('age_preview', "✨ العمر: {$ageText} | الفئة المقترحة: {$stageName}");
                                }
                            }),

                        Placeholder::make('age_preview')
                            ->label('الفئة والمرحلة العمرية المحسوبة')
                            ->content(function ($get, $record) {
                                $date = $get('birth_date') ?? ($record?->birth_date);
                                if (! $date) {
                                    return 'حدد تاريخ الميلاد لعرض المرحلة والقسم المناسب';
                                }
                                $birth = Carbon::parse($date);
                                $months = (int) $birth->diffInMonths(Carbon::now());
                                $years = (int) $birth->diffInYears(Carbon::now());
                                $remMonths = $months % 12;
                                $ageText = ($years > 0 ? "{$years} سنة " : '').($remMonths > 0 ? "و {$remMonths} أشهر" : "{$months} شهراً");
                                $rule = AgeStageRule::where('is_active', true)
                                    ->where('min_age_months', '<=', $months)
                                    ->where('max_age_months', '>', $months)
                                    ->first();
                                $stageName = $rule ? $rule->name : 'حضانة عامة';

                                return "✨ العمر: {$ageText} | القسم المقترح: {$stageName}";
                            }),
                    ]),

                Section::make('بيانات ولي الأمر والتواصل')
                    ->columns(2)
                    ->schema([
                        TextInput::make('guardian_name')
                            ->label('اسم ولي الأمر (الأب / الأم)')
                            ->placeholder('مثال: أحمد محمد الغامدي')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('guardian_phone')
                            ->label('رقم جوال ولي الأمر')
                            ->placeholder('05xxxxxxxx')
                            ->tel()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state && strlen($state) >= 9) {
                                    $other = Child::where('guardian_phone', $state)->count();
                                    if ($other > 0) {
                                        $set('has_siblings', true);
                                    }
                                }
                            }),

                        TextInput::make('emergency_contact')
                            ->label('جهة اتصال الطوارئ (شخص بديل)')
                            ->placeholder('مثال: الخال / العمة')
                            ->nullable(),

                        TextInput::make('emergency_phone')
                            ->label('رقم هاتف الطوارئ')
                            ->placeholder('05xxxxxxxx')
                            ->tel()
                            ->nullable(),
                    ]),

                Section::make('معلومات إضافية والخصومات')
                    ->columns(2)
                    ->schema([
                        Toggle::make('has_siblings')
                            ->label('تفعيل خصم الإخوة (لديه إخوة مسجلين في الحضانة)')
                            ->helperText('يمنح الطفل تلقائياً خصم 5% على كافة الاشتراكات الشهرية والسنوية'),

                        Textarea::make('medical_notes')
                            ->label('ملاحظات طبية / حساسية')
                            ->placeholder('هل يعاني الطفل من أي حساسية غذائية أو أمراض معينة؟')
                            ->rows(2)
                            ->nullable(),

                        Textarea::make('notes')
                            ->label('ملاحظات عامة وتوصيات')
                            ->placeholder('أي تعليمات خاصة من ولي الأمر')
                            ->rows(2)
                            ->nullable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('birth_date')
                    ->label('تاريخ الميلاد')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('age_label')
                    ->label('العمر'),
                TextColumn::make('age_stage')
                    ->label('الفئة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? $state->label() : null),
                TextColumn::make('guardian_name')
                    ->label('ولي الأمر')
                    ->searchable(),
                TextColumn::make('guardian_phone')
                    ->label('الهاتف')
                    ->searchable(),
                IconColumn::make('has_siblings')
                    ->label('إخوة')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('has_siblings')
                    ->label('إخوة مسجلين'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListChildren::route('/'),
            'create' => Pages\CreateChild::route('/create'),
            'edit'   => Pages\EditChild::route('/{record}/edit'),
            'view'   => Pages\ViewChild::route('/{record}'),
        ];
    }
}
