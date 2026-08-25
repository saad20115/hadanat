<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Webkul\NurserySubscription\Filament\Admin\Clusters\Configurations;
use Webkul\NurserySubscription\Filament\Admin\Resources\AgeStageRuleResource\Pages;
use Webkul\NurserySubscription\Models\AgeStageRule;

class AgeStageRuleResource extends Resource
{
    protected static ?string $model = AgeStageRule::class;
    protected static ?string $slug = 'age-stages';
    protected static ?string $cluster = Configurations::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'فئة عمرية';
    }

    public static function getPluralModelLabel(): string
    {
        return 'فئات الأعمار والأقسام';
    }

    public static function getNavigationLabel(): string
    {
        return 'فئات الأعمار والأقسام';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تعريف فئة الطفل والقواعد العمرية')
                    ->description('تحديد المرحلة العمرية للأطفال وحساب الفئات تلقائياً بناءً على تاريخ الميلاد بالشهور والسنوات')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم الفئة (المرحلة)')
                            ->placeholder('مثال: الرضع، البراعم، رياض الأطفال، الحضانة المتقدمة')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('code')
                            ->label('رمز الفئة البرمجي')
                            ->placeholder('مثال: infant, toddler, kg')
                            ->required()
                            ->maxLength(50),

                        TextInput::make('min_age_months')
                            ->label('الحد الأدنى للعمر (بالشهور)')
                            ->helperText('مثال: 6 أشهر (= 0.5 سنة)')
                            ->numeric()
                            ->required()
                            ->default(0),

                        TextInput::make('max_age_months')
                            ->label('الحد الأقصى للعمر (بالشهور)')
                            ->helperText('مثال: 18 شهراً (= 1.5 سنة)، أو 36 شهراً (= 3 سنوات)')
                            ->numeric()
                            ->required()
                            ->default(72),

                        Textarea::make('description')
                            ->label('وصف المرحلة وشروط القبول')
                            ->columnSpanFull()
                            ->rows(2)
                            ->nullable(),

                        Toggle::make('is_active')
                            ->label('مفعّلة في النظام')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم الفئة (المرحلة)')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('code')
                    ->label('الرمز')
                    ->badge()
                    ->color('info'),

                TextColumn::make('min_age_months')
                    ->label('من عمر')
                    ->formatStateUsing(fn ($state) => $state . ' شهر (' . round($state / 12, 1) . ' سنة)')
                    ->sortable(),

                TextColumn::make('max_age_months')
                    ->label('إلى عمر')
                    ->formatStateUsing(fn ($state) => $state . ' شهر (' . round($state / 12, 1) . ' سنة)')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50),

                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgeStageRules::route('/'),
            'create' => Pages\CreateAgeStageRule::route('/create'),
            'edit' => Pages\EditAgeStageRule::route('/{record}/edit'),
        ];
    }
}
