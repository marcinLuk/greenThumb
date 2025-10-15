<div class="w-full space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Search Your Journal</flux:heading>
    </div>

    {{-- Search Input Form --}}
    <x-search.search-form :has-searched="$hasSearched" />

    {{-- State-Based Content --}}
    @switch($this->currentState)
        @case('empty')
            <x-search.empty-state />
            @break

        @case('loading')
            <x-search.loading-state />
            @break

        @case('error')
            <x-search.error-state :message="$errorMessage" />
            @break

        @case('results')
            <x-search.results-list :results="$searchResults" :summary="$resultsSummary" />
            @break

        @case('no-results')
            <x-search.no-results />
            @break
    @endswitch
</div>
