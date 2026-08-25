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
        $roles = [
            'super_admin'        => 'مدير عام النظام',
            'nursery_manager'    => 'مدير الحضانة',
            'nursery_accountant' => 'محاسب الحضانة',
            'nursery_registrar'  => 'مسؤول التسجيل والاستقبال',
            'nursery_supervisor' => 'مشرفة الحضانة',
        ];

        foreach ($roles as $roleName => $label) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
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
