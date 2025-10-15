# Pest PHP Browser Testing - Coding Standards and Best Practices

## Installation Requirements

### Mandatory Installation Steps

1. Install the Pest Browser plugin using Composer with the dev dependency flag
2. Install Playwright npm package at the latest version
3. Run Playwright installation command to install browser binaries
4. Add the screenshots directory to version control ignore file to prevent committing test artifacts

### Required Console Commands

```bash
composer require pestphp/pest-plugin-browser --dev
npm install playwright@latest
npx playwright install
```

### Required File Path Configuration

1. Add `tests/Browser/Screenshots` to your `.gitignore` file

## Test Execution Standards

### Running Tests

1. Execute browser tests using the standard Pest test runner command
2. Use parallel execution option for improved test performance and reduced execution time
3. Enable debug mode when troubleshooting failing tests to pause execution and inspect browser state
4. Use headed mode option to visualize browser interactions during test runs

### Required Console Commands for Test Execution

```bash
./vendor/bin/pest
./vendor/bin/pest --parallel
./vendor/bin/pest --debug
./vendor/bin/pest --headed
```

## Browser Configuration Standards

### Browser Selection Rules

1. Chrome is used as the default browser when no browser specification is provided
2. Specify alternative browsers using the browser command-line option when needed
3. Configure default browser preference in the Pest configuration file for project-wide settings
4. Supported browsers include Chrome, Firefox, and Safari

### Browser Command-Line Options

```bash
./vendor/bin/pest --browser=firefox
./vendor/bin/pest --browser=safari
```

### Configuration File Path

1. Browser configuration must be set in `Pest.php` file

## Device and Viewport Standards

### Viewport Configuration Rules

1. Desktop viewport is used as the default viewport size
2. Use mobile viewport method when testing mobile-specific functionality
3. Specify exact device models when testing device-specific features
4. Available device presets include macbook14, iPhone14Pro, and other common devices

### Color Scheme Standards

1. Light color scheme is enforced by default for all tests
2. Enable dark mode explicitly when testing dark theme functionality
3. Color scheme configuration affects how the page renders during tests

## Navigation Standards

### Page Navigation Rules

1. Use the visit method as the primary entry point for accessing application URLs
2. Support multiple simultaneous page visits by passing array of URLs for parallel testing scenarios
3. Use the navigate method for subsequent navigation within the same browser context
4. Maintain browser context when navigating between pages to preserve state

## Element Location Standards

### Selector Strategy Rules

1. Locate elements using plain text for the most readable approach
2. Use CSS class selectors with dot notation for class-based selection
3. Use data-test attribute selectors with at-sign notation for test-specific element identification
4. Use ID selectors with hash notation for unique element selection
5. Prioritize data-test attributes for test stability and maintenance

## Timeout Configuration Standards

### Timeout Management Rules

1. Default timeout for element interactions is set to 5 seconds
2. Configure custom timeout values in the Pest configuration file for project-wide timeout settings
3. Adjust timeout values based on application performance characteristics and network conditions

### Configuration File Path

1. Timeout configuration must be set in `Pest.php` file

## Assertion Standards

### Element Content Assertions

1. Verify page title matches expected value using title assertion
2. Verify page title contains expected text using title contains assertion
3. Verify text presence on page using see assertion
4. Verify text absence on page using don't see assertion
5. Verify text presence within specific selector using see in assertion
6. Verify text absence within specific selector using don't see in assertion
7. Verify any text exists within selector using see anything in assertion
8. Verify no text exists within selector using see nothing in assertion

### Element Count Assertions

1. Verify exact number of elements matching selector using count assertion

### Source Code Assertions

1. Verify raw HTML source contains expected markup using source has assertion
2. Verify raw HTML source excludes unwanted markup using source missing assertion

### Link Assertions

1. Verify link presence on page using see link assertion
2. Verify link absence on page using don't see link assertion

### Form Element State Assertions

1. Verify checkbox checked state using checked assertion
2. Verify checkbox unchecked state using not checked assertion
3. Verify checkbox indeterminate state using indeterminate assertion
4. Verify radio button selected state using radio selected assertion
5. Verify radio button unselected state using radio not selected assertion
6. Verify dropdown selected value using selected assertion
7. Verify dropdown unselected value using not selected assertion

### Element Value Assertions

1. Verify element value matches expected value using value assertion
2. Verify element value does not match expected value using value is not assertion

### Element Attribute Assertions

1. Verify element attribute has specific value using attribute assertion
2. Verify element attribute is absent using attribute missing assertion
3. Verify element attribute contains specific value using attribute contains assertion
4. Verify element attribute excludes specific value using attribute doesn't contain assertion
5. Verify ARIA attribute values using aria attribute assertion
6. Verify data attribute values using data attribute assertion

### Element Visibility Assertions

1. Verify element is visible on page using visible assertion
2. Verify element exists in DOM using present assertion
3. Verify element does not exist in DOM using not present assertion
4. Verify element is not visible using missing assertion

### Element State Assertions

1. Verify field enabled state using enabled assertion
2. Verify field disabled state using disabled assertion
3. Verify button enabled state using button enabled assertion
4. Verify button disabled state using button disabled assertion

