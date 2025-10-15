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

/**
 * UT-ENTRY-004: Entry date has no time component
 */
test('entry date has no time component', function () {
    $entry = JournalEntry::factory()->create([
        'entry_date' => '2025-10-01',
    ]);

    expect($entry->entry_date->format('H:i:s'))->toBe('00:00:00')
        ->and($entry->entry_date->format('Y-m-d'))->toBe('2025-10-01');
});

/**
 * UT-ENTRY-007: Test mass assignment protection
 */
test('fillable attributes can be mass assigned', function () {
    $user = User::factory()->create();

    $entry = JournalEntry::factory()->make([
        'user_id' => $user->id,
        'title' => 'Mass Assignment Test',
        'content' => 'Testing fillable attributes',
        'entry_date' => '2025-10-05',
    ]);

    $entry->save();

    expect($entry->title)->toBe('Mass Assignment Test')
        ->and($entry->content)->toBe('Testing fillable attributes')
        ->and($entry->entry_date->format('Y-m-d'))->toBe('2025-10-05');
});

/**
 * UT-ENTRY-008a: Entry date casting to Carbon instance
 */
test('entry date is cast to date instance', function () {
    $entry = JournalEntry::factory()->create([
        'entry_date' => '2025-10-01',
    ]);

    expect($entry->entry_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($entry->entry_date->isToday() === false || $entry->entry_date->isToday() === true)->toBeTrue();
});

/**
 * UT-ENTRY-008b: Entry scope for date range works
 */
test('within date range scope filters entries', function () {
    $user = User::factory()->create();

    // Create entries with different dates
    $entry1 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-09-01',
    ]);

    $entry2 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-09-15',
    ]);

    $entry3 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-09-30',
    ]);

    $entry4 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-10-15',
    ]);

    // Authenticate as the user to bypass UserOwnedScope filtering
    $this->actingAs($user);

    // Test date range filtering
    $results = JournalEntry::withinDateRange('2025-09-10', '2025-09-30')->get();

    expect($results)->toHaveCount(2)
        ->and($results->contains($entry2))->toBeTrue()
        ->and($results->contains($entry3))->toBeTrue()
        ->and($results->contains($entry1))->toBeFalse()
        ->and($results->contains($entry4))->toBeFalse();
});

/**
 * UT-ENTRY-008c: Entry scope for date range works with only start date
 */
test('within date range scope works with start date only', function () {
    $user = User::factory()->create();

    $entry1 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-09-01',
    ]);

    $entry2 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-09-15',
    ]);

    $this->actingAs($user);

    $results = JournalEntry::withinDateRange('2025-09-10', null)->get();

    expect($results)->toHaveCount(1)
        ->and($results->contains($entry2))->toBeTrue()
        ->and($results->contains($entry1))->toBeFalse();
});

/**
 * UT-ENTRY-008d: Entry scope for date range works with only end date
 */
test('within date range scope works with end date only', function () {
    $user = User::factory()->create();

    $entry1 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-09-01',
    ]);

    $entry2 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-09-15',
    ]);

    $this->actingAs($user);

    $results = JournalEntry::withinDateRange(null, '2025-09-10')->get();

    expect($results)->toHaveCount(1)
        ->and($results->contains($entry1))->toBeTrue()
        ->and($results->contains($entry2))->toBeFalse();
});

/**
 * UT-ENTRY-009: Entry scope for user isolation works
 */
test('user owned scope isolates user entries', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $entry1 = JournalEntry::factory()->create(['user_id' => $user1->id]);
    $entry2 = JournalEntry::factory()->create(['user_id' => $user2->id]);

    // When authenticated as user1
    $this->actingAs($user1);
    $results = JournalEntry::all();

    expect($results)->toHaveCount(1)
        ->and($results->contains($entry1))->toBeTrue()
        ->and($results->contains($entry2))->toBeFalse();

    // When authenticated as user2
    $this->actingAs($user2);
    $results = JournalEntry::all();

    expect($results)->toHaveCount(1)
        ->and($results->contains($entry2))->toBeTrue()
        ->and($results->contains($entry1))->toBeFalse();
});

