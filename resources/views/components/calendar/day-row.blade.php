<div class="flex min-h-32 flex-col rounded-lg border bg-white p-3 dark:border-zinc-700 dark:bg-zinc-800">
    {{-- Day Header --}}
    <div class="mb-3 flex items-center justify-between border-b pb-2 dark:border-zinc-700">
        <div class="flex items-center gap-2">
            {{-- Day Name (responsive) --}}
            <span class="text-sm font-semibold md:hidden">{{ $shortDayName() }}</span>
            <span class="hidden text-sm font-semibold md:inline">{{ $fullDayName() }}</span>

            {{-- Day Number --}}
            <span class="text-lg font-bold {{ $isToday() ? 'text-blue-600 dark:text-blue-400' : '' }}">
                {{ $dayNumber() }}
            </span>

            {{-- Today Badge --}}
            @if($isToday())
                <flux:badge size="sm" variant="primary">Today</flux:badge>
            @endif
        </div>
    </div>

    {{-- Entries List --}}
    <div class="flex-1 space-y-2">
        @forelse($entries as $entry)
            <div class="rounded-md border bg-zinc-50 p-2 dark:border-zinc-600 dark:bg-zinc-700/50">
                <flux:text class="line-clamp-2 text-sm font-medium">
                    {{ $entry['title'] ?? 'Untitled' }}
                </flux:text>
                @if(!empty($entry['content']))
                    <flux:text class="line-clamp-2 mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                        {{ $entry['content'] }}
                    </flux:text>
                @endif
            </div>
        @empty
            <div class="flex items-center justify-center py-4">
                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500">
                    No entries
                </flux:text>
            </div>
        @endforelse
    </div>

    {{-- Add Entry Button --}}
    @if($canAddEntry && $canCreateEntryForDate())
        <div class="mt-3 border-t pt-2 dark:border-zinc-700">
            <flux:button
                variant="ghost"
                size="sm"
                icon="plus"
                class="w-full"
            >
                Add Entry
            </flux:button>
        </div>
    @elseif(!$canCreateEntryForDate())
        <div class="mt-3 border-t pt-2 dark:border-zinc-700">
            <flux:text class="text-center text-xs text-zinc-400">
                Future date
            </flux:text>
        </div>
    @endif
</div>
