<?php

namespace App\Policies;

use App\Models\JournalEntry;
use App\Models\User;

class JournalEntryPolicy
{
    /**
     * Determine whether the user can view any journal entries.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the journal entry.
     */
    public function view(User $user, JournalEntry $journalEntry): bool
    {
        return $user->id === $journalEntry->user_id;
    }

    /**
     * Determine whether the user can create journal entries.
     *
     * Users are limited to 50 journal entries maximum.
     */
    public function create(User $user): bool
    {
        $entriesCount = $user->entriesCount()->first();
        
        if (!$entriesCount) {
            return true;
        }
        
        return $entriesCount->count < 50;
    }

    /**
     * Determine whether the user can update the journal entry.
     */
    public function update(User $user, JournalEntry $journalEntry): bool
    {
        return $user->id === $journalEntry->user_id;
    }

    /**
     * Determine whether the user can delete the journal entry.
     */
    public function delete(User $user, JournalEntry $journalEntry): bool
    {
        return $user->id === $journalEntry->user_id;
    }

    /**
     * Determine whether the user can restore the journal entry.
     */
    public function restore(User $user, JournalEntry $journalEntry): bool
    {
        return $user->id === $journalEntry->user_id;
    }

    /**
     * Determine whether the user can permanently delete the journal entry.
     */
    public function forceDelete(User $user, JournalEntry $journalEntry): bool
    {
        return $user->id === $journalEntry->user_id;
    }
}