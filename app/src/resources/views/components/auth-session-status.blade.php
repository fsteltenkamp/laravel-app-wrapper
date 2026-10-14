@props([
    'status',
])

@if ($status)
    <x-fltc::alert color="success" {{ $attributes }}>
        {{ $status }}
    </x-fltc::alert>
@endif
