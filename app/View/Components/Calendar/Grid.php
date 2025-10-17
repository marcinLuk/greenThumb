<?php

namespace App\View\Components\Calendar;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Grid extends Component
{
    public array $weekDays;

    public Collection $entriesByDate;

    public bool $canAddEntry;

    /**
     * Create a new component instance.
     */
    public function __construct(array $weekDays, Collection $entriesByDate, bool $canAddEntry = true)
    {
        $this->weekDays = $weekDays;
        $this->entriesByDate = $entriesByDate;
        $this->canAddEntry = $canAddEntry;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.calendar.grid');
    }
}
