<?php

namespace Panoscape\Access;

trait HasRoles
{
    /**
     * Boot the trait
     *
     * @return void
     */
    public static function bootHasRoles()
    {
        Role::$userModel = static::class;
        static::deleting(function($user) {
            if(!in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses($user)) || $user->isForceDeleting()) {
                $user->roles()->detach([]);
            }
        });
    }

    /**
     * The roles that belong to the user
     *
     * @return mixed
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'access_role_user', 'user_id', 'role_id');
    }

    /**
     * The permissions that belong to the user
     *
     * @return mixed
     */
    public function permissions()
    {
        return Permission::join('access_permission_role', 'access_permissions.id', '=', 'access_permission_role.permission_id')
                            ->join('access_roles', 'access_roles.id', '=', 'access_permission_role.role_id')
                            ->join('access_role_user', 'access_roles.id', '=', 'access_role_user.role_id')
                            ->join($this->getTable(), "{$this->getTable()}.{$this->getKeyName()}", '=', 'access_role_user.user_id')
                            ->where("{$this->getTable()}.{$this->getKeyName()}", '=', $this->getKey())
                            ->select('access_permissions.*')
                            ->distinct();
    }

    /**
     * Check if this model has the provided roles
     *
     * @param array|string $roles
     * @param bool $requireAll
     * @param string $column
     *
     * @return bool
     */
    public function hasRoles($roles, $requireAll = true, $column = 'name')
    {
        return Facades\Access::hasRoles($roles, $requireAll, $column, $this);
    }

    /**
     * Check if this model has the provided permissions
     *
     * @param array|string $permissions
     * @param bool $requireAll
     * @param string $column
     *
     * @return bool
     */
    public function hasPermissions($permissions, $requireAll = true, $column = 'name')
    {
        return Facades\Access::hasPermissions($permissions, $requireAll, $column, $this);
    }

    /**
     * Attach roles to the model
     *
     * @param array|integer|string|\Panoscape\Access\Role $roles
     * @param string $column
     *
     * @return void
     */
    public function attachRoles($roles, $column = 'name')
    {        
        Facades\Access::attachRoles($roles, $column, $this);
    }

    /**
     * Detach roles from the model
     *
     * @param array|integer|string|\Panoscape\Access\Role $roles
     * @param string $column
     *
     * @return void
     */
    public function detachRoles($roles, $column = 'name')
    {        
        Facades\Access::detachRoles($roles, $column, $this);
    }

    /**
     * Sync roles with the model
     *
     * @param array|integer|string|\Panoscape\Access\Role $roles
     * @param bool $detaching
     * @param string $column
     *
     * @return void
     */
    public function syncRoles($roles, $detaching = true, $column = 'name')
    { 
        Facades\Access::syncRoles($roles, $detaching, $column, $this);
    }
}