### URL Component Assertions

1. Verify complete URL matches expected value using url is assertion
2. Verify URL scheme matches expected protocol using scheme is assertion
3. Verify URL scheme does not match unwanted protocol using scheme is not assertion
4. Verify URL host matches expected domain using host is assertion
5. Verify URL host does not match unwanted domain using host is not assertion
6. Verify URL port matches expected port number using port is assertion
7. Verify URL port does not match unwanted port using port is not assertion
8. Verify URL path begins with expected string using path begins with assertion
9. Verify URL path ends with expected string using path ends with assertion
10. Verify URL path contains expected string using path contains assertion
11. Verify URL path matches exactly using path is assertion
12. Verify URL path does not match using path is not assertion
13. Verify query string parameter presence and optionally its value using query string has assertion
14. Verify query string parameter absence using query string missing assertion
15. Verify URL fragment matches expected value using fragment is assertion
16. Verify URL fragment begins with expected value using fragment begins with assertion
17. Verify URL fragment does not match expected value using fragment is not assertion

### Console and Error Assertions

1. Verify absence of both console logs and JavaScript errors using no smoke assertion
2. Verify absence of console logs using no console logs assertion
3. Verify absence of JavaScript errors using no JavaScript errors assertion

### JavaScript Assertions

1. Verify JavaScript expression evaluates to expected value using script assertion

### Screenshot Assertions

1. Verify screenshot matches expected baseline image using screenshot matches assertion
2. Support full page screenshots and diff visualization as optional parameters

## Element Interaction Standards

### Click Interactions

1. Use click method for activating links and buttons by text or selector
2. Click method accepts text, CSS selectors, data-test attributes, and ID selectors

### Text Retrieval

1. Use text method to extract visible text content from elements matching selector

### Attribute Retrieval

1. Use attribute method to extract specific attribute values from elements

### Keyboard Input

1. Use keys method for sending keyboard input including special keys and keyboard shortcuts
2. Use type method for entering text into form fields with automatic clearing
3. Use append method for adding text to existing field values without clearing

### Field Clearing

1. Use clear method to remove all content from input fields

### Form Selection

1. Use select method for choosing dropdown options
2. Support single and multiple selection scenarios with array values for multiple selections

### Radio Button Interaction

1. Use radio method to select specific radio button value within radio group

### Checkbox Interaction

1. Use check method to mark checkbox as checked
2. Use uncheck method to mark checkbox as unchecked
3. Support checkboxes with specific values as optional parameter

### File Upload

1. Use attach method to upload files by providing file path to file input element

### Button Interaction

1. Use press method to activate buttons by text or name
2. Use press and wait for method when button action requires additional loading time

### Drag and Drop

1. Use drag method to move elements between positions using source and target selectors

### Form Submission

1. Use submit method to submit the first form element found on page

### Value Retrieval

1. Use value method to get current value of form input elements

### JavaScript Execution

1. Use script method to execute arbitrary JavaScript code in page context and retrieve results

### Content Retrieval

1. Use content method to get complete HTML content of current page

### URL Retrieval

1. Use url method to get current page URL

### Wait Operations

1. Use wait method to pause test execution for specified number of seconds
2. Use wait for key method to pause test and open browser for manual inspection

## Debugging Standards

### Debug Mode Configuration

1. Enable debug mode via command-line flag to pause on test failure and inspect browser state
2. Use debug method within tests to pause execution at specific points
3. Use headed mode to visually observe browser interactions during test execution

### Required Console Commands for Debugging

```bash
./vendor/bin/pest --debug
./vendor/bin/pest --headed
```

### Configuration File Path

1. Headed mode default can be configured in `Pest.php` file

### Screenshot Debugging

1. Use screenshot method to capture current page state for visual debugging
2. Support full page screenshots as optional parameter
3. Support custom filename specification for organized debugging output

### Interactive Debugging

1. Use tinker method to open interactive PHP session in page context for runtime inspection and experimentation

## Continuous Integration Standards

### CI Configuration Requirements

1. Configure Node.js setup with LTS version in CI workflow
2. Install npm dependencies using ci command for consistent builds
3. Install Playwright browsers with dependencies flag in CI environment

### Required CI Workflow Steps

```yaml
- uses: actions/setup-node@v4
  with:
    node-version: lts/*

- name: Install dependencies
  run: npm ci

- name: Install Playwright Browsers
  run: npx playwright install --with-deps
```

## General Best Practices

### Test Organization

1. Organize browser tests in appropriate test directory structure
2. Use descriptive test names that clearly indicate test purpose
3. Group related assertions together for better test readability

### Performance Optimization

1. Run tests in parallel whenever possible to reduce total execution time
2. Configure appropriate timeout values to balance between test reliability and execution speed
3. Visit multiple pages simultaneously when testing scenarios require parallel page interactions

### Maintenance and Reliability

1. Prioritize data-test attributes over brittle CSS selectors for long-term test stability
2. Use appropriate assertion methods that match the specific verification need
3. Leverage framework capabilities for state management, event faking, and authentication assertions
4. Use no smoke assertion for comprehensive page health checks across multiple pages
