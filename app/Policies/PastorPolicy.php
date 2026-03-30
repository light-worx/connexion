<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Pastor;
use Illuminate\Auth\Access\HandlesAuthorization;

class PastorPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Pastor');
    }

    public function view(AuthUser $authUser, Pastor $pastor): bool
    {
        return $authUser->can('View:Pastor');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Pastor');
    }

    public function update(AuthUser $authUser, Pastor $pastor): bool
    {
        return $authUser->can('Update:Pastor');
    }

    public function delete(AuthUser $authUser, Pastor $pastor): bool
    {
        return $authUser->can('Delete:Pastor');
    }

    public function restore(AuthUser $authUser, Pastor $pastor): bool
    {
        return $authUser->can('Restore:Pastor');
    }

    public function forceDelete(AuthUser $authUser, Pastor $pastor): bool
    {
        return $authUser->can('ForceDelete:Pastor');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Pastor');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Pastor');
    }

    public function replicate(AuthUser $authUser, Pastor $pastor): bool
    {
        return $authUser->can('Replicate:Pastor');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Pastor');
    }

}