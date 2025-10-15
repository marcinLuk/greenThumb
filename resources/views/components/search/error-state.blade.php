@props(['message'])

<div class="">
    <div class="border-red-200 dark:border-red-800">
        <div class="flex items-start gap-3">
            <flux:icon.exclamation-triangle class="size-6 text-red-600 dark:text-red-400 flex-shrink-0" />
            <div class="flex-1">
                <p class="font-medium text-red-900 dark:text-red-100">
                    Search Error
                </p>
                <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                    {{ $message }}
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
