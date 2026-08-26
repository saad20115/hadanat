<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Webkul\NurserySubscription\Models\AcademicYear;
use Webkul\NurserySubscription\Traits\HandlesNurseryAuthorization;
use Webkul\Security\Models\User;

class AcademicYearPolicy
{
    use HandlesAuthorization, HandlesNurseryAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_nursery_subscription_academic::year') || $user->can('app_nursery');
    }

    public function view(User $user, $model): bool
    {
        return $user->can('view_nursery_subscription_academic::year') || $user->can('app_nursery');
    }

    public function create(User $user): bool
    {
        return $user->can('create_nursery_subscription_academic::year') || $user->can('app_nursery');
    }

    public function update(User $user, $model): bool
    {
        return $user->can('update_nursery_subscription_academic::year') || $user->can('app_nursery');
    }

    public function delete(User $user, AcademicYear $academicYear): bool
    {
        return $user->can('delete_nursery_subscription_academic::year');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_nursery_subscription_academic::year');
    }
}
