# UX Agents for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vormkracht10/agents.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/agents)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/vormkracht10/agents/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/vormkracht10/agents/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/vormkracht10/agents/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/vormkracht10/agents/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/vormkracht10/agents.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/agents)

**UX Agents** is a Laravel package that automatically configures AI coding assistants (Claude, Cursor, Gemini) with framework-specific rules, skills, and agents based on your project's installed packages. It bridges the gap between your Laravel application and AI assistants by providing contextual guidelines that help AI understand your codebase conventions and best practices.

## The Problem It Solves

When working with AI coding assistants on Laravel projects, the AI often lacks context about:
- Framework-specific conventions (Filament patterns, Pest testing standards)
- Project-specific best practices and coding standards
- Available testing utilities and helpers
- Proper usage patterns for third-party packages

This leads to AI-generated code that doesn't follow your project conventions, uses outdated patterns, or misses framework-specific optimizations.

## The Solution

UX Agents automatically:
1. **Detects** installed packages in your `composer.lock`
2. **Matches** them against registered drivers (Filament, Pest, etc.)
3. **Provisions** AI-specific configuration directories (`.claude/`, `.cursor/`, `.gemini/`)
4. **Generates** instruction files (`CLAUDE.md`, `.cursorrules`, `GEMINI.md`, `AGENTS.md`)
5. **Links** rules, skills, and specialized agents that teach AI assistants about your stack

## Key Features

- **Auto-Discovery**: Automatically detects relevant packages from your composer dependencies
- **Driver-Based Architecture**: Extensible system for adding support for new frameworks and tools
- **Multi-AI Support**: Configures Claude Code, Cursor, and Gemini simultaneously
- **Rules & Skills**: Provides both high-level rules and detailed skill documentation
- **Specialized Agents**: Includes pre-configured AI agents for specific tasks (code quality, testing)
- **Zero Configuration**: Works out of the box with sensible defaults
- **Package Discovery Hook**: Automatically runs when you install/update composer packages

## Installation

Install the package via composer:

```bash
composer require vormkracht10/agents
```

The package will automatically configure itself during composer's package discovery process. You can also manually trigger configuration:

```bash
composer dump-autoload
```

## How It Works

### Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                   composer.lock                         │
│  (your project's installed packages)                    │
└─────────────────┬───────────────────────────────────────┘
                  │
                  │ Package Discovery Event
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│              AgentsServiceProvider                      │
│  Listens for package:discover command                   │
└─────────────────┬───────────────────────────────────────┘
                  │
                  │ Triggers Configuration
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│                  Agents Class                           │
│  - Scans composer.lock for installed packages           │
│  - Matches packages against registered drivers          │
│  - Collects rules, skills, and agents                   │
└─────────────────┬───────────────────────────────────────┘
                  │
                  │ Creates Resources
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│           AI Provider Directories                       │
│                                                          │
│  .claude/                                               │
│  ├── rules/                                             │
│  │   ├── filament.md                                   │
│  │   └── pest.md                                       │
│  ├── skills/                                            │
│  │   ├── filament/                                     │
│  │   │   ├── 01-overview.md                           │
│  │   │   └── 02-testing-resources.md                  │
│  │   └── pest/                                         │
│  │       └── testing-guide.md                         │
│  └── agents/                                            │
│      ├── code-quality-guardian.md                      │
│      └── unit-test-writer.md                           │
│                                                          │
│  .cursor/     (same structure)                          │
│  .gemini/     (same structure)                          │
│                                                          │
│  CLAUDE.md    (instruction file with references)        │
│  .cursorrules (instruction file for Cursor)             │
│  GEMINI.md    (instruction file for Gemini)             │
│  AGENTS.md    (cross-provider documentation)            │
└─────────────────────────────────────────────────────────┘
```

### Component Breakdown

#### 1. Service Provider (`AgentsServiceProvider`)

The service provider is the entry point that:
- Registers itself with Laravel's service container
- Listens for the `package:discover` command via Laravel events
- Triggers the configuration process automatically when packages are installed/updated
- Registers the `AgentMap` singleton for managing drivers

```php
Event::listen(CommandFinished::class, function (CommandFinished $event) {
    if ($event->command === 'package:discover') {
        Agents::configure($command);
    }
});
```

#### 2. Agents Class (`Agents`)

The main orchestrator that:

**Package Detection**
```php
protected function getInstalledPackages(): array
{
    $lockPath = base_path('composer.lock');
    $lock = json_decode(File::get($lockPath), true);

    return collect($lock['packages'] ?? [])
        ->merge($lock['packages-dev'] ?? [])
        ->pluck('name')
        ->toArray();
}
```

**Driver Matching**
```php
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
```

**Resource Provisioning**
- Creates `.claude/`, `.cursor/`, `.gemini/` directories
- Copies rules, skills, and agent files from package resources
- Generates instruction files with references to all resources
- Creates an `AGENTS.md` file documenting all configured resources

#### 3. Driver System

**AgentDriver (Abstract Base)**

All drivers extend this base class and must implement:

```php
abstract class AgentDriver implements DriverContract
{
    // The composer package name to detect
    abstract public function getSlug(): string;

    // The directory name for resources (lowercase-hyphenated)
    abstract public function getPath(): string;

    // Human-readable name for display
    abstract public function getTitle(): string;

    // Returns path to rules file
    public function getRules(): string

    // Returns array of skill files
    public function getSkills(): array
}
```

**Example: FilamentDriver**

```php
class FilamentDriver extends AgentDriver
{
    public function getSlug(): string
    {
        return 'filament/filament'; // Detects filament/filament in composer.lock
    }

    public function getPath(): string
    {
        return 'filament'; // Uses resources/rules/filament/ and resources/skills/filament/
    }

    public function getTitle(): string
    {
        return 'Filament'; // Displayed in console output
    }
}
```

#### 4. AgentMap (Driver Registry)

Manages driver instances using Laravel's Manager pattern:

```php
class AgentMap extends Manager
{
    protected array $registeredDrivers = [
        'pest',
        'filament',
    ];

    // Factory methods
    protected function createPestDriver(): AgentDriver
    {
        return new \Vormkracht10\Agents\Drivers\PestDriver;
    }

    protected function createFilamentDriver(): AgentDriver
    {
        return new \Vormkracht10\Agents\Drivers\FilamentDriver;
    }
}
```

### Resource Types

#### Rules (`resources/rules/{driver}/rules.md`)

High-level guidelines and conventions that AI assistants should follow. These are typically:
- Framework conventions (Laravel 11/12 patterns)
- Project-specific coding standards
- Architecture patterns and best practices
- Security considerations
- Naming conventions

**Example Structure:**
```markdown
# Filament v4 Guidelines

## General Principles
- Target Filament v4 only
- Prefer clarity over cleverness
- Keep logic out of UI layer

## Resources
- One Eloquent model = one Filament Resource
- Resources must be thin (no business logic)

## Actions (MANDATORY RULE)
- Every Action MUST be in its own stand-alone class file
- ❌ Defining actions inline is forbidden
```

#### Skills (`resources/skills/{driver}/*.md`)

Detailed, tutorial-style documentation with code examples. Skills teach:
- How to test specific framework features
- Step-by-step implementation guides
- Common patterns and their solutions
- Testing utilities and helpers

**Example Structure:**
```markdown
---
title: Testing Filament Resources
description: Guide to testing Filament v4 resources using Pest
---

## Introduction
This guide covers testing Filament resources...

## Setup
```php
use function Pest\Livewire\livewire;
```

## Testing Resource Authorization
```php
it('prevents unauthorized access', function () {
    livewire(UserResource\Pages\ListUsers::class)
        ->assertForbidden();
});
```
```

#### Agents (`resources/agents/*.md`)

Specialized AI agent configurations with frontmatter that defines:
- Agent name and description
- When to invoke the agent
- Model preference (sonnet, opus, haiku)
- Color coding for UI
- Detailed system prompt with responsibilities and processes

**Example Structure:**
```markdown
---
name: code-quality-guardian
description: "Use this agent after writing new code..."
model: opus
color: blue
---

You are an elite Laravel code quality specialist...

## Your Core Responsibilities
1. Laravel Pint - Code style formatting
2. PHPStan - Static analysis
3. Rector - Automated refactoring
...

## Your Review Process
### Step 1: Initial Assessment
### Step 2: Run Quality Tools
### Step 3: Manual Code Review
...
```

### Generated Files

#### Provider-Specific Instruction Files

**CLAUDE.md**
```markdown
<agents-guidelines>
## Agent Skills & Rules

This project has the following agent configurations.

### Filament

#### Rules
Read and follow the rules in [.claude/rules/filament.md](.claude/rules/filament.md)

#### Skills
- [.claude/skills/filament/01-overview.md](.claude/skills/filament/01-overview.md)
- [.claude/skills/filament/02-testing-resources.md](.claude/skills/filament/02-testing-resources.md)

### Agents
- [.claude/agents/code-quality-guardian.md](.claude/agents/code-quality-guardian.md)
</agents-guidelines>
```

**.cursorrules** (same structure, different format)

**GEMINI.md** (same structure)

#### AGENTS.md (Cross-Provider Documentation)

A comprehensive overview of all configured resources across all AI providers:

```markdown
<ux-agents-guidelines>
## AI Assistant Resources

### Claude
#### Filament
- **Rules**: [.claude/rules/filament.md](.claude/rules/filament.md)
- **Skills**:
  - [.claude/skills/filament/01-overview.md](.claude/skills/filament/01-overview.md)

### Cursor
#### Filament
- **Rules**: [.cursor/rules/filament.md](.cursor/rules/filament.md)
...
</ux-agents-guidelines>
```

## Usage

### Automatic Configuration

The package automatically configures itself when you:

1. Install the package: `composer require vormkracht10/agents`
2. Install/update any package: `composer install` or `composer update`
3. Manually refresh: `composer dump-autoload`

You'll see output like:

```
Collecting packages...

┌───────────┐
│ Drivers   │
├───────────┤
│ Filament  │
│ Pest      │
└───────────┘

Setting rules for the selected driver...
```

### Using with AI Assistants

#### Claude Code

Claude automatically reads `CLAUDE.md` and the `.claude/` directory. Start coding and Claude will:
- Follow the rules defined in `.claude/rules/`
- Reference skills from `.claude/skills/` when needed
- Suggest using specialized agents from `.claude/agents/`

Example interaction:
```
You: "Create a new Filament resource for managing products"

Claude: "I'll create a Filament v4 resource following the project guidelines.
Based on .claude/rules/filament.md, I need to ensure actions are in
separate classes and follow the established patterns..."
```

#### Cursor

Cursor reads `.cursorrules` and the `.cursor/` directory automatically. The IDE will:
- Apply rules during code generation
- Suggest patterns from skills documentation
- Integrate specialized agents into workflows

#### Gemini

Gemini uses `GEMINI.md` and the `.gemini/` directory for context-aware assistance.

### Viewing Configuration

Check `AGENTS.md` in your project root to see all configured resources across all providers.

## Creating Custom Drivers

Adding support for new frameworks or tools is straightforward:

### Step 1: Create the Driver Class

Create `src/Drivers/YourDriver.php`:

```php
<?php

namespace Vormkracht10\Agents\Drivers;

use Vormkracht10\Agents\Managers\AgentDriver;

class YourDriver extends AgentDriver
{
    /**
     * The composer package slug (e.g., 'vendor/package').
     * Used to detect if the package is installed.
     */
    public function getSlug(): string
    {
        return 'vendor/package-name';
    }

    /**
     * The folder name used for resources.
     * Rules: resources/rules/{path}/rules.md
     * Skills: resources/skills/{path}/*.md
     */
    public function getPath(): string
    {
        return 'your-driver';
    }

    /**
     * Human-readable title shown in console output.
     */
    public function getTitle(): string
    {
        return 'Your Framework Name';
    }
}
```

### Step 2: Create Rules (Optional)

Create `resources/rules/your-driver/rules.md`:

```markdown
---
title: Your Framework Guidelines
description: Comprehensive rules for Your Framework
---

