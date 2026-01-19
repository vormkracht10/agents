<?php

namespace Vormkracht10\Agents\Facades;

use Illuminate\Support\Facades\Facade;

class AgentMap extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'agent.map';
    }
}
