@extends('layouts.app')

@section('titel', $preset->name)
@section('omschrijving', $preset->tagline . ' — ' . $preset->category->name . ' preset voor After Effects.')

@section('inhoud')

    <section class="sectie--kop" style="padding-bottom:var(--sectie)">
        <div class="omhulsel">
            <nav aria-label="Kruimelpad">
                <ol class="kruimels">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('presets.index') }}">Presets</a></li>
                    <li><a href="{{ route('presets.index', ['categorie' => $preset->category->slug]) }}">{{ $preset->category->name }}</a></li>
                    <li aria-current="page">{{ $preset->name }}</li>
                </ol>
            </nav>

            <div class="detail">

                {{-- Beeld --}}
                <div class="detail__beeld">
                        @if ($preset->image_path ?? false)
                        <img src="{{ asset('storage/' . $preset->image_path) }}"
                             alt="Voorbeeld van de preset {{ $preset->name }}"
                             width="900" height="562">
                    @else
                        <div class="plaatshouder" data-cat="{{ $preset->category->slug }}" aria-hidden="true">
                            {{ $preset->name }}
                        </div>
                    @endif
                </div>

                {{-- Koopblok: op mobiel direct onder het beeld --}}
                <div class="detail__paneel">
                    <span class="kaart__tag">{{ $preset->category->name }}</span>

                    <h1 style="font-size:clamp(30px,4vw,42px); margin-top:14px">{{ $preset->name }}</h1>
                    <p class="dim" style="margin-top:8px">{{ $preset->tagline }}</p>

                    <div class="detail__prijsregel">
                        <span class="prijs">&euro;{{ number_format($preset->price, 2, ',', '.') }}</span>
                        <small class="dim">eenmalig · incl. btw</small>
                    </div>

                    {{-- In fase 5 wordt dit de winkelwagen. --}}
                    <a class="knop knop--vol knop--breed" href="mailto:hello@lxrs2004.com?subject={{ rawurlencode('Bestelling: ' . $preset->name) }}">
                        Bestellen
                    </a>
                    <p class="dim" style="font-size:13px; text-align:center; margin-top:12px">
                        Winkelwagen en directe download volgen binnenkort.
                    </p>

                    <dl class="specs">
                        <div>
                            <dt>Categorie</dt>
                            <dd><a href="{{ route('presets.index', ['categorie' => $preset->category->slug]) }}">{{ $preset->category->name }}</a></dd>
                        </div>
                        <div>
                            <dt>Werkt met</dt>
                            <dd>{{ $preset->ae_version }}</dd>
                        </div>
                        <div>
                            <dt>Bestandsgrootte</dt>
                            <dd>{{ $preset->bestandsgrootte }}</dd>
                        </div>
                        <div>
                            <dt>Licentie</dt>
                            <dd>Persoonlijk &amp; commercieel</dd>
                        </div>
                    </dl>
                </div>

                {{-- Uitleg --}}
                <div class="detail__tekst">
                    <div class="blok">
                        <h2 class="kop-klein">
                            @switch ($preset->soort)
                                @case('bundel') Over dit pack @break
                                @case('pack') Over dit pack @break
                                @default Over deze preset
                            @endswitch
                        </h2>
                        <p style="color:var(--tekst-zacht)">{{ $preset->description }}</p>
                    </div>

                    <div class="blok">
                        <h2 class="kop-klein">Wat je krijgt</h2>
                        <ul class="inbegrepen">
                            @foreach ($preset->includes as $onderdeel)
                                <li>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                        <path d="M20 6 9 17l-5-5"/>
                                    </svg>
                                    {{ $onderdeel }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($gerelateerd->isNotEmpty())
        <section>
            <div class="omhulsel">
                <div class="sectiekop">
                    <div>
                        <span class="oogje">{{ $preset->soort === 'los' ? 'Meer uit ' . $preset->category->name : 'Wat er in zit' }}</span>
                        <h2>{{ $preset->soort === 'los' ? 'Past hier goed bij' : 'Los te koop' }}</h2>
                    </div>
                </div>

                <div class="raster">
                    @foreach ($gerelateerd as $ander)
                        <x-preset-kaart :preset="$ander" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
