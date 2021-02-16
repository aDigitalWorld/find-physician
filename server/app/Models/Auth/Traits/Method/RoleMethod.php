<?php

namespace App\Models\Auth\Traits\Method;

/**
 * Trait RoleMethod.
 */
trait RoleMethod
{
    /**
     * @return mixed
     */
    public function isAdmin()
    {
        return $this->name === config('access.users.admin_role');
    }
    /**
     * @return mixed
     */
    public function isClient()
    {
        return strtolower($this->name) === 'client';
    }
}
