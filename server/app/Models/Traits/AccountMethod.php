<?php

namespace app\Models\Auth\Traits;

/**
 * Trait AccountMethod.
 */
trait AccountMethod
{
    /**
     * @return bool
     */
    public function scopeActive()
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function scopeOverride()
    {
        return $this->override;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return ! $this->override;
    }
}
