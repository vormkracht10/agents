<?php

namespace Vormkracht10\Agents\Drivers;

use Vormkracht10\Agents\Managers\AgentDriver;

class FilamentDriver extends AgentDriver
{
    public function getSlug(): string
    {
        return 'filament/filament';
    }

    public function getPath(): string
    {
        return 'filament';
    }

    public function getTitle(): string
    {
        return 'Filament';
    }
}