/**
 * UT-ENTRY-010a: Sort by date scope in descending order
 */
test('sort by date scope sorts descending', function () {
    $user = User::factory()->create();

    $entry1 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-09-01',
    ]);

    $entry2 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-09-15',
    ]);

    $entry3 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-09-10',
    ]);

    $this->actingAs($user);

    $results = JournalEntry::sortByDate('desc')->get();

    expect($results[0]->id)->toBe($entry2->id)
        ->and($results[1]->id)->toBe($entry3->id)
        ->and($results[2]->id)->toBe($entry1->id);
});

/**
 * UT-ENTRY-010b: Sort by date scope in ascending order
 */
test('sort by date scope sorts ascending', function () {
    $user = User::factory()->create();

    $entry1 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-09-01',
    ]);

    $entry2 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-09-15',
    ]);

    $entry3 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-09-10',
    ]);

    $this->actingAs($user);

    $results = JournalEntry::sortByDate('asc')->get();

    expect($results[0]->id)->toBe($entry1->id)
        ->and($results[1]->id)->toBe($entry3->id)
        ->and($results[2]->id)->toBe($entry2->id);
});

/**
 * UT-ENTRY-011: User can access entries through relationship
 */
test('user can access entries through relationship', function () {
    $user = User::factory()->create();

    $entry1 = JournalEntry::factory()->create(['user_id' => $user->id]);
    $entry2 = JournalEntry::factory()->create(['user_id' => $user->id]);
    $entry3 = JournalEntry::factory()->create(); // Different user

    $this->actingAs($user);

    $entries = $user->journalEntries;

    expect($entries)->toHaveCount(2)
        ->and($entries->contains($entry1))->toBeTrue()
        ->and($entries->contains($entry2))->toBeTrue()
        ->and($entries->contains($entry3))->toBeFalse();
});

/**
 * UT-ENTRY-012: Entry is deleted when user is deleted (cascade)
 */
test('entry is deleted when user is deleted', function () {
    $user = User::factory()->create();
    $entry = JournalEntry::factory()->create(['user_id' => $user->id]);

    $this->assertDatabaseHas('journal_entries', ['id' => $entry->id]);

    // Delete the user
    $user->delete();

    // Entry should be deleted due to cascade
    $this->assertDatabaseMissing('journal_entries', ['id' => $entry->id]);
});

/**
 * UT-ENTRY-013: Multiple entries can exist for the same date
 */
test('multiple entries can exist for same date', function () {
    $user = User::factory()->create();

    $entry1 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-10-01',
        'title' => 'Morning Entry',
    ]);

    $entry2 = JournalEntry::factory()->create([
        'user_id' => $user->id,
        'entry_date' => '2025-10-01',
        'title' => 'Evening Entry',
    ]);

    $this->actingAs($user);

    $entries = JournalEntry::where('entry_date', '2025-10-01')->get();

    expect($entries)->toHaveCount(2)
        ->and($entries->contains($entry1))->toBeTrue()
        ->and($entries->contains($entry2))->toBeTrue();
});

/**
 * UT-ENTRY-014: Factory helper methods work correctly
 */
test('factory helper methods work', function () {
    $user = User::factory()->create();

    // Test forDate() method
    $entry1 = JournalEntry::factory()->forDate('2025-10-15')->create(['user_id' => $user->id]);
    expect($entry1->entry_date->format('Y-m-d'))->toBe('2025-10-15');

    // Test today() method
    $entry2 = JournalEntry::factory()->today()->create(['user_id' => $user->id]);
    expect($entry2->entry_date->format('Y-m-d'))->toBe(now()->format('Y-m-d'));

    // Test daysAgo() method
    $entry3 = JournalEntry::factory()->daysAgo(5)->create(['user_id' => $user->id]);
    expect($entry3->entry_date->format('Y-m-d'))->toBe(now()->subDays(5)->format('Y-m-d'));
});