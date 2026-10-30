{{--
    De afbeelding van een preset, zoals ingesteld in de beheeromgeving:

    - positie (afbeelding_x / _y): welk deel in beeld is
    - zoom (afbeelding_zoom): hoe ver ingezoomd
    - passend (afbeelding_passend): hele afbeelding tonen; de lege ruimte
      wordt opgevuld met een vervaagde versie van dezelfde afbeelding

    De waarden gaan als CSS-variabelen naar style.css (.beeld-kader). Het kader
    vult zijn ouder, dus de maat komt van .kaart__beeld of .detail__beeld.

    <x-preset-afbeelding :preset="$preset" alt="…" width="560" height="350" loading="lazy" />
--}}
@props(['preset', 'alt' => ''])

@php
    // Nog geen afbeelding (nieuwe preset): dan geen src, het formulier vult hem zelf.
    $bron = $preset->image_path ? asset('storage/' . $preset->image_path) : null;
@endphp

<div class="beeld-kader @if ($preset->afbeelding_passend) beeld-kader--passend @endif"
     style="--x: {{ $preset->afbeelding_x }}%; --y: {{ $preset->afbeelding_y }}%; --zoom: {{ $preset->afbeelding_zoom / 100 }}"
     data-beeld-kader>
    <img class="beeld-kader__waas" @if ($bron) src="{{ $bron }}" @endif alt="" aria-hidden="true">
    <img class="beeld-kader__img" @if ($bron) src="{{ $bron }}" @endif alt="{{ $alt }}"
         {{ $attributes }} draggable="false">
</div>
