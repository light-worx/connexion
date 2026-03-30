<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Roster;
use Illuminate\Auth\Access\HandlesAuthorization;

class RosterPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Roster');
    }

    public function view(AuthUser $authUser, Roster $roster): bool
    {
        return $authUser->can('View:Roster');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Roster');
    }

    public function update(AuthUser $authUser, Roster $roster): bool
    {
        return $authUser->can('Update:Roster');
    }

    public function delete(AuthUser $authUser, Roster $roster): bool
    {
        return $authUser->can('Delete:Roster');
    }

    public function restore(AuthUser $authUser, Roster $roster): bool
    {
        return $authUser->can('Restore:Roster');
    }

    public function forceDelete(AuthUser $authUser, Roster $roster): bool
    {
        return $authUser->can('ForceDelete:Roster');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Roster');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Roster');
    }

    public function replicate(AuthUser $authUser, Roster $roster): bool
    {
        return $authUser->can('Replicate:Roster');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Roster');
    }

}