# Your Framework Guidelines

## General Principles
- Follow existing conventions
- Use framework built-in methods
- Write tests for new functionality

## Code Organization
...

## Testing Standards
...
```

### Step 3: Create Skills (Optional)

Create skill files in `resources/skills/your-driver/`:

**01-getting-started.md**
```markdown
---
title: Getting Started with Your Framework
description: Introduction to Your Framework basics
---

## Installation

```bash
composer require vendor/your-framework
```

## Configuration
...

## Basic Usage
```php
$framework = new YourFramework();
$framework->doSomething();
```
```

### Step 4: Register the Driver

Add your driver to `src/Managers/AgentMap.php`:

```php
protected array $registeredDrivers = [
    'pest',
    'filament',
    'your-driver', // Add here
];

protected function createYourDriverDriver(): AgentDriver
{
    return new \Vormkracht10\Agents\Drivers\YourDriver;
}
```

> **Important**: The method name must follow the pattern `create{DriverName}Driver()` where `{DriverName}` is the PascalCase version of the name in `$registeredDrivers`. For example: `'your-driver'` → `createYourDriverDriver()`

### Step 5: Test Your Driver

1. Add `vendor/package-name` to a test project's composer dependencies
2. Run `composer dump-autoload`
3. Check that your resources appear in `.claude/`, `.cursor/`, `.gemini/`
4. Verify references in instruction files

## Advanced Features

### Custom Agents

Create specialized AI agents for specific workflows by adding markdown files to `resources/agents/`:

```markdown
---
name: api-documentation-generator
description: "Use this agent to generate comprehensive API documentation..."
model: sonnet
color: green
---

