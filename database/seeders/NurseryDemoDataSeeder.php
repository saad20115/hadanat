<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Webkul\NurserySubscription\Database\Seeders\PricingPlanSeeder;
use Webkul\NurserySubscription\Enums\PaymentMethod;
use Webkul\NurserySubscription\Enums\SubscriptionStatus;
use Webkul\NurserySubscription\Models\AcademicYear;
use Webkul\NurserySubscription\Models\AgeStageRule;
use Webkul\NurserySubscription\Models\Child;
use Webkul\NurserySubscription\Models\Payment;
use Webkul\NurserySubscription\Models\PricingPlan;
use Webkul\NurserySubscription\Models\Subscription;

class NurseryDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1;
        $creatorId = 1;

        // 1. Seed Pricing Plans if none exist
        if (PricingPlan::where('company_id', $companyId)->count() === 0) {
            $this->call(PricingPlanSeeder::class);
        }

        // 2. Academic Year
        $academicYear = AcademicYear::firstOrCreate(
            ['name' => 'العام الدراسي 1447هـ (2025-2026م)', 'company_id' => $companyId],
            [
                'start_date' => '2025-08-30',
                'end_date'   => '2026-07-01',
                'is_current' => true,
                'notes'      => 'العام الدراسي المعتمد لروضة وبراعم الأطفال',
                'company_id' => $companyId,
            ]
        );

        \Webkul\NurserySubscription\Models\AcademicTerm::firstOrCreate(
            ['name' => 'الفصل الدراسي الأول', 'academic_year_id' => $academicYear->id],
            [
                'start_date' => '2025-08-30',
                'end_date'   => '2026-01-07',
                'is_current' => false,
                'company_id' => $companyId,
            ]
        );

        \Webkul\NurserySubscription\Models\AcademicTerm::firstOrCreate(
            ['name' => 'الفصل الدراسي الثاني', 'academic_year_id' => $academicYear->id],
            [
                'start_date' => '2026-01-17',
                'end_date'   => '2026-07-01',
                'is_current' => true,
                'company_id' => $companyId,
            ]
        );

        // 3. Age Stage Rules
        $rules = [
            [
                'code'           => 'infant',
                'name'           => 'فئة الرضع والحضانة المبكرة',
                'min_age_months' => 6,
                'max_age_months' => 18,
                'sort_order'     => 1,
            ],
            [
                'code'           => 'toddler',
                'name'           => 'فئة الحضانة وبراعم الصغار',
                'min_age_months' => 18,
                'max_age_months' => 36,
                'sort_order'     => 2,
            ],
            [
                'code'           => 'kg1',
                'name'           => 'مرحلة الروضة الأولى (KG1)',
                'min_age_months' => 36,
                'max_age_months' => 48,
                'sort_order'     => 3,
            ],
            [
                'code'           => 'kg2',
                'name'           => 'مرحلة الروضة الثانية (KG2)',
                'min_age_months' => 48,
                'max_age_months' => 60,
                'sort_order'     => 4,
            ],
            [
                'code'           => 'kg3',
                'name'           => 'مرحلة التمهيدي (KG3)',
                'min_age_months' => 60,
                'max_age_months' => 72,
                'sort_order'     => 5,
            ],
        ];

        foreach ($rules as $r) {
            AgeStageRule::firstOrCreate(
                ['code' => $r['code'], 'company_id' => $companyId],
                array_merge($r, [
                    'company_id' => $companyId,
                    'is_active'  => true,
                    'creator_id' => $creatorId,
                ])
            );
        }

        // 4. Children
        $childrenData = [
            [
                'full_name'         => 'ريان محمد القحطاني',
                'birth_date'        => Carbon::now()->subYears(3)->subMonths(4)->format('Y-m-d'),
                'gender'            => 'male',
                'guardian_name'     => 'محمد ناصر القحطاني',
                'guardian_phone'    => '0501234567',
                'emergency_contact' => 'أم ريان (فاطمة الدوسري)',
                'emergency_phone'   => '0509876543',
                'has_siblings'      => true,
                'medical_notes'     => 'لا توجد أي حساسية أو أمراض مزمنة ولله الحمد.',
                'notes'             => 'طفل متفاعل ومحب للأنشطة الحركية واللغة الإنجليزية.',
            ],
            [
                'full_name'         => 'فهد محمد القحطاني',
                'birth_date'        => Carbon::now()->subYears(4)->subMonths(8)->format('Y-m-d'),
                'gender'            => 'male',
                'guardian_name'     => 'محمد ناصر القحطاني',
                'guardian_phone'    => '0501234567',
                'emergency_contact' => 'أم فهد (فاطمة الدوسري)',
                'emergency_phone'   => '0509876543',
                'has_siblings'      => true,
                'medical_notes'     => 'حساسية طفيفة من الفراولة.',
                'notes'             => 'أخ الطفل ريان - يستحق خصم الإخوة 5%.',
            ],
            [
                'full_name'         => 'ليان أحمد الشمري',
                'birth_date'        => Carbon::now()->subYears(4)->subMonths(2)->format('Y-m-d'),
                'gender'            => 'female',
                'guardian_name'     => 'أحمد عبد الله الشمري',
                'guardian_phone'    => '0559876543',
                'emergency_contact' => 'الخال: فهد الشمري',
                'emergency_phone'   => '0551122334',
                'has_siblings'      => false,
                'medical_notes'     => 'سليمة ومعافاة ولله الحمد.',
                'notes'             => 'موهوبة في الرسم والألوان.',
            ],
            [
                'full_name'         => 'عمر خالد العتيبي',
                'birth_date'        => Carbon::now()->subYears(1)->subMonths(6)->format('Y-m-d'),
                'gender'            => 'male',
                'guardian_name'     => 'خالد سلطان العتيبي',
                'guardian_phone'    => '0561122334',
                'emergency_contact' => 'أم عمر (منى الحربي)',
                'emergency_phone'   => '0569988776',
                'has_siblings'      => false,
                'medical_notes'     => 'لا توجد ملاحظات طبية.',
                'notes'             => 'يحتاج متابعة وقت الوجبات الغذائية.',
            ],
            [
                'full_name'         => 'سارة عبد العزيز الدوسري',
                'birth_date'        => Carbon::now()->subYears(5)->subMonths(3)->format('Y-m-d'),
                'gender'            => 'female',
                'guardian_name'     => 'عبد العزيز إبراهيم الدوسري',
                'guardian_phone'    => '0543322110',
                'emergency_contact' => 'الجد: إبراهيم الدوسري',
                'emergency_phone'   => '0548877665',
                'has_siblings'      => false,
                'medical_notes'     => 'ترتدي نظارات طبية أثناء القراءة.',
                'notes'             => 'مرحلة التمهيدي - متفوقة في الأرقام والحروف.',
            ],
            [
                'full_name'         => 'نورة فهد السبيعي',
                'birth_date'        => Carbon::now()->subYears(2)->subMonths(1)->format('Y-m-d'),
                'gender'            => 'female',
                'guardian_name'     => 'فهد عبد الله السبيعي',
                'guardian_phone'    => '0509988776',
                'emergency_contact' => 'أم نورة (هيا السبيعي)',
                'emergency_phone'   => '0501144778',
                'has_siblings'      => false,
                'medical_notes'     => 'لا توجد ملاحظات.',
                'notes'             => 'تسجيل جديد في قسم الحضانة.',
            ],
        ];

        $createdChildren = [];
        foreach ($childrenData as $c) {
            $child = Child::firstOrCreate(
                ['full_name' => $c['full_name'], 'company_id' => $companyId],
                array_merge($c, [
                    'company_id' => $companyId,
                    'creator_id' => $creatorId,
                ])
            );
            $createdChildren[] = $child;
        }

        // 5. Pick representative pricing plans
        $monthlyPlan = PricingPlan::where('company_id', $companyId)->where('duration_type', 'monthly')->first() ?? PricingPlan::first();
        $termPlan = PricingPlan::where('company_id', $companyId)->where('duration_type', 'term')->first() ?? $monthlyPlan;

        if (! $monthlyPlan) {
            return;
        }

        // 6. Seed Realistic Subscriptions & Payments

        // Sub 1: ريان القحطاني - اشتراك ساري كامل السداد
        $sub1Price = (float) $termPlan->price;
        $sub1Net = $sub1Price + 75.00;
        $sub1 = Subscription::firstOrCreate(
            ['child_id' => $createdChildren[0]->id, 'pricing_plan_id' => $termPlan->id],
            [
                'start_date'           => Carbon::now()->startOfMonth()->format('Y-m-d'),
                'end_date'             => Carbon::now()->addMonths(4)->endOfMonth()->format('Y-m-d'),
                'base_price'           => $sub1Price,
                'sibling_discount_pct' => 0.00,
                'discount_amount'      => 0.00,
                'includes_tshirt'      => true,
                'tshirt_amount'        => 75.00,
                'net_amount'           => $sub1Net,
                'paid_amount'          => $sub1Net,
                'remaining_amount'     => 0.00,
                'status'               => SubscriptionStatus::ACTIVE,
                'notes'                => 'اشتراك فصل دراسي أول - مسدد بالكامل عبر مدى مع استلام التيشيرت.',
                'company_id'           => $companyId,
                'creator_id'           => $creatorId,
            ]
        );

        Payment::firstOrCreate(
            ['subscription_id' => $sub1->id, 'reference_number' => 'MADA-982341'],
            [
                'amount'         => $sub1Net,
                'payment_method' => PaymentMethod::CARD,
                'payment_date'   => Carbon::now()->startOfMonth()->format('Y-m-d'),
                'notes'          => 'سداد كامل قيمة الاشتراك والزي الرسمي عبر جهاز نقاط البيع مدى.',
                'company_id'     => $companyId,
                'creator_id'     => $creatorId,
            ]
        );

        // Sub 2: فهد القحطاني - اشتراك أخ (خصم إخوة 5%)
        $sub2Base = (float) $termPlan->price;
        $sub2Discount = round($sub2Base * 0.05, 2);
        $sub2Net = $sub2Base - $sub2Discount + 75.00;
        $sub2 = Subscription::firstOrCreate(
            ['child_id' => $createdChildren[1]->id, 'pricing_plan_id' => $termPlan->id],
            [
                'start_date'           => Carbon::now()->startOfMonth()->format('Y-m-d'),
                'end_date'             => Carbon::now()->addMonths(4)->endOfMonth()->format('Y-m-d'),
                'base_price'           => $sub2Base,
                'sibling_discount_pct' => 5.00,
                'discount_amount'      => $sub2Discount,
                'includes_tshirt'      => true,
                'tshirt_amount'        => 75.00,
                'net_amount'           => $sub2Net,
                'paid_amount'          => $sub2Net,
                'remaining_amount'     => 0.00,
                'status'               => SubscriptionStatus::ACTIVE,
                'notes'                => 'مطبق عليه خصم الإخوة 5% (أخ الطفل ريان القحطاني).',
                'company_id'           => $companyId,
                'creator_id'           => $creatorId,
            ]
        );

        Payment::firstOrCreate(
            ['subscription_id' => $sub2->id, 'reference_number' => 'TRF-554210'],
            [
                'amount'         => $sub2Net,
                'payment_method' => PaymentMethod::TRANSFER,
                'payment_date'   => Carbon::now()->startOfMonth()->format('Y-m-d'),
                'notes'          => 'تحويل بنكي من حساب ولي الأمر (مصرف الراجحي).',
                'company_id'     => $companyId,
                'creator_id'     => $creatorId,
            ]
        );

        // Sub 3: ليان الشمري - اشتراك قرب ينتهي (ينتهي بعد 4 أيام)
        $sub3Price = (float) $monthlyPlan->price;
        $sub3 = Subscription::firstOrCreate(
            ['child_id' => $createdChildren[2]->id, 'pricing_plan_id' => $monthlyPlan->id],
            [
                'start_date'           => Carbon::now()->subDays(26)->format('Y-m-d'),
                'end_date'             => Carbon::now()->addDays(4)->format('Y-m-d'),
                'base_price'           => $sub3Price,
                'sibling_discount_pct' => 0.00,
                'discount_amount'      => 0.00,
                'includes_tshirt'      => false,
                'tshirt_amount'        => 0.00,
                'net_amount'           => $sub3Price,
                'paid_amount'          => $sub3Price,
                'remaining_amount'     => 0.00,
                'status'               => SubscriptionStatus::EXPIRING_SOON,
                'notes'                => 'تنبيه: متبقي 4 أيام على نهاية الاشتراك - تم التواصل مع ولي الأمر للتجديد.',
                'company_id'           => $companyId,
                'creator_id'           => $creatorId,
            ]
        );

        Payment::firstOrCreate(
            ['subscription_id' => $sub3->id, 'reference_number' => 'CASH-10029'],
            [
                'amount'         => $sub3Price,
                'payment_method' => PaymentMethod::CASH,
                'payment_date'   => Carbon::now()->subDays(26)->format('Y-m-d'),
                'notes'          => 'سداد نقدي في مكتب الاستقبال.',
                'company_id'     => $companyId,
                'creator_id'     => $creatorId,
            ]
        );

        // Sub 4: عمر العتيبي - اشتراك سداد جزئي
        $sub4Price = (float) $monthlyPlan->price;
        $sub4Paid = round($sub4Price / 2, 2);
        $sub4Remaining = $sub4Price - $sub4Paid;
        $sub4 = Subscription::firstOrCreate(
            ['child_id' => $createdChildren[3]->id, 'pricing_plan_id' => $monthlyPlan->id],
            [
                'start_date'           => Carbon::now()->subDays(10)->format('Y-m-d'),
                'end_date'             => Carbon::now()->addDays(20)->format('Y-m-d'),
                'base_price'           => $sub4Price,
                'sibling_discount_pct' => 0.00,
                'discount_amount'      => 0.00,
                'includes_tshirt'      => false,
                'tshirt_amount'        => 0.00,
                'net_amount'           => $sub4Price,
                'paid_amount'          => $sub4Paid,
                'remaining_amount'     => $sub4Remaining,
                'status'               => SubscriptionStatus::ACTIVE,
                'notes'                => 'دفعة أولى تم سدادها، والمتبقي متفق عليه نهاية الشهر.',
                'company_id'           => $companyId,
                'creator_id'           => $creatorId,
            ]
        );

        Payment::firstOrCreate(
            ['subscription_id' => $sub4->id, 'reference_number' => 'MADA-771829'],
            [
                'amount'         => $sub4Paid,
                'payment_method' => PaymentMethod::CARD,
                'payment_date'   => Carbon::now()->subDays(10)->format('Y-m-d'),
                'notes'          => 'دفعة مقدمة 50% عبر بطاقة مدى.',
                'company_id'     => $companyId,
                'creator_id'     => $creatorId,
            ]
        );

        // Sub 5: سارة الدوسري - اشتراك منتهي
        $sub5Price = (float) $monthlyPlan->price;
        $sub5 = Subscription::firstOrCreate(
            ['child_id' => $createdChildren[4]->id, 'pricing_plan_id' => $monthlyPlan->id],
            [
                'start_date'           => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'end_date'             => Carbon::now()->subMonth()->format('Y-m-d'),
                'base_price'           => $sub5Price,
                'sibling_discount_pct' => 0.00,
                'discount_amount'      => 0.00,
                'includes_tshirt'      => false,
                'tshirt_amount'        => 0.00,
                'net_amount'           => $sub5Price,
                'paid_amount'          => $sub5Price,
                'remaining_amount'     => 0.00,
                'status'               => SubscriptionStatus::EXPIRED,
                'notes'                => 'اشتراك الشهر الماضي المنتهي - في انتظار تأكيد التجديد.',
                'company_id'           => $companyId,
                'creator_id'           => $creatorId,
            ]
        );

        Payment::firstOrCreate(
            ['subscription_id' => $sub5->id, 'reference_number' => 'TRF-330192'],
            [
                'amount'         => $sub5Price,
                'payment_method' => PaymentMethod::TRANSFER,
                'payment_date'   => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'notes'          => 'سداد الاشتراك المنتهي بحوالة بنكية.',
                'company_id'     => $companyId,
                'creator_id'     => $creatorId,
            ]
        );

        // Sub 6: نورة السبيعي - اشتراك نشط جديد
        $sub6Price = (float) $monthlyPlan->price;
        $sub6 = Subscription::firstOrCreate(
            ['child_id' => $createdChildren[5]->id, 'pricing_plan_id' => $monthlyPlan->id],
            [
                'start_date'           => Carbon::now()->format('Y-m-d'),
                'end_date'             => Carbon::now()->addMonth()->format('Y-m-d'),
                'base_price'           => $sub6Price,
                'sibling_discount_pct' => 0.00,
                'discount_amount'      => 0.00,
                'includes_tshirt'      => false,
                'tshirt_amount'        => 0.00,
                'net_amount'           => $sub6Price,
                'paid_amount'          => $sub6Price,
                'remaining_amount'     => 0.00,
                'status'               => SubscriptionStatus::ACTIVE,
                'notes'                => 'اشتراك شهري جديد بقسم الحضانة.',
                'company_id'           => $companyId,
                'creator_id'           => $creatorId,
            ]
        );

        Payment::firstOrCreate(
            ['subscription_id' => $sub6->id, 'reference_number' => 'MADA-449102'],
            [
                'amount'         => $sub6Price,
                'payment_method' => PaymentMethod::CARD,
                'payment_date'   => Carbon::now()->format('Y-m-d'),
                'notes'          => 'سداد كامل عبر مدى.',
                'company_id'     => $companyId,
                'creator_id'     => $creatorId,
            ]
        );
    }
}
