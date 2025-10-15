# AI Rules for greenTumb Project

GreenThumb is a web-based gardener's journal application designed to solve the critical problem of searching through historical gardening entries.
The application enables gardeners to maintain a chronological record of their gardening activities and quickly retrieve specific information through AI-powered natural
language search, eliminating the tedious process of manually browsing through months of entries.

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application using:
- **Livewire v3** and **Livewire Volt v1.7** for reactive components
- **Livewire Flux** for UI components
- **Laravel Fortify** for authentication (currently only 2FA is enabled)
- **Tailwind CSS v4** for styling
- **Vite** for asset building
- **Pest PHP v4** with Browser Testing plugin for testing
- **Playwright** for browser automation

## CODING_PRACTICES

### Guidelines for ARCHITECTURE

#### ADR

- Create ADRs in /docs/adr/{name}.md for:
- 1) Major dependency changes
- 2) Architectural pattern changes
- 3) New integration patterns
- 4) Database schema changes


#### Frontend
- Entry point: `resources/js/app.js` and `resources/css/app.css`
- Vite config at `vite.config.js` with Laravel plugin and Tailwind CSS v4 plugin
- Flux UI components are used throughout (Livewire's official UI library)

## BACKEND

### Guidelines for PHP/Laravel

#### Route Organization
- `routes/web.php` - Main application routes (dashboard, settings)
- `routes/auth.php` - Authentication routes (included in web.php)
- `routes/console.php` - Artisan commands


### LARAVEL ELOQUENT MODELS
- Follow snake_case plural naming for table names
- Use standard primary key conventions or explicitly define custom keys
- Always define `$fillable` or `$guarded` properties to protect against mass assignment vulnerabilities
- Enable `preventSilentlyDiscardingAttributes()` in development to catch unfillable attribute errors
- Use chunking methods for processing large datasets
- Leverage query scopes for reusable query logic
- Implement soft deletes when records should be recoverable
- Use model pruning for automatic cleanup of old records
- Define default attribute values when appropriate
- Properly configure timestamp behavior
- Use observers to centralize event handling logic
- Use global scopes for universal query constraints
- Use local scopes for common query patterns
- Implement `ShouldHandleEventsAfterCommit` on observers when working with database transactions
- Consider UUID or ULID primary keys for distributed systems
- Use `withAttributes()` in scopes for consistent model creation
- Keep models focused on database interaction, not business logic
- Use model events appropriately without overusing them

### API
- all API routes should be defined in `routes/api.php`
- all API controllers should be in `app/Http/Controllers/Api/`

#### REGISTERING MICROSERVICES
- Always register service bindings within service providers
- Use type-hinted interfaces for dependency injection
- Prefer automatic resolution over manual binding (#[Bind] attribute)
- Use constructor injection for required dependencies
- Accept the Application container for sub-dependencies
- Never use the service locator pattern
- Use singleton binding for shared state services (#[Singleton] attribute)
- Use scoped binding for request-specific services
- Use contextual binding for different implementations-
- Bind interfaces to implementations, not concrete to concrete

#### Best Practices
- Use kebab-case for file names, class names and variables
- Follow Single Responsibility Principle
- Use dependency injection over static methods
- Declare class visibility modifiers explicitly
- Use strict type declarations
- Type-hint all method parameters
- Declare return types for all methods
- Use property type declarations
- Use constructor property promotion
- Use readonly properties for immutable data
- Try to avoid necessary comments, write self-documenting code using clear names

## FRONTEND

### Guidelines for BLADE/LIVEWIRE/FLUX

#### BLADE COMPONENTS, BLADE LAYOUTS ,LIVEWIRE
- Divide Blade templates into components, layouts and Livewire components
- Try to keep each component focused on a single responsibility

#### Livewire Components
- Use Livewire components only for interactive, stateful UI elements only
- All LiveWire components are located in `resources/views/livewire/` directory
- All livewire component classes are in `app/Livewire/` directory
- Use Artisan command to generate components: `php artisan make:livewire ComponentName`
- For nested components, use dot-notation syntax: `php artisan make:livewire Namespace.ComponentName`
- Try to avoid generating large monolithic components; break them into nested components instead
- Every component must have a `render()` and `with()` method

#### BLADE COMPONENTS
- Use Blade components for reusable, stateless UI elements
- All BLade components are located in `resources/views/components/`
- All BLade components are located in `app/View/Components/`
- Always use Artisan command to generate components: `php artisan make:component ComponentName`
- Always use Artisan command For nested components `php artisan make:component Namespace.ComponentName`
- When creating nested components, create subdirectory `resources/views/components/namespace/`, save main file as `index.blade.php` and all related files in same directory
- Use the `--view` flag to create anonymous components without a class.
- Components should be as small as possible, break large components into smaller ones using nesting
- Each component class should have a single responsibility, move logic to services if needed
- Use anonymous components for simple, reusable UI elements that do not require a dedicated class

#### BLADE LAYOUTS
- Organize layouts in `resources/views/components/layouts/`
- Use layouts for overall page structure (e.g., app layout, auth layout)

#### BLADE PARTIALS
- Do not use Blade partials; prefer components

#### Flux Components
- **Custom Flux components**: `resources/views/flux/` (custom icons, navlist groups)

### Guidelines for STYLING

#### TAILWIND

- Use the @layer directive to organize styles into components, utilities, and base layers
- Implement Just-in-Time (JIT) mode for development efficiency and smaller CSS bundles
- Use arbitrary values with square brackets (e.g., w-[123px]) for precise one-off designs
- Leverage the @apply directive in component classes to reuse utility combinations
- Implement the Tailwind configuration file for customizing theme, plugins, and variants
- Use component extraction for repeated UI patterns instead of copying utility classes
- Leverage the theme() function in CSS for accessing Tailwind theme values
- Use responsive variants (sm:, md:, lg:, etc.) for adaptive designs
- Leverage state variants (hover:, focus:, active:, etc.) for interactive elements

### Guidelines for HTML/Blade

#### Blade Templates
- Use primarily Blade components instead Blade partials
- Use semantic HTML structure
- Use double curly braces for escaped output
- Use {!! !!} syntax only for trusted HTML
- Use Blade directives over raw PHP
- Always add wire:key to loops in Livewire templates
- Use @once directive for single-execution code
- Use x- prefix for Blade components
- Use @props directive in anonymous components

## TESTING

### Test Organization
Tests are organized into the following directories:
- `tests/Unit/` - Unit tests for models, services, and other isolated components
- `tests/Feature/` - Feature tests for application functionality
- `tests/Browser/` - Browser tests for end-to-end UI testing with Pest Browser plugin
- `tests/Integration/` - Integration tests for external service interactions
- `tests/Performance/` - Performance testing
- `tests/Security/` - Security testing

### Browser Testing with Pest v4

The project uses **Pest PHP v4** with the **Browser Testing plugin** for end-to-end testing.

#### Key Features:
- Browser automation via Playwright
- Support for Chrome, Firefox, and Safari
- Screenshot capture on failures (saved to `tests/Browser/Screenshots/`)
- Headless and headed modes
- Parallel test execution support

#### Browser Testing Best Practices:
- Organize browser tests in `tests/Browser/` directory
- Use data-test attributes for stable element selection (preferred over CSS selectors)
- Follow the comprehensive guidelines in `.ai/rules/PestBrowserRules.md` for:
  - Element location strategies
  - Assertion patterns
  - Form interactions
  - Debugging techniques
  - CI/CD configuration

#### Configuration:
- Browser configuration: `tests/Pest.php`
- Default browser: Chrome
- Default timeout: 5 seconds
- Screenshots directory: `tests/Browser/Screenshots/` (git-ignored)

## Development Commands

### Livewire
```bash
php artisan livewire:make ComponentName
```
Creates a new Livewire component.

### Components
```bash
php artisan make:component ComponentName
```
Creates a new Blade component.

```bash
php  artisan make:component ComponentName --view
```
Creates a new anonymous Blade component.

### Starting Development Server
```bash
composer dev
```
This runs 4 concurrent processes:
- Laravel development server (port 8000)
- Queue worker with single retry
- Laravel Pail for log monitoring
- Vite dev server for hot module replacement

### Running Tests
```bash
composer test
```
Clears config cache and runs Pest tests. Tests use in-memory SQLite database.

To run a specific test:
```bash
php artisan test --filter test_name
```

To run a specific test file:
```bash
php artisan test tests/Unit/UserTest.php
```

To run tests with a specific group:
```bash
php artisan test --group models
```

### Browser Testing
```bash
./vendor/bin/pest tests/Browser
```
Run all browser tests.

```bash
./vendor/bin/pest tests/Browser --headed
```
Run browser tests with visible browser window (useful for debugging).

```bash
./vendor/bin/pest tests/Browser --debug
```
Run browser tests in debug mode (pauses on failure for inspection).

```bash
./vendor/bin/pest tests/Browser --parallel
```
Run browser tests in parallel for improved performance.

```bash
./vendor/bin/pest tests/Browser --browser=firefox
```
Run browser tests with a specific browser (chrome, firefox, safari).

```bash
./vendor/bin/pest tests/Browser/ExampleTest.php
```
Run a specific browser test file.

**Note**: Detailed browser testing standards and best practices are documented in `.ai/rules/PestBrowserRules.md`

### Code Style
```bash
./vendor/bin/pint
```
Uses Laravel Pint for code formatting.

### Building Assets
```bash
npm run build    # Production build
npm run dev      # Development build with watch
```

### Database
```bash
php artisan make:migration create_table_name --create=table_name  # Create a new migration
php artisan migrate              # Run migrations
php artisan migrate:fresh        # Fresh migration (drops all tables)
php artisan migrate:fresh --seed # Fresh migration with seeding
```
Default database is Mysql

### Observers
```bash
php artisan make:observer UserObserver --model=User

```
Creates a new model observer.


