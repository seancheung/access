<?php

namespace Panoscape\Access;

class Access
{
    /**
     * Check if this user has the provided roles
     *
     * @param array|string $roles
     * @param bool $requireAll
     * @param string $column
     *
     * @return bool
     */
    public function hasRoles($roles, $requireAll = true, $column = 'name')
    {
        if(auth()->guest() || !in_array(HasRoles::class, class_uses(auth()->user()))) {
            return false;
        }

        if(is_string($roles)) {
            $roles = preg_split('/[\|\s]+/', $roles, 0, PREG_SPLIT_NO_EMPTY);
        }
        if(is_string($requireAll)) {
            $requireAll = $requireAll == 'true';
        }

        return auth()->user()->hasRoles($roles, $requireAll, $column);
    }

    /**
     * Check if this user has the provided permissions
     *
     * @param array|string $permissions
     * @param bool $requireAll
     * @param string $column
     *
     * @return bool
     */
    public function hasPermissions($permissions, $requireAll = true, $column = 'name')
    {
        if(auth()->guest() || !in_array(HasRoles::class, class_uses(auth()->user()))) {
            return false;
        }

        if(is_string($permissions)) {
            $permissions = preg_split('/[\|\s]+/', $permissions, 0, PREG_SPLIT_NO_EMPTY);
        }
        if(is_string($requireAll)) {
            $requireAll = $requireAll == 'true';
        }

        return auth()->user()->hasPermissions($permissions, $requireAll, $column);
    }
}