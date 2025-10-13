<div class="w-full space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Gardening Journal</flux:heading>
    </div>

    {{-- Calendar Header with Week Navigation --}}
    <x-calendar.header
        :week-start="$currentWeekStart"
        :week-end="$this->weekEnd"
    />

    {{-- Loading Spinner --}}
    @if($isLoading)
        <div class="flex items-center justify-center py-12">
            <flux:icon.loading />
        </div>
    @endif

    {{-- Calendar Grid --}}
    @if(!$isLoading)
        <x-calendar.grid
            :week-days="$this->weekDays"
            :entries-by-date="$this->entriesByDate"
            :can-add-entry="$canAddEntry"
        />
    @endif

    {{-- Entry Modal --}}
    <livewire:entry-modal />
</div>
