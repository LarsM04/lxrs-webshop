{{-- niveau: welk kopniveau de titel krijgt. Op een overzichtspagina staan
     de kaarten direct onder de h1 (dus h2), op de homepage onder een
     sectiekop (dus h3). --}}
@props(['preset', 'vertraging' => null, 'niveau' => 3])

<article class="kaart" @if($vertraging) data-reveal="{{ $vertraging }}" @endif>
    <div class="kaart__beeld">
        @if ($preset->image_path ?? false)
            <img src="{{ asset('storage/' . $preset->image_path) }}"
                 alt="Voorbeeld van de preset {{ $preset->name }}"
                 loading="lazy" width="560" height="350">
        @else
            {{-- Tot er echte previews zijn: een kleurvlak per categorie. --}}
            <div class="plaatshouder" data-cat="{{ $preset->category->slug }}" aria-hidden="true">
                {{ $preset->category->afkorting }}
            </div>
        @endif
    </div>

    <div class="kaart__body">
        <div class="kaart__tags">
            <span class="kaart__tag">{{ $preset->category->name }}</span>
            @if ($preset->soort !== 'los')
                <span class="kaart__tag kaart__tag--pack">{{ $preset->aantal }} presets</span>
            @endif
        </div>

        <h{{ $niveau }} class="kaart__titel kop-klein">
            <a class="kaart__link" href="{{ route('presets.show', $preset->slug) }}">{{ $preset->name }}</a>
        </h{{ $niveau }}>

        <p class="kaart__tekst">{{ $preset->tagline }}</p>

        <div class="kaart__voet">
            <span class="prijs">&euro;{{ number_format($preset->price, 2, ',', '.') }}</span>
            <span class="kaart__cta" aria-hidden="true">Bekijken &rarr;</span>
        </div>
    </div>
</article>
