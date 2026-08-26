<?php

namespace Webkul\Security\Models;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as BaseRole;
use Spatie\Permission\PermissionRegistrar;

class Role extends BaseRole
{
    protected const SYSTEM_ROLE_FALLBACKS = [
        'admin',
        'super_admin',
    ];

    protected $fillable = [
        'name',
        'guard_name',
        'default_landing_page',
        'is_default',
    ];

    public function getNameAttribute(string $value): string
    {
        return Str::ucfirst($value);
    }

    protected static function booted(): void
    {
        static::updating(function (self $role): void {
            if (! $role->isSystemRole()) {
                return;
            }

            if ($role->isDirty(['name', 'guard_name'])) {
                throw new AuthorizationException(__('You are not allowed to modify this system role.'));
            }
        });

        static::deleting(function (self $role): void {
            if ($role->isSystemRole()) {
                throw new AuthorizationException(__('You are not allowed to delete this system role.'));
            }
        });
    }

    public function isSystemRole(): bool
    {
        $name = $this->getRawOriginal('name') ?: $this->attributes['name'] ?? null;

        if ((! is_string($name) || $name === '') && $this->exists) {
            $name = static::query()
                ->whereKey($this->getKey())
                ->value('name');
        }

        if (! is_string($name) || $name === '') {
            return false;
        }

        return in_array(static::normalizeRoleName($name), static::getSystemRoleNames(), true);
    }

    public static function getSystemRoleNames(): array
    {
        $configuredNames = [
            config('filament-shield.panel_user.name'),
            config('filament-shield.super_admin.name'),
        ];

        return collect(array_merge(static::SYSTEM_ROLE_FALLBACKS, $configuredNames))
            ->filter(fn ($name) => is_string($name) && $name !== '')
            ->map(fn (string $name) => static::normalizeRoleName($name))
            ->unique()
            ->values()
            ->all();
    }

    protected static function normalizeRoleName(string $name): string
    {
        return Str::of($name)->trim()->lower()->toString();
    }

    /**
     * Sync permissions by their names.
     * Creates missing permissions and syncs them to the role.
     */
    public function syncPermissionsByNames(Collection|array $permissionNames): void
    {
        $permissionNames = collect($permissionNames)->unique()->values();

        if ($permissionNames->isEmpty()) {
            $this->syncPermissions([]);

            return;
        }

        $permissionIds = $this->ensurePermissionsExist($permissionNames);

        $this->syncPermissionsToRole($permissionIds);
    }

    /**
     * Ensure all permissions exist in the database and return their IDs.
     */
    private function ensurePermissionsExist(Collection $permissionNames): Collection
    {
        $permissionModel = Utils::getPermissionModel();

        $guard = $this->guard_name;

        $chunkSize = 500;

        $allPermissionIds = collect();

        $permissionNames->chunk($chunkSize)->each(function ($chunk) use ($permissionModel, $guard, &$allPermissionIds) {
            $existingPermissions = $permissionModel::whereIn('name', $chunk)
                ->where('guard_name', $guard)
                ->pluck('id', 'name');

            $missingPermissions = $chunk->diff($existingPermissions->keys());

            if ($missingPermissions->isNotEmpty()) {
                $this->createMissingPermissions($permissionModel, $missingPermissions, $guard);

                $newPermissions = $permissionModel::whereIn('name', $missingPermissions)
                    ->where('guard_name', $guard)
                    ->pluck('id', 'name');

                $existingPermissions = $existingPermissions->merge($newPermissions);
            }

            $allPermissionIds = $allPermissionIds->merge($existingPermissions->values());
        });

        return $allPermissionIds->unique()->values();
    }

