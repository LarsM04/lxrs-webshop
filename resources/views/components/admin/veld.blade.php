{{--
    Eén veld in een formulier van de beheeromgeving: label, invoer, een
    optionele uitleg en de foutmelding. Na een fout staat wat je had ingevuld
    er weer in (old()).

    <x-admin.veld naam="price" label="Prijs" type="number" step="0.01" :waarde="$preset->price" required />

    type="textarea" geeft een groot tekstvak. Extra attributen (required,
    step, maxlength…) komen op de input zelf terecht.
--}}
@props(['naam', 'label', 'waarde' => null, 'type' => 'text', 'hulp' => null])

@php
    $id = 'veld-' . $naam;
    $beschreven = collect([
        $hulp ? $id . '-hulp' : null,
        $errors->has($naam) ? $id . '-fout' : null,
    ])->filter()->implode(' ');
@endphp

<div {{ $attributes->only('class')->class('veld') }}>
    <label for="{{ $id }}">{{ $label }}</label>

    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $naam }}" rows="5"
                  {{ $attributes->except('class') }}
                  @if ($beschreven) aria-describedby="{{ $beschreven }}" @endif
                  @error($naam) aria-invalid="true" @enderror>{{ old($naam, $waarde) }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $naam }}" type="{{ $type }}" value="{{ old($naam, $waarde) }}"
               {{ $attributes->except('class') }}
               @if ($beschreven) aria-describedby="{{ $beschreven }}" @endif
               @error($naam) aria-invalid="true" @enderror>
    @endif

    @if ($hulp)
        <p class="veld__hulp" id="{{ $id }}-hulp">{{ $hulp }}</p>
    @endif

    @error($naam)
        <p class="veld__fout" id="{{ $id }}-fout">{{ $message }}</p>
    @enderror
</div>
