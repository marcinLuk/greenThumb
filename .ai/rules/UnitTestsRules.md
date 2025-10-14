# Laravel Testing Standards and Best Practices

## Test Directory Structure

1. All tests must be organized in the `tests/` directory at the root of your Laravel application
2. Feature tests must be placed in the `tests/Feature` directory
3. Unit tests must be placed in the `tests/Unit` directory
4. The `phpunit.xml` configuration file must be present at the application root
5. An `ExampleTest.php` file is provided by default in both Feature and Unit directories as a reference

## Test Types and Classification

### Unit Tests

1. Unit tests must focus on small, isolated portions of code
2. Unit tests should typically focus on a single method or function
3. Unit tests must not boot the Laravel application
4. Unit tests cannot access the application's database
5. Unit tests cannot access other framework services
6. All unit tests must use the `--unit` flag when creating via Artisan command

### Feature Tests

1. Feature tests must test larger portions of code and object interactions
2. Feature tests should test full HTTP requests to endpoints
3. Most of your test suite should consist of feature tests
4. Feature tests provide the highest confidence that the system functions as intended
5. Feature tests have full access to the Laravel application and framework services

## Creating Tests

1. All new tests must be created using the `make:test` Artisan command
2. Feature tests must be created with: `php artisan make:test TestName`
3. Unit tests must be created with: `php artisan make:test TestName --unit`
4. Test class names must end with the word "Test"
5. Test method names must start with `test_` or use the `@test` annotation
6. Custom test stubs must be published using stub publishing feature when needed

## Test Lifecycle Methods

1. If defining custom `setUp()` methods, you must call `parent::setUp()` at the start of your method
2. If defining custom `tearDown()` methods, you must call `parent::tearDown()` at the end of your method
3. Always invoke parent lifecycle methods to ensure proper framework initialization and cleanup

## Running Tests

### Basic Test Execution

1. Tests can be run using one of three commands:
    - `./vendor/bin/pest`
    - `./vendor/bin/phpunit`
    - `php artisan test`
2. The Artisan test runner (`php artisan test`) must be used for verbose test reports during development
3. Any arguments valid for Pest or PHPUnit can be passed to the Artisan test command

### Test Execution Options

1. Run specific test suites using: `php artisan test --testsuite=Feature`
2. Stop execution on first failure using: `php artisan test --stop-on-failure`
3. Combine multiple options as needed for targeted test runs

## Environment Configuration

1. Laravel automatically sets the configuration environment to `testing` when running tests
2. The testing environment is defined in the `phpunit.xml` file
3. Session driver is automatically set to `array` during testing
4. Cache driver is automatically set to `array` during testing
5. No session or cache data will persist while testing
6. Custom testing environment variables must be defined in `phpunit.xml`
7. Configuration cache must be cleared using `php artisan config:clear` before running tests after configuration changes

### Testing Environment File

1. A `.env.testing` file may be created in the project root
2. The `.env.testing` file takes precedence over `.env` when running tests
3. The `.env.testing` file is used when executing Artisan commands with the `--env=testing` option

## Parallel Testing

### Setup and Configuration

1. The `brianium/paratest` package must be installed as a dev dependency for parallel testing
2. Install parallel testing with: `composer require brianium/paratest --dev`
3. Enable parallel testing using: `php artisan test --parallel`
4. Laravel creates processes equal to available CPU cores by default
5. Adjust process count using: `php artisan test --parallel --processes=4`
6. Some Pest/PHPUnit options are not available in parallel mode (such as `--do-not-cache-result`)

### Parallel Testing and Databases

1. Laravel automatically creates and migrates test databases for each parallel process
2. Test databases are suffixed with process tokens (e.g., `your_db_test_1`, `your_db_test_2`)
3. A primary database connection must be configured for parallel testing
4. Test databases persist between test runs by default
5. Recreate test databases using: `php artisan test --parallel --recreate-databases`

### Parallel Testing Hooks

1. Use the `ParallelTesting` facade to prepare resources for parallel execution
2. Implement `setUpProcess()` for process-level setup operations
3. Implement `tearDownProcess()` for process-level cleanup operations
4. Implement `setUpTestCase()` for test case-level setup operations
5. Implement `tearDownTestCase()` for test case-level cleanup operations
6. Implement `setUpTestDatabase()` for database seeding during parallel test database creation
7. Parallel testing hooks must be registered in a service provider (typically `App\Providers\AppServiceProvider`)
8. Access the parallel process token using `ParallelTesting::token()` method

## Test Coverage

### Measuring Coverage

1. Generate test coverage reports using: `php artisan test --coverage`
2. Coverage reports must be used to identify untested application code
3. Review coverage reports regularly to maintain code quality

### Coverage Thresholds

1. Enforce minimum coverage thresholds using: `php artisan test --coverage --min=80.3`
2. The test suite will fail if the minimum threshold is not met
3. Coverage thresholds should be set based on project requirements and maintained consistently

## Test Performance

1. Profile slow tests using: `php artisan test --profile`
2. The profile option displays the ten slowest tests
3. Regularly review and optimize slow tests to improve test suite performance
4. Investigate and refactor tests that consistently appear in the slowest tests list

## Mandatory Testing Practices

1. Every new feature must include corresponding feature tests
2. Complex business logic must be covered by unit tests
3. All tests must pass before code can be merged to main branches
4. Tests must be run before every deployment
5. Test files must follow Laravel's default naming conventions
6. Never commit commented-out tests - remove or fix them
7. Tests must be independent and not rely on execution order
8. Tests must clean up after themselves to avoid affecting other tests
9. Database transactions should be used to roll back database changes in tests when appropriate
10. External API calls must be mocked in tests to avoid dependencies on external services
