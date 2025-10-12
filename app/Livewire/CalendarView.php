<?php

namespace App\Livewire;

use App\Models\JournalEntry;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CalendarView extends Component
{
    public Carbon $currentWeekStart;

    public Collection $entries;

    public bool $isLoading = false;

    public bool $canAddEntry = true;

    /**
     * Initialize the component.
     */
    public function mount(): void
    {
        // Initialize to Monday of current week
        $this->currentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $this->entries = collect([]);

        // Load initial data
        $this->loadWeekEntries();
        $this->checkEntryLimit();
    }

    /**
     * Navigate to previous or next week.
     */
    public function navigateWeek(string $direction): void
    {
        if ($this->isLoading) {
            return;
        }

        if ($direction === 'previous') {
            $this->currentWeekStart = $this->currentWeekStart->copy()->subWeek();
        } elseif ($direction === 'next') {
            $this->currentWeekStart = $this->currentWeekStart->copy()->addWeek();
        }

        $this->loadWeekEntries();
    }

    /**
     * Load entries for the current week from the database.
     */
    public function loadWeekEntries(): void
    {
        $this->isLoading = true;

        try {
            $startDate = $this->currentWeekStart->format('Y-m-d');
            $endDate = $this->weekEnd->format('Y-m-d');

            $entries = JournalEntry::query()
                ->withinDateRange($startDate, $endDate)
                ->orderBy('entry_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            // Transform to match API resource format
            $this->entries = $entries->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'title' => $entry->title,
                    'content' => $entry->content,
                    'entry_date' => $entry->entry_date->format('Y-m-d'),
                    'created_at' => $entry->created_at->toIso8601String(),
                    'updated_at' => $entry->updated_at->toIso8601String(),
                ];
            });
        } catch (\Exception $e) {
            Flux::toast(
                heading: 'Error Loading Entries',
                text: 'Failed to load entries. Please try again.',
                variant: 'danger'
            );
            $this->entries = collect([]);
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Check if the user has reached the 50-entry limit.
     */
    public function checkEntryLimit(): void
    {
        try {
            $entriesCount = auth()->user()->entriesCount;
            $this->canAddEntry = $entriesCount ? $entriesCount->count < 50 : true;
        } catch (\Exception $e) {
            // If we can't check the limit, assume they can add entries
            $this->canAddEntry = true;
        }
    }

    /**
     * Get the end date of the current week (Sunday).
     */
    #[Computed]
    public function weekEnd(): Carbon
    {
        return $this->currentWeekStart->copy()->endOfWeek(Carbon::SUNDAY);
    }

    /**
     * Get an array of all 7 days in the current week.
     */
    #[Computed]
    public function weekDays(): array
    {
        $days = [];
        $date = $this->currentWeekStart->copy();

        for ($i = 0; $i < 7; $i++) {
            $days[] = $date->copy();
            $date->addDay();
        }

        return $days;
    }

    /**
     * Get entries grouped by date.
     */
    #[Computed]
    public function entriesByDate(): Collection
    {
        return $this->entries->groupBy('entry_date');
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.calendar-view')
            ->layout('components.layouts.app', ['title' => 'Calendar']);
    }
}
