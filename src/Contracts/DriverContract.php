<?php

namespace Vormkracht10\Agents\Contracts;

interface DriverContract
{
    /**
     * Get the unique slug identifier for this driver.
     */
    public function getSlug(): string;
}
