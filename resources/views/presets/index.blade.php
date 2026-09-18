@extends('layouts.app')

@section('titel', $actieveCategorie ? $actieveCategorie->name : 'Alle presets')
@section('omschrijving', 'Bekijk alle edit presets van LXRS2004: color corrections, text presets, shakes en zooms voor After Effects.')

@section('inhoud')

    <section class="sectie--kop">
        <div class="omhulsel">
            <nav aria-label="Kruimelpad">
                <ol class="kruimels">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    @if ($actieveCategorie)
                        <li><a href="{{ route('presets.index') }}">Presets</a></li>
                        <li aria-current="page">{{ $actieveCategorie->name }}</li>
                    @else
                        <li aria-current="page">Presets</li>
                    @endif
                </ol>
            </nav>

            <div class="sectiekop">
                <div>
                    <span class="oogje">Shop</span>
                    <h1 class="kop-pagina">{{ $actieveCategorie ? $actieveCategorie->name : 'Alle presets' }}</h1>
                </div>
                <p class="lead">
                    {{ $actieveCategorie
                        ? $actieveCategorie->omschrijving
                        : 'Alles wat ik zelf gebruik, gesorteerd op waar je het voor inzet.' }}
                </p>
            </div>
        </div>
    </section>

    <section style="padding-top:clamp(20px,3vw,32px)">
        <div class="omhulsel">

            {{-- Filterbalk. Gewoon een GET-formulier, dus filters blijven deelbaar via de URL. --}}
            <form class="filters" method="GET" action="{{ route('presets.index') }}" role="search">
                <label class="zoek">
                    <span class="visueel-verborgen">Zoek in presets</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input type="search" name="zoek" value="{{ $zoekterm }}"
                           placeholder="Zoek op naam of stijl…" autocomplete="off">
                </label>

                @if ($actieveCategorie)
                    <input type="hidden" name="categorie" value="{{ $actieveCategorie->slug }}">
                @endif
                @if ($actieveSoort)
                    <input type="hidden" name="soort" value="{{ $actieveSoort }}">
                @endif

                <button class="knop knop--vol" type="submit">Zoeken</button>
            </form>

            <div class="filterrij">
                <span class="filterrij__label">Categorie</span>
                <ul class="pillen">
                    <li>
                        <a class="pil" href="{{ route('presets.index', array_filter(['zoek' => $zoekterm, 'soort' => $actieveSoort])) }}"
                           @if(! $actieveCategorie) aria-current="true" @endif>Alles</a>
                    </li>
                    @foreach ($categorieen as $categorie)
                        <li>
                            <a class="pil"
                               href="{{ route('presets.index', array_filter(['categorie' => $categorie->slug, 'zoek' => $zoekterm, 'soort' => $actieveSoort])) }}"
                               @if($actieveCategorie && $actieveCategorie->id === $categorie->id) aria-current="true" @endif>
                                {{ $categorie->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="filterrij">
                <span class="filterrij__label">Soort</span>
                <ul class="pillen">
                    <li>
                        <a class="pil" href="{{ route('presets.index', array_filter(['zoek' => $zoekterm, 'categorie' => $actieveCategorie?->slug])) }}"
                           @if(! $actieveSoort) aria-current="true" @endif>Alles</a>
                    </li>
                    @foreach ($soorten as $s)
                        <li>
                            <a class="pil"
                               href="{{ route('presets.index', array_filter(['soort' => $s->slug, 'zoek' => $zoekterm, 'categorie' => $actieveCategorie?->slug])) }}"
                               @if($actieveSoort === $s->slug) aria-current="true" @endif>
                                {{ $s->naam }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="telling" role="status">
                {{ $presets->count() }}
                {{ $presets->count() === 1 ? 'product' : 'producten' }}
                @if ($zoekterm) gevonden voor &ldquo;{{ $zoekterm }}&rdquo; @endif
            </p>

            <div class="raster">
                @forelse ($presets as $preset)
                    <x-preset-kaart :preset="$preset" :niveau="2" />
                @empty
                    <div class="leeg">
                        <h2 class="kop-klein">Niets gevonden</h2>
                        <p>
                            Geen presets die voldoen aan je filter.
                            Probeer een andere zoekterm of bekijk het hele aanbod.
                        </p>
                        <a class="knop knop--rand" href="{{ route('presets.index') }}">Alle presets</a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

@endsection
