<?php

namespace Panoscape\Access;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'access_permissions';

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
     * The roles that belong to the permission
     *
     * @return mixed
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'access_permission_role', 'permission_id', 'role_id');
    }
}