<?php

namespace Webkul\Security\Filament\Resources;

use BackedEnum;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource as RolesRoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Webkul\Security\Filament\Resources\RoleResource\Pages\CreateRole;
use Webkul\Security\Filament\Resources\RoleResource\Pages\EditRole;
use Webkul\Security\Filament\Resources\RoleResource\Pages\ListRoles;
use Webkul\Security\Filament\Resources\RoleResource\Pages\ViewRole;
use Webkul\Security\Filament\Resources\RoleResource\Schemas\RoleForm;
use Webkul\Security\Filament\Resources\RoleResource\Tables\RolesTable;
use Webkul\Security\Models\Role;
use Webkul\Support\Enums\NavigationGroup;

class RoleResource extends RolesRoleResource
{
    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|\UnitEnum
    {
        return NavigationGroup::Setting;
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return (bool) ($user && ($user->hasRole('Super_admin') || $user->hasRole('super_admin')));
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return (bool) ($user && ($user->hasRole('Super_admin') || $user->hasRole('super_admin')));
    }

    protected static bool $isGloballySearchable = false;

    protected static $permissionsCollection;

    public static $permissions = null;

    protected static ?Collection $allFormPermissions = null;

    public static function canGloballySearch(): bool
    {
        return false;
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return null;
    }

