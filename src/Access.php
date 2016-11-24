<?php

namespace Panoscape\Access;

class Access
{
    /**
     * Check if the user has the given roles
     *
     * @param array|string $roles
     * @param bool $requireAll
     * @param string $column
     * @param mixed $user
     *
     * @return bool
     */
    public function hasRoles($roles, $requireAll = true, $column = 'name', $user = null)
    {
        if(!$this->checkUser($user)){
            return false;
        }

        if(is_string($roles)) {
            $roles = preg_split('/[\|\s]+/', $roles, 0, PREG_SPLIT_NO_EMPTY);
        }
        $query = $user->roles()->whereIn($column, $roles);
        if(is_string($requireAll)) {
            $requireAll = $requireAll == 'true';
        }
        if($requireAll) {
            return $query->count() >= count($roles);
        }
        return $query->exists();
    }

    /**
     * Check if the user has the given permissions
     *
     * @param array|string $permissions
     * @param bool $requireAll
     * @param string $column
     * @param mixed $user
     *
     * @return bool
     */
    public function hasPermissions($permissions, $requireAll = true, $column = 'name', $user = null)
    {
        if(!($user instanceof Role) && !$this->checkUser($user)){
            return false;
        }

        if(is_string($permissions)) {
            $permissions = preg_split('/[\|\s]+/', $permissions, 0, PREG_SPLIT_NO_EMPTY);
        }
        if(!($user instanceof Role)) {
            $column = "access_permissions.$column";
        }
        $query = $user->permissions()->whereIn($column, $permissions);
        if(is_string($requireAll)) {
            $requireAll = $requireAll == 'true';
        }
        if($requireAll) {
            return $query->count() >= count($permissions);
        }
        return $query->exists();
    }

    /**
     * Attach roles to the model
     *
     * @param array|integer|string|\Panoscape\Access\Role $roles
     * @param string $column
     * @param mixed $user
     *
     * @return void
     */
    public function attachRoles($roles, $column = 'name', $user = null)
    {
        if(!$this->checkUser($user)){
            throw new Exception("Invalid user instance");
        }

        if(is_array($roles)) {
            foreach($roles as $key=>$role) {
                $this->attachRoles($role, $column, $user);
            }
        }
        elseif($roles instanceof Role) {
            $user->roles()->attach($roles->id);
        }
        elseif(is_integer($roles)) {
            $user->roles()->attach($roles);
        }
        elseif(is_string($roles)) {
            $user->roles()->attach(Role::where($column, $roles)->firstOrFail()->id);
        }
        else {
            throw new \Exception("arument roles must be array|integer|string|\Panoscape\Access\Role");
        }
    }

