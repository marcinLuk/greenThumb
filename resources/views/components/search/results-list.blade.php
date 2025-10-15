@props(['results', 'summary'])

<div class="space-y-4">
    {{-- Results Summary --}}
    <div>
        <p class="text-zinc-700 dark:text-zinc-300">
            {{ $summary }}
        </p>
    </div>

    {{-- Results List --}}
    <div class="space-y-3">
        @foreach($results as $result)
            <x-search.result-item
                :result="$result"
                wire:key="result-{{ $result['id'] }}"
            />
        @endforeach
    </div>
</div>
