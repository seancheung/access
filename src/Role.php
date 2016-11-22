<?php

namespace Panoscape\Access;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'access_roles';

    /**
    * Indicates if the model should be timestamped.
    *
    * @var bool
    */
    public $timestamps = false;
    
    /**
    * The attributes that are not mass assignable.
    *
    * @var array
    */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The related user's class
     *
     * @var string
     */
    public static $userModel = '\App\User';

    /**
     * The permissions that belong to the role
     *
     * @return mixed
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'access_permission_role', 'role_id', 'permission_id');
    }

    /**
     * The users that belong to the role
     *
     * @return mixed
     */
    public function users()
    {
        return $this->belongsToMany(static::$userModel, 'access_role_user', 'user_id', 'role_id');
    }

    /**
     * Check if this role has the provided permissions
     *
     * @param array $permissions
     * @param bool $requireAll
     * @param string $column
     *
     * @return bool
     */
    public function hasPermissions($permissions, $requireAll = true, $column = 'name')
    {
        $count = $this->permissions()->whereIn($column, $permissions)->count();
        if($requireAll) {
            return $count >= count($permissions);
        }
        return $count > 0;
    }

    /**
     * Attach permissions to the model
     *
     * @param array|integer|string|\Panoscape\Access\Permission $permissions
     * @param string $column
     *
     * @return void
     */
    public function attachPermissions($permissions, $column = 'name')
    {        
        if(is_array($permissions)) {
            foreach($permissions as $key=>$permission) {
                $this->attachPermissions($permission, $column);
            }
        }
        elseif($permissions instanceof Permission) {
            $this->permissions()->attach($permissions->id);
        }
        elseif(is_integer($permissions)) {
            $this->permissions()->attach($permissions);
        }
        elseif(is_string($permissions)) {
            $this->permissions()->attach(Permission::where($column, $permissions)->firstOrFail()->id);
        }
        else {
            throw new \Exception("Key must be array|integer|string|\Panoscape\Access\Permission");
        }
    }

    /**
     * Detach permissions from the model
     *
     * @param array|integer|string|\Panoscape\Access\Permission $permissions
     * @param string $column
     *
     * @return void
     */
    public function detachPermissions($permissions, $column = 'name')
    {        
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
            $this->permissions()->detach($array);
        }
        elseif($permissions instanceof Permission) {
            $this->permissions()->detach($permissions->id);
        }
        elseif(is_integer($permissions)) {
            $this->permissions()->detach($permissions);
        }
        elseif(is_string($permissions)) {
            $this->permissions()->detach(Permission::where($column, $permissions)->firstOrFail()->id);
        }
        else {
            throw new \Exception("Key must be array|integer|string|\Panoscape\Access\Permission");
        }
    }

    /**
     * Sync permissions with the model
     *
     * @param array|integer|string|\Panoscape\Access\Permission $permissions
     * @param bool $detaching
     * @param string $column
     *
     * @return void
     */
    public function syncPermissions($permissions, $detaching = true, $column = 'name')
    { 
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
            $this->permissions()->sync($array);
        }
        else {
            $this->permissions()->syncWithoutDetaching($array);
        }
    }
} 