    /**
     * Detach roles from the model
     *
     * @param array|integer|string|\Panoscape\Access\Role $roles
     * @param string $column
     * @param mixed $user
     *
     * @return void
     */
    public function detachRoles($roles, $column = 'name', $user = null)
    {
        if(!$this->checkUser($user)){
            throw new Exception("Invalid user instance");
        }

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
                    throw new \Exception("argument roles must be integer|string|\Panoscape\Access\Role");
                }
            }
            $user->roles()->detach($array);
        }
        elseif($roles instanceof Role) {
            $user->roles()->detach($roles->id);
        }
        elseif(is_integer($roles)) {
            $user->roles()->detach($roles);
        }
        elseif(is_string($roles)) {
            $user->roles()->detach(Role::where($column, $roles)->firstOrFail()->id);
        }
        else {
            throw new \Exception("argument roles must be array|integer|string|\Panoscape\Access\Role");
        }
    }

    /**
     * Sync roles with the model
     *
     * @param array|integer|string|\Panoscape\Access\Role $roles
     * @param bool $detaching
     * @param string $column
     * @param mixed $user
     *
     * @return void
     */
    public function syncRoles($roles, $detaching = true, $column = 'name', $user = null)
    {
        if(!$this->checkUser($user)){
            throw new Exception("Invalid user instance");
        }

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
                    throw new \Exception("argument roles must be integer|string|\Panoscape\Access\Role");
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
            throw new \Exception("argument roles must be array|integer|string|\Panoscape\Access\Role");
        }

        if($detaching) {
            $user->roles()->sync($array);
        }
        else {
            $user->roles()->syncWithoutDetaching($array);
        }
    }

    /**
     * Attach permissions to the model
     *
     * @param \Panoscape\Access\Role $role
     * @param array|integer|string|\Panoscape\Access\Permission $permissions
     * @param string $column
     *
     * @return void
     */
    public function attachPermissions($role, $permissions, $column = 'name')
    {
        if(!($role instanceof Role)) {
            throw new Exception("Invalid role instance");
        }

        if(is_array($permissions)) {
            foreach($permissions as $key=>$permission) {
                $this->attachPermissions($role, $permission, $column);
            }
        }
        elseif($permissions instanceof Permission) {
            $role->permissions()->attach($permissions->id);
        }
        elseif(is_integer($permissions)) {
            $role->permissions()->attach($permissions);
        }
        elseif(is_string($permissions)) {
            $role->permissions()->attach(Permission::where($column, $permissions)->firstOrFail()->id);
        }
        else {
            throw new \Exception("argument permissions must be array|integer|string|\Panoscape\Access\Permission");
        }
    }

    /**
     * Detach permissions from the model
     *
     * @param \Panoscape\Access\Role $role
     * @param array|integer|string|\Panoscape\Access\Permission $permissions
     * @param string $column
     *
     * @return void
     */
    public function detachPermissions($role, $permissions, $column = 'name')
    {
        if(!($role instanceof Role)) {
            throw new Exception("Invalid role instance");
        }

        if(is_array($permissions)) {
            $array = [];
            foreach($permissions as $key=>$permission) {
                if($permission instanceof Permission) {
                    array_push($array, $permission->id);
                }
                elseif(is_integer($permission)) {
                    array_push($array, $permission);
                }
                elseif(is_string($permission)) {
                    array_push($array, Permission::where($column, $permission)->firstOrFail()->id);
                }
                else {
                    throw new \Exception("Key must be integer|string|\Panoscape\Access\Permission");
                }
            }
            $role->permissions()->detach($array);
        }
        elseif($permissions instanceof Permission) {
            $role->permissions()->detach($permissions->id);
        }
        elseif(is_integer($permissions)) {
            $role->permissions()->detach($permissions);
        }
        elseif(is_string($permissions)) {
            $role->permissions()->detach(Permission::where($column, $permissions)->firstOrFail()->id);
        }
        else {
            throw new \Exception("Key must be array|integer|string|\Panoscape\Access\Permission");
        }
    }

    /**
     * Sync permissions with the model
     *
     * @param \Panoscape\Access\Role $role
     * @param array|integer|string|\Panoscape\Access\Permission $permissions
     * @param bool $detaching
     * @param string $column
     *
     * @return void
     */
    public function syncPermissions($role, $permissions, $detaching = true, $column = 'name')
    {
        if(!($role instanceof Role)) {
            throw new Exception("Invalid role instance");
        }

        $array = [];       
        if(is_array($permissions)) {            
            foreach($permissions as $key=>$permission) {
                if($permission instanceof Permission) {
                    array_push($array, $permission->id);
                }
                elseif(is_integer($permission)) {
                    array_push($array, $permission);
                }
                elseif(is_string($permission)) {
                    array_push($array, Permission::where($column, $permission)->firstOrFail()->id);
                }
                else {
                    throw new \Exception("Key must be integer|string|\Panoscape\Access\Permission");
                }
            }            
        }
        elseif($permissions instanceof Permission) {
            array_push($array, $permissions->id);
        }
        elseif(is_integer($permissions)) {
            array_push($array, $permissions);
        }
        elseif(is_string($permissions)) {
            array_push($array, Permission::where($column, $permissions)->firstOrFail()->id);
        }
        else {
            throw new \Exception("Key must be array|integer|string|\Panoscape\Access\Permission");
        }

        if($detaching) {
            $role->permissions()->sync($array);
        }
        else {
            $role->permissions()->syncWithoutDetaching($array);
        }
    }

    protected function checkUser(&$user)
    {
        if(is_null($user)) {
            if(auth()->guest()) {
                return false;
            }
            $user = auth()->user();
        }
        return in_array(HasRoles::class, class_uses($user));
    }
}