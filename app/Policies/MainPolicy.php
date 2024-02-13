<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MainPolicy
{
    use HandlesAuthorization;

    private $tableName; 

    public function checkPermission($user, $permission, $section)
    {
        return $user->hasPermissionTo($permission) || $user->hasPermissionTo("{$permission}_{$section->getModel()->getTable()}");
    }

    // public function before(User $user, $ability, $section)
    // {
    //     return $this->checkPermission($user, 'view', $section);
    // }

    public function display(User $user, $section)
    {
        return $this->checkPermission($user, 'view', $section);
    }

    public function create(User $user, $section)
    {
        return $this->checkPermission($user, 'create', $section);
    }

    public function edit(User $user, $section)
    {
        return $this->checkPermission($user, 'edit', $section);
    }

    public function delete(User $user, $section)
    {
        return $this->checkPermission($user, 'remove', $section);
    }
}
