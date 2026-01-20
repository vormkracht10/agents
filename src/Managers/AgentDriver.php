<?php

namespace Vormkracht10\Agents\Managers;

use Illuminate\Support\Facades\File;
use Vormkracht10\Agents\Contracts\DriverContract;

abstract class AgentDriver implements DriverContract
{
    /**
     * Get the unique slug identifier for this driver.
     */
    abstract public function getSlug(): string;

    abstract public function getPath(): string;

    abstract public function getTitle(): mixed;

    protected function getResourcesPath(): string
    {
        return dirname(__DIR__, 2).'/resources';
    }

    public function getRules(): string
    {
        return $this->getResourcesPath().'/rules/'.$this->getPath().'/rules.md';
    }

    public function getSkills(): array
    {
        $resourcePath = $this->getResourcesPath().'/skills/'.$this->getPath().'/';

        if (! File::isDirectory($resourcePath)) {
            return [];
        }

        $skills = File::allFiles($resourcePath);

        $paths = [];
        foreach ($skills as $skill) {
            $paths[$skill->getFilename()] = $skill->getPathname();
        }

        return $paths;
    }
}
