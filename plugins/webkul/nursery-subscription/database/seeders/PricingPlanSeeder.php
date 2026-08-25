<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\NurserySubscription\Models\PricingPlan;

class PricingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1;
        $plans = [];
        $sortOrder = 1;

        // INFANT (6-18 months) - الرضع
        $infantPlans = [
            // Hourly
            ['duration_type' => 'hourly', 'hours_per_day' => null, 'duration_value' => 1, 'duration_label' => 'ساعة واحدة', 'price' => 60],
            ['duration_type' => 'hourly', 'hours_per_day' => null, 'duration_value' => 2, 'duration_label' => 'ساعتان', 'price' => 100],
            ['duration_type' => 'hourly', 'hours_per_day' => null, 'duration_value' => 3, 'duration_label' => '3 ساعات', 'price' => 120],
            
            // Daily
            ['duration_type' => 'daily', 'hours_per_day' => 4, 'duration_value' => 1, 'duration_label' => 'يوم واحد (4 ساعات)', 'price' => 140],
            ['duration_type' => 'daily', 'hours_per_day' => 6, 'duration_value' => 1, 'duration_label' => 'يوم واحد (6 ساعات)', 'price' => 180],
            ['duration_type' => 'daily', 'hours_per_day' => 8, 'duration_value' => 1, 'duration_label' => 'يوم واحد (8 ساعات)', 'price' => 200],
            
            // Weekly
            ['duration_type' => 'weekly', 'hours_per_day' => 4, 'duration_value' => 1, 'duration_label' => 'أسبوع واحد (4 ساعات)', 'price' => 520],
            ['duration_type' => 'weekly', 'hours_per_day' => 6, 'duration_value' => 1, 'duration_label' => 'أسبوع واحد (6 ساعات)', 'price' => 690],
            ['duration_type' => 'weekly', 'hours_per_day' => 4, 'duration_value' => 2, 'duration_label' => 'أسبوعان (4 ساعات)', 'price' => 1040],
            ['duration_type' => 'weekly', 'hours_per_day' => 6, 'duration_value' => 2, 'duration_label' => 'أسبوعان (6 ساعات)', 'price' => 1265],
            
            // Monthly
            ['duration_type' => 'monthly', 'hours_per_day' => 4, 'duration_value' => 1, 'duration_label' => 'شهر واحد (4 ساعات)', 'price' => 2070],
            ['duration_type' => 'monthly', 'hours_per_day' => 4, 'duration_value' => 3, 'duration_label' => '3 أشهر (4 ساعات)', 'price' => 5865],
            ['duration_type' => 'monthly', 'hours_per_day' => 4, 'duration_value' => 6, 'duration_label' => '6 أشهر (4 ساعات)', 'price' => 11040],
            ['duration_type' => 'monthly', 'hours_per_day' => 6, 'duration_value' => 1, 'duration_label' => 'شهر واحد (6 ساعات)', 'price' => 2300],
            ['duration_type' => 'monthly', 'hours_per_day' => 6, 'duration_value' => 3, 'duration_label' => '3 أشهر (6 ساعات)', 'price' => 6555],
            ['duration_type' => 'monthly', 'hours_per_day' => 6, 'duration_value' => 6, 'duration_label' => '6 أشهر (6 ساعات)', 'price' => 12420],
            ['duration_type' => 'monthly', 'hours_per_day' => 8, 'duration_value' => 1, 'duration_label' => 'شهر واحد (8 ساعات)', 'price' => 2530],
            ['duration_type' => 'monthly', 'hours_per_day' => 8, 'duration_value' => 3, 'duration_label' => '3 أشهر (8 ساعات)', 'price' => 7545],
            ['duration_type' => 'monthly', 'hours_per_day' => 8, 'duration_value' => 6, 'duration_label' => '6 أشهر (8 ساعات)', 'price' => 13800],
            
            // Visit Packages
            ['duration_type' => 'visit_package', 'hours_per_day' => 6, 'duration_value' => 1, 'duration_label' => '10 زيارات (صلاحية شهر)', 'visits_count' => 10, 'visits_period_months' => 1, 'price' => 1380],
            ['duration_type' => 'visit_package', 'hours_per_day' => 6, 'duration_value' => 2, 'duration_label' => '12 زيارة (صلاحية شهرين)', 'visits_count' => 12, 'visits_period_months' => 2, 'price' => 1725],
        ];

        foreach ($infantPlans as $plan) {
            $plans[] = array_merge($plan, [
                'age_stage' => 'infant',
                'stage_label' => 'الرضع',
                'company_id' => $companyId,
                'is_active' => true,
                'sort_order' => $sortOrder++,
            ]);
        }

        // TODDLER (18m-3y) - البراعم
        $toddlerPlans = [
            // Hourly
            ['duration_type' => 'hourly', 'hours_per_day' => null, 'duration_value' => 1, 'duration_label' => 'ساعة واحدة', 'price' => 60],
            ['duration_type' => 'hourly', 'hours_per_day' => null, 'duration_value' => 2, 'duration_label' => 'ساعتان', 'price' => 100],
            ['duration_type' => 'hourly', 'hours_per_day' => null, 'duration_value' => 3, 'duration_label' => '3 ساعات', 'price' => 120],
            
            // Daily
            ['duration_type' => 'daily', 'hours_per_day' => 4, 'duration_value' => 1, 'duration_label' => 'يوم واحد (4 ساعات)', 'price' => 140],
            ['duration_type' => 'daily', 'hours_per_day' => 6, 'duration_value' => 1, 'duration_label' => 'يوم واحد (6 ساعات)', 'price' => 180],
            ['duration_type' => 'daily', 'hours_per_day' => 8, 'duration_value' => 1, 'duration_label' => 'يوم واحد (8 ساعات)', 'price' => 200],
            
            // Weekly
            ['duration_type' => 'weekly', 'hours_per_day' => 4, 'duration_value' => 1, 'duration_label' => 'أسبوع واحد (4 ساعات)', 'price' => 490],
            ['duration_type' => 'weekly', 'hours_per_day' => 6, 'duration_value' => 1, 'duration_label' => 'أسبوع واحد (6 ساعات)', 'price' => 690],
            ['duration_type' => 'weekly', 'hours_per_day' => 4, 'duration_value' => 2, 'duration_label' => 'أسبوعان (4 ساعات)', 'price' => 980],
            ['duration_type' => 'weekly', 'hours_per_day' => 6, 'duration_value' => 2, 'duration_label' => 'أسبوعان (6 ساعات)', 'price' => 1265],
            
            // Monthly
            ['duration_type' => 'monthly', 'hours_per_day' => 4, 'duration_value' => 1, 'duration_label' => 'شهر واحد (4 ساعات)', 'price' => 1955],
            ['duration_type' => 'monthly', 'hours_per_day' => 4, 'duration_value' => 3, 'duration_label' => '3 أشهر (4 ساعات)', 'price' => 5520],
            ['duration_type' => 'monthly', 'hours_per_day' => 4, 'duration_value' => 6, 'duration_label' => '6 أشهر (4 ساعات)', 'price' => 11040],
            ['duration_type' => 'monthly', 'hours_per_day' => 4, 'duration_value' => 9, 'duration_label' => '9 أشهر (4 ساعات)', 'price' => 15525],
            ['duration_type' => 'monthly', 'hours_per_day' => 6, 'duration_value' => 1, 'duration_label' => 'شهر واحد (6 ساعات)', 'price' => 2185],
            ['duration_type' => 'monthly', 'hours_per_day' => 6, 'duration_value' => 3, 'duration_label' => '3 أشهر (6 ساعات)', 'price' => 6210],
            ['duration_type' => 'monthly', 'hours_per_day' => 6, 'duration_value' => 6, 'duration_label' => '6 أشهر (6 ساعات)', 'price' => 11730],
            ['duration_type' => 'monthly', 'hours_per_day' => 6, 'duration_value' => 9, 'duration_label' => '9 أشهر (6 ساعات)', 'price' => 17595],
            ['duration_type' => 'monthly', 'hours_per_day' => 8, 'duration_value' => 1, 'duration_label' => 'شهر واحد (8 ساعات)', 'price' => 2415],
            ['duration_type' => 'monthly', 'hours_per_day' => 8, 'duration_value' => 3, 'duration_label' => '3 أشهر (8 ساعات)', 'price' => 6900],
            ['duration_type' => 'monthly', 'hours_per_day' => 8, 'duration_value' => 6, 'duration_label' => '6 أشهر (8 ساعات)', 'price' => 13110],
            ['duration_type' => 'monthly', 'hours_per_day' => 8, 'duration_value' => 9, 'duration_label' => '9 أشهر (8 ساعات)', 'price' => 19665],

            // Visit Packages
            ['duration_type' => 'visit_package', 'hours_per_day' => 6, 'duration_value' => 1, 'duration_label' => '10 زيارات (صلاحية شهر)', 'visits_count' => 10, 'visits_period_months' => 1, 'price' => 1280],
            ['duration_type' => 'visit_package', 'hours_per_day' => 6, 'duration_value' => 2, 'duration_label' => '12 زيارة (صلاحية شهرين)', 'visits_count' => 12, 'visits_period_months' => 2, 'price' => 1625],
        ];

        foreach ($toddlerPlans as $plan) {
            $plans[] = array_merge($plan, [
                'age_stage' => 'toddler',
                'stage_label' => 'البراعم',
                'company_id' => $companyId,
                'is_active' => true,
                'sort_order' => $sortOrder++,
            ]);
        }

        // KG (3-6y) - رياض الأطفال
        $kgPlans = [
            // Hourly
            ['duration_type' => 'hourly', 'hours_per_day' => null, 'duration_value' => 1, 'duration_label' => 'ساعة واحدة', 'price' => 50],
            ['duration_type' => 'hourly', 'hours_per_day' => null, 'duration_value' => 2, 'duration_label' => 'ساعتان', 'price' => 85],
            ['duration_type' => 'hourly', 'hours_per_day' => null, 'duration_value' => 3, 'duration_label' => '3 ساعات', 'price' => 105],
            
            // Daily
            ['duration_type' => 'daily', 'hours_per_day' => 4, 'duration_value' => 1, 'duration_label' => 'يوم واحد (4 ساعات)', 'price' => 140],
            ['duration_type' => 'daily', 'hours_per_day' => 6, 'duration_value' => 1, 'duration_label' => 'يوم واحد (6 ساعات)', 'price' => 160],
            ['duration_type' => 'daily', 'hours_per_day' => 8, 'duration_value' => 1, 'duration_label' => 'يوم واحد (8 ساعات)', 'price' => 180],
            
            // Weekly
            ['duration_type' => 'weekly', 'hours_per_day' => 4, 'duration_value' => 1, 'duration_label' => 'أسبوع واحد (4 ساعات)', 'price' => 460],
            ['duration_type' => 'weekly', 'hours_per_day' => 6, 'duration_value' => 1, 'duration_label' => 'أسبوع واحد (6 ساعات)', 'price' => 632],
            ['duration_type' => 'weekly', 'hours_per_day' => 4, 'duration_value' => 2, 'duration_label' => 'أسبوعان (4 ساعات)', 'price' => 920],
            ['duration_type' => 'weekly', 'hours_per_day' => 6, 'duration_value' => 2, 'duration_label' => 'أسبوعان (6 ساعات)', 'price' => 1150],
            
            // Monthly
            ['duration_type' => 'monthly', 'hours_per_day' => 4, 'duration_value' => 1, 'duration_label' => 'شهر واحد (4 ساعات)', 'price' => 1840],
            ['duration_type' => 'monthly', 'hours_per_day' => 6, 'duration_value' => 1, 'duration_label' => 'شهر واحد (6 ساعات)', 'price' => 2070],
            ['duration_type' => 'monthly', 'hours_per_day' => 8, 'duration_value' => 1, 'duration_label' => 'شهر واحد (8 ساعات)', 'price' => 2300],

            // Term
            ['duration_type' => 'term', 'hours_per_day' => 4, 'duration_value' => 4.25, 'duration_label' => 'الترم الأول', 'price' => 7330],
            ['duration_type' => 'term', 'hours_per_day' => 4, 'duration_value' => 5.5, 'duration_label' => 'الترم الثاني', 'price' => 8855],
            ['duration_type' => 'term', 'hours_per_day' => 6, 'duration_value' => 4.25, 'duration_label' => 'الترم الأول', 'price' => 8310],
            ['duration_type' => 'term', 'hours_per_day' => 6, 'duration_value' => 5.5, 'duration_label' => 'الترم الثاني', 'price' => 10120],
            ['duration_type' => 'term', 'hours_per_day' => 8, 'duration_value' => 4.25, 'duration_label' => 'الترم الأول', 'price' => 9285],
            ['duration_type' => 'term', 'hours_per_day' => 8, 'duration_value' => 5.5, 'duration_label' => 'الترم الثاني', 'price' => 11385],

            // Yearly
            ['duration_type' => 'yearly', 'hours_per_day' => 4, 'duration_value' => 10, 'duration_label' => 'سنة كاملة', 'price' => 15200],
            ['duration_type' => 'yearly', 'hours_per_day' => 6, 'duration_value' => 10, 'duration_label' => 'سنة كاملة', 'price' => 17500],
            ['duration_type' => 'yearly', 'hours_per_day' => 8, 'duration_value' => 10, 'duration_label' => 'سنة كاملة', 'price' => 19800],

            // Visit Packages
            ['duration_type' => 'visit_package', 'hours_per_day' => 6, 'duration_value' => 1, 'duration_label' => '10 زيارات (صلاحية شهر)', 'visits_count' => 10, 'visits_period_months' => 1, 'price' => 1180],
            ['duration_type' => 'visit_package', 'hours_per_day' => 6, 'duration_value' => 2, 'duration_label' => '12 زيارة (صلاحية شهرين)', 'visits_count' => 12, 'visits_period_months' => 2, 'price' => 1525],
        ];

        foreach ($kgPlans as $plan) {
            $plans[] = array_merge($plan, [
                'age_stage' => 'kg',
                'stage_label' => 'رياض الأطفال',
                'company_id' => $companyId,
                'is_active' => true,
                'sort_order' => $sortOrder++,
            ]);
        }

        foreach ($plans as $planData) {
            PricingPlan::create($planData);
        }
    }
}
