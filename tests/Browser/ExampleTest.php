<?php

declare(strict_types=1);

/**
 * Example browser test to verify Pest Browser plugin installation
 *
 * This test demonstrates basic browser testing functionality.
 */
test('can visit login page', function () {
    $this->visit('/login')
        ->assertPathIs('/login')
        ->assertVisible('input[type="email"]')
        ->assertVisible('input[type="password"]');
});
