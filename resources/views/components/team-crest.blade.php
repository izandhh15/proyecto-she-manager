@props(['team'])

@php
    $isNational = ($team->type ?? 'club') === 'national';
@endphp

@if($isNational)
<img
    src="{{ $team->image }}"
    style="height: auto; aspect-ratio: 4/3; border-radius: 15%;"
    {{ $attributes->class('object-cover object-center')->merge(['alt' => $team->name, 'loading' => 'lazy', 'decoding' => 'async']) }}>
@else
<img
    src="{{ $team->image }}"
    {{ $attributes->class('object-contain object-center')->merge(['alt' => $team->name, 'loading' => 'lazy', 'decoding' => 'async']) }}>
@endif