    /**
     * Create missing permissions in bulk.
     */
    private function createMissingPermissions(string $permissionModel, Collection $permissionNames, string $guard): void
    {
        $insertData = $permissionNames->map(fn ($name) => [
            'name'       => $name,
            'guard_name' => $guard,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        $permissionModel::insertOrIgnore($insertData);
    }

    /**
     * Sync permissions to the role in the pivot table.
     */
    private function syncPermissionsToRole(Collection $permissionIds): void
    {
        $tableName = config('permission.table_names.role_has_permissions');

        $permissionRegistrar = app(PermissionRegistrar::class);

        $roleColumn = $permissionRegistrar->pivotRole;

        $permissionColumn = $permissionRegistrar->pivotPermission;

        $existingPermissionIds = DB::table($tableName)
            ->where($roleColumn, $this->id)
            ->pluck($permissionColumn)
            ->map(fn ($permissionId) => (int) $permissionId);

        $permissionIds = $permissionIds
            ->map(fn ($permissionId) => (int) $permissionId)
            ->unique()
            ->values();

        $permissionIdsToDelete = $existingPermissionIds->diff($permissionIds)->values();
        $permissionIdsToInsert = $permissionIds->diff($existingPermissionIds)->values();

        if ($permissionIdsToDelete->isNotEmpty()) {
            DB::table($tableName)
                ->where($roleColumn, $this->id)
                ->whereIn($permissionColumn, $permissionIdsToDelete)
                ->delete();
        }

        if ($permissionIdsToInsert->isNotEmpty()) {
            $chunkSize = 1000;

            $permissionIdsToInsert->chunk($chunkSize)->each(function ($chunk) use ($tableName, $roleColumn, $permissionColumn) {
                $insertData = $chunk->map(fn ($permissionId) => [
                    $roleColumn       => $this->id,
                    $permissionColumn => $permissionId,
                ])->toArray();

                DB::table($tableName)->insert($insertData);
            });
        }

        $this->forgetCachedPermissions();
    }

    public static function getLandingPageOptions(): array
    {
        return [
            'براعم (Baraem)' => [
                'nursery/subscriptions'           => 'الاشتراكات (Subscriptions)',
                'nursery/children'                => 'الأطفال (Children)',
                'nursery/payments'                => 'المدفوعات (Payments)',
                'nursery/pricing-plans'           => 'الباقات والأسعار (Pricing Plans)',
                'nursery/subscription-calculator' => 'حاسبة الاشتراكات (Calculator)',
                'nursery/reports'                 => 'لوحة التقارير ومؤشرات الأداء (Reports)',
                'nursery/academic-years'          => 'التقويم والفصول الأكاديمية (Academic Years)',
                'nursery/age-stage-rules'         => 'قواعد المراحل العمرية (Age Stages)',
                'nursery/holidays'                => 'العطلات والإجازات الرسمية (Holidays)',
                'nursery/company'                 => 'بيانات الحضانة والمنشأة (Company Profile)',
                'nursery/configurations/users'    => 'المستخدمين والصلاحيات (Users & Roles)',
            ],
            'لوحة التحكم الرئيسية' => [
                'dashboard' => 'لوحة التحكم الرئيسية (Main Dashboard)',
            ],
            'المبيعات (Sales)' => [
                'sales/orders'      => 'أوامر البيع (Sales Orders)',
                'sales/quotations'  => 'عروض الأسعار (Quotations)',
                'sales/teams'       => 'فرق المبيعات (Sales Teams)',
            ],
            'المشتريات (Purchases)' => [
                'purchases/orders'        => 'أوامر الشراء (Purchase Orders)',
                'purchases/requisitions'  => 'طلبات الشراء (Purchase Requisitions)',
            ],
            'الفواتير (Invoices)' => [
                'invoices/invoices' => 'فواتير العملاء والموردين (Invoices)',
                'invoices/payments' => 'الدفعات والتحصيلات (Payments)',
            ],
            'المحاسبة (Accounting)' => [
                'accounts/accounts' => 'شجرة الحسابات (Chart of Accounts)',
                'accounts/journals' => 'اليوميات والقيود (Journals)',
                'accounts/moves'    => 'القيود المحاسبية (Journal Entries)',
            ],
            'المخزون (Inventory)' => [
                'inventories/products'   => 'المنتجات (Products)',
                'inventories/warehouses' => 'المستودعات (Warehouses)',
                'inventories/moves'      => 'حركات المخزون (Stock Moves)',
            ],
            'الموظفين (Employees)' => [
                'employees/employees'   => 'سجلات الموظفين (Employees)',
                'employees/departments' => 'الأقسام الإدارية (Departments)',
            ],
            'التوظيف (Recruitment)' => [
                'recruitments/job-positions' => 'الوظائف الشاغرة (Job Positions)',
                'recruitments/applicants'    => 'طلبات التوظيف (Applicants)',
            ],
            'الإجازات (Time Off)' => [
                'time-off/leaves'      => 'طلبات الإجازات (Leave Requests)',
                'time-off/leave-types' => 'أنواع الإجازات (Leave Types)',
            ],
            'المشاريع (Projects)' => [
                'projects/projects' => 'المشاريع والمهام (Projects)',
            ],
            'التصنيع (Manufacturing)' => [
                'manufacturing/orders' => 'أوامر التصنيع (Manufacturing Orders)',
            ],
            'الصيانة (Maintenance)' => [
                'maintenance/requests' => 'طلبات الصيانة (Maintenance Requests)',
            ],
            'الموقع الإلكتروني (Website)' => [
                'website/pages' => 'صفحات الموقع (Website Pages)',
                'blogs/posts'   => 'المدونات والمقالات (Blog Posts)',
            ],
            'جهات الاتصال (Contacts)' => [
                'contacts/contacts' => 'جهات الاتصال والعملاء (Contacts)',
            ],
            'الأمان والإعدادات (Settings)' => [
                'shield/roles' => 'الأدوار والصلاحيات (Roles)',
                'users'        => 'المستخدمين (Users)',
            ],
            'مدير الإضافات (Plugins)' => [
                'plugins' => 'مدير الإضافات (Plugins Manager)',
            ],
        ];
    }

    public static function getLandingPageForUser($user): string
    {
        if (! $user) {
            return '/admin/login';
        }

        $isSuperAdmin = $user->hasRole('super_admin') || $user->hasRole('Super_admin') || (bool) ($user->is_default ?? false);

        // 1. Check user roles explicit default_landing_page
        $roles = $user->roles;
        $configuredRole = null;

        if ($roles instanceof Collection) {
            $configuredRole = $roles->first(fn ($r) => ! empty($r->default_landing_page) && ! in_array($r->default_landing_page, ['admin', 'dashboard']));
        } elseif (method_exists($user, 'roles')) {
            $configuredRole = $user->roles()
                ->whereNotNull('default_landing_page')
                ->whereNotIn('default_landing_page', ['', 'admin', 'dashboard'])
                ->first();
        }

        if ($configuredRole && ! empty($configuredRole->default_landing_page)) {
            $path = ltrim($configuredRole->default_landing_page, '/');

            return '/admin/'.$path;
        }

        // 2. Super Admin defaults to Nursery Reports
        if ($isSuperAdmin) {
            return '/admin/nursery/reports';
        }

        // 3. Fallback based on specific role names
        if ($user->hasRole('nursery_manager') || $user->hasRole('manager')) {
            return '/admin/nursery/subscriptions';
        }

        if ($user->hasRole('nursery_accountant') || $user->hasRole('accountant')) {
            return '/admin/nursery/payments';
        }

        if ($user->hasRole('nursery_registrar') || $user->hasRole('registrar') || $user->hasRole('nursery_supervisor') || $user->hasRole('supervisor')) {
            return '/admin/nursery/children';
        }

        // 4. General Default for everyone is Baraem Subscriptions
        return '/admin/nursery/subscriptions';
    }
}
