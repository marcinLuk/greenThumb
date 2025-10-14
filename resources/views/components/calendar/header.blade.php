<div class="flex items-center justify-between gap-4 rounded-lg border border-[#2cc755] bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
    {{-- Previous Week Button --}}
    <flux:button
        wire:click="navigateWeek('previous')"
        variant="ghost"
        size="sm"
        icon="chevron-left"
        aria-label="Previous week"
    />

    {{-- Week Range Display --}}
    <div class="flex-1 text-center">
        <flux:heading size="lg" class="font-semibold">
            {{ $weekRangeText() }}
        </flux:heading>
    </div>

    {{-- Next Week Button --}}
    <flux:button
        wire:click="navigateWeek('next')"
        variant="ghost"
        size="sm"
        icon="chevron-right"
        aria-label="Next week"
    />
</div>
