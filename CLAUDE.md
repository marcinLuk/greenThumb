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

## Architecture

### Livewire 
- **Livewire Component's class**: `app/Livewire/*` Livewire Component classes

### Route Organization
- `routes/web.php` - Main application routes (dashboard, settings)
- `routes/auth.php` - Authentication routes (included in web.php)
- `routes/console.php` - Artisan commands

### View Components
- **Layouts**: `resources/views/components/layouts/` (app, auth)
- **Custom Flux components**: `resources/views/flux/` (custom icons, navlist groups)
- **Reusable components**: `resources/views/components/` (auth-header, app-logo, input-otp, etc.)

### Frontend
- Entry point: `resources/js/app.js` and `resources/css/app.css`
- Vite config at `vite.config.js` with Laravel plugin and Tailwind CSS v4 plugin
- Flux UI components are used throughout (Livewire's official UI library)

## CODING_PRACTICES

### Guidelines for ARCHITECTURE

#### ADR

- Create ADRs in /docs/adr/{name}.md for:
- 1) Major dependency changes
- 2) Architectural pattern changes
- 3) New integration patterns
- 4) Database schema changes

#### DDD

- Define bounded contexts to separate different parts of the domain with clear boundaries
- Implement ubiquitous language within each context to align code with business terminology
- Create rich domain models with behavior, not just data structures, for {{core_domain_entities}}
- Use value objects for concepts with no identity but defined by their attributes
- Implement domain events to communicate between bounded contexts
- Use aggregates to enforce consistency boundaries and transactional integrity


## BACKEND

### Guidelines for PHP/Laravel

#### REGISTERING MICROSERVICES
- Always register service bindings within service providers
- Use type-hinted interfaces for dependency injection
- Prefer automatic resolution over manual binding (#[Bind] attribute)
- Use constructor injection for required dependencies
- Accept the Application container for sub-dependencies
- -Never use the service locator pattern
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
- 
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
- Implement dark mode with the dark: variant
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
- Use $attributes bag for pass-through attributes (Components MUST render the {{ $attributes }} variable to allow parent-defined attributes to pass through to the root element-)


## Development Commands

### Livewire
```bash
php artisan livewire:make ComponentName
```
Creates a new Livewire component.

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
php artisan migrate              # Run migrations
php artisan migrate:fresh        # Fresh migration (drops all tables)
php artisan migrate:fresh --seed # Fresh migration with seeding
```
Default database is Mysql

### Rules
