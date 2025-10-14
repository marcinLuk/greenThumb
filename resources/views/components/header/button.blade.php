@props(['href', 'variant' => 'outline'])

@php
    $baseClasses = 'inline-block px-5 py-1.5 text-green-600 hover:text-green-700 rounded-sm text-sm leading-normal';

    $variantClasses = match($variant) {
        'outline' => 'border border-[#19140035]',
        'ghost' => 'border border-transparent hover:border-[#19140035]',
        default => 'border border-[#19140035]',
    };
@endphp

<a href="{{ $href }}" class="{{ $baseClasses }} {{ $variantClasses }}">
    {{ $slot }}
</a>
