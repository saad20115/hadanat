<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources;

use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Webkul\NurserySubscription\Filament\Admin\Clusters\Configurations;
use Webkul\NurserySubscription\Filament\Admin\Resources\NurseryCompanyResource\Pages;
use Webkul\Support\Models\Company;

class NurseryCompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    protected static ?string $slug = 'company';
    protected static ?string $cluster = Configurations::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'بيانات المنشأة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'بيانات الحضانة والمنشأة';
    }

    public static function getNavigationLabel(): string
    {
        return 'بيانات الحضانة والمنشأة';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الأساسية للحضانة')
                    ->description('تعديل الاسم الرسمي، السجل والترخيص، والرقم الضريبي')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم الرسمي للحضانة / المدرسة')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('registration_number')
                            ->label('رقم الترخيص / السجل التجاري')
                            ->placeholder('مثال: 1010xxxxxx')
                            ->nullable(),

                        TextInput::make('tax_id')
                            ->label('الرقم الضريبي (VAT ID)')
                            ->placeholder('300xxxxxxxxxxxx')
                            ->nullable(),

                        TextInput::make('email')
                            ->label('البريد الإلكتروني الرسمي')
                            ->email()
                            ->nullable(),

                        TextInput::make('phone')
                            ->label('رقم الهاتف الثابت')
                            ->tel()
                            ->nullable(),

                        TextInput::make('mobile')
                            ->label('رقم الجوال / الواتساب')
                            ->tel()
                            ->nullable(),
                    ]),

                Section::make('العنوان والموقع الجغرافي')
                    ->columns(2)
                    ->schema([
                        TextInput::make('city')
                            ->label('المدينة')
                            ->placeholder('الرياض / جدة / الدمام...')
                            ->nullable(),

                        TextInput::make('street1')
                            ->label('الحي والشارع')
                            ->placeholder('اسم الحي، اسم الشارع...')
                            ->nullable(),

                        TextInput::make('website')
                            ->label('الموقع الإلكتروني')
                            ->url()
                            ->columnSpanFull()
                            ->nullable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم الحضانة / المنشأة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),

                TextColumn::make('tax_id')
                    ->label('الرقم الضريبي')
                    ->badge()
                    ->color('info'),

                TextColumn::make('city')
                    ->label('المدينة'),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNurseryCompanies::route('/'),
            'edit' => Pages\EditNurseryCompany::route('/{record}/edit'),
        ];
    }
}
