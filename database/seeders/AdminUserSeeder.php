<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Webkul\Security\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure SAR currency exists
        $sarId = DB::table('currencies')->where('name', 'SAR')->value('id') ?? 150;

        // 2. Ensure Partner 1 exists
        if (! DB::table('partners_partners')->where('id', 1)->exists()) {
            DB::table('partners_partners')->insert([
                'id'           => 1,
                'name'         => 'مدرسة العقول النامية الأهلية',
                'email'        => 'info@hadanat.com',
                'account_type' => 'company',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // 3. Ensure Company 1 exists
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

        // 4. Ensure Super Admin Role
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $user = User::updateOrCreate(
            ['email' => 'admin@hadanat.com'],
            [
                'name'               => 'المدير العام (Super Admin)',
                'password'           => Hash::make('password123'),
                'default_company_id' => 1,
                'is_active'          => true,
                'is_default'         => true,
            ]
        );

        $user->assignRole($role);
        $user->allowedCompanies()->syncWithoutDetaching([1]);
    }
}
