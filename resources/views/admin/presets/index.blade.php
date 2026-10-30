@extends('layouts.admin')

@section('titel', 'Presets')

@section('inhoud')

    <section class="sectie--kop">
        <div class="omhulsel">
            <div class="sectiekop">
                <div>
                    <span class="oogje">Beheer</span>
                    <h1 class="kop-pagina">Presets</h1>
                </div>
                <div class="sectiekop__rechts">
                    <p class="lead">Alles wat er in de shop staat. Zoek op naam, of filter op categorie en soort.</p>
                    <a class="knop knop--vol" href="{{ route('admin.presets.create') }}">Nieuwe preset</a>
                </div>
            </div>
        </div>
    </section>

    <section style="padding-top:0">
        <div class="omhulsel">

            {{-- Gewoon een GET-formulier, net als op de website: filters blijven in de URL staan. --}}
            <form class="filters" method="GET" action="{{ route('admin.presets.index') }}" role="search">
                <label class="zoek">
                    <span class="visueel-verborgen">Zoek in presets</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input type="search" name="zoek" value="{{ $zoekterm }}"
                           placeholder="Zoek op naam of stijl…" autocomplete="off">
                </label>

                <label class="keuze">
                    <span class="visueel-verborgen">Categorie</span>
                    <select name="categorie">
                        <option value="">Alle categorieën</option>
                        @foreach ($categorieen as $categorie)
                            <option value="{{ $categorie->slug }}" @selected($actieveCategorie?->is($categorie))>{{ $categorie->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="keuze">
                    <span class="visueel-verborgen">Soort</span>
                    <select name="soort">
                        <option value="">Alle soorten</option>
                        @foreach ($soorten as $s)
                            <option value="{{ $s->slug }}" @selected($actieveSoort === $s->slug)>{{ $s->naam }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="filters__knoppen">
                    <button class="knop knop--vol" type="submit">Filteren</button>
                    @if ($zoekterm || $actieveCategorie || $actieveSoort)
                        <a class="knop knop--rand" href="{{ route('admin.presets.index') }}">Wissen</a>
                    @endif
                </div>
            </form>

            <p class="telling" role="status">
                {{ $presets->total() }}
                {{ $presets->total() === 1 ? 'preset' : 'presets' }}
                @if ($zoekterm) gevonden voor &ldquo;{{ $zoekterm }}&rdquo; @endif
            </p>

            @if ($presets->isEmpty())
                <div class="leeg">
                    <h2 class="kop-klein">Niets gevonden</h2>
                    <p>Geen presets die voldoen aan je zoekopdracht of filter.</p>
                    <a class="knop knop--rand" href="{{ route('admin.presets.index') }}">Alle presets</a>
                </div>
            @else
                {{-- Op een smal scherm scrollt de tabel zijwaarts in plaats van de hele pagina. --}}
                <div class="tabel-wrap" role="region" aria-label="Presets" tabindex="0">
                    <table class="tabel">
                        <thead>
                            <tr>
                                <th scope="col"><span class="visueel-verborgen">Afbeelding</span></th>
                                <th scope="col">Naam</th>
                                <th scope="col">Categorie</th>
                                <th scope="col">Soort</th>
                                <th scope="col" class="tabel__getal">Prijs</th>
                                <th scope="col">Uitgelicht</th>
                                <th scope="col"><span class="visueel-verborgen">Acties</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($presets as $preset)
                                <tr>
                                    <td>
                                        @if ($preset->image_path)
                                            <div class="duim">
                                                <x-preset-afbeelding :preset="$preset" width="64" height="40" loading="lazy" />
                                            </div>
                                        @else
                                            <div class="duim plaatshouder" data-cat="{{ $preset->category->slug }}" aria-hidden="true">
                                                {{ $preset->category->afkorting }}
                                            </div>
                                        @endif
                                    </td>
                                    <th scope="row">
                                        {{ $preset->name }}
                                        <small class="tabel__sub">{{ $preset->slug }}</small>
                                    </th>
                                    <td>{{ $preset->category->name }}</td>
                                    <td>
                                        {{ ucfirst($preset->soort) }}
                                        @if ($preset->soort !== 'los')
                                            <small class="tabel__sub">{{ $preset->aantal }} presets</small>
                                        @endif
                                    </td>
                                    <td class="tabel__getal">&euro;{{ number_format($preset->price, 2, ',', '.') }}</td>
                                    <td>
                                        @if ($preset->is_featured)
                                            Ja
                                        @else
                                            <span class="dim">Nee</span>
                                        @endif
                                    </td>
                                    <td class="tabel__acties">
                                        <a href="{{ route('admin.presets.edit', $preset) }}">Bewerken<span class="visueel-verborgen"> {{ $preset->name }}</span></a>
                                        <a href="{{ route('presets.show', $preset->slug) }}">Bekijk op de site</a>

                                        {{-- Eerst bevestigen; Js::from maakt van de naam een veilige JavaScript-tekst,
                                             ook als er aanhalingstekens in staan. --}}
                                        <form class="tabel__verwijder" method="POST" action="{{ route('admin.presets.destroy', $preset) }}"
                                              onsubmit="return confirm({{ Js::from('Weet je zeker dat je ' . $preset->name . ' wilt verwijderen?') }})">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">Verwijderen<span class="visueel-verborgen"> {{ $preset->name }}</span></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $presets->links('admin.paginering') }}
            @endif
        </div>
    </section>

@endsection
