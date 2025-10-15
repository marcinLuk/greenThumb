# Tech Stack for GreenThumb MVP

## Core Framework & Runtime
- **PHP ^8.3**
- **Laravel Framework ^12.0**
- **Eloquent ORM** for database interactions

## Frontend & UI
- **Livewire v3** and **Livewire Volt ^1.7.0** for reactive components
- **Livewire Flux ^2.1.1** for UI components
- **Tailwind CSS ^4.0.7** for styling
- **@tailwindcss/vite ^4.1.11** for Tailwind integration with Vite
- **Autoprefixer ^10.4.20** for CSS vendor prefixing

## Build Tools & Asset Management
- **Vite ^7.0.4** for asset building and hot module replacement
- **Laravel Vite Plugin ^2.0** for Laravel integration
- **Concurrently ^9.0.1** for running parallel development processes

## Authentication & Security
- **Laravel Fortify ^1.30** for authentication (2FA and email verification enabled)
- **Laravel Sanctum ^4.2** for API token authentication

## HTTP Client & APIs
- **Axios ^1.7.4** for HTTP requests
- **OpenRouter.ai** for AI-powered search functionality

## Database
- **MySQL** (production environment)
- **SQLite** (testing environment, in-memory)

## Testing & Quality Assurance
- **Pest PHP ^4.0** for testing framework
- **Pest PHP Laravel Plugin ^4.0** for Laravel-specific testing
- **Pest PHP Livewire Plugin ^4.0** for Livewire component testing
- **Pest PHP Browser Plugin ^4.1** for end-to-end browser testing
- **Playwright ^1.56.0** for browser automation
- **FakerPHP ^1.23** for generating fake test data
- **Mockery ^1.6** for mocking in tests
- **Nunomaduro Collision ^8.6** for beautiful error reporting

## Development Tools
- **Laravel Tinker ^2.10.1** for REPL and interactive shell
- **Laravel Pail ^1.2.2** for real-time log monitoring
- **Laravel Pint ^1.18** for code formatting (PSR-12 style)
- **Laravel Sail ^1.41** for Docker-based development environment

## Platform-Specific Dependencies (Linux x64)
- **@rollup/rollup-linux-x64-gnu 4.9.5** (optional)
- **@tailwindcss/oxide-linux-x64-gnu ^4.0.1** (optional)
- **lightningcss-linux-x64-gnu ^1.29.1** (optional)
