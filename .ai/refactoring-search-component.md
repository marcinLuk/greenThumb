# Refactoring: search.blade.php

**File**: `resources/views/livewire/search.blade.php`
**Lines of Code**: 165
**Complexity**: Medium-High
**Priority**: Medium

---

## Current Issues

- **Repetitive state checking** - Multiple `x-show`, `wire:loading`, and `wire:target` directives scattered throughout
- **Four distinct UI states** in one file (empty, loading, error, results) creating visual clutter
- **Duplicate styling patterns** for empty states (lines 54-68 and 129-137)
- **Mixed Alpine.js and Livewire directives** creating cognitive load and maintenance challenges
- **Tightly coupled presentation logic** making it difficult to test or reuse UI patterns

---

## Refactoring Recommendations

### A) State-Based Component Extraction ✅

Create separate **Blade components** for each UI state to improve maintainability and reusability.

**Important:** These are all **Blade components** (not Livewire components) because they display static content. Livewire directives (`wire:click`, `wire:model`, etc.) work within them because they are rendered **inside the parent Livewire component's context**.

#### Components to Create:

```bash
php artisan make:component Search/EmptyState --view
php artisan make:component Search/LoadingState --view
php artisan make:component Search/ErrorState --view
php artisan make:component Search/ResultsList --view
php artisan make:component Search/ResultItem --view
php artisan make:component Search/NoResults --view
```

#### Refactored Main Template:

```blade
<div class="w-full space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Search Your Journal</flux:heading>
    </div>

    {{-- Search Input Form --}}
    <x-search.search-form
        wire:model.defer="query"
        wire:submit="submitSearch"
        :has-searched="$hasSearched"
    />

    {{-- State-Based Content --}}
    @if(!$hasSearched)
        <x-search.empty-state />
    @elseif($hasError)
        <x-search.error-state
            :message="$errorMessage"
        />
    @elseif($resultsCount > 0)
        <x-search.results-list
            :results="$searchResults"
            :summary="$resultsSummary"
        />
    @else
        <x-search.no-results />
    @endif
</div>
```

**Benefits:**
- Clear separation of concerns - each state has its own component
- Easier to test individual states
- Reduced nesting and conditional logic
- Components can be reused in other parts of the application

---

### B) Create Reusable Icon-Text Pattern Component

Lines 54-68 and 129-137 follow the same pattern: icon + heading + description.

**Component Type:** Blade component (static, reusable pattern)

#### Component to Create:

```bash
php artisan make:component EmptyStateCard --view
```

#### Component Implementation:

```blade
{{-- resources/views/components/empty-state-card.blade.php --}}
@props([
    'icon',
    'iconClass' => 'size-12 text-zinc-400 dark:text-zinc-500 mb-4',
    'heading',
    'description',
    'descriptionClass' => 'mt-2 text-sm text-zinc-500 dark:text-zinc-400 max-w-md',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 text-center']) }}>
    <flux:icon.{{ $icon }} :class="$iconClass" />
    <p class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
        {{ $heading }}
    </p>
    <p :class="$descriptionClass">
        {{ $description }}
    </p>

    @if($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
```

#### Usage Examples:

```blade
{{-- Empty State --}}
<x-empty-state-card
    icon="magnifying-glass"
    heading="Search your journal entries"
    description="Use natural language to find entries. For example: 'when did I water the tomatoes?'"
/>

{{-- No Results State --}}
<x-empty-state-card
    icon="document-magnifying-glass"
    heading="No entries found"
    description="Try using different keywords or check your spelling."
/>
```

#### Specific State Component Implementations:

**EmptyState Component:**
```blade
{{-- resources/views/components/search/empty-state.blade.php --}}
<x-empty-state-card
    icon="magnifying-glass"
    heading="Search your journal entries"
    description="Use natural language to find entries. For example: 'when did I water the tomatoes?'"
/>
```

**LoadingState Component:**
```blade
{{-- resources/views/components/search/loading-state.blade.php --}}
<div class="flex flex-col items-center justify-center py-12">
    <flux:icon.loading class="size-12 mb-4" />
    <p class="text-sm text-zinc-500 dark:text-zinc-400">
        Searching your journal entries...
    </p>
</div>
```

