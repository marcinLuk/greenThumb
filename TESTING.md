# Testing Guide for GreenThumb

This document provides information on setting up and running tests for the GreenThumb application.

## Test Environment Setup

The project uses the following testing tools:

- **PHPUnit** (v11.5.3) - Core testing framework for unit and feature tests
- **Laravel Dusk** (v8.3) - Browser automation and end-to-end testing
- **Mockery** (v1.6) - Mocking framework for unit tests
- **FakerPHP** (v1.23) - Test data generation

## Test Structure

Tests are organized into the following directories:

- `tests/Unit/` - Unit tests for models, services, and helpers
- `tests/Feature/` - Feature tests for controllers, middleware, and application flows
- `tests/Browser/` - Laravel Dusk browser tests for end-to-end testing
- `tests/Integration/` - Integration tests for external services (OpenRouter API, email delivery)
- `tests/Security/` - Security-focused tests (authentication, data isolation, privacy)
- `tests/Performance/` - Performance and load tests

## Running Tests

### Standard Tests (Unit + Feature)

```bash
composer test
```

Or directly with PHPUnit:

```bash
php artisan test
```

### Browser Tests (Dusk)

```bash
php artisan dusk
```

### Running Specific Tests

Run a specific test file:
```bash
php artisan test tests/Feature/Auth/RegistrationTest.php
```

Run a specific test method:
```bash
php artisan test --filter test_user_can_register
```

Run tests in a specific directory:
```bash
php artisan test tests/Unit
```

### Running Tests with Code Coverage

To generate code coverage reports, you need to install a code coverage driver (PCOV or Xdebug).

#### Installing PCOV (Recommended - Faster)

```bash
pecl install pcov
```

Then add to your `php.ini`:
```ini
extension=pcov.so
pcov.enabled=1
```

Verify installation:
```bash
php -m | grep pcov
```

#### Installing Xdebug (Alternative)

```bash
pecl install xdebug
```

Then add to your `php.ini`:
```ini
zend_extension=xdebug.so
xdebug.mode=coverage
```

Verify installation:
```bash
php -m | grep xdebug
```

#### Running Tests with Coverage

Once a coverage driver is installed:

```bash
php artisan test --coverage
```

Or generate HTML coverage report:
```bash
php artisan test --coverage-html tests/coverage/html
```

The coverage reports will be saved in:
- HTML: `tests/coverage/html/index.html`
- Text: `tests/coverage/coverage.txt`

### Test Coverage Targets

According to the test plan, aim for:
- **80%+** overall code coverage
- **95%+** coverage for critical business logic (models, services)
- **90%+** coverage for controllers

## Test Database

Tests use an in-memory SQLite database by default (configured in `phpunit.xml`):
- Database connection: `sqlite`
- Database: `:memory:`

For Dusk tests, a persistent SQLite database is used (`database/database.sqlite`).

## Test Environment Configuration

### Standard Tests (.env configuration in phpunit.xml)
- `APP_ENV=testing`
- `DB_CONNECTION=sqlite`
- `DB_DATABASE=:memory:`
- `MAIL_MAILER=array`
- `QUEUE_CONNECTION=sync`

### Browser Tests (.env.dusk.local)
- `APP_ENV=dusk.local`
- `APP_URL=http://127.0.0.1:8000`
- `DB_CONNECTION=sqlite`
- `QUEUE_CONNECTION=sync`
- `CACHE_STORE=array`

## Factories and Seeders

The project includes factories for generating test data:

- `UserFactory` - Creates test users with verified/unverified states
- `JournalEntryFactory` - Creates journal entries with various dates and content
- `SearchAnalyticsFactory` - Creates search analytics records

Test seeders:
- `TestUserSeeder` - Seeds users for testing (at limit, various states)
- `JournalEntrySeeder` - Seeds journal entries across multiple dates

## Browser Testing with Dusk

### Prerequisites

Chrome/Chromium browser should be installed. Dusk automatically downloads ChromeDriver.

### Running the Development Server for Dusk

Before running Dusk tests, start the Laravel development server:

```bash
php artisan serve
```

Then in another terminal:

```bash
php artisan dusk
```

### Dusk Configuration

Dusk configuration is in `tests/DuskTestCase.php`. The base URL is set to `http://127.0.0.1:8000` by default.

## Continuous Integration

The project should be configured with GitHub Actions or similar CI/CD to run tests automatically:

1. Run unit tests
2. Run feature tests
3. Run browser tests
4. Generate and upload coverage reports

## Troubleshooting

### "Class not found" errors
Run:
```bash
composer dump-autoload
```

### Browser tests failing
- Ensure the development server is running (`php artisan serve`)
- Check that ChromeDriver is installed: `php artisan dusk:chrome-driver --detect`
- Clear browser cache: `php artisan dusk:chrome-driver --update`

### Coverage not generating
- Verify PCOV or Xdebug is installed: `php -m | grep -E 'pcov|xdebug'`
- Ensure the coverage driver is enabled in php.ini

## Writing Tests

### Test Organization

Follow this structure:
- Place unit tests in `tests/Unit/`
- Place feature tests in `tests/Feature/`
- Place browser tests in `tests/Browser/`
- Use descriptive test method names: `test_user_can_register_with_valid_data()`
- Add `@group` annotations for test organization

### Best Practices

1. **Arrange-Act-Assert**: Structure tests with clear setup, action, and verification
2. **One assertion per test**: Keep tests focused
3. **Use factories**: Generate test data with factories instead of manual creation
4. **Mock external services**: Mock API calls to OpenRouter and other external services
5. **Clean up**: Use `RefreshDatabase` trait to reset database between tests
6. **Isolate tests**: Tests should not depend on each other

## Additional Resources

- [Laravel Testing Documentation](https://laravel.com/docs/12.x/testing)
- [Laravel Dusk Documentation](https://laravel.com/docs/12.x/dusk)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Test Plan](.ai/tets-plan.md) - Comprehensive test plan with 200+ test cases
