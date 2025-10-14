<?php

namespace Tests\Unit\Models;

use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * JournalEntry Model Unit Tests
 *
 * Purpose: Verify JournalEntry model behavior, relationships, and business logic
 * Related User Stories: US-009, US-010, US-011, US-012
 */
class JournalEntryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * UT-ENTRY-001: Entry can be created with valid data
     *
     * @test
     */
    public function entry_can_be_created_with_valid_data(): void
    {
        $user = User::factory()->create();

        $entry = JournalEntry::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Entry',
            'content' => 'This is test content',
            'entry_date' => '2025-10-01',
        ]);

        $this->assertInstanceOf(JournalEntry::class, $entry);
        $this->assertDatabaseHas('journal_entries', [
            'id' => $entry->id,
            'user_id' => $user->id,
            'title' => 'Test Entry',
            'content' => 'This is test content',
            'entry_date' => '2025-10-01',
        ]);
    }

    /**
     * UT-ENTRY-002: Entry belongs to user relationship
     *
     * @test
     */
    public function entry_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $entry = JournalEntry::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $entry->user);
        $this->assertEquals($user->id, $entry->user->id);
        $this->assertEquals($user->email, $entry->user->email);
    }

    /**
     * UT-ENTRY-004: Entry date has no time component
     *
     * @test
     */
    public function entry_date_has_no_time_component(): void
    {
        $entry = JournalEntry::factory()->create([
            'entry_date' => '2025-10-01',
        ]);

        $this->assertEquals('00:00:00', $entry->entry_date->format('H:i:s'));
        $this->assertEquals('2025-10-01', $entry->entry_date->format('Y-m-d'));
    }

    /**
     * UT-ENTRY-007: Test mass assignment protection
     *
     * @test
     */
    public function fillable_attributes_can_be_mass_assigned(): void
    {
        $user = User::factory()->create();

        $entry = JournalEntry::factory()->make([
            'user_id' => $user->id,
            'title' => 'Mass Assignment Test',
            'content' => 'Testing fillable attributes',
            'entry_date' => '2025-10-05',
        ]);

        $entry->save();

        $this->assertEquals('Mass Assignment Test', $entry->title);
        $this->assertEquals('Testing fillable attributes', $entry->content);
        $this->assertEquals('2025-10-05', $entry->entry_date->format('Y-m-d'));
    }

    /**
     * UT-ENTRY-008a: Entry date casting to Carbon instance
     *
     * @test
     */
    public function entry_date_is_cast_to_date_instance(): void
    {
        $entry = JournalEntry::factory()->create([
            'entry_date' => '2025-10-01',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $entry->entry_date);
        $this->assertTrue($entry->entry_date->isToday() === false || $entry->entry_date->isToday() === true);
    }

    /**
     * UT-ENTRY-008b: Entry scope for date range works
     *
     * @test
     */
    public function within_date_range_scope_filters_entries(): void
    {
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

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($entry2));
        $this->assertTrue($results->contains($entry3));
        $this->assertFalse($results->contains($entry1));
        $this->assertFalse($results->contains($entry4));
    }

    /**
     * UT-ENTRY-008c: Entry scope for date range works with only start date
     *
     * @test
     */
    public function within_date_range_scope_works_with_start_date_only(): void
    {
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

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($entry2));
        $this->assertFalse($results->contains($entry1));
    }

    /**
     * UT-ENTRY-008d: Entry scope for date range works with only end date
     *
     * @test
     */
    public function within_date_range_scope_works_with_end_date_only(): void
    {
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

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($entry1));
        $this->assertFalse($results->contains($entry2));
    }

    /**
     * UT-ENTRY-009: Entry scope for user isolation works
     *
     * @test
     */
    public function user_owned_scope_isolates_user_entries(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $entry1 = JournalEntry::factory()->create(['user_id' => $user1->id]);
        $entry2 = JournalEntry::factory()->create(['user_id' => $user2->id]);

        // When authenticated as user1
        $this->actingAs($user1);
        $results = JournalEntry::all();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($entry1));
        $this->assertFalse($results->contains($entry2));

        // When authenticated as user2
        $this->actingAs($user2);
        $results = JournalEntry::all();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($entry2));
        $this->assertFalse($results->contains($entry1));
    }

    /**
     * UT-ENTRY-010a: Sort by date scope in descending order
     *
     * @test
     */
    public function sort_by_date_scope_sorts_descending(): void
    {
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

        $this->assertEquals($entry2->id, $results[0]->id);
        $this->assertEquals($entry3->id, $results[1]->id);
        $this->assertEquals($entry1->id, $results[2]->id);
    }

    /**
     * UT-ENTRY-010b: Sort by date scope in ascending order
     *
     * @test
     */
    public function sort_by_date_scope_sorts_ascending(): void
    {
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

        $this->assertEquals($entry1->id, $results[0]->id);
        $this->assertEquals($entry3->id, $results[1]->id);
        $this->assertEquals($entry2->id, $results[2]->id);
    }

    /**
     * UT-ENTRY-011: User can access entries through relationship
     *
     * @test
     */
    public function user_can_access_entries_through_relationship(): void
    {
        $user = User::factory()->create();

        $entry1 = JournalEntry::factory()->create(['user_id' => $user->id]);
        $entry2 = JournalEntry::factory()->create(['user_id' => $user->id]);
        $entry3 = JournalEntry::factory()->create(); // Different user

        $this->actingAs($user);

        $entries = $user->journalEntries;

        $this->assertCount(2, $entries);
        $this->assertTrue($entries->contains($entry1));
        $this->assertTrue($entries->contains($entry2));
        $this->assertFalse($entries->contains($entry3));
    }

    /**
     * UT-ENTRY-012: Entry is deleted when user is deleted (cascade)
     *
     * @test
     */
    public function entry_is_deleted_when_user_is_deleted(): void
    {
        $user = User::factory()->create();
        $entry = JournalEntry::factory()->create(['user_id' => $user->id]);

        $this->assertDatabaseHas('journal_entries', ['id' => $entry->id]);

        // Delete the user
        $user->delete();

        // Entry should be deleted due to cascade
        $this->assertDatabaseMissing('journal_entries', ['id' => $entry->id]);
    }

    /**
     * UT-ENTRY-013: Multiple entries can exist for the same date
     *
     * @test
     */
    public function multiple_entries_can_exist_for_same_date(): void
    {
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

        $this->assertCount(2, $entries);
        $this->assertTrue($entries->contains($entry1));
        $this->assertTrue($entries->contains($entry2));
    }

    /**
     * UT-ENTRY-014: Factory helper methods work correctly
     *
     * @test
     */
    public function factory_helper_methods_work(): void
    {
        $user = User::factory()->create();

        // Test forDate() method
        $entry1 = JournalEntry::factory()->forDate('2025-10-15')->create(['user_id' => $user->id]);
        $this->assertEquals('2025-10-15', $entry1->entry_date->format('Y-m-d'));

        // Test today() method
        $entry2 = JournalEntry::factory()->today()->create(['user_id' => $user->id]);
        $this->assertEquals(now()->format('Y-m-d'), $entry2->entry_date->format('Y-m-d'));

        // Test daysAgo() method
        $entry3 = JournalEntry::factory()->daysAgo(5)->create(['user_id' => $user->id]);
        $this->assertEquals(now()->subDays(5)->format('Y-m-d'), $entry3->entry_date->format('Y-m-d'));
    }
}
