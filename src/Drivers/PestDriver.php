<?php

namespace Vormkracht10\Agents\Drivers;

use Vormkracht10\Agents\Managers\AgentDriver;

class PestDriver extends AgentDriver
{
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
        return 'Pest';
    }
}
