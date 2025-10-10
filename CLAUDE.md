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
- 

## CODING_PRACTICES

### Guidelines for ARCHITECTURE

#### ADR

- Create ADRs in /docs/adr/{name}.md for:
- 1) Major dependency changes
- 2) Architectural pattern changes
- 3) New integration patterns
- 4) Database schema changes

#### Route Organization
- `routes/web.php` - Main application routes (dashboard, settings)
- `routes/auth.php` - Authentication routes (included in web.php)
- `routes/console.php` - Artisan commands

#### Frontend
- Entry point: `resources/js/app.js` and `resources/css/app.css`
- Vite config at `vite.config.js` with Laravel plugin and Tailwind CSS v4 plugin
- Flux UI components are used throughout (Livewire's official UI library)

#### BLADE COMPONENTS, BLADE LAYOUTS ,LIVEWIRE
- Divide Blade templates into components, layouts and Livewire components
- Try to keep each component focused on a single responsibility

#### BLADE COMPONENTS
- Organize components in `resources/views/components/`
- Components should be as small as possible, better to have more smaller components than fewer large ones
- Use components for reusable UI elements (e.g., buttons, form inputs)
- Organize components class in `app/View/Components/`
- Each component class should have a single responsibility, move logic to services if needed
- Use anonymous components for simple, reusable UI elements that do not require a dedicated class

#### BLADE LAYOUTS
- Organize layouts in `resources/views/components/layouts/`
- Use layouts for overall page structure (e.g., app layout, auth layout)

#### BLADE PARTIALS
- Do not use Blade partials; prefer components

#### Livewire Components
- Use Livewire components for interactive elements (e.g., forms, dynamic lists)
- Organize Livewire class in `app/Livewire/`
- Organize Livewire views in `resources/views/livewire/`
- Each Livewire component should have a single responsibility, move logic to services if needed

#### Flux Components
- **Custom Flux components**: `resources/views/flux/` (custom icons, navlist groups)

## BACKEND

### Guidelines for PHP/Laravel

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
- Document custom properties and methods clearly

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
Clears config cache and runs PHPUnit tests. Tests use in-memory SQLite database.

To run a specific test:
```bash
php artisan test --filter TestName
```

To run a specific test file:
```bash
php artisan test tests/Feature/ExampleTest.php
```

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


