@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <x-fltc::heading size="xl" level="1">{{ $title }}</x-fltc::heading>
    <x-fltc::subheading>{{ $description }}</x-fltc::subheading>
</div>
