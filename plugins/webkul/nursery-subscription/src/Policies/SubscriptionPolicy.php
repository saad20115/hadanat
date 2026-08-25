<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Webkul\NurserySubscription\Models\Subscription;
use Webkul\Security\Models\User;

class SubscriptionPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('Super_admin') || $user->is_default) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_nursery_subscription_subscription') || $user->can('app_nursery');
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $user->can('view_nursery_subscription_subscription') || $user->can('app_nursery');
    }

    public function create(User $user): bool
    {
        return $user->can('create_nursery_subscription_subscription') || $user->can('app_nursery');
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $user->can('update_nursery_subscription_subscription') || $user->can('app_nursery');
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return $user->can('delete_nursery_subscription_subscription');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_nursery_subscription_subscription');
    }

    public function forceDelete(User $user, Subscription $subscription): bool
    {
        return $user->can('force_delete_nursery_subscription_subscription');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_nursery_subscription_subscription');
    }

    public function restore(User $user, Subscription $subscription): bool
    {
        return $user->can('restore_nursery_subscription_subscription') || $user->can('app_nursery');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_nursery_subscription_subscription') || $user->can('app_nursery');
    }
}
