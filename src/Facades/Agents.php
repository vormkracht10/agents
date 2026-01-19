<?php

namespace Vormkracht10\Agents\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vormkracht10\Agents\Agents
 *
 * @method static void configure()
 */
class Agents extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Vormkracht10\Agents\Agents::class;
    }
}
