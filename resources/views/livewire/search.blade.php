<div class="w-full space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Search Your Journal</flux:heading>
    </div>

    {{-- Search Input Form --}}
    <div>
        <form wire:submit.prevent="submitSearch" class="space-y-4 flex gap-3">
            <div class="flex-auto">
                <flux:input
                    wire:model.defer="query"
                    placeholder="Ask AI to find..."
                    x-data
                    x-init="$el.focus()"
                    :disabled="$isLoading"
                />
                <flux:error name="query" />
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    Enter at least 3 characters to search
                </p>
            </div>

            <div class="flex-auto flex gap-3">
                <flux:button type="submit" :disabled="$isLoading">
                    @if($isLoading)
                        <flux:icon.loading class="size-5" />
                        Searching...
                    @else
                        <flux:icon.magnifying-glass class="size-5" />
                        Search
                    @endif
                </flux:button>

                @if($hasSearched && !$isLoading)
                    <flux:button wire:click="clearSearch" variant="filled">
                        Clear
                    </flux:button>
                @endif
            </div>
        </form>
    </div>

    {{-- Empty State (before first search) --}}
    @if(!$hasSearched && !$isLoading)
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <flux:icon.magnifying-glass class="size-16 text-zinc-400 dark:text-zinc-500 mb-4" />
            <p class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                Search your journal entries
            </p>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400 max-w-md">
                Use natural language to find entries. For example: "when did I water the tomatoes?"
            </p>
        </div>
    @endif

    {{-- Loading State --}}
    @if($isLoading)
        <div class="flex flex-col items-center justify-center py-12">
            <flux:icon.loading class="size-12 mb-4" />
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Searching your journal entries...
            </p>
        </div>
    @endif

    {{-- Error State --}}
    @if($hasError && !$isLoading)
        <div class="">
            <div class="border-red-200 dark:border-red-800">
                <div class="flex items-start gap-3">
                    <flux:icon.exclamation-triangle class="size-6 text-red-600 dark:text-red-400 flex-shrink-0" />
                    <div class="flex-1">
                        <p class="font-medium text-red-900 dark:text-red-100">
                            Search Error
                        </p>
                        <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                            {{ $errorMessage }}
                        </p>
                        <div class="mt-4 flex gap-3">
                            <flux:button wire:click="retrySearch" size="sm" variant="primary">
                                Try Again
                            </flux:button>
                            <flux:button wire:click="clearSearch" size="sm" variant="ghost">
                                New Search
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Search Results --}}
    @if($hasSearched && !$hasError && !$isLoading)
        <div class=" space-y-4">
            {{-- Results Summary --}}
            <div>
                <p class="text-sm text-zinc-700 dark:text-zinc-300">
                    {{ $resultsSummary }}
                </p>
            </div>

            {{-- No Results --}}
            @if($resultsCount === 0)
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <flux:icon.document-magnifying-glass class="size-12 text-zinc-400 dark:text-zinc-500 mb-4" />
                    <p class="text-base font-medium text-zinc-900 dark:text-zinc-100">
                        No entries found
                    </p>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400 max-w-md">
                        Try using different keywords or check your spelling.
                    </p>
                </div>
            @endif

            {{-- Results List --}}
            @if($resultsCount > 0)
                <div class="space-y-3">
                    {{-- Each Result --}}
                    @foreach($searchResults as $result)
                        <div
                            class=" border p-5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700"
                            x-on:click="openEntryFromSearch({{ $result['id'] }})"
                        >
                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-4">
                                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ $result['title'] }}
                                    </h3>
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400 flex-shrink-0">
                                        {{ $result['formatted_date'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- JavaScript to open entry modal --}}
    <script>
        openEntryFromSearch = function(entryId) {
            console.log(Livewire)

            const modal = Livewire.getByName('entry-modal')[0];
            console.log(modal)
            if (modal) {
                modal.openEditModal(entryId);
            }
        }
    </script>
</div>
