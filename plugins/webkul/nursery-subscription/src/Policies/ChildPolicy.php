<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Webkul\Security\Models\User;
use Webkul\NurserySubscription\Models\Child;

class ChildPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin') || $user->is_default || $user->is_active) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool { return $user->can('view_any_nursery_child'); }
    public function view(User $user, Child $child): bool { return $user->can('view_nursery_child'); }
    public function create(User $user): bool { return $user->can('create_nursery_child'); }
    public function update(User $user, Child $child): bool { return $user->can('update_nursery_child'); }
    public function delete(User $user, Child $child): bool { return $user->can('delete_nursery_child'); }
    public function deleteAny(User $user): bool { return $user->can('delete_any_nursery_child'); }
    public function forceDelete(User $user, Child $child): bool { return $user->can('force_delete_nursery_child'); }
    public function forceDeleteAny(User $user): bool { return $user->can('force_delete_any_nursery_child'); }
    public function restore(User $user, Child $child): bool { return $user->can('restore_nursery_child'); }
    public function restoreAny(User $user): bool { return $user->can('restore_any_nursery_child'); }
}
