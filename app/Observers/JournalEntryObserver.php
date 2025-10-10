<?php

namespace App\Observers;

use App\Models\EntriesCount;
use App\Models\JournalEntry;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class JournalEntryObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the JournalEntry "created" event.
     *
     * Increments the user's journal entry count when a new entry is created.
     */
    public function created(JournalEntry $journalEntry): void
    {
        EntriesCount::updateOrCreate(
            ['user_id' => $journalEntry->user_id],
            ['count' => \DB::raw('count + 1')]
        );
    }

    /**
     * Handle the JournalEntry "deleted" event.
     *
     * Decrements the user's journal entry count when an entry is deleted.
     */
    public function deleted(JournalEntry $journalEntry): void
    {
        $entriesCount = EntriesCount::where('user_id', $journalEntry->user_id)->first();

        if ($entriesCount && $entriesCount->count > 0) {
            $entriesCount->decrement('count');
        }
    }
}