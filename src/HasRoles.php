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
        if(!is_array($roles)) {
            $roles = [$roles];
        }
        $count = $this->roles()->whereIn($column, $roles)->count();
        if($requireAll) {
            return $count >= count($roles);
        }
        return $count > 0;
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
        if(!is_array($permissions)) {
            $permissions = [$permissions];
        }
        $count = $this->permissions()->whereIn("access_permissions.$column", $permissions)->count();
        if($requireAll) {
            return $count >= count($permissions);
        }
        return $count > 0;
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
        if(is_array($roles)) {
            foreach($roles as $key=>$role) {
                $this->attachRoles($role, $column);
            }
        }
        elseif($roles instanceof Role) {
            $this->roles()->attach($roles->id);
        }
        elseif(is_integer($roles)) {
            $this->roles()->attach($roles);
        }
        elseif(is_string($roles)) {
            $this->roles()->attach(Role::where($column, $roles)->firstOrFail()->id);
        }
        else {
            throw new \Exception("Key must be array|integer|string|\Panoscape\Access\Role");
        }
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
        if(is_array($roles)) {
            $array = [];
            foreach($roles as $key=>$role) {
                if($role instanceof Role) {
                    array_push($array, $role->id);
                }
                elseif(is_integer($role)) {
                    array_push($array, $role);
                }
                elseif(is_string($role)) {
                    array_push($array, Role::where($column, $role)->firstOrFail()->id);
                }
                else {
                    throw new \Exception("Key must be integer|string|\Panoscape\Access\Role");
                }
            }
            $this->roles()->detach($array);
        }
        elseif($roles instanceof Role) {
            $this->roles()->detach($roles->id);
        }
        elseif(is_integer($roles)) {
            $this->roles()->detach($roles);
        }
        elseif(is_string($roles)) {
            $this->roles()->detach(Role::where($column, $roles)->firstOrFail()->id);
        }
        else {
            throw new \Exception("Key must be array|integer|string|\Panoscape\Access\Role");
        }
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
        $array = [];       
        if(is_array($roles)) {            
            foreach($roles as $key=>$role) {
                if($role instanceof Role) {
                    array_push($array, $role->id);
                }
                elseif(is_integer($role)) {
                    array_push($array, $role);
                }
                elseif(is_string($role)) {
                    array_push($array, Role::where($column, $role)->firstOrFail()->id);
                }
                else {
                    throw new \Exception("Key must be integer|string|\Panoscape\Access\Role");
                }
            }            
        }
        elseif($roles instanceof Role) {
            array_push($array, $roles->id);
        }
        elseif(is_integer($roles)) {
            array_push($array, $roles);
        }
        elseif(is_string($roles)) {
            array_push($array, Role::where($column, $roles)->firstOrFail()->id);
        }
        else {
            throw new \Exception("Key must be array|integer|string|\Panoscape\Access\Role");
        }

        if($detaching) {
            $this->roles()->sync($array);
        }
        else {
            $this->roles()->syncWithoutDetaching($array);
        }
    }
}