You are an API documentation specialist...

## Your Responsibilities
- Generate OpenAPI specifications
- Create example requests/responses
- Document authentication flows
...
```

The agent will automatically be:
- Copied to all provider directories
- Listed in instruction files
- Available for invocation in AI assistants

### Multi-File Skills

Organize complex topics across multiple skill files:

```
resources/skills/your-driver/
├── 01-overview.md
├── 02-basic-concepts.md
├── 03-advanced-patterns.md
├── 04-testing.md
└── 05-deployment.md
```

Each file is independently referenced, allowing AI to load only relevant sections.

### Conditional Resources

If your driver needs environment-specific configurations:

```php
public function getRules(): string
{
    $env = app()->environment();
    $rulesFile = $env === 'production'
        ? 'rules-production.md'
        : 'rules-development.md';

    return $this->getResourcesPath().'/rules/'.$this->getPath().'/'.$rulesFile;
}
```

### Dynamic Skill Loading

Filter skills based on installed packages:

```php
public function getSkills(): array
{
    $skills = parent::getSkills();

    // Only include API testing skills if Laravel Sanctum is installed
    if (!class_exists(\Laravel\Sanctum\Sanctum::class)) {
        unset($skills['api-testing.md']);
    }

    return $skills;
}
```

## Architecture Decisions

### Why Driver-Based?

The driver pattern provides:
- **Extensibility**: Add new frameworks without modifying core code
- **Separation of Concerns**: Each driver manages its own resources
- **Discoverability**: Automatically detect relevant packages
- **Maintainability**: Update framework guidelines independently

### Why Multiple AI Providers?

Different AI assistants have different strengths:
- **Claude Code**: Excellent at understanding context and following rules
- **Cursor**: Tight IDE integration with inline suggestions
- **Gemini**: Emerging capabilities and alternative perspectives

Supporting all three ensures developers can use their preferred tools.

### Why Separate Rules and Skills?

- **Rules**: Prescriptive, must be followed (code style, security, patterns)
- **Skills**: Descriptive, reference material (how-to guides, examples)

This separation allows AI to:
1. Always apply rules during code generation
2. Selectively reference skills when relevant context is needed

### Why Auto-Configuration?

Manual configuration is error-prone and often forgotten. Auto-configuration via package discovery ensures:
- Resources stay synchronized with installed packages
- No configuration drift between environments
- Zero-friction developer experience

## File Structure Reference

```
vormkracht10/agents/
├── config/
│   └── agents.php                    # Configuration file (future use)
├── database/
│   └── migrations/                   # No migrations currently
├── resources/
│   ├── agents/                       # Specialized AI agent configs
│   │   ├── code-quality-guardian.md
│   │   └── unit-test-writer.md
│   ├── rules/                        # Framework-specific rules
│   │   ├── filament/
│   │   │   └── rules.md
│   │   └── pest/
│   │       └── rules.md
│   └── skills/                       # Detailed skill documentation
│       ├── filament/
│       │   ├── 01-overview.md
│       │   ├── 02-testing-resources.md
│       │   ├── 03-testing-tables.md
│       │   ├── 04-testing-schemas.md
│       │   ├── 05-testing-actions.md
│       │   └── 06-testing-notifications.md
│       └── pest/
│           └── testing-guide.md
├── src/
│   ├── Contracts/
│   │   └── DriverContract.php        # Driver interface
│   ├── Drivers/
│   │   ├── FilamentDriver.php        # Filament support
│   │   └── PestDriver.php            # Pest support
│   ├── Managers/
│   │   ├── AgentDriver.php           # Abstract base driver
│   │   └── AgentMap.php              # Driver registry
│   ├── Facades/
│   │   ├── Agents.php                # Agents facade
│   │   └── AgentMap.php              # AgentMap facade
│   ├── Agents.php                    # Main orchestrator
│   └── AgentsServiceProvider.php     # Service provider
├── tests/                            # Package tests
├── composer.json                     # Package manifest
└── README.md                         # This file
```

## Configuration

Currently, the package works with zero configuration. Future versions may support:

```php
// config/agents.php
return [
    // Disable auto-configuration
    'auto_configure' => true,

    // Providers to generate configs for
    'providers' => [
        'claude' => true,
        'cursor' => true,
        'gemini' => true,
    ],

    // Custom driver paths
    'driver_paths' => [
        app_path('Agents/Drivers'),
    ],

    // Custom resource paths
    'resource_paths' => [
        base_path('resources/agents'),
    ],
];
```

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

Run static analysis:

```bash
composer analyse
```

Fix code style:

```bash
composer format
```

## Troubleshooting

### Resources Not Appearing

1. Verify package is in `composer.lock`:
```bash
composer show | grep your-package
```

2. Check driver slug matches exactly:
```php
public function getSlug(): string
{
    return 'vendor/package'; // Must match composer.lock exactly
}
```

3. Manually trigger configuration:
```bash
composer dump-autoload
```

### Instruction Files Not Updating

The package preserves existing instruction file content and wraps its additions in tags:

```markdown
# Your custom content

