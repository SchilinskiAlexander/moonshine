@props([
    'icon' => null,
    'filled' => false,
    'badge' => false,
])
<a {{ $attributes->class(['flex items-center gap-1 hover:text-primary', 'text-primary' => $filled]) }}>
    {{ $icon ?? '' }}
    {{ $slot ?? '' }}
    @if($badge !== false)
        <x-moonshine::badge color="">{{ $badge }}</x-moonshine::badge>
    @endif
</a>
