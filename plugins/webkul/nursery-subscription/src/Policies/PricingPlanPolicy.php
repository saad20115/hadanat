<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Webkul\Security\Models\User;
use Webkul\NurserySubscription\Models\PricingPlan;

class PricingPlanPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin') || $user->is_default || $user->is_active) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool { return $user->can('view_any_nursery_pricing_plan'); }
    public function view(User $user, PricingPlan $pricingPlan): bool { return $user->can('view_nursery_pricing_plan'); }
    public function create(User $user): bool { return $user->can('create_nursery_pricing_plan'); }
    public function update(User $user, PricingPlan $pricingPlan): bool { return $user->can('update_nursery_pricing_plan'); }
    public function delete(User $user, PricingPlan $pricingPlan): bool { return $user->can('delete_nursery_pricing_plan'); }
    public function deleteAny(User $user): bool { return $user->can('delete_any_nursery_pricing_plan'); }
    public function forceDelete(User $user, PricingPlan $pricingPlan): bool { return $user->can('force_delete_nursery_pricing_plan'); }
    public function forceDeleteAny(User $user): bool { return $user->can('force_delete_any_nursery_pricing_plan'); }
    public function restore(User $user, PricingPlan $pricingPlan): bool { return $user->can('restore_nursery_pricing_plan'); }
    public function restoreAny(User $user): bool { return $user->can('restore_any_nursery_pricing_plan'); }
}
