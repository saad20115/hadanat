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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Webkul\NurserySubscription\Filament\Admin\Clusters\Configurations;
use Webkul\NurserySubscription\Filament\Admin\Resources\NurseryUserResource\Pages;
use Webkul\Security\Models\User;
use Webkul\Support\Services\CompanyContext;

class NurseryUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'users';

    protected static ?string $cluster = Configurations::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

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
                            ->getOptionLabelFromRecordUsing(fn ($record) => self::formatRoleName($record->name))
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

    public static function formatRoleName(?string $role): string
    {
        return match ($role) {
            'super_admin'        => 'المدير العام (Super Admin)',
            'nursery_manager'    => 'مديرة الحضانة (Nursery Manager)',
            'nursery_accountant' => 'المحاسب المالي (Accountant)',
            'nursery_registrar'  => 'مسؤول القبول والتسجيل (Registrar)',
            'nursery_supervisor' => 'مشرفة الأطفال والأنشطة (Supervisor)',
            default              => ucfirst(str_replace('_', ' ', $role ?? '')),
        };
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasRole('super_admin')
            || $user->hasRole('Super_admin')
            || $user->can('view_any_security_user');
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        return (bool) ($user && ($user->hasRole('super_admin') || $user->hasRole('Super_admin') || $user->can('create_security_user')));
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        return (bool) ($user && ($user->hasRole('super_admin') || $user->hasRole('Super_admin') || $user->can('update_security_user')));
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        if (! $user || $user->id === $record->id) {
            return false;
        }

        return (bool) ($user->hasRole('super_admin') || $user->hasRole('Super_admin') || $user->can('delete_security_user'));
    }

    public static function table(Table $table): Table
    {
        $currentUser = Auth::user();

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
                    ->formatStateUsing(fn ($state) => self::formatRoleName($state))
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
                Action::make('changePassword')
                    ->label('تغيير كلمة المرور')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible(fn (User $record): bool => (bool) ($currentUser && (
                        $currentUser->hasRole('super_admin')
                        || $currentUser->hasRole('Super_admin')
                        || $currentUser->can('update_security_user')
                        || $currentUser->id === $record->id
                    )))
                    ->modalHeading(fn (User $record): string => 'تغيير كلمة المرور للمستخدم: '.$record->name)
                    ->modalDescription('أدخل كلمة المرور الجديدة لهذا الحساب وسيتم تحديثها فوراً.')
                    ->form([
                        TextInput::make('new_password')
                            ->label('كلمة المرور الجديدة')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(6)
                            ->placeholder('أدخل 6 خانات على الأقل...'),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update([
                            'password' => Hash::make($data['new_password']),
                        ]);
                        Notification::make()
                            ->title('تم تغيير كلمة المرور بنجاح')
                            ->body('تم تحديث كلمة المرور للمستخدم '.$record->name.' بنجاح.')
                            ->success()
                            ->send();
                    }),

                Action::make('manageRoles')
                    ->label('تعديل الصلاحيات')
                    ->icon('heroicon-o-shield-check')
                    ->color('info')
                    ->visible(fn (User $record): bool => (bool) ($currentUser && (
                        $currentUser->hasRole('super_admin')
                        || $currentUser->hasRole('Super_admin')
                        || $currentUser->can('update_security_role')
                        || $currentUser->can('update_security_user')
                    )))
                    ->modalHeading(fn (User $record): string => 'تعديل الأدوار والصلاحيات للمستخدم: '.$record->name)
                    ->modalDescription('حدد الأدوار الوظيفية الممنوحة لهذا المستخدم في النظام:')
                    ->fillForm(fn (User $record): array => [
                        'roles' => $record->roles->pluck('id')->toArray(),
                    ])
                    ->form([
                        Select::make('roles')
                            ->label('الأدوار والصلاحيات')
                            ->options(
                                Role::all()
                                    ->pluck('name', 'id')
                                    ->map(fn ($name) => self::formatRoleName($name))
                            )
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $roleIds = $data['roles'] ?? [];
                        $roles = Role::whereIn('id', $roleIds)->get();
                        $record->syncRoles($roles);
                        Notification::make()
                            ->title('تم تحديث الأدوار والصلاحيات بنجاح')
                            ->body('تم حفظ وتطبيق الصلاحيات الجديدة للمستخدم '.$record->name.' فوراً.')
                            ->success()
                            ->send();
                    }),

                Action::make('toggleActive')
                    ->label(fn (User $record): string => $record->is_active ? 'تعطيل الحساب' : 'تنشيط الحساب')
                    ->icon(fn (User $record): string => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                    ->color(fn (User $record): string => $record->is_active ? 'danger' : 'success')
                    ->visible(fn (User $record): bool => (bool) ($currentUser && $currentUser->id !== $record->id && (
                        $currentUser->hasRole('super_admin')
                        || $currentUser->hasRole('Super_admin')
                        || $currentUser->can('update_security_user')
                    )))
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
                DeleteAction::make()
                    ->hidden(fn (User $record): bool => (bool) ($currentUser && $currentUser->id === $record->id)),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = app(CompanyContext::class)->currentId() ?? Auth::user()?->default_company_id ?? 1;

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
            'index'  => Pages\ListNurseryUsers::route('/'),
            'create' => Pages\CreateNurseryUser::route('/create'),
            'edit'   => Pages\EditNurseryUser::route('/{record}/edit'),
        ];
    }
}
