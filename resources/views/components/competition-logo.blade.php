@props([
    'competition',
    'alt' => null,
    'fallback' => true,
])

@php
    $competitionName = data_get($competition, 'name');
    $src = $fallback
        ? \App\Support\CompetitionBranding::iconUrl($competition)
        : \App\Support\CompetitionBranding::logoUrl($competition);
@endphp

@if($src)
    <img
        src="{{ $src }}"
        {{ $attributes->merge([
            'alt' => $alt ?? ($competitionName ? __($competitionName) : 'Competition'),
            'class' => 'object-contain',
        ]) }}>
@endif
