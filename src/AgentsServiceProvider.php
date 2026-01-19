<?php

namespace Vormkracht10\Agents;

use Illuminate\Console\Command;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Vormkracht10\Agents\Facades\Agents;
use Vormkracht10\Agents\Managers\AgentMap as AgentMapManager;

class AgentsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('agents');
    }

    public function boot()
    {
        parent::boot();

        Event::listen(CommandFinished::class, function (CommandFinished $event) {
            if ($event->command === 'package:discover') {
                $newCommand = new Command;
                $output = new OutputStyle(new ArgvInput, new ConsoleOutput);
                $newCommand->setOutput($output);
                Agents::configure($newCommand);
            }
        });
    }

    public function packageRegistered()
    {
        parent::packageRegistered();

        $this->app->singleton('agent.map', function ($app) {
            return new AgentMapManager($app);
        });
    }
}
