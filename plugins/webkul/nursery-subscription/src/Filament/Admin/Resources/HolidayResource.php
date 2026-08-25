<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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
use Webkul\NurserySubscription\Filament\Admin\Resources\HolidayResource\Pages;
use Webkul\NurserySubscription\Models\Holiday;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;
    protected static ?string $slug = 'holidays';
    protected static ?string $cluster = Configurations::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-sun';
    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return 'إجازة دراسية';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الإجازات والعطل المدرسية';
    }

    public static function getNavigationLabel(): string
    {
        return 'الإجازات والعطل المدرسية';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل الإجازة أو العطلة الرسمية')
                    ->description('تحديد تواريخ وأثر الإجازة على التقويم والاشتراكات')
                    ->columns(2)
                    ->schema([
                        Select::make('academic_year_id')
                            ->relationship('academicYear', 'name')
                            ->label('السنة الدراسية')
                            ->preload()
                            ->searchable()
                            ->required(),

                        TextInput::make('name')
                            ->label('اسم الإجازة / المناسبة')
                            ->placeholder('مثال: إجازة اليوم الوطني، إجازة عيد الفطر')
                            ->required()
                            ->maxLength(150),

                        DatePicker::make('start_date')
                            ->label('تاريخ بداية الإجازة (ميلادي)')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                if ($state && $get('end_date')) {
                                    $start = \Carbon\Carbon::parse($state);
                                    $end = \Carbon\Carbon::parse($get('end_date'));
                                    $set('days_count', max(1, $start->diffInDays($end) + 1));
                                }
                            }),

                        DatePicker::make('end_date')
                            ->label('تاريخ نهاية الإجازة (ميلادي)')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                if ($state && $get('start_date')) {
                                    $start = \Carbon\Carbon::parse($get('start_date'));
                                    $end = \Carbon\Carbon::parse($state);
                                    $set('days_count', max(1, $start->diffInDays($end) + 1));
                                }
                            }),

                        TextInput::make('days_count')
                            ->label('عدد الأيام')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        Toggle::make('affects_subscriptions')
                            ->label('تمديد الاشتراكات تلقائياً')
                            ->helperText('إضافة أيام الإجازة لتاريخ نهاية اشتراكات الأطفال النشطة')
                            ->default(false),

                        Textarea::make('notes')
                            ->label('ملاحظات وتفاصيل')
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
                TextColumn::make('name')
                    ->label('اسم الإجازة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('academicYear.name')
                    ->label('السنة الدراسية')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('start_date')
                    ->label('البداية')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('النهاية')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('days_count')
                    ->label('عدد الأيام')
                    ->suffix(' يوماً')
                    ->sortable(),

                IconColumn::make('affects_subscriptions')
                    ->label('تمديد الاشتراكات')
                    ->boolean(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('start_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHolidays::route('/'),
            'create' => Pages\CreateHoliday::route('/create'),
            'edit' => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}
