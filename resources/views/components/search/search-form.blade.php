@props([
    'hasSearched' => false,
])

<form wire:submit.prevent="submitSearch" class="space-y-4 flex gap-3">
    <div class="flex-auto">
        <flux:input
            wire:model.defer="query"
            placeholder="Ask AI to find..."
            x-data
            x-init="$el.focus()"
            wire:loading.attr="disabled"
            wire:target="submitSearch"
        />
        <flux:error name="query" />
        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
            Enter at least 3 characters to search
        </p>
    </div>

    <div class="flex-auto flex gap-3">
        <flux:button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="submitSearch"
        >
            <span wire:loading.remove wire:target="submitSearch">
                <flux:icon.magnifying-glass class="size-5" />
                Search
            </span>
            <span wire:loading wire:target="submitSearch">
                <flux:icon.loading class="size-5" />
                Searching...
            </span>
        </flux:button>

        <flux:button
            wire:click="clearSearch"
            variant="filled"
            wire:loading.remove
            wire:target="submitSearch"
            x-show="{{ $hasSearched ? 'true' : 'false' }}"
        >
            Clear
        </flux:button>
    </div>
</form>
