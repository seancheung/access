<?php

namespace Panoscape\Access\Middleware;

use Closure;
use Panoscape\Access\Facades\Access;

class VerifyAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string $type 'role' or 'permission'
     * @param  string $access
     * @param  bool $requireAll
     * @param  string $column
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $type, $access, $requireAll = true, $column = 'name')
    {
        if($type == 'role' && Access::hasRoles($access, $requireAll, $column) ||
            $type == 'permission' && Access::hasPermissions($access, $requireAll, $column)) {
                return $next($request);
            }
        abort(403);
    }
}