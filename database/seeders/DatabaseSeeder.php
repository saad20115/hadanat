<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\PluginManager\Database\Seeders\PluginSeeder;
use Webkul\Security\Database\Seeders\DatabaseSeeder as SecurityDatabaseSeeder;
use Webkul\Support\Database\Seeders\DatabaseSeeder as SupportDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SecurityDatabaseSeeder::class,
            SupportDatabaseSeeder::class,
            PluginSeeder::class,
        ]);

        require_once base_path('plugins/webkul/nursery-subscription/database/seeders/PricingPlanSeeder.php');
        $this->call(\Webkul\NurserySubscription\Database\Seeders\PricingPlanSeeder::class);
    }
}
