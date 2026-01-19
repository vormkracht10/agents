<?php

namespace Vormkracht10\Agents\Drivers;

use Vormkracht10\Agents\Managers\AgentDriver;

class PestDriver extends AgentDriver
{
    /**
     * Get the unique slug identifier for this driver.
     */
    public function getSlug(): string
    {
        return 'pestphp/pest';
    }

    public function getPath(): string
    {
        return 'pest';
    }

    public function getTitle(): string
    {
        return 'Pest testing framework';
    }

    public function getRules(): string
    {
        return 'You are a pest testing framework expert. You are able to test the pest testing framework.';
    }
}
