<?php

namespace Panoscape\Access\Facades;

use Illuminate\Support\Facades\Facade;

class Access extends Facade
{
    /**
     * The name of the binding in the IoC container.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Panoscape\Access\Access::class;
    }
}
