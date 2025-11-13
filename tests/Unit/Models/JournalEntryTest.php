<?php

use App\Models\JournalEntry;
use App\Models\User;

uses()->group('models');

/**
 * UT-ENTRY-001: Entry can be created with valid data
 */
test('entry can be created with valid data', function () {
    $user = User::factory()->create();

    $entry = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test Entry',
        'content' => 'This is test content',
        'entry_date' => '2025-10-01',
    ]);

    expect($entry)->toBeInstanceOf(JournalEntry::class);

    $this->assertDatabaseHas('journal_entries', [
        'id' => $entry->id,
        'user_id' => $user->id,
        'title' => 'Test Entry',
        'content' => 'This is test content',
        'entry_date' => '2025-10-01',
    ]);
});

/**
 * UT-ENTRY-002: Entry belongs to user relationship
 */
test('entry belongs to user', function () {
    $user = User::factory()->create();
    $entry = JournalEntry::factory()->create(['user_id' => $user->id]);

    expect($entry->user)->toBeInstanceOf(User::class)
        ->and($entry->user->id)->toBe($user->id)
        ->and($entry->user->email)->toBe($user->email);
});
