<?php

namespace App\Traits;

use App\Models\Permission;

trait HasRolesAndPermissions
{
	public function roles()
	{
		return $this->belongsToMany('App\Models\Role', 'users_roles');
	}

	public function permissions()
	{
		return $this->belongsToMany('App\Models\Permission', 'users_permissions');
	}

	public function hasRole(...$roles)
	{
		foreach ($roles as $role) {
			if ($this->roles->contains('slug', $role)) {
				return true;
			}
		}
		return false;
	}

	public function hasPermission($permission)
	{
		return (bool) $this->permissions->contains('slug', $permission);
	}

	public function hasPermissionTroughRole($permission)
	{
		if ($permission = Permission::where('slug', $permission)->first()) {
			foreach ($permission->roles as $role) {
				if ($this->roles->contains($role)) {
					return true;
				}
			}
			return false;
		}
		return false;
	}

	public function hasPermissionTo($permission)
	{
		return $this->hasPermission($permission) || $this->hasPermissionTroughRole($permission);
	}
}