<agents-guidelines>
<!-- Auto-generated content here -->
</agents-guidelines>

# More custom content
```

To force regeneration, delete the tag section and run `composer dump-autoload`.

### Driver Not Registered

Ensure the driver is:
1. Added to `$registeredDrivers` array in `AgentMap`
2. Has a corresponding `create{Name}Driver()` method
3. Method name matches PascalCase version of array entry

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

We welcome contributions! To contribute:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Make your changes
4. Add tests for new functionality
5. Run test suite: `composer test`
6. Run static analysis: `composer analyse`
7. Fix code style: `composer format`
8. Commit changes: `git commit -m "Add your feature"`
9. Push to branch: `git push origin feature/your-feature`
10. Open a Pull Request

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Adding New Drivers

When contributing new drivers:

1. Create driver class extending `AgentDriver`
2. Add comprehensive rules file
3. Provide detailed skill documentation with examples
4. Consider adding specialized agents if applicable
5. Update `AgentMap` with driver registration
6. Add tests for driver detection and resource provisioning
7. Update this README with driver examples

## Best Practices

### For Rule Files

- Be prescriptive and specific
- Use ❌ and ✅ to show good/bad patterns
- Include reasoning behind rules
- Organize by topic (Forms, Tables, Security, etc.)
- Keep rules concise but complete
- Reference official documentation

### For Skill Files

- Use frontmatter for metadata (title, description)
- Start with overview/introduction
- Provide complete, runnable code examples
- Explain the 'why' behind patterns
- Include common pitfalls and solutions
- Link to related skills
- Use progressive complexity (basic → advanced)

### For Agent Files

- Define clear triggering conditions in description
- Provide detailed system prompt with context
- Specify model preference (opus for complex, haiku for simple)
- Include step-by-step process
- Add self-verification checklists
- Define output format expectations

## Real-World Examples

### Example 1: Filament Resource Generation

**Without UX Agents:**
```
User: "Create a Filament resource for products"

