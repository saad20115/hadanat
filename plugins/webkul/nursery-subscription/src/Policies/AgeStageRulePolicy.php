<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Webkul\NurserySubscription\Models\AgeStageRule;
use Webkul\Security\Models\User;

class AgeStageRulePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin') || $user->is_default || $user->is_active) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_nursery_age_stage') || $user->is_active;
    }

    public function view(User $user, AgeStageRule $rule): bool
    {
        return $user->can('view_nursery_age_stage') || $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->can('create_nursery_age_stage') || $user->is_active;
    }

    public function update(User $user, AgeStageRule $rule): bool
    {
        return $user->can('update_nursery_age_stage') || $user->is_active;
    }

    public function delete(User $user, AgeStageRule $rule): bool
    {
        return $user->can('delete_nursery_age_stage') || $user->is_active;
    }
}