**ErrorState Component:**
```blade
{{-- resources/views/components/search/error-state.blade.php --}}
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
```

**NoResults Component:**
```blade
{{-- resources/views/components/search/no-results.blade.php --}}
<x-empty-state-card
    icon="document-magnifying-glass"
    heading="No entries found"
    description="Try using different keywords or check your spelling."
/>
```

**ResultsList Component:**
```blade
{{-- resources/views/components/search/results-list.blade.php --}}
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
```

---

### C) Use Livewire's Computed Properties

Replace Alpine.js visibility logic with Livewire computed properties for cleaner templates.

#### Before (Current Implementation):

```blade
<div x-show="!$wire.hasSearched" wire:loading.remove wire:target="submitSearch">
    {{-- Empty state content --}}
</div>

<div wire:loading wire:target="submitSearch">
    {{-- Loading state content --}}
</div>

<div x-show="$wire.hasError" wire:loading.remove wire:target="submitSearch">
    {{-- Error state content --}}
</div>

<div x-show="$wire.hasSearched && !$wire.hasError" wire:loading.remove wire:target="submitSearch">
    {{-- Results content --}}
</div>
```

#### After (Cleaner with Computed Property):

```php
// In app/Livewire/Search.php

#[Computed]
public function currentState(): string
{
    if ($this->isSearching) {
        return 'loading';
    }

    if ($this->hasError) {
        return 'error';
    }

    if (!$this->hasSearched) {
        return 'empty';
    }

    return $this->resultsCount > 0 ? 'results' : 'no-results';
}
```

```blade
{{-- In search.blade.php --}}
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
```

**Benefits:**
- Single source of truth for UI state
- Eliminates complex conditional logic in the template
- Easier to add new states
- Better testability - state logic is in the PHP class

---

### D) Extract Search Form Component

The search form (lines 8-52) should be its own component for better separation.

**Component Type:** Blade component (rendered inside Livewire component context)

**Important Note:** The `wire:submit`, `wire:model`, `wire:loading`, and `wire:click` directives work because this Blade component is rendered **inside** the parent Livewire component (`Search`). The component has access to the parent's Livewire context and can call parent methods directly.

#### Component to Create:

```bash
php artisan make:component Search/SearchForm --view
```

#### Component Implementation:

```blade
{{-- resources/views/components/search/search-form.blade.php --}}
@props([
    'query' => '',
    'hasSearched' => false,
])

<form wire:submit="submitSearch" class="space-y-4 flex gap-3">
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
```

---

### E) Extract Result Item Component

The individual result rendering (lines 144-160) should be a dedicated component.

**Component Type:** Blade component (uses Alpine.js for client-side interaction)

**Important Note:** The current implementation uses Alpine.js `x-on:click="openEntryFromSearch({{ $result['id'] }})"` for client-side navigation. This Alpine.js function should remain unchanged to avoid unnecessary server round-trips.

#### Component to Create:

```bash
php artisan make:component Search/ResultItem --view
```

#### Component Implementation:

```blade
{{-- resources/views/components/search/result-item.blade.php --}}
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
```

#### Usage:

```blade
<div class="space-y-3">
    @foreach($searchResults as $result)
        <x-search.result-item :result="$result" wire:key="result-{{ $result['id'] }}" />
    @endforeach
</div>
```

**Note:** The `wire:key` attribute is **mandatory** for all items in a loop according to Livewire best practices. This ensures proper tracking and rendering when the list changes.

---

### F) Component Communication Strategy

All refactored components are **Blade components** (not nested Livewire components), which means they inherit the parent Livewire component's context.

#### Communication Patterns:

| Pattern | When to Use | Example |
|---------|-------------|---------|
| **Wire directives** | Calling parent methods, binding to parent properties | `wire:click="clearSearch"`, `wire:model="query"` |
| **Alpine.js** | Client-side interactions without server round-trips | `x-on:click="openEntryFromSearch(id)"` |
| **Props** | Passing data from parent to child | `:message="$errorMessage"` |

#### Why NOT Nested Livewire Components?