    public static function getActiveNavigationIcon(): BackedEnum|Htmlable|null|string
    {
        return null;
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view'   => ViewRole::route('/{record}'),
            'edit'   => EditRole::route('/{record}/edit'),
        ];
    }

    public static function getTabFormComponentForResources(): Component
    {
        return self::shield()->hasSimpleResourcePermissionView()
            ? self::getTabFormComponentForSimpleResourcePermissionsView()
            : Tab::make('resources')
                ->label(__('filament-shield::filament-shield.resources'))
                ->visible(fn (): bool => Utils::isResourceTabEnabled())
                ->badge(static::getResourceTabBadgeCount())
                ->schema(static::getPluginResourceEntitiesSchema());
    }

    public static function getTabFormComponentForPage(): Component
    {
        $options = static::getPageOptions();
        $count = count($options);

        return Tab::make('pages')
            ->label(__('filament-shield::filament-shield.pages'))
            ->visible(fn (): bool => Utils::isPageTabEnabled() && $count > 0)
            ->badge($count)
            ->schema(static::getPluginPageEntitiesSchema());
    }

    public static function getTabFormComponentForWidget(): Component
    {
        $options = static::getWidgetOptions();
        $count = count($options);

        return Tab::make('widgets')
            ->label(__('filament-shield::filament-shield.widgets'))
            ->visible(fn (): bool => Utils::isWidgetTabEnabled() && $count > 0)
            ->badge($count)
            ->schema(static::getPluginWidgetEntitiesSchema());
    }

    public static function getShieldFormComponents(): Component
    {
        return Tabs::make('Permissions')
            ->contained()
            ->tabs([
                static::getTabFormComponentForResources(),
                static::getTabFormComponentForPage(),
                static::getTabFormComponentForWidget(),
                static::getTabFormComponentForApps(),
            ])
            ->columnSpan('full');
    }

    public static function getTabFormComponentForApps(): Component
    {
        $apps = static::getAppModulesList();
        $count = count($apps);

        $options = collect($apps)->mapWithKeys(function ($app) {
            return [$app['permission'] => $app['name']];
        })->toArray();

        $descriptions = collect($apps)->mapWithKeys(function ($app) {
            return [$app['permission'] => $app['description']];
        })->toArray();

        return Tab::make('apps')
            ->label('التطبيقات')
            ->icon('heroicon-o-squares-2x2')
            ->badge($count)
            ->schema([
                Section::make('صلاحيات التطبيقات والموديولات الرئيسية')
                    ->description('تحكم في ظهور أو إخفاء أيقونة التطبيق والوصول إلى كافة شاشاته وروابطه الداخلية لهذا الدور. التطبيق المحدد (متاح) والتطبيق غير المحدد (مخفي ومحظور).')
                    ->schema([
                        CheckboxList::make('apps_tab')
                            ->hiddenLabel()
                            ->options($options)
                            ->descriptions($descriptions)
                            ->searchable()
                            ->bulkToggleable()
                            ->columns([
                                'default' => 1,
                                'sm'      => 2,
                                'lg'      => 2,
                                'xl'      => 3,
                            ])
                            ->afterStateHydrated(function (Component $component, string $operation, ?Model $record) use ($options): void {
                                static::setPermissionStateForRecordPermissions(
                                    component: $component,
                                    operation: $operation,
                                    permissions: $options,
                                    record: $record
                                );
                            })
                            ->dehydrated(fn ($state): bool => ! blank($state)),
                    ]),
            ]);
    }

    public static function getAppModulesList(): array
    {
        return [
            'nursery' => [
                'name'        => 'براعم (Baraem)',
                'description' => 'إدارة قبول وتسجيل الأطفال، خطط الاشتراكات، السداد المالي، والتقويم الأكاديمي',
                'permission'  => 'app_nursery',
            ],
            'sales' => [
                'name'        => 'المبيعات (Sales)',
                'description' => 'إدارة أوامر البيع وعروض الأسعار والعملاء ومسار المبيعات',
                'permission'  => 'app_sales',
            ],
            'purchases' => [
                'name'        => 'المشتريات (Purchases)',
                'description' => 'إدارة أوامر الشراء وطلبات عروض الأسعار والموردين',
                'permission'  => 'app_purchases',
            ],
            'invoices' => [
                'name'        => 'الفواتير (Invoices)',
                'description' => 'إنشاء وإدارة فواتير العملاء والموردين والدفعات المالية',
                'permission'  => 'app_invoices',
            ],
            'accounts' => [
                'name'        => 'المحاسبة (Accounting)',
                'description' => 'إدارة شجرة الحسابات واليوميات والقيود المحاسبية والإقفال',
                'permission'  => 'app_accounts',
            ],
            'inventories' => [
                'name'        => 'المخزون (Inventory)',
                'description' => 'إدارة المنتجات والمستودعات والتحويلات وجرد المخزون',
                'permission'  => 'app_inventories',
            ],
            'employees' => [
                'name'        => 'الموظفين (Employees)',
                'description' => 'إدارة سجلات الموظفين والأقسام وعقود العمل',
                'permission'  => 'app_employees',
            ],
            'recruitments' => [
                'name'        => 'التوظيف (Recruitment)',
                'description' => 'إدارة الوظائف الشاغرة وطلبات التوظيف ومراحل التقييم',
                'permission'  => 'app_recruitments',
            ],
            'time-off' => [
                'name'        => 'الإجازات (Time Off)',
                'description' => 'إدارة طلبات الإجازات وأنواعها وأرصدة الموظفين',
                'permission'  => 'app_time_off',
            ],
            'projects' => [
                'name'        => 'المشاريع (Projects)',
                'description' => 'إدارة المشاريع والمهام وساعات العمل وتتبع الإنجاز',
                'permission'  => 'app_projects',
            ],
            'manufacturing' => [
                'name'        => 'التصنيع (Manufacturing)',
                'description' => 'إدارة أوامر التصنيع والإنتاج وقوائم المواد (BOM)',
                'permission'  => 'app_manufacturing',
            ],
            'maintenance' => [
                'name'        => 'الصيانة (Maintenance)',
                'description' => 'إدارة طلبات الصيانة الوقائية والطارئة والمعدات',
                'permission'  => 'app_maintenance',
            ],
            'website' => [
                'name'        => 'الموقع الإلكتروني (Website)',
                'description' => 'إدارة صفحات الموقع الإلكتروني والمدونات والمحتوى',
                'permission'  => 'app_website',
            ],
            'contacts' => [
                'name'        => 'جهات الاتصال (Contacts)',
                'description' => 'إدارة سجلات العملاء والموردين والشركاء',
                'permission'  => 'app_contacts',
            ],
            'security' => [
                'name'        => 'الأمان والإعدادات (Settings & Security)',
                'description' => 'إدارة المستخدمين والأدوار وصلاحيات النظام العامة',
                'permission'  => 'app_security',
            ],
            'plugin-manager' => [
                'name'        => 'مدير الإضافات (Plugins)',
                'description' => 'إدارة وتفعيل وتثبيت الإضافات والتطبيقات في النظام',
                'permission'  => 'app_plugins',
            ],
        ];
    }

    public static function getAllFormPermissions(): Collection
    {
        if (static::$allFormPermissions instanceof Collection) {
            return static::$allFormPermissions;
        }

        $resourcePermissions = collect(static::getResources())
            ->flatMap(fn (array $entity): array => array_keys(static::getResourcePermissionOptions($entity)));

        $appPermissions = collect(static::getAppModulesList())->pluck('permission');

        return static::$allFormPermissions = $resourcePermissions
            ->merge(array_keys(static::getPageOptions()))
            ->merge(array_keys(static::getWidgetOptions()))
            ->merge($appPermissions)
            ->unique()
            ->values();
    }

    public static function getPluginResources(): ?array
    {
        return once(fn (): array => collect(static::getResources())
            ->groupBy(function ($value, $key) {
                return explode('\\', $key)[1] ?? 'Unknown';
            })
            ->toArray());
    }

    public static function getResources(): ?array
    {
        return FilamentShield::discoverResources()
            ->reject(function ($resource) {
                if ($resource == 'BezhanSalleh\FilamentShield\Resources\Roles\RoleResource') {
                    return true;
                }

                if (Utils::getConfig()->resources->exclude) {
                    return in_array(
                        Str::of($resource)->afterLast('\\'),
                        Utils::getConfig()->resources->exclude
                    );
                }
            })
            ->mapWithKeys(function (string $resource) {
                return [
                    $resource => [
                        'model'        => str($resource::getModel())->afterLast('\\')->toString(),
                        'modelFqcn'    => str($resource::getModel())->toString(),
                        'resourceFqcn' => $resource,
                    ],
                ];
            })
            ->sortKeys()
            ->toArray();
    }

    public static function getPluginPages(): array
    {
        return collect(FilamentShield::getPages())
            ->groupBy(function ($value, $key) {
                return explode('\\', $key)[1] ?? 'Unknown';
            })
            ->toArray();
    }

    public static function getPluginWidgets(): array
    {
        return collect(FilamentShield::getWidgets())
            ->groupBy(function ($value, $key) {
                return explode('\\', $key)[1] ?? 'Unknown';
            })
            ->toArray();
    }

    public static function getPluginResourceEntitiesSchema(): ?array
    {
        return collect(static::getPluginResources())
            ->sortKeys()
            ->map(function ($plugin, $key) {
                $hasAnyOptions = collect($plugin)->contains(function ($entity) {
                    return ! empty(static::getResourcePermissionOptions($entity));
                });

                if (! $hasAnyOptions) {
                    return;
                }

                return Section::make($key)
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->schema([
                        Grid::make()
                            ->schema(function () use ($plugin) {
                                return collect($plugin)
                                    ->flatMap(function ($entity) {
                                        $options = static::getResourcePermissionOptions($entity);

                                        if (empty($options)) {
                                            return [];
                                        }

                                        $fieldsetLabel = strval(
                                            static::shield()->hasLocalizedPermissionLabels()
                                                ? FilamentShield::getLocalizedResourceLabel($entity['resourceFqcn'])
                                                : $entity['model']
                                        );

                                        return [
                                            Fieldset::make($fieldsetLabel)
                                                ->schema([
                                                    static::getCheckBoxListComponentForResource($entity)->hiddenLabel(),
                                                ])
                                                ->columnSpan(static::shield()->getSectionColumnSpan()),
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->columns(static::shield()->getGridColumns()),
                    ]);
            })
            ->toArray();
    }

    public static function getPluginPageEntitiesSchema(): ?array
    {
        return collect(static::getPluginPages())
            ->sortKeys()
            ->map(function ($plugin, $key) {
                return Section::make($key)
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->schema([
                        Grid::make()
                            ->schema(function () use ($plugin, $key) {
                                $options = collect($plugin)
                                    ->flatMap(fn ($page) => $page['permissions'])
                                    ->toArray();

                                return [
                                    static::getCheckboxListFormComponent(
                                        name: $key.'_pages_tab',
                                        options: $options,
                                    ),
                                ];
                            }),
                    ]);
            })
            ->values()
            ->toArray();
    }

    public static function getPluginWidgetEntitiesSchema(): ?array
    {
        return collect(static::getPluginWidgets())
            ->sortKeys()
            ->map(function ($plugin, $key) {
                return Section::make($key)
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->schema([
                        Grid::make()
                            ->schema(function () use ($plugin, $key) {
                                $options = collect($plugin)
                                    ->flatMap(fn ($page) => $page['permissions'])
                                    ->toArray();

                                return [
                                    static::getCheckboxListFormComponent(
                                        name: $key.'_widgets_tab',
                                        options: $options,
                                    ),
                                ];
                            }),
                    ]);
            })
            ->values()
            ->toArray();
    }

    public static function getCheckboxListFormComponent(
        string $name,
        array $options,
        bool $searchable = true,
        array|int|string|null $columns = null,
        array|int|string|null $columnSpan = null
    ): Component {
        return CheckboxList::make($name)
            ->hiddenLabel()
            ->options(fn (): array => $options)
            ->searchable($searchable)
            ->bulkToggleable()
            ->afterStateHydrated(function (Component $component, string $operation, ?Model $record) use ($options): void {
                static::setPermissionStateForRecordPermissions(
                    component: $component,
                    operation: $operation,
                    permissions: $options,
                    record: $record
                );
            })
            ->dehydrated(fn ($state): bool => ! blank($state))
            ->gridDirection('row')
            ->columns($columns ?? static::shield()->getCheckboxListColumns())
            ->columnSpan($columnSpan ?? static::shield()->getCheckboxListColumnSpan());
    }

    public static function setPermissionStateForRecordPermissions(Component $component, string $operation, array $permissions, ?Model $record): void
    {
        if (in_array($operation, ['edit', 'view'])) {
            if (blank($record)) {
                return;
            }

            if ($component->isVisible() && count($permissions) > 0) {
                $component->state(
                    collect($permissions)
                        ->filter(function ($value, $key) use ($record) {
                            return static::getPermissions($record)->contains($key);
                        })
                        ->keys()
                        ->toArray()
                );
            }
        }
    }

    public static function getPermissions($record)
    {
        if (! is_null(static::$permissions)) {
            return static::$permissions;
        }

        return static::$permissions = $record->permissions()->pluck('name');
    }

    public static function isProtectedRoleRecord(?Model $record): bool
    {
        return $record instanceof Role && $record->isSystemRole();
    }
}
