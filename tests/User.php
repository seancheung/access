<?php

namespace Panoscape\Access\Tests;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Panoscape\Access\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = [];
}