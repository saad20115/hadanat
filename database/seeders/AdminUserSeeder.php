<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Webkul\Security\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $user = User::updateOrCreate(
            ['email' => 'admin@hadanat.com'],
            [
                'name'               => 'مدير النظام والحضانة',
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
