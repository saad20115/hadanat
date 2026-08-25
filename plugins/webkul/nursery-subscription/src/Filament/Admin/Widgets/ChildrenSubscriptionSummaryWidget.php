<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Webkul\NurserySubscription\Models\AgeStageRule;
use Webkul\NurserySubscription\Models\Child;
use Webkul\NurserySubscription\Models\Subscription;
use Webkul\Support\Services\CompanyContext;

class ChildrenSubscriptionSummaryWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'nursery-subscription::filament.admin.widgets.children-subscription-summary';

    public function getViewData(): array
    {
        $companyId = app(CompanyContext::class)->currentId() ?? Auth::user()?->default_company_id ?? 1;
        $today = Carbon::today();

        // Dynamically fetch age stage settings configured for this company
        $ageRules = AgeStageRule::where('company_id', $companyId)
            ->active()
            ->orderBy('sort_order')
            ->get();

        $rows = [];
        $totalChildren = 0;
        $totalActive = 0;
        $totalExpiringSoon = 0;
        $totalExpired = 0;
        $totalAmount = 0.00;
        $totalRemaining = 0.00;

        $processedChildIds = [];

        foreach ($ageRules as $rule) {
            // Find children matching this age bracket rule
            $children = Child::where('company_id', $companyId)->get()->filter(function ($child) use ($rule, $today) {
                if (! $child->birth_date) {
                    return false;
                }
                $months = (int) Carbon::parse($child->birth_date)->diffInMonths($today);

                return $rule->matchesMonths($months);
            });

            $childrenCount = $children->count();
            $childIds = $children->pluck('id')->toArray();
            $processedChildIds = array_merge($processedChildIds, $childIds);

            // Subscriptions for these children
            $subscriptions = Subscription::where('company_id', $companyId)
                ->whereIn('child_id', $childIds)
                ->get();

            $activeCount = $subscriptions->where('status.value', 'active')->count();
            $expiringSoonCount = $subscriptions->where('status.value', 'expiring_soon')->count();
            $expiredCount = $subscriptions->where('status.value', 'expired')->count();
            $subAmount = (float) $subscriptions->sum('net_amount');
            $subRemaining = (float) $subscriptions->sum('remaining_amount');

            $rows[] = [
                'rule_id'             => $rule->id,
                'section'             => $rule->name,
                'age_bracket'         => $rule->age_range_label,
                'description'         => $rule->description,
                'children_count'      => $childrenCount,
                'active_count'        => $activeCount,
                'expiring_soon_count' => $expiringSoonCount,
                'expired_count'       => $expiredCount,
                'total_amount'        => $subAmount,
                'remaining_amount'    => $subRemaining,
                'is_total'            => false,
            ];

            $totalChildren += $childrenCount;
            $totalActive += $activeCount;
            $totalExpiringSoon += $expiringSoonCount;
            $totalExpired += $expiredCount;
            $totalAmount += $subAmount;
            $totalRemaining += $subRemaining;
        }

        // Check for any children not covered by configured rules
        $unclassifiedChildren = Child::where('company_id', $companyId)
            ->whereNotIn('id', $processedChildIds)
            ->get();

        if ($unclassifiedChildren->isNotEmpty()) {
            $unclassifiedCount = $unclassifiedChildren->count();
            $uChildIds = $unclassifiedChildren->pluck('id')->toArray();
            $uSubs = Subscription::where('company_id', $companyId)->whereIn('child_id', $uChildIds)->get();

            $uActive = $uSubs->where('status.value', 'active')->count();
            $uExpSoon = $uSubs->where('status.value', 'expiring_soon')->count();
            $uExp = $uSubs->where('status.value', 'expired')->count();
            $uAmount = (float) $uSubs->sum('net_amount');
            $uRemaining = (float) $uSubs->sum('remaining_amount');

            $rows[] = [
                'rule_id'             => null,
                'section'             => 'أخرى (غير مصنف)',
                'age_bracket'         => 'خارج النطاقات المعرفة',
                'description'         => 'أعمار غير مطابقة لإعدادات الفئات الحالية',
                'children_count'      => $unclassifiedCount,
                'active_count'        => $uActive,
                'expiring_soon_count' => $uExpSoon,
                'expired_count'       => $uExp,
                'total_amount'        => $uAmount,
                'remaining_amount'    => $uRemaining,
                'is_total'            => false,
            ];

            $totalChildren += $unclassifiedCount;
            $totalActive += $uActive;
            $totalExpiringSoon += $uExpSoon;
            $totalExpired += $uExp;
            $totalAmount += $uAmount;
            $totalRemaining += $uRemaining;
        }

        // Add Totals row
        $rows[] = [
            'rule_id'             => null,
            'section'             => 'الإجمالي',
            'age_bracket'         => 'كافة الأقسام والفئات',
            'description'         => 'إجمالي الحضانة بالكامل',
            'children_count'      => $totalChildren,
            'active_count'        => $totalActive,
            'expiring_soon_count' => $totalExpiringSoon,
            'expired_count'       => $totalExpired,
            'total_amount'        => $totalAmount,
            'remaining_amount'    => $totalRemaining,
            'is_total'            => true,
        ];

        return [
            'rows'       => $rows,
            'rulesCount' => $ageRules->count(),
        ];
    }
}
