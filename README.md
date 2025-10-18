# GreenThumb

A web-based gardener's journal application with AI-powered search capabilities.

## Overview

GreenThumb solves the critical problem of searching through historical gardening entries by enabling gardeners to maintain a chronological record of their activities and quickly retrieve specific information through AI-powered natural language search. No more tedious clicking through months of entries to find when you planted those tomatoes or how often you watered the roses in July.

**Key Differentiator**: AI-powered natural language search that understands gardening-specific queries like "when did I plant tomatoes?" or "how often did I water the roses in July?"

## Features

- **Journal Entry Management**: Create, edit, and delete journal entries with titles and free-form text content
- **Weekly Calendar View**: Browse your gardening activities in a full-width weekly calendar with intuitive navigation
- **AI-Powered Search**: Query your journal entries using natural language powered by OpenRouter.ai
- **User Authentication**: Secure email/password authentication with email verification and password reset
- **Privacy-Focused**: All AI processing happens server-side with no user data used for model training
- **Mobile Responsive**: Access your journal from any device with a mobile-friendly interface
- **Entry Limit**: 50 entries per user account (MVP scope)

## Tech Stack

### Backend
- **PHP 8.3+**
- **Laravel 12.0** - Web application framework
- **Laravel Fortify 1.30** - Authentication with 2FA and email verification
- **Laravel Sanctum 4.2** - API token authentication
- **Eloquent ORM** - Database interactions
- **MySQL** - Production database
- **SQLite** - Testing database (in-memory)

### Frontend
- **Livewire v3** - Reactive components
- **Livewire Volt 1.7.0** - Single-file Livewire components
- **Livewire Flux 2.1.1** - Official UI component library
- **Tailwind CSS 4.0.7** - Utility-first CSS framework
- **Vite 7.0.4** - Build tool and asset bundler
- **Axios 1.7.4** - HTTP client

### Testing
- **Pest PHP 4.0** - Testing framework
- **Pest PHP Laravel Plugin 4.0** - Laravel-specific testing utilities
- **Pest PHP Livewire Plugin 4.0** - Livewire component testing
- **Pest PHP Browser Plugin 4.1** - End-to-end browser testing
- **Playwright 1.56.0** - Browser automation
- **FakerPHP 1.23** - Test data generation
- **Mockery 1.6** - Mocking library

### Development Tools
- **Laravel Tinker 2.10.1** - Interactive REPL
- **Laravel Pail 1.2.2** - Real-time log monitoring
- **Laravel Pint 1.18** - Code formatting (PSR-12)
- **Laravel Sail 1.41** - Docker development environment
- **Concurrently 9.0.1** - Parallel process runner

## Getting Started

### Prerequisites

- PHP 8.3 or higher
- Composer
- Node.js and npm
- MySQL (for production) or SQLite (for testing)
- OpenRouter.ai API key

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/greenThumb.git
   cd greenThumb
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure your `.env` file**
   - Set up your database connection
   - Add your OpenRouter.ai API key
   - Configure mail settings for email verification

6. **Run database migrations**
   ```bash
   php artisan migrate
   ```

7. **Build assets**
   ```bash
   npm run build
   ```

8. **Start the development server**
   ```bash
   composer dev
   ```

   This runs four concurrent processes:
   - Laravel development server (port 8000)
   - Queue worker
   - Laravel Pail for log monitoring
   - Vite dev server for hot module replacement

9. **Access the application**

   Open your browser and navigate to `http://localhost:8000`

## Available Scripts

### Composer Scripts

```bash
composer dev          # Start development server with all services
composer test         # Run Pest test suite
```

### NPM Scripts

```bash
npm run dev          # Start Vite dev server with hot reload
npm run build        # Build production assets
```

### Artisan Commands

```bash
# Livewire
php artisan livewire:make ComponentName

# Blade Components
php artisan make:component ComponentName
php artisan make:component ComponentName --view  # Anonymous component

# Database
php artisan migrate
php artisan migrate:fresh
php artisan migrate:fresh --seed

# Testing
php artisan test
php artisan test --filter test_name
php artisan test tests/Unit/UserTest.php
```

### Testing

```bash
# Run all tests
composer test

# Run browser tests
./vendor/bin/pest tests/Browser

# Browser tests with visible window (debugging)
./vendor/bin/pest tests/Browser --headed

# Browser tests in debug mode
./vendor/bin/pest tests/Browser --debug

# Run tests in parallel
./vendor/bin/pest tests/Browser --parallel

# Use specific browser
./vendor/bin/pest tests/Browser --browser=firefox
```

### Code Formatting

```bash
./vendor/bin/pint    # Format code using Laravel Pint (PSR-12)
```

## Project Scope

### In Scope (MVP)

- Text-only journal entries with title and content fields
- Single location per user (no multi-garden support)
- Web application only
- Email/password authentication
- AI search using general-purpose models via OpenRouter.ai
- Calendar-based weekly navigation
- Basic entry management (CRUD operations)
- 50 entry limit per user

### Out of Scope (MVP)

- Categories, tags, or entry classification systems
- Photo, video, or file attachments
- Social features or collaboration
- External platform integrations (weather APIs, plant databases)
- Mobile native applications (iOS/Android)
- Data export functionality
- Future-dated entries or planning features
- Multiple garden or location support
- Advanced search filters
- Entry limit warnings or upgrade paths
- Custom AI model training
- Rich text formatting
- Entry versioning or history tracking

## Project Status

**Current Phase**: MVP Development

### Success Metrics

**Primary Metric**: 80% of registered users perform at least 3 AI searches

**Secondary Metrics**:
- Weekly and monthly active users
- User registration completion rate
- Average AI searches per active user
- Search success rate
- Average entries created per user per week
- AI search response time
- Application uptime and availability

## Architecture

- **Frontend**: Livewire components with Flux UI library
- **Backend**: Laravel MVC architecture with Eloquent ORM
- **Authentication**: Laravel Fortify (email/password with 2FA)
- **AI Integration**: Server-side OpenRouter.ai API calls
- **Database**: MySQL (production), SQLite (testing)
- **Asset Pipeline**: Vite with Tailwind CSS v4

### Key Directories

```
app/
├── Http/Controllers/    # Application controllers
├── Livewire/           # Livewire component classes
├── Models/             # Eloquent models
└── View/Components/    # Blade component classes

resources/
├── views/
│   ├── components/     # Blade components
│   ├── livewire/       # Livewire component views
│   └── flux/           # Custom Flux components
├── js/                 # JavaScript files
└── css/                # CSS files

tests/
├── Unit/              # Unit tests
├── Feature/           # Feature tests
├── Browser/           # End-to-end browser tests
├── Integration/       # Integration tests
├── Performance/       # Performance tests
└── Security/          # Security tests
```

## Data Privacy

- All AI processing occurs server-side only
- User data is never transmitted to AI providers for model training
- Self-hosted deployment for complete data control
- User passwords are securely hashed
- User data is isolated (users cannot access other users' entries)
- Data encrypted at rest

## Contributing

This is an MVP project. Contributions should align with the defined scope and user stories outlined in the Product Requirements Document (`.ai/prd.md`).

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Documentation

- **Product Requirements**: `.ai/prd.md`
- **Tech Stack Details**: `.ai/tech-stack.md`
- **Coding Guidelines**: `CLAUDE.md`
- **Browser Testing Standards**: `.ai/rules/PestBrowserRules.md`

## Support

For issues, questions, or feature requests, please open an issue on GitHub.

---

**Built with Laravel, Livewire, and AI-powered intelligence to make gardening journaling effortless.**