AI: Creates resource with:
- Actions defined inline (violates project rules)
- Missing authorization checks
- Incorrect form component usage
- No policy references
```

**With UX Agents:**
```
User: "Create a Filament resource for products"

AI: Reads .claude/rules/filament.md and:
- Creates separate action classes
- Adds policy references
- Uses correct Filament v4 patterns
- Follows project conventions
- Suggests running code-quality-guardian agent
```

### Example 2: Test Generation

**Without UX Agents:**
```
User: "Write tests for the UserResource"

AI: Creates PHPUnit tests with basic assertions
```

**With UX Agents:**
```
User: "Write tests for the UserResource"

AI: Reads .claude/skills/filament/02-testing-resources.md and:
- Uses Pest syntax (per project standards)
- Tests authorization properly
- Uses Livewire testing helpers correctly
- Tests form validation
- Tests table functionality
- Includes relationship manager tests
```

## Support

- **Documentation**: You're reading it!
- **Issues**: [GitHub Issues](https://github.com/vormkracht10/agents/issues)
- **Discussions**: [GitHub Discussions](https://github.com/vormkracht10/agents/discussions)
- **Security**: [Security Policy](../../security/policy)

## Roadmap

Future enhancements planned:

- [ ] Configuration file for customizing behavior
- [ ] CLI command for manual configuration: `php artisan agents:configure`
- [ ] CLI command for listing detected packages: `php artisan agents:list`
- [ ] Support for project-specific custom drivers
- [ ] Agent templates for common tasks
- [ ] Skill validation and linting
- [ ] Integration tests with real AI assistants
- [ ] Community driver repository
- [ ] Version-specific rules (e.g., Filament v3 vs v4)
- [ ] Rule conflict detection
- [ ] Skill search and discovery CLI

## Credits

- [Mark van Eijk](https://github.com/markvaneijk)
- [Manoj Hortulanus](https://github.com/arduinomaster22)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Inspiration

This package was inspired by the need for context-aware AI assistance in Laravel projects. Special thanks to:
- The Laravel community for establishing clear conventions
- Filament for comprehensive documentation patterns
- Claude, Cursor, and Gemini teams for enabling AI-assisted development
- The broader PHP community for static analysis tools (PHPStan, Pint, etc.)
