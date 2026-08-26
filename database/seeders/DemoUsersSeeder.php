<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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

        $roles = [
            'super_admin'        => ['label' => 'مدير عام النظام', 'landing' => 'dashboard'],
            'nursery_manager'    => ['label' => 'مدير الحضانة', 'landing' => 'nursery/subscriptions'],
            'nursery_accountant' => ['label' => 'محاسب الحضانة', 'landing' => 'nursery/payments'],
            'nursery_registrar'  => ['label' => 'مسؤول التسجيل والاستقبال', 'landing' => 'nursery/children'],
            'nursery_supervisor' => ['label' => 'مشرفة الحضانة', 'landing' => 'nursery/children'],
        ];

        foreach ($roles as $roleName => $roleData) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->update(['default_landing_page' => $roleData['landing']]);
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