According to LiveWireNestedComponents.md:
> "Only create a Livewire component if the content needs to be 'live' or dynamic. Use simple Blade components for static content that doesn't benefit from Livewire's dynamic nature."

**Our components display static content** - they show data passed from the parent but don't need independent state management or server-side reactivity. Therefore, Blade components are the correct choice.

#### Wire Directives in Blade Components

Livewire directives work in Blade components because:
1. The Blade component is rendered **inside** the parent Livewire component's DOM
2. Livewire's JavaScript processes all `wire:*` directives within the component boundary
3. Actions like `wire:click="clearSearch"` call methods on the **parent** Livewire component

This is different from nested Livewire components where:
- Each component has its own state and lifecycle
- Props need `#[Reactive]` attribute to update
- Communication requires events or `$parent` magic variable

---

## Implementation Order

1. **First**: Create `EmptyStateCard` component (B) - reusable across the app
2. **Second**: Extract state components (A) - `EmptyState`, `LoadingState`, `ErrorState`, `NoResults`
3. **Third**: Extract `SearchForm` component (D) - isolate form logic
4. **Fourth**: Extract `ResultItem` component (E) - isolate result rendering
5. **Fifth**: Implement computed property for state management (C) - clean up conditionals
6. **Sixth**: Refactor main `search.blade.php` to use new components

---

## Expected Outcome

**Before**: 165 lines in a single file with complex conditional logic
**After**: ~30-40 lines in main file + 6 focused, reusable components

### File Structure After Refactoring:

```
resources/views/
├── livewire/
│   └── search.blade.php (30-40 lines)
└── components/
    ├── empty-state-card.blade.php
    └── search/
        ├── empty-state.blade.php
        ├── loading-state.blade.php
        ├── error-state.blade.php
        ├── no-results.blade.php
        ├── search-form.blade.php
        ├── results-list.blade.php
        └── result-item.blade.php
```

---

## Related Patterns

This refactoring follows the project's architectural guidelines:
- **Single Responsibility Principle** - Each component has one job
- **Component-First Development** - Prefer Blade components over partials
- **Livewire-First** - Use Livewire for state management where possible
- **Tailwind Utilities** - Keep styling in the templates using utility classes
- **Progressive Enhancement** - Alpine.js for client-side interactivity only when needed

---

## Key Architectural Decisions

### Blade Components vs Livewire Components

**Decision:** Use Blade components for all extracted UI pieces.

**Rationale:**
1. **Static Content** - These components display data but don't manage their own state
2. **Performance** - Blade components have no network overhead
3. **Simplicity** - No need for `#[Reactive]` attributes or event dispatching
4. **Context Inheritance** - Wire directives work because components render inside parent Livewire component

### Wire Directives Work Because:
- Blade components are rendered **inside** the parent Livewire component's DOM
- Livewire's JavaScript processes all `wire:*` directives within the component boundary
- `wire:click="clearSearch"` calls the `clearSearch()` method on the **parent** `Search` component
- This is transparent to the developer - just use wire directives as normal

### When Would We Use Nested Livewire Components Instead?

Only if individual components needed:
- Independent state management (e.g., a sortable results list with its own sort state)
- Server-side reactivity (e.g., polling for updates)
- Lifecycle hooks (e.g., loading data when component mounts)
- Actions that don't affect parent state

**Current case:** None of our components need these features, so Blade components are correct.

---

**Date Created**: 2025-10-15
**Last Updated**: 2025-10-15
**Status**: Recommended (Corrected)
**Estimated Effort**: 2-3 hours

---

## Corrections Made

This document was corrected to fix the following technical errors:

1. ✅ **Clarified component types** - All components are Blade components, not Livewire
2. ✅ **Fixed wire directive syntax** - Removed invalid `wire:click:retry` syntax
3. ✅ **Explained wire directives in Blade** - Added explanation of how/why they work
4. ✅ **Preserved Alpine.js integration** - Kept `x-on:click` for `openEntryFromSearch()`
5. ✅ **Added communication strategy** - Documented patterns for component interaction
6. ✅ **Added complete implementations** - Included all missing component examples
7. ✅ **Fixed loop keys** - Emphasized mandatory `wire:key` in ResultsList
