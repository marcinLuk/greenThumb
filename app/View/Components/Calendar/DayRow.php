<?php

namespace App\View\Components\Calendar;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class DayRow extends Component
{
    public Carbon $date;

    public Collection $entries;

    public bool $canAddEntry;

    /**
     * Create a new component instance.
     */
    public function __construct(Carbon $date, Collection $entries, bool $canAddEntry = true)
    {
        $this->date = $date;
        $this->entries = $entries;
        $this->canAddEntry = $canAddEntry;
    }

    /**
     * Check if the date is today or in the past (can create entries).
     */
    public function canCreateEntryForDate(): bool
    {
        return $this->date->lte(Carbon::today());
    }

    /**
     * Check if this date is today.
     */
    public function isToday(): bool
    {
        return $this->date->isToday();
    }

    /**
     * Get the abbreviated day name for mobile.
     */
    public function shortDayName(): string
    {
        return $this->date->format('D'); // Mon, Tue, etc.
    }

    /**
     * Get the full day name for desktop.
     */
    public function fullDayName(): string
    {
        return $this->date->format('l'); // Monday, Tuesday, etc.
    }

    /**
     * Get the day number.
     */
    public function dayNumber(): string
    {
        return $this->date->format('j'); // 1, 2, 3, etc.
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.calendar.day-row');
    }
}
