<?php

namespace Vormkracht10\Agents\Managers;

use Illuminate\Support\Manager;
use Vormkracht10\Agents\Managers\AgentDriver;
use Vormkracht10\Agents\Contracts\DriverContract;

class AgentMap extends Manager
{
    public $drivers = [
        'pest',
    ];

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        throw new \Exception('No default driver configured');
    }

    /**
     * Create an instance of the pest driver.
     */
    protected function createPestDriver(): AgentDriver
    {
        return new \Vormkracht10\Agents\Drivers\PestDriver;
    }
}
