# Livewire Component Nesting - Coding Standards and Best Practices

## Component Creation Standards

### 1. When to Create Nested Components

1. Only create a Livewire component if the content needs to be "live" or dynamic
2. Use simple Blade components for static content that doesn't benefit from Livewire's dynamic nature
3. Create Livewire components only when there is a direct performance benefit
4. Consult the technical examination of component nesting for performance implications before implementing nested structures

## Component Nesting Rules

### 2. Basic Nesting Implementation

1. Include nested Livewire components directly in the parent component's Blade view
2. Use the syntax `<livewire:component-name />` to render child components
3. Understand that on initial page render, nested components are rendered in place
4. Recognize that on subsequent network requests, nested components skip rendering as they become independent components
5. Treat every component as an "island" - independent and self-contained

### 3. Namespace and Class Structure

**File Path Pattern:**
1. Store component classes in `App\Livewire` namespace
2. Extend all Livewire components from `Livewire\Component` base class
3. Implement `render()` method that returns a view for all components

## Props and Data Passing Standards

### 4. Passing Props to Child Components

1. Pass data from parent to child using the syntax `:prop-name="$variable"`
2. Receive props through the child component's `mount()` method
3. Omit the `mount()` method if the property name matches the parameter name
4. Define public properties with the same name as the expected props

### 5. Static Props Rules

1. Omit the colon prefix when passing simple static string values
2. Use only the key name without a value to pass boolean `true` values
3. Use the shortened syntax `:$variable` when variable name matches prop name

### 6. Component Keys in Loops

1. Always include a unique `key` value when rendering child components within loops
2. Set the key using the `:key` prop on each child component iteration
3. Ensure keys are unique and stable to properly track component identity
4. Never render looped components without explicit keys - this will cause incorrect behavior
5. Use model IDs or other unique identifiers as key values

## Reactive Properties Standards

### 7. Reactive Props Implementation

1. Understand that props are NOT reactive by default in Livewire
2. Add the `#[Reactive]` attribute to properties that must update when parent data changes
3. Import the attribute using `use Livewire\Attributes\Reactive`
4. Only use `#[Reactive]` when necessary due to performance implications
5. Minimize data sent between server and client by limiting reactive props

## Modelable Properties Standards

### 8. Wire Model Binding

1. Use `#[Modelable]` attribute to enable `wire:model` binding on child components
2. Import the attribute using `use Livewire\Attributes\Modelable`
3. Apply `#[Modelable]` to a single property in the child component
4. Bind to modelable components from parent using `wire:model="propertyName"` syntax
5. Note that only the first `#[Modelable]` attribute is supported per component

## Event Communication Standards

### 9. Event Dispatching Rules

1. Use events for parent-child communication when loose coupling is desired
2. Add `#[On('event-name')]` attribute to listener methods in parent components
3. Import the attribute using `use Livewire\Attributes\On`
4. Dispatch events from child components using `$this->dispatch('event-name', param: $value)`
5. Always prefer client-side event dispatching over server-side when possible
6. Use `$dispatch('event-name', { param: value })` syntax in Blade templates for client-side dispatching
7. Avoid unnecessary network requests by dispatching events client-side

### 10. Direct Parent Access

1. Use the magic `$parent` variable in child Blade templates to access parent methods directly
2. Call parent actions using `$parent.methodName(params)` syntax in child templates
3. Access parent properties directly via `$parent.propertyName`
4. Choose between event-based or direct parent communication based on coupling requirements

## Dynamic Components Standards

### 11. Dynamic Component Rendering

1. Use `<livewire:dynamic-component :is="$componentName" />` for runtime component selection
2. Always provide a unique `:key` prop when using dynamic components
3. Assign keys that change when the component should re-render
4. Use alternative syntax `<livewire:is :component="$componentName" :key="$key" />` if preferred
5. Understand that without unique keys, subsequent renders will be skipped

## Recursive Components Standards

### 12. Recursive Nesting Rules

1. Allow components to render themselves as children when needed
2. Implement logic to prevent infinite recursion loops
3. Ensure proper termination conditions in templates
4. Always include unique `:key` props for recursive child instances
5. Use this pattern sparingly and only when truly necessary

## Component Re-rendering Standards

### 13. Forcing Component Re-renders

1. Understand that Livewire generates and tracks keys for each nested component
2. Change a component's key to force complete re-initialization
3. Use dynamic key generation based on relevant data to control rendering behavior
4. Generate keys using patterns like `$collection->pluck('id')->join('-')` for data-dependent re-renders
5. Recognize that changing a key destroys the old component and creates a new instance

## Performance Optimization Rules

### 14. Network Request Optimization

1. Minimize the number of network requests by dispatching events client-side
2. Send only minimal data between server and client on updates
3. Leverage component independence to avoid unnecessary parent re-renders
4. Only send parent component state during parent updates, not child state
5. Consider performance implications before making properties reactive

## Authorization and Security Standards

### 15. Authorization in Nested Components

1. Always authorize user actions before performing destructive operations
2. Use `$this->authorize('action', $model)` before deleting or modifying data
3. Find models explicitly before performing operations on them
4. Never trust data from client-side without server-side validation and authorization
