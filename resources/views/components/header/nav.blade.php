@if (Route::has('login'))
    <nav class="flex items-center justify-end gap-4">
        {{$slot}}
    </nav>
@endif

