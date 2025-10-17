<?php

namespace App\View\Components\Calendar;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Header extends Component
{
    public Carbon $weekStart;

    public Carbon $weekEnd;

    /**
     * Create a new component instance.
     */
    public function __construct(Carbon $weekStart, Carbon $weekEnd)
    {
        $this->weekStart = $weekStart;
        $this->weekEnd = $weekEnd;
    }

    /**
     * Get the formatted week range string.
     */
    public function weekRangeText(): string
    {
        // If same month: "October 7-13, 2025"
        if ($this->weekStart->month === $this->weekEnd->month) {
            return $this->weekStart->format('F j').'-'.$this->weekEnd->format('j, Y');
        }

        // If different months: "September 30 - October 6, 2025"
        if ($this->weekStart->year === $this->weekEnd->year) {
            return $this->weekStart->format('F j').' - '.$this->weekEnd->format('F j, Y');
        }

        // If different years: "December 30, 2024 - January 5, 2025"
        return $this->weekStart->format('F j, Y').' - '.$this->weekEnd->format('F j, Y');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.calendar.header');
    }
}
