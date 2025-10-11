<header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
    <x-header.nav>
        <x-header.button href="{{ route('login') }}" variant="ghost">
            Log in
        </x-header.button>

        @if (Route::has('register'))
            <x-header.button href="{{ route('register') }}" variant="outline">
                Register
            </x-header.button>
        @endif
    </x-header.nav>
</header>
