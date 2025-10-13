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
            <flux:modal.trigger name="entry-modal">
                <div
                    class="cursor-pointer rounded-md border bg-zinc-50 p-2 transition-colors hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-700/50 dark:hover:bg-zinc-700"
                    x-on:click="editEntry({{$entry['id']}})"
                >
                    <flux:text class="line-clamp-2 text-sm font-medium">
                        {{ $entry['title'] ?? 'Untitled' }}
                    </flux:text>
                    @if(!empty($entry['content']))
                        <flux:text class="line-clamp-2 mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                            {{ $entry['content'] }}
                        </flux:text>
                    @endif
                </div>
            </flux:modal.trigger>
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
            <flux:modal.trigger name="entry-modal">
                <flux:button
                    variant="ghost"
                    size="sm"
                    icon="plus"
                    class="w-full"
                    x-on:click="setEntryDate('{{ $date->toDateString() }}')"
                >
                    Add Entry
                </flux:button>
            </flux:modal.trigger>
        </div>
    @elseif(!$canCreateEntryForDate())
        <div class="mt-3 border-t pt-2 dark:border-zinc-700">
            <flux:text class="text-center text-xs text-zinc-400">
                Future date
            </flux:text>
        </div>
    @endif
    <script>
        setEntryDate = function(date) {
            Livewire.getByName('entry-modal')[0].entry_date = date;
        }
        editEntry = function(entryId) {
            const modal = Livewire.getByName('entry-modal')[0];
            if (modal) {
                modal.openEditModal(entryId);
            }
        }
    </script>
</div>
