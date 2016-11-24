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


    public static function boot()
    {
        parent::boot();
        static::deleting(function($role) {
            $role->permissions()->detach([]);
        });
    }

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
        return Facades\Access::hasPermissions($permissions, $requireAll, $column, $this);
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
        Facades\Access::attachPermissions($this, $permissions, $column);
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
        Facades\Access::detachPermissions($this, $permissions, $column);
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
        Facades\Access::syncPermissions($this, $permissions, $detaching, $column);
    }
} 
