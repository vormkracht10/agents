<?php

namespace Vormkracht10\Agents;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Vormkracht10\Agents\Facades\AgentMap;

use function Laravel\Prompts\table;

class Agents
{
    protected array $instructionFiles = [
        '.claude' => 'CLAUDE.md',
        '.cursor' => '.cursorrules',
        '.gemini' => 'GEMINI.md',
    ];

    public function configure(Command $command): void
    {
        $command->info("Collecting packages...\n");

        $foundDrivers = $this->listDrivers();

        if (empty($foundDrivers)) {
            $command->error('No agents found. Please install an agent package.');

            return;
        }

        table(['Drivers'], array_map(fn ($driver) => [$driver->getTitle()], $foundDrivers));

        $command->line('Setting rules for the selected driver...');

        $aiDirs = array_keys($this->instructionFiles);

        $allProviderResources = [];

        // Gather agents first so they can be included in instruction files
        $agentFiles = File::exists(__DIR__.'/../resources/agents')
            ? File::allFiles(__DIR__.'/../resources/agents')
            : [];

        foreach ($aiDirs as $dir) {
            $dirPath = base_path($dir);
            $createdResources = [];

            File::ensureDirectoryExists($dirPath);

            // Only delete rules, skills and agents directories, preserve other files
            foreach (['rules', 'skills', 'agents'] as $subDir) {
                $subDirPath = $dirPath.'/'.$subDir;

                if (File::exists($subDirPath)) {
                    File::deleteDirectory($subDirPath);
                }
            }

            foreach ($foundDrivers as $driver) {
                $driverResources = [
                    'title' => $driver->getTitle(),
                    'skills' => [],
                    'rules' => null,
                ];

                $skills = $driver->getSkills();

                if (! empty($skills)) {
                    $skillPath = $dirPath.'/skills/'.$driver->getPath().'/';

                    File::makeDirectory($skillPath, 0755, true);

                    foreach ($skills as $name => $path) {
                        if (File::exists($path)) {
                            File::copy($path, $skillPath.$name);
                            $driverResources['skills'][] = $dir.'/skills/'.$driver->getPath().'/'.$name;
                        }
                    }
                }

                $rulePath = $driver->getRules();

                if (File::exists($rulePath)) {
                    $file = $dirPath.'/rules/'.$driver->getPath().'.md';

                    File::ensureDirectoryExists(dirname($file));

                    File::put($file, File::get($rulePath));
                    $driverResources['rules'] = $dir.'/rules/'.$driver->getPath().'.md';
                }

                if (! empty($driverResources['skills']) || $driverResources['rules']) {
                    $createdResources[] = $driverResources;
                }
            }

            // Copy agents to this provider's directory and collect their paths
            $agentPaths = [];
            if (! empty($agentFiles)) {
                $agentDir = $dirPath.'/agents/';
                File::ensureDirectoryExists($agentDir);

                foreach ($agentFiles as $agent) {
                    $agentName = $agent->getFilename();
                    $agentPath = $agent->getPathname();

                    File::copy($agentPath, $agentDir.$agentName);
                    $agentPaths[] = $dir.'/agents/'.$agentName;
                }
            }

            $this->createInstructionFile($dir, $createdResources, $agentPaths);

            if (! empty($createdResources) || ! empty($agentPaths)) {
                $allProviderResources[$dir] = [
                    'resources' => $createdResources,
                    'agents' => $agentPaths,
                ];
            }
        }

        $this->updateAgentsFile($allProviderResources);
    }

    protected function createInstructionFile(string $dir, array $resources, array $agents = []): void
    {
        if (empty($resources) && empty($agents)) {
            return;
        }

        $instructionFile = $this->instructionFiles[$dir] ?? null;

        if (! $instructionFile) {
            return;
        }

        $filePath = base_path($instructionFile);
        $tagName = 'agents-guidelines';
        $guidelinesContent = $this->buildProviderGuidelinesContent($resources, $tagName, $agents);

        if (File::exists($filePath)) {
            $existingContent = File::get($filePath);
            $pattern = '/<'.$tagName.'>.*?<\/'.$tagName.'>/s';

            if (preg_match($pattern, $existingContent)) {
                $newContent = preg_replace($pattern, $guidelinesContent, $existingContent);
            } else {
                $newContent = $existingContent."\n\n".$guidelinesContent;
            }

            File::put($filePath, $newContent);
        } else {
            File::put($filePath, $guidelinesContent."\n");
        }
    }

