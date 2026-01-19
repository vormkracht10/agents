<?php

namespace Vormkracht10\Agents\Managers;

use Vormkracht10\Agents\Contracts\DriverContract;

abstract class AgentDriver implements DriverContract
{
    /**
     * Get the unique slug identifier for this driver.
     */
    abstract public function getSlug(): string;

    abstract public function getPath(): string;

    abstract public function getTitle(): mixed;

    abstract public function getRules(): string;
}
