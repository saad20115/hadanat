<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Webkul\NurserySubscription\Models\AgeStageRule;
use Webkul\NurserySubscription\Traits\HandlesNurseryAuthorization;
use Webkul\Security\Models\User;

class AgeStageRulePolicy
{
    use HandlesAuthorization, HandlesNurseryAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_nursery_subscription_age::stage::rule') || $user->can('view_any_nursery_age_stage') || $user->can('app_nursery');
    }

    public function view(User $user, $model): bool
    {
        return $user->can('view_nursery_subscription_age::stage::rule') || $user->can('view_nursery_age_stage') || $user->can('app_nursery');
    }

    public function create(User $user): bool
    {
        return $user->can('create_nursery_subscription_age::stage::rule') || $user->can('create_nursery_age_stage') || $user->can('app_nursery');
    }

    public function update(User $user, $model): bool
    {
        return $user->can('update_nursery_subscription_age::stage::rule') || $user->can('update_nursery_age_stage') || $user->can('app_nursery');
    }

    public function delete(User $user, AgeStageRule $ageStageRule): bool
    {
        return $user->can('delete_nursery_subscription_age::stage::rule') || $user->can('delete_nursery_age_stage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_nursery_subscription_age::stage::rule') || $user->can('delete_any_nursery_age_stage');
    }

    public function forceDelete(User $user, AgeStageRule $ageStageRule): bool
    {
        return $user->can('force_delete_nursery_subscription_age::stage::rule') || $user->can('force_delete_nursery_age_stage');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_nursery_subscription_age::stage::rule') || $user->can('force_delete_any_nursery_age_stage');
    }

    public function restore(User $user, AgeStageRule $ageStageRule): bool
    {
        return $user->can('restore_nursery_subscription_age::stage::rule') || $user->can('restore_nursery_age_stage');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_nursery_subscription_age::stage::rule') || $user->can('restore_any_nursery_age_stage');
    }
}
