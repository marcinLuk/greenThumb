<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;

uses(RefreshDatabase::class);

/**
 * Login Page Browser Tests
 *
 * Tests cover all aspects of the user login flow including:
 * - Basic UI and accessibility
 * - Successful login scenarios (redirects to dashboard)
 * - Failed login validation
 * - Rate limiting
 * - Navigation flows
 *
 * Following standards from .ai/rules/PestBrowserRules.md
 * Screenshots are captured at key points for debugging and documentation
 */

// ============================================================================
// 1. BASIC UI & ACCESSIBILITY TESTS
// ============================================================================

test('login page loads and displays all required elements', function () {
    $this->visit('/login')
        ->assertPathIs('/login')
        ->assertSee('Log in to your account')
        ->assertVisible('@login-email')
        ->assertVisible('@login-password')
        ->assertVisible('@login-remember')
        ->assertVisible('@login-button')
        ->assertSee('Remember me')
        ->assertSee('Forgot your password?')
        ->assertSee("Don't have an account?")
        ->assertSee('Sign up')
        ->screenshot(true, 'login-page-initial-load');
});

test('login form elements have correct attributes', function () {
    $this->visit('/login')
        ->assertAttribute('@login-email', 'type', 'email')
        ->assertAttribute('@login-email', 'required', 'required')
        ->assertAttribute('@login-password', 'type', 'password')
        ->assertAttribute('@login-password', 'required', 'required');
});

// ============================================================================
// 2. SUCCESSFUL LOGIN FLOW TESTS - ALL REDIRECT TO DASHBOARD
// ============================================================================

test('user can login with valid credentials and sees dashboard', function () {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $this->visit('/login')
        ->screenshot(true, 'login-before-credentials')
        ->type('@login-email', 'test@example.com')
        ->type('@login-password', 'password123')
        ->screenshot(true, 'login-credentials-entered')
        ->click('@login-button')
        ->assertPathIs('/dashboard')
        ->screenshot(true, 'login-success-dashboard');

    assertAuthenticated();
});

test('successful login redirects to dashboard', function () {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('SecurePass123'),
    ]);

    $this->visit('/login')
        ->type('@login-email', 'user@example.com')
        ->type('@login-password', 'SecurePass123')
        ->press('Log in')
        ->assertPathIs('/dashboard');

    assertAuthenticated();
});

test('remember me checkbox persists user session', function () {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'remember@example.com',
        'password' => bcrypt('password123'),
    ]);

    $this->visit('/login')
        ->type('@login-email', 'remember@example.com')
        ->type('@login-password', 'password123')
        ->check('@login-remember')
        ->screenshot(true, 'login-remember-me-checked')
        ->click('@login-button')
        ->assertPathIs('/dashboard');

    assertAuthenticated();
});

// ============================================================================
// 3. FAILED LOGIN TESTS
// ============================================================================

test('login fails with invalid credentials', function () {
    User::factory()->withoutTwoFactor()->create([
        'email' => 'valid@example.com',
        'password' => bcrypt('correctPassword'),
    ]);

    $this->visit('/login')
        ->type('@login-email', 'valid@example.com')
        ->type('@login-password', 'wrongPassword')
        ->click('@login-button')
        ->assertPathIs('/login')
        ->assertSee('These credentials do not match our records')
        ->screenshot(true, 'login-failed-invalid-credentials');

    assertGuest();
});

test('login fails with non-existent email', function () {
    $this->visit('/login')
        ->type('@login-email', 'nonexistent@example.com')
        ->type('@login-password', 'anyPassword')
        ->click('@login-button')
        ->assertPathIs('/login')
        ->assertSee('These credentials do not match our records')
        ->screenshot(true, 'login-failed-nonexistent-email');

    assertGuest();
});

test('login form validates required email field', function () {
    $this->visit('/login')
        ->type('@login-password', 'password123')
        ->click('@login-button')
        ->assertPathIs('/login')
        ->screenshot(true, 'login-validation-email-required');

    assertGuest();
});

test('login form validates required password field', function () {
    $this->visit('/login')
        ->type('@login-email', 'test@example.com')
        ->click('@login-button')
        ->assertPathIs('/login')
        ->screenshot(true, 'login-validation-password-required');

    assertGuest();
});

test('login form validates email format', function () {
    $this->visit('/login')
        ->type('@login-email', 'invalid-email-format')
        ->type('@login-password', 'password123')
        ->click('@login-button')
        ->assertPathIs('/login')
        ->screenshot(true, 'login-validation-email-format');

    assertGuest();
});

// ============================================================================
// 4. RATE LIMITING TESTS
// ============================================================================

test('login rate limits after multiple failed attempts', function () {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'ratelimit@example.com',
        'password' => bcrypt('correctPassword'),
    ]);

    // Attempt 5 failed logins to trigger rate limiting
    for ($i = 0; $i < 5; $i++) {
        $this->visit('/login')
            ->type('@login-email', 'ratelimit@example.com')
            ->type('@login-password', 'wrongPassword')
            ->click('@login-button')
            ->assertPathIs('/login');
    }

    // The 6th attempt should show rate limit error
    $this->visit('/login')
        ->type('@login-email', 'ratelimit@example.com')
        ->type('@login-password', 'wrongPassword')
        ->click('@login-button')
        ->assertPathIs('/login')
        ->assertSee('Too many login attempts')
        ->screenshot(true, 'login-rate-limited');

    assertGuest();
});

test('rate limit error message includes wait time', function () {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'throttle@example.com',
        'password' => bcrypt('correctPassword'),
    ]);

    // Trigger rate limiting
    for ($i = 0; $i < 5; $i++) {
        $this->visit('/login')
            ->type('@login-email', 'throttle@example.com')
            ->type('@login-password', 'wrongPassword')
            ->click('@login-button');
    }

    // Check for wait time message
    $this->visit('/login')
        ->type('@login-email', 'throttle@example.com')
        ->type('@login-password', 'wrongPassword')
        ->click('@login-button')
        ->assertSee('seconds')
        ->screenshot(true, 'login-rate-limit-wait-time');
});

// ============================================================================
// 5. NAVIGATION TESTS
// ============================================================================

test('can navigate to forgot password page', function () {
    $this->visit('/login')
        ->screenshot(true, 'login-before-forgot-password-click')
        ->click('Forgot your password?')
        ->assertPathIs('/forgot-password')
        ->screenshot(true, 'forgot-password-page');
});

test('can navigate to register page', function () {
    $this->visit('/login')
        ->screenshot(true, 'login-before-register-click')
        ->click('Sign up')
        ->assertPathIs('/register')
        ->assertSee('Create an account')
        ->screenshot(true, 'register-page');
});

test('unauthenticated users are redirected to login when accessing protected routes', function () {
    $this->visit('/dashboard')
        ->assertPathIs('/login')
        ->screenshot(true, 'protected-route-redirect-to-login');

    assertGuest();
});

test('authenticated users cannot access login page and are redirected to dashboard', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user)
        ->visit('/login')
        ->assertPathIs('/dashboard')
        ->screenshot(true, 'authenticated-user-login-redirect');
});

// ============================================================================
// 6. CONSOLE AND ERROR HANDLING
// ============================================================================

test('login page loads without javascript errors', function () {
    $this->visit('/login')
        ->assertNoJavaScriptErrors();
});

test('login page loads without console logs', function () {
    $this->visit('/login')
        ->assertNoSmoke();
});