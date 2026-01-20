# UX agents

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vormkracht10/agents.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/agents)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/vormkracht10/agents/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/vormkracht10/agents/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/vormkracht10/agents/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/vormkracht10/agents/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/vormkracht10/agents.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/agents)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/Agents.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/Agents)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require vormkracht10/agents
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="agents-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="agents-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="agents-views"
```

## Usage

```php
$agents = new Vormkracht10\Agents();
echo $agents->echoPhrase('Hello, Vormkracht10!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Creating New Drivers

This package uses a driver-based architecture to support different tools and frameworks. Each driver provides rules and skills that AI assistants can use when working with that specific tool.

#### Driver Structure

```
src/Drivers/
└── YourDriver.php

resources/
├── rules/
│   └── your-driver/
│       └── rules.md
└── skills/
    └── your-driver/
        ├── skill-one.md
        └── skill-two.md
```

#### Step 1: Create the Driver Class

Create a new driver class in `src/Drivers/` that extends `AgentDriver`:

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
     * Human-readable title shown in the console output.
     */
    public function getTitle(): string
    {
        return 'Your Framework Name';
    }
}
```

#### Step 2: Create Rules (Optional)

Create `resources/rules/your-driver/rules.md` with guidelines for the AI assistant:

```markdown
## Your Framework Rules

- Follow existing conventions in the codebase
- Use the framework's built-in methods
- Write tests for new functionality
```

#### Step 3: Create Skills (Optional)

Create skill files in `resources/skills/your-driver/`:

```markdown
# Skill: Testing Your Framework

This skill teaches how to write tests for Your Framework.

## Examples

...
```

#### Step 4: Register the Driver

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

> **Note**: The method name must follow the pattern `create{DriverName}Driver()` where `{DriverName}` is the pascal-case version of the name in `$registeredDrivers`.

#### How It Works

1. **Auto-discovery**: The package checks `composer.lock` for installed packages
2. **Matching**: If a package matches your driver's `getSlug()`, the driver is activated
3. **Resource copying**: Rules and skills are copied to `.claude/`, `.cursor/`, and `.gemini/` directories
4. **Instruction files**: References are added to `CLAUDE.md`, `.cursorrules`, `GEMINI.md`, and `AGENTS.md`

#### Best Practices

- **Rules**: Focus on conventions, best practices, and common patterns
- **Skills**: Provide detailed examples, code snippets, and step-by-step guides
- **Slug**: Use the exact composer package name (e.g., `laravel/framework`, `pestphp/pest`)
- **Path**: Use lowercase, hyphenated names (e.g., `your-driver`, `my-framework`)

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Manoj Hortulanus](https://github.com/arduinomaster22)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
