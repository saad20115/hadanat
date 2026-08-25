<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
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
use Webkul\NurserySubscription\Filament\Admin\Resources\AcademicYearResource\Pages;
use Webkul\NurserySubscription\Filament\Admin\Resources\AcademicYearResource\RelationManagers\TermsRelationManager;
use Webkul\NurserySubscription\Models\AcademicYear;

class AcademicYearResource extends Resource
{
    protected static ?string $model = AcademicYear::class;

    protected static ?string $slug = 'academic-years';

    protected static ?string $cluster = Configurations::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return 'سنة دراسية';
    }

    public static function getPluralModelLabel(): string
    {
        return 'السنوات والفصول الدراسية';
    }

    public static function getNavigationLabel(): string
    {
        return 'السنوات والفصول الدراسية';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات السنة الدراسية والتقويم الأكاديمي')
                    ->description('تحديد السنة الدراسية وتواريخ بدايتها ونهايتها الميلادية (مثال: 2026-2027)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم السنة الدراسية')
                            ->placeholder('مثال: 2026-2027')
                            ->required()
                            ->maxLength(50),

                        Toggle::make('is_current')
                            ->label('السنة الحالية النشطة في النظام')
                            ->helperText('يتم اعتماد هذه السنة بشكل تلقائي في كافة شاشات الاشتراكات والتقارير')
                            ->default(false),

                        DatePicker::make('start_date')
                            ->label('تاريخ بداية السنة (ميلادي)')
                            ->required()
                            ->default('2026-08-30'),

                        DatePicker::make('end_date')
                            ->label('تاريخ نهاية السنة (ميلادي)')
                            ->required()
                            ->default('2027-07-01'),

                        Textarea::make('notes')
                            ->label('ملاحظات وتفاصيل التقويم')
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
                    ->label('السنة الدراسية')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('start_date')
                    ->label('بداية السنة (ميلادي)')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('نهاية السنة (ميلادي)')
                    ->date('Y-m-d')
                    ->sortable(),

                IconColumn::make('is_current')
                    ->label('السنة الحالية')
                    ->boolean(),

                TextColumn::make('terms_count')
                    ->counts('terms')
                    ->label('عدد الفصول')
                    ->badge()
                    ->color('info'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            TermsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAcademicYears::route('/'),
            'create' => Pages\CreateAcademicYear::route('/create'),
            'edit'   => Pages\EditAcademicYear::route('/{record}/edit'),
        ];
    }
}
