# Laravel Dusk ChromeDriver Management - Standards and Best Practices

## Installation Requirements

### Mandatory Prerequisites

1. Google Chrome must be installed on the system before using Laravel Dusk
2. The `laravel/dusk` package must be installed as a development dependency
3. Dusk's service provider must never be registered in production environments to prevent security vulnerabilities
4. The initial setup must include running the installation command to create the required directory structure

### Initial Setup Commands

1. Install the Dusk package using Composer:
   ```
   composer require laravel/dusk --dev
   ```

2. Execute the Dusk installation command:
   ```
   php artisan dusk:install
   ```

3. Set the `APP_URL` environment variable in the `.env` file to match the application's browser access URL

### Generated Directory Structure

1. The installation command creates the `tests/Browser` directory
2. An example Dusk test file is generated automatically
3. The Chrome Driver binary for the operating system is installed automatically

## ChromeDriver Installation Management

### Version Installation Rules

1. Always use the `dusk:chrome-driver` command to manage ChromeDriver installations
2. The default installation installs the latest version of ChromeDriver for the current operating system
3. Specific ChromeDriver versions can be installed by providing the version number as an argument
4. The `--all` flag installs the specified ChromeDriver version for all supported operating systems
5. The `--detect` flag automatically installs the ChromeDriver version matching the detected Chrome/Chromium version

### ChromeDriver Installation Commands

1. Install the latest ChromeDriver version:
   ```
   php artisan dusk:chrome-driver
   ```

2. Install a specific ChromeDriver version (e.g., version 86):
   ```
   php artisan dusk:chrome-driver 86
   ```

3. Install a specific version for all supported operating systems:
   ```
   php artisan dusk:chrome-driver --all
   ```

4. Install the version matching the detected Chrome/Chromium installation:
   ```
   php artisan dusk:chrome-driver --detect
   ```

## Binary Permissions

### Executable Requirements

1. ChromeDriver binaries must have executable permissions to function properly
2. If Dusk fails to run, verify that binaries in the vendor directory have proper permissions
3. Use the chmod command to set executable permissions when encountering execution problems

### Permission Configuration Command

1. Set executable permissions for all ChromeDriver binaries:
   ```
   chmod -R 0755 vendor/laravel/dusk/bin/
   ```

## File Path References

### Critical File Locations

1. ChromeDriver binaries are stored in: `vendor/laravel/dusk/bin/`
2. Browser tests are located in: `tests/Browser`
3. The base Dusk test case file is located at: `tests/DuskTestCase.php`
4. Test screenshots are saved to: `tests/Browser/screenshots`
5. Console output logs are saved to: `tests/Browser/console`
6. Page source files are saved to: `tests/Browser/source`

## Alternative Browser Configuration

### Custom Selenium Server Setup

1. To use a custom Selenium server, modify the `tests/DuskTestCase.php` file
2. Comment out the `startChromeDriver()` method call in the `prepare()` method to prevent automatic ChromeDriver startup
3. Modify the `driver()` method to connect to the custom Selenium server URL and port
4. Configure desired capabilities for the WebDriver based on the target browser

### Manual ChromeDriver Management

1. ChromeDriver can be started manually instead of using Dusk's automatic startup
2. When starting ChromeDriver manually, comment out the `startChromeDriver()` line in the `prepare()` method
3. If ChromeDriver runs on a non-default port (not 9515), update the port in the `driver()` method accordingly
4. The default ChromeDriver port is 9515

## Laravel Sail Integration

### Sail-Specific Requirements

1. When using Laravel Sail for local development, consult the Sail documentation for Dusk-specific configuration
2. Sail environments require special setup for configuring and running Dusk tests
3. Never assume standard Dusk configuration will work in Sail without reviewing the Sail documentation

## Test Execution Standards

### Running Tests

1. Use the `dusk` Artisan command to execute all browser tests:
   ```
   php artisan dusk
   ```

2. Re-run only failed tests using the fails command:
   ```
   php artisan dusk:fails
   ```

3. The dusk command accepts standard Pest/PHPUnit arguments for filtering tests
4. Group-specific tests can be executed using the `--group` flag:
   ```
   php artisan dusk --group=foo
   ```

## Continuous Integration Standards

### CI Environment Configuration

1. CI environments must set the `APP_URL` environment variable to `http://127.0.0.1:8000`
2. The built-in PHP development server should serve the application on port 8000 in CI environments
3. ChromeDriver must be upgraded to the correct version before running tests in CI
4. The ChromeDriver service must be started in the background before executing tests

### Required CI Commands Sequence

1. Copy the environment configuration file
2. Install Composer dependencies
3. Generate the application key
4. Upgrade or install the appropriate ChromeDriver version
5. Start ChromeDriver in the background
6. Start the Laravel development server in the background
7. Execute the Dusk test suite

## Test Generation

### Creating New Tests

1. Use the `dusk:make` Artisan command to generate new Dusk tests:
   ```
   php artisan dusk:make LoginTest
   ```

2. All generated tests are automatically placed in the `tests/Browser` directory
3. Test names should be descriptive and follow the naming convention `NameTest`

## Page Object Generation

### Creating Page Objects

1. Use the `dusk:page` Artisan command to generate page objects:
   ```
   php artisan dusk:page Login
   ```

2. All page objects are placed in: `tests/Browser/Pages`

## Component Generation

### Creating Reusable Components

1. Use the `dusk:component` Artisan command to generate components:
   ```
   php artisan dusk:component DatePicker
   ```

2. All components are placed in: `tests/Browser/Components`
3. Components should represent reusable UI elements that appear across multiple pages

## Environment-Specific Configuration

### Dusk Environment Files

1. Create environment-specific Dusk configuration files using the pattern: `.env.dusk.{environment}`
2. For local development, create: `.env.dusk.local`
3. Dusk automatically backs up the `.env` file and uses the Dusk-specific environment during test execution
4. The original `.env` file is restored after test completion

## Critical Security Rules

1. Never register Dusk's service provider in production environments
2. Only install Dusk as a development dependency (using `--dev` flag)
3. Arbitrary user authentication vulnerabilities can occur if Dusk is available in production
4. Production builds must exclude development dependencies to prevent Dusk exposure
