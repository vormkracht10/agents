<?php

namespace Vormkracht10\Agents\Managers;

use Illuminate\Support\Manager;

class AgentMap extends Manager
{
    protected array $registeredDrivers = [
        'pest',
        'filament',
    ];

    /**
     * Get all registered driver names.
     */
    public function getDrivers(): array
    {
        return $this->registeredDrivers;
    }

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

    /**
     * Create an instance of the filament driver.
     */
    protected function createFilamentDriver(): AgentDriver
    {
        return new \Vormkracht10\Agents\Drivers\FilamentDriver;
    }
}
