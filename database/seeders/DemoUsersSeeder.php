<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Webkul\Security\Models\Role;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $sarId = DB::table('currencies')->where('name', 'SAR')->value('id') ?? 150;

        if (! DB::table('partners_partners')->where('id', 1)->exists()) {
            DB::table('partners_partners')->insert([
                'id'         => 1,
                'name'       => 'مدرسة العقول النامية الأهلية',
                'email'      => 'info@hadanat.com',
                'account_type' => 'company',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! DB::table('companies')->where('id', 1)->exists()) {
            DB::table('companies')->insert([
                'id'          => 1,
                'name'        => 'مدرسة العقول النامية الأهلية',
                'partner_id'  => 1,
                'currency_id' => $sarId,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('partners_partners', 'id'), coalesce(max(id), 1)) FROM partners_partners");
            DB::statement("SELECT setval(pg_get_serial_sequence('companies', 'id'), coalesce(max(id), 1)) FROM companies");
            DB::statement("SELECT setval(pg_get_serial_sequence('users', 'id'), coalesce(max(id), 1)) FROM users");
        }

        $nurseryAllPermissions = [
            'app_nursery',
            'view_any_nursery_subscription_subscription', 'view_nursery_subscription_subscription', 'create_nursery_subscription_subscription', 'update_nursery_subscription_subscription', 'delete_nursery_subscription_subscription', 'delete_any_nursery_subscription_subscription', 'restore_nursery_subscription_subscription', 'restore_any_nursery_subscription_subscription', 'force_delete_nursery_subscription_subscription', 'force_delete_any_nursery_subscription_subscription',
            'view_any_nursery_subscription_child', 'view_nursery_subscription_child', 'create_nursery_subscription_child', 'update_nursery_subscription_child', 'delete_nursery_subscription_child', 'delete_any_nursery_subscription_child', 'restore_nursery_subscription_child', 'restore_any_nursery_subscription_child', 'force_delete_nursery_subscription_child', 'force_delete_any_nursery_subscription_child',
            'view_any_nursery_subscription_payment', 'view_nursery_subscription_payment', 'create_nursery_subscription_payment', 'update_nursery_subscription_payment', 'delete_nursery_subscription_payment', 'delete_any_nursery_subscription_payment',
            'view_any_nursery_subscription_pricing::plan', 'view_nursery_subscription_pricing::plan', 'create_nursery_subscription_pricing::plan', 'update_nursery_subscription_pricing::plan', 'delete_nursery_subscription_pricing::plan', 'delete_any_nursery_subscription_pricing::plan',
            'view_any_nursery_subscription_age::stage::rule', 'view_nursery_subscription_age::stage::rule', 'create_nursery_subscription_age::stage::rule', 'update_nursery_subscription_age::stage::rule',
            'view_any_nursery_subscription_academic::year', 'view_nursery_subscription_academic::year', 'create_nursery_subscription_academic::year', 'update_nursery_subscription_academic::year',
            'view_any_nursery_subscription_holiday', 'view_nursery_subscription_holiday', 'create_nursery_subscription_holiday', 'update_nursery_subscription_holiday',
            'page_nursery_subscription_nursery_reports', 'page_nursery_subscription_subscription_calculator',
        ];

        $roles = [
            'super_admin'        => ['label' => 'مدير عام النظام', 'landing' => 'dashboard', 'perms' => null],
            'nursery_manager'    => ['label' => 'مدير الحضانة', 'landing' => 'nursery/subscriptions', 'perms' => $nurseryAllPermissions],
            'nursery_accountant' => ['label' => 'محاسب الحضانة', 'landing' => 'nursery/payments', 'perms' => [
                'app_nursery', 'app_invoices', 'app_accounts',
                'view_any_nursery_subscription_payment', 'view_nursery_subscription_payment', 'create_nursery_subscription_payment', 'update_nursery_subscription_payment',
                'view_any_nursery_subscription_subscription', 'view_nursery_subscription_subscription',
                'view_any_nursery_subscription_child', 'view_nursery_subscription_child',
                'view_any_nursery_subscription_pricing::plan', 'view_nursery_subscription_pricing::plan',
                'page_nursery_subscription_nursery_reports', 'page_nursery_subscription_subscription_calculator',
            ]],
            'nursery_registrar'  => ['label' => 'مسؤول التسجيل والاستقبال', 'landing' => 'nursery/children', 'perms' => [
                'app_nursery',
                'view_any_nursery_subscription_child', 'view_nursery_subscription_child', 'create_nursery_subscription_child', 'update_nursery_subscription_child',
                'view_any_nursery_subscription_subscription', 'view_nursery_subscription_subscription', 'create_nursery_subscription_subscription', 'update_nursery_subscription_subscription',
                'view_any_nursery_subscription_pricing::plan', 'view_nursery_subscription_pricing::plan',
                'page_nursery_subscription_subscription_calculator',
            ]],
            'nursery_supervisor' => ['label' => 'مشرفة الحضانة', 'landing' => 'nursery/children', 'perms' => [
                'app_nursery',
                'view_any_nursery_subscription_child', 'view_nursery_subscription_child', 'update_nursery_subscription_child',
                'view_any_nursery_subscription_subscription', 'view_nursery_subscription_subscription',
                'page_nursery_subscription_nursery_reports',
            ]],
        ];

        foreach ($roles as $roleName => $roleData) {
            // Delete duplicate roles (Title Case aliases like "Nursery Manager")
            $titleCase = ucwords(str_replace('_', ' ', $roleName));
            $aliasRole = Role::whereRaw("LOWER(REPLACE(name, ' ', '_')) = ?", [strtolower($roleName)])
                ->whereRaw("LOWER(name) != ?", [strtolower($roleName)])
                ->first();

            if ($aliasRole) {
                DB::table('model_has_roles')
                    ->where('role_id', $aliasRole->id)
                    ->update(['role_id' => Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'])->id]);
                $aliasRole->delete();
            }

            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->update(['default_landing_page' => $roleData['landing']]);

            if (is_array($roleData['perms'])) {
                $role->syncPermissionsByNames($roleData['perms']);
            }
        }

        $users = [
            [
                'name'       => 'المدير العام (Super Admin)',
                'email'      => 'admin@hadanat.com',
                'password'   => 'password123',
                'role'       => 'super_admin',
                'is_default' => true,
            ],
            [
                'name'       => 'مديرة الحضانة (Nursery Manager)',
                'email'      => 'manager@hadanat.com',
                'password'   => 'manager123',
                'role'       => 'nursery_manager',
                'is_default' => false,
            ],
            [
                'name'       => 'المحاسب المالي (Accountant)',
                'email'      => 'accountant@hadanat.com',
                'password'   => 'account123',
                'role'       => 'nursery_accountant',
                'is_default' => false,
            ],
            [
                'name'       => 'موظف التسجيل والقبول (Registrar)',
                'email'      => 'registrar@hadanat.com',
                'password'   => 'registrar123',
                'role'       => 'nursery_registrar',
                'is_default' => false,
            ],
            [
                'name'       => 'مشرفة الأطفال والأنشطة (Supervisor)',
                'email'      => 'supervisor@hadanat.com',
                'password'   => 'supervisor123',
                'role'       => 'nursery_supervisor',
                'is_default' => false,
            ],
        ];

        foreach ($users as $u) {
            $role = Role::where('name', $u['role'])->first();

            $existing = DB::table('users')->where('email', $u['email'])->first();
            if ($existing) {
                $userId = $existing->id;
                DB::table('users')->where('id', $userId)->update([
                    'name'               => $u['name'],
                    'password'           => Hash::make($u['password']),
                    'is_active'          => true,
                    'is_default'         => $u['is_default'],
                    'default_company_id' => 1,
                    'updated_at'         => now(),
                ]);
            } else {
                $userId = DB::table('users')->insertGetId([
                    'name'               => $u['name'],
                    'email'              => $u['email'],
                    'password'           => Hash::make($u['password']),
                    'is_active'          => true,
                    'is_default'         => $u['is_default'],
                    'default_company_id' => 1,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            DB::table('model_has_roles')->where('model_id', $userId)->where('model_type', 'Webkul\\Security\\Models\\User')->delete();
            DB::table('model_has_roles')->insert([
                'role_id'    => $role->id,
                'model_type' => 'Webkul\\Security\\Models\\User',
                'model_id'   => $userId,
            ]);

            DB::table('user_allowed_companies')->insertOrIgnore([
                'user_id'    => $userId,
                'company_id' => 1,
            ]);
        }
    }
}
