<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Individual;
use Illuminate\Auth\Access\HandlesAuthorization;

class IndividualPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Individual');
    }

    public function view(AuthUser $authUser, Individual $individual): bool
    {
        return $authUser->can('View:Individual');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Individual');
    }

    public function update(AuthUser $authUser, Individual $individual): bool
    {
        return $authUser->can('Update:Individual');
    }

    public function delete(AuthUser $authUser, Individual $individual): bool
    {
        return $authUser->can('Delete:Individual');
    }

    public function restore(AuthUser $authUser, Individual $individual): bool
    {
        return $authUser->can('Restore:Individual');
    }

    public function forceDelete(AuthUser $authUser, Individual $individual): bool
    {
        return $authUser->can('ForceDelete:Individual');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Individual');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Individual');
    }

    public function replicate(AuthUser $authUser, Individual $individual): bool
    {
        return $authUser->can('Replicate:Individual');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Individual');
    }

}