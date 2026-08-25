<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\AcademicYearResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TermsRelationManager extends RelationManager
{
    protected static string $relationship = 'terms';

    protected static ?string $title = 'الفصول الدراسية التابعة لهذه السنة';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الفصل الدراسي')
                    ->placeholder('مثال: الفصل الدراسي الأول، الفصل الثاني، الفصل الصيفي')
                    ->required()
                    ->maxLength(100),

                DatePicker::make('start_date')
                    ->label('تاريخ بداية الفصل (ميلادي)')
                    ->required(),

                DatePicker::make('end_date')
                    ->label('تاريخ نهاية الفصل (ميلادي)')
                    ->required(),

                Toggle::make('is_current')
                    ->label('الفصل الدراسي الحالي النشط')
                    ->helperText('اعتماد هذا الفصل كفصل دراسي حالي مفعل')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('الفصل الدراسي')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('start_date')
                    ->label('تاريخ البداية (ميلادي)')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('تاريخ النهاية (ميلادي)')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('duration_weeks')
                    ->label('المدة التقديرية')
                    ->state(function ($record) {
                        if ($record->start_date && $record->end_date) {
                            $days = $record->start_date->diffInDays($record->end_date);
                            $weeks = ceil($days / 7);
                            return "{$days} يوماً ({$weeks} أسبوع)";
                        }
                        return '-';
                    })
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_current')
                    ->label('الفصل الحالي')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة فصل دراسي جديد')
                    ->icon('heroicon-o-plus-circle')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['company_id'] = Auth::user()?->default_company_id ?? 2;
                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('start_date', 'asc');
    }
}
