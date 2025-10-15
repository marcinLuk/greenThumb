@props(['result'])

<div
    class="border p-5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-750 cursor-pointer transition-colors"
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
