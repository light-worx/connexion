<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WeekdayService;
use Illuminate\Auth\Access\HandlesAuthorization;

class WeekdayServicePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WeekdayService');
    }

    public function view(AuthUser $authUser, WeekdayService $weekdayService): bool
    {
        return $authUser->can('View:WeekdayService');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WeekdayService');
    }

    public function update(AuthUser $authUser, WeekdayService $weekdayService): bool
    {
        return $authUser->can('Update:WeekdayService');
    }

    public function delete(AuthUser $authUser, WeekdayService $weekdayService): bool
    {
        return $authUser->can('Delete:WeekdayService');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:WeekdayService');
    }

    public function restore(AuthUser $authUser, WeekdayService $weekdayService): bool
    {
        return $authUser->can('Restore:WeekdayService');
    }

    public function forceDelete(AuthUser $authUser, WeekdayService $weekdayService): bool
    {
        return $authUser->can('ForceDelete:WeekdayService');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WeekdayService');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WeekdayService');
    }

    public function replicate(AuthUser $authUser, WeekdayService $weekdayService): bool
    {
        return $authUser->can('Replicate:WeekdayService');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WeekdayService');
    }

}