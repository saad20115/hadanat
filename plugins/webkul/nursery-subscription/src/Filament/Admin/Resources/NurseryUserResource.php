<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Webkul\NurserySubscription\Filament\Admin\Clusters\Configurations;
use Webkul\NurserySubscription\Filament\Admin\Resources\NurseryUserResource\Pages;
use Webkul\Security\Models\User;

class NurseryUserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $slug = 'users';
    protected static ?string $cluster = Configurations::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return 'مستخدم';
    }

    public static function getPluralModelLabel(): string
    {
        return 'المستخدمين والصلاحيات';
    }

    public static function getNavigationLabel(): string
    {
        return 'المستخدمين والصلاحيات';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات حساب المستخدم')
                    ->description('إدارة معلومات الدخول والصلاحيات وحالة تفعيل الحساب')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم الكامل')
                            ->placeholder('مثال: سارة محمد الغامدي')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('البريد الإلكتروني (اسم المستخدم)')
                            ->placeholder('sara@nursery.com')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateNurseryUser)
                            ->helperText(fn ($livewire) => $livewire instanceof Pages\EditNurseryUser ? 'اتركه فارغاً إذا كنت لا ترغب في تغيير كلمة المرور' : null),

                        Select::make('roles')
                            ->label('الأدوار والصلاحيات')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),

                        Toggle::make('is_active')
                            ->label('تنشيط الحساب (مفعّل)')
                            ->helperText('عند التعطيل لن يتمكن المستخدم من تسجيل الدخول للنظام')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المستخدم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label('الدور / الصلاحيات')
                    ->badge()
                    ->color('primary'),

                IconColumn::make('is_active')
                    ->label('حالة الحساب')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->actions([
                Action::make('toggleActive')
                    ->label(fn (User $record): string => $record->is_active ? 'تعطيل الحساب' : 'تنشيط الحساب')
                    ->icon(fn (User $record): string => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                    ->color(fn (User $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => $record->is_active ? 'تأكيد تعطيل حساب المستخدم' : 'تأكيد تنشيط حساب المستخدم')
                    ->modalDescription(fn (User $record): string => $record->is_active ? 'هل أنت متأكد من تعطيل هذا الحساب؟ لن يتمكن المستخدم من الدخول للنظام.' : 'هل تريد تنشيط هذا الحساب والسماح له بالدخول؟')
                    ->action(function (User $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? 'تم تنشيط الحساب بنجاح' : 'تم تعطيل الحساب بنجاح')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = Auth::user()?->default_company_id ?? 2;

        return parent::getEloquentQuery()
            ->where(function ($q) use ($companyId) {
                $q->where('default_company_id', $companyId)
                    ->orWhereHas('allowedCompanies', fn ($c) => $c->where('companies.id', $companyId))
                    ->orWhere('id', Auth::id());
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNurseryUsers::route('/'),
            'create' => Pages\CreateNurseryUser::route('/create'),
            'edit' => Pages\EditNurseryUser::route('/{record}/edit'),
        ];
    }
}
