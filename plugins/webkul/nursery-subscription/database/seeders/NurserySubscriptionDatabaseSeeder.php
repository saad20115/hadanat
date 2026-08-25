<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Database\Seeders;

use Illuminate\Database\Seeder;

class NurserySubscriptionDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PricingPlanSeeder::class,
        ]);
    }
}
