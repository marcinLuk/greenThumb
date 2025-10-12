<div class="grid grid-cols-1 gap-4 md:grid-cols-1 md:gap-2">
    @foreach($weekDays as $date)
        <x-calendar.day-row
            :date="$date"
            :entries="$entriesByDate->get($date->format('Y-m-d'), collect([]))"
            :can-add-entry="$canAddEntry"
        />
    @endforeach
</div>
