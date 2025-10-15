# Laravel Livewire Coding Standards & Best Practices

## Component Creation

### Mandatory Rules

1. All Livewire components must extend the `Livewire\Component` class
2. Component classes must be created in the `app/Livewire/` directory
3. Component views must be created in the `resources/views/livewire/` directory
4. Use kebab-case naming convention when rendering components in Blade templates
5. Never use StudlyCase version of component names in Blade templates

### Component Generation Commands

1. Create a standard component:
   ```
   php artisan make:livewire ComponentName
   ```

2. Create a component with kebab-case:
   ```
   php artisan make:livewire component-name
   ```

3. Create an inline component:
   ```
   php artisan make:livewire ComponentName --inline
   ```

4. Create a component in a subdirectory using namespace syntax:
   ```
   php artisan make:livewire Namespace\\ComponentName
   ```

5. Create a component in a subdirectory using dot-notation:
   ```
   php artisan make:livewire namespace.component-name
   ```

6. Publish component stubs for customization:
   ```
   php artisan livewire:stubs
   ```

7. Generate layout file:
   ```
   php artisan livewire:layout
   ```

### File Paths

1. Component class location: `app/Livewire/ComponentName.php`
2. Component view location: `resources/views/livewire/component-name.blade.php`
3. Custom stubs location:
    - `stubs/livewire.stub` (standard components)
    - `stubs/livewire.inline.stub` (inline components)
    - `stubs/livewire.test.stub` (test files)
    - `stubs/livewire.view.stub` (component views)
4. Layout file location: `resources/views/components/layouts/app.blade.php`
5. Routes file location: `routes/web.php`
6. Livewire configuration: `config/livewire.php`

## Component Structure

### Mandatory Rules

1. Every component must have a `render()` method that returns a view, unless omitted for conventional naming
2. When omitting the `render()` method, Livewire will use the conventional view name based on the component name
3. Inline components must contain the view template directly in the `render()` method using heredoc syntax
4. The root element in component Blade views must be a single wrapper element

## Properties

### Mandatory Rules

1. All properties that need to be accessible in the view must be declared as public
2. Properties have specific performance and security implications and should be used judiciously
3. Properties passed to components are received through the `mount()` lifecycle hook as method parameters
4. If property names match passed-in values, the `mount()` method can be omitted and Livewire will auto-assign
5. Properties do not automatically update if the outer value changes after initial page load
6. Use the `with()` method on the view instance to pass additional data without storing it as a property

## Data Binding

### Mandatory Rules

1. Use the `wire:model` directive to bind form inputs to component properties
2. Livewire only updates components when an action is submitted by default
3. Use `wire:model.live` for real-time updates as users type
4. All bound properties must be declared as public in the component class

## Loops and Keys

### Mandatory Rules

1. Every element inside a `@foreach` loop must have a unique `wire:key` attribute
2. The `wire:key` value must be unique within the loop iteration
3. For nested Livewire components in loops, use `:key` attribute or pass key as third argument in `@livewire` directive
4. Failure to add `wire:key` will cause hard-to-diagnose issues when loop data changes

## Actions

### Mandatory Rules

1. Actions must be defined as public methods in the component class
2. Use the `wire:submit` directive on forms to call actions when forms are submitted
3. Actions trigger component re-rendering after execution
4. Actions can return redirects using Laravel's redirect helper

## Rendering Components

### Mandatory Rules

1. Components must be rendered using the `<livewire:component-name />` syntax in Blade templates
2. For nested components, use dot notation to indicate directory nesting
3. Component names in Blade must always be kebab-cased
4. Data passed to components via attributes must be prefixed with colon for PHP expressions

### Component Inclusion Syntax

1. Standard inclusion: `<livewire:component-name />`
2. Nested component inclusion: `<livewire:directory.component-name />`
3. Passing static data: `<livewire:component-name attribute="value" />`
4. Passing dynamic data: `<livewire:component-name :attribute="$variable" />`