    protected function buildProviderGuidelinesContent(array $resources, string $tagName, array $agents = []): string
    {
        $content = "<{$tagName}>\n";
        $content .= "## Agent Skills & Rules\n\n";
        $content .= "This project has the following agent configurations. Read and follow these rules and skills.\n\n";

        foreach ($resources as $resource) {
            $content .= "### {$resource['title']}\n\n";

            if ($resource['rules']) {
                $content .= "#### Rules\n\n";
                $content .= "Read and follow the rules in [{$resource['rules']}]({$resource['rules']})\n\n";
            }

            if (! empty($resource['skills'])) {
                $content .= "#### Skills\n\n";
                $content .= "The following skills are available for reference:\n\n";
                foreach ($resource['skills'] as $skill) {
                    $content .= "- [{$skill}]({$skill})\n";
                }
                $content .= "\n";
            }
        }

        if (! empty($agents)) {
            $content .= "### Agents\n\n";
            $content .= "The following agents are available:\n\n";
            foreach ($agents as $agent) {
                $content .= "- [{$agent}]({$agent})\n";
            }
            $content .= "\n";
        }

        $content .= "</{$tagName}>";

        return $content;
    }

    protected function updateAgentsFile(array $allProviderResources): void
    {
        if (empty($allProviderResources)) {
            return;
        }

        $agentsFile = base_path('AGENTS.md');
        $tagName = 'ux-agents-guidelines';

        $guidelinesContent = $this->buildAgentsGuidelinesContent($allProviderResources, $tagName);

        if (File::exists($agentsFile)) {
            $existingContent = File::get($agentsFile);

            $pattern = '/<'.$tagName.'>.*?<\/'.$tagName.'>/s';

            if (preg_match($pattern, $existingContent)) {
                $newContent = preg_replace($pattern, $guidelinesContent, $existingContent);
            } else {
                $newContent = $existingContent."\n\n".$guidelinesContent;
            }

            File::put($agentsFile, $newContent);
        } else {
            File::put($agentsFile, $guidelinesContent."\n");
        }
    }

    protected function buildAgentsGuidelinesContent(array $allProviderResources, string $tagName): string
    {
        $providerNames = [
            '.claude' => 'Claude',
            '.cursor' => 'Cursor',
            '.gemini' => 'Gemini',
        ];

        $content = "<{$tagName}>\n";
        $content .= "## AI Assistant Resources\n\n";
        $content .= "This project has skills and rules configured for the following AI assistants.\n";
        $content .= "Each assistant should read and follow the rules and skills for the relevant drivers.\n\n";

        foreach ($allProviderResources as $dir => $providerData) {
            $providerName = $providerNames[$dir] ?? ucfirst(ltrim($dir, '.'));
            $content .= "### {$providerName}\n\n";

            $resources = $providerData['resources'] ?? [];
            $agents = $providerData['agents'] ?? [];

            foreach ($resources as $resource) {
                $content .= "#### {$resource['title']}\n\n";

                if ($resource['rules']) {
                    $content .= "- **Rules**: [{$resource['rules']}]({$resource['rules']})\n";
                }

                if (! empty($resource['skills'])) {
                    $content .= "- **Skills**:\n";
                    foreach ($resource['skills'] as $skill) {
                        $content .= "  - [{$skill}]({$skill})\n";
                    }
                }

                $content .= "\n";
            }

            if (! empty($agents)) {
                $content .= "#### Agents\n\n";
                foreach ($agents as $agent) {
                    $content .= "- [{$agent}]({$agent})\n";
                }
                $content .= "\n";
            }
        }

        $content .= "</{$tagName}>";

        return $content;
    }

    /** @return \Vormkracht10\Agents\Managers\AgentDriver[] */
    public function listDrivers(): array
    {
        $installedPackages = $this->getInstalledPackages();
        $drivers = AgentMap::getDrivers();

        $foundDrivers = [];

        foreach ($drivers as $driver) {
            $driverInstance = AgentMap::driver($driver);

            if (in_array($driverInstance->getSlug(), $installedPackages)) {
                $foundDrivers[] = $driverInstance;
            }
        }

        return $foundDrivers;
    }

    protected function getInstalledPackages(): array
    {
        $lockPath = base_path('composer.lock');

        if (! File::exists($lockPath)) {
            return [];
        }

        $lock = json_decode(File::get($lockPath), true);

        return collect($lock['packages'] ?? [])
            ->merge($lock['packages-dev'] ?? [])
            ->pluck('name')
            ->toArray();
    }
}
