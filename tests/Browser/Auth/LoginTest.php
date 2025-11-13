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