## Full-Page Components

### Mandatory Rules

1. Full-page components must be assigned directly to routes in `routes/web.php`
2. Use the component class reference when defining full-page component routes
3. Full-page components must have a layout file with a `{{ $slot }}` placeholder
4. The layout file must be located at the path specified in global or per-component configuration
5. Ensure layout file exists before rendering full-page components

### Route Configuration

1. Basic full-page route: Define in `routes/web.php` using `Route::get()` with component class
2. Routes with parameters must have matching parameter names in the `mount()` method
3. Route model binding requires type-hinted parameters in the `mount()` method

## Layout Configuration

### Mandatory Rules

1. Global layout configuration must be set in the `layout` key of `config/livewire.php`
2. Layout path must be relative to `resources/views/`
3. Layout file must include `{{ $slot }}` placeholder for component content
4. Layout file must include dynamic title: `<title>{{ $title ?? 'Page Title' }}</title>`
5. Per-component layouts override global configuration

### Layout Methods

1. Use `#[Layout('path.to.layout')]` attribute above `render()` method or class declaration
2. Use `->layout('path.to.layout')` fluent method in `render()` method
3. Use `->extends('path.to.layout')` for traditional Blade layouts with `@extends`
4. Use `->section('section-name')` to specify the section when using `->extends()`

## Page Titles

### Mandatory Rules

1. Page titles for full-page components must be set using the `#[Title('Page Title')]` attribute
2. The layout file must include dynamic title placeholder in the head section
3. Title attributes can be placed above the `render()` method or class declaration
4. Use `->title('Page Title')` fluent method for dynamic titles that use component properties

## Additional Layout Slots

### Mandatory Rules

1. Named slots in layout files can be set using `<x-slot:name>` syntax in component views
2. Named slot definitions must be placed outside the root element in the component view
3. Slot content is passed to the corresponding named slot in the layout file

## Route Parameters

### Mandatory Rules

1. Route parameters must match parameter names in the `mount()` method
2. Route model binding requires type-hint matching the model class
3. When using route model binding, the property name must match the route parameter name
4. The `mount()` method can be omitted if property names match route parameters and type hints are correct

## Response Modification

### Mandatory Rules

1. To modify responses, use the `->response()` method with a closure parameter
2. Response modifications must be done in the `render()` method
3. The closure receives an `Illuminate\Http\Response` instance

## JavaScript Integration

### Mandatory Rules

1. Use the `@script` directive to wrap scripts that should execute when component initializes
2. The `$wire` object is automatically available inside `@script` blocks
3. Use the `@assets` directive to load script and style dependencies
4. Assets loaded with `@assets` are loaded only once per browser page
5. Both `@script` and `@assets` must be closed with their respective end directives
6. Scripts and assets can be used inside Blade components within Livewire components
7. `@script` and `@assets` only work within the context of Livewire components

### JavaScript Directives

1. Script execution: Wrap in `@script` and `@endscript`
2. Asset loading: Wrap in `@assets` and `@endassets`
3. Access component instance: Use `$wire` object
4. Access component element: Use `$wire.$el`
5. Refresh component: Use `$wire.$refresh()`

## Naming Conventions

### Mandatory Rules

1. Component class names must use StudlyCase
2. Component view file names must use kebab-case
3. Component rendering in Blade must use kebab-case
4. Directory separators in Blade use dot notation
5. Namespace separators use backslashes in Artisan commands or dots in alternative syntax

## File Organization

### Mandatory Rules

1. Keep component classes in `app/Livewire/` directory structure
2. Keep component views in `resources/views/livewire/` directory structure
3. Maintain parallel directory structures between classes and views
4. Use subdirectories to organize related components

## Security and Performance

### Mandatory Rules

1. Be aware that public properties have specific performance and security implications
2. Use the `with()` method to pass data that doesn't need to be stored as properties
3. Avoid exposing sensitive data through public properties
4. Consider property implications before making data publicly accessible
