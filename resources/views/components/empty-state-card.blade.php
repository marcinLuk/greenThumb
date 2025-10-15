@props([
    'iconClass' => 'size-12 text-zinc-400 dark:text-zinc-500 mb-4',
    'heading',
    'description',
    'descriptionClass' => 'mt-2 text-sm text-zinc-500 dark:text-zinc-400 max-w-md',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 text-center']) }}>
    <flux:icon.document-magnifying-glass class="{{ $iconClass }}" />
    <p class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
        {{ $heading }}
    </p>
    <p class="{{ $descriptionClass }}">
        {{ $description }}
    </p>

    @if($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
