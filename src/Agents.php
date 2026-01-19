<?php

namespace Vormkracht10\Agents;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Vormkracht10\Agents\Facades\AgentMap;

use function Laravel\Prompts\table;

class Agents
{
    public function configure(Command $command): void
    {
        $command->line(str_repeat('-', 80)."\n");
        $command->info("Collecting packages...\n");

        $foundDrivers = $this->listDrivers();

        if (empty($foundDrivers)) {
            $command->error('No agents found. Please install an agent package.');

            return;
        }

       table(['Driver'],array_map(fn ($driver) => [$driver->getTitle()], $foundDrivers));

        $command->line('Setting rules for the selected driver...');

        $cursorPath = [
            '.cursor',
            '.gemini',
            '.claude',
        ];
        
        foreach ($cursorPath as $path) {
            $cursorPath = base_path($path);

            if(!File::exists($cursorPath)) {
                File::makeDirectory($cursorPath, 0755, true);
            }

            foreach ($foundDrivers as $driver) {
               $file = $cursorPath . '/rules/' . $driver->getPath() . '.md';

               File::ensureDirectoryExists(dirname($file));

               if(File::exists($file)) {
                    File::delete($file);
               }

               File::put($file, $driver->getRules());
            }
        }

        $command->line(str_repeat('-', 80)."\n\n");
    }

    public function listDrivers(): array
    {
        $drivers = AgentMap::getDrivers();

        $foundDrivers = [];

        foreach ($drivers as $driver) {
            $foundDrivers[] = AgentMap::driver($driver);
        }

        return $foundDrivers;
    }
}
