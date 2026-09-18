@extends('layouts.app')

@section('titel', 'Presets en tools voor editors')
@section('omschrijving', 'Edit presets, After Effects extensies en clip packs van LXRS2004. Getest op honderden edits, direct te downloaden.')

@section('inhoud')

    {{-- Hero --}}
    <section class="hero">
        <div class="gloed gloed--hero" aria-hidden="true"></div>
        <div class="omhulsel">
            <span class="oogje" data-reveal="1">Open voor edits &amp; samenwerkingen</span>
            <h1 data-reveal="2">Edits die<br>niet stilstaan</h1>
            <p class="lead" data-reveal="3">
                Ik ben Lars — TikTok editor voor F1, film en voetbal. Naast m'n edits bouw ik
                extensies voor After Effects en verkoop ik de presets, clips en tools waar ik
                zelf elke dag mee werk.
            </p>

            <div class="hero__acties" data-reveal="4">
                <a class="knop knop--vol" href="{{ route('presets.index') }}">Bekijk de presets</a>
                <a class="knop knop--rand" href="#werk">Recente edits</a>
            </div>

            <div class="hero__cijfers">
                <div class="hero__cijfer">
                    <strong>{{ $aantalPresets }}</strong>
                    <span>Presets</span>
                </div>
                <div class="hero__cijfer">
                    <strong>{{ $categorieen->count() }}</strong>
                    <span>Categorieën</span>
                </div>
                <div class="hero__cijfer">
                    <strong>3</strong>
                    <span>TikTok-kanalen</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Uitgelicht aanbod --}}
    <section id="shop">
        <div class="omhulsel">
            <div class="sectiekop">
                <div>
                    <span class="oogje">01 — Shop</span>
                    <h2>Mijn toolkit,<br>jouw timeline</h2>
                </div>
                <p class="lead">
                    Alles wat ik zelf gebruik: getest op honderden edits, direct te downloaden na aankoop.
                </p>
            </div>

            <div class="raster raster--vier">
                @foreach ($uitgelicht as $preset)
                    <x-preset-kaart :preset="$preset" />
                @endforeach
            </div>

            <div style="margin-top:32px">
                <a class="knop knop--rand" href="{{ route('presets.index') }}">
                    Alle {{ $aantalPresets }} presets bekijken
                </a>
            </div>
        </div>
    </section>

    {{-- Categorieën --}}
    <section>
        <div class="omhulsel">
            <div class="sectiekop">
                <div>
                    <span class="oogje">02 — Categorieën</span>
                    <h2>Waar wil je mee werken?</h2>
                </div>
            </div>

            <div class="tegels">
                @foreach ($categorieen as $categorie)
                    <a class="tegel" href="{{ route('presets.index', ['categorie' => $categorie->slug]) }}">
                        <strong>{{ $categorie->name }}</strong>
                        <span>{{ $categorie->omschrijving }}</span>
                        <em>{{ $aantalPerCategorie[$categorie->id] ?? 0 }} presets →</em>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Waarom hier kopen --}}
    <section>
        <div class="omhulsel">
            <div class="vertrouwen">
                <div>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M12 2 4 6v6c0 5 3.4 9.2 8 10 4.6-.8 8-5 8-10V6l-8-4Z"/>
                    </svg>
                    <div>
                        <strong>Zelf gebruikt, niet bedacht</strong>
                        <p>Elke preset komt uit m'n eigen edits en is op honderden clips getest.</p>
                    </div>
                </div>
                <div>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                    </svg>
                    <div>
                        <strong>Direct downloaden</strong>
                        <p>Na je aankoop staat het bestand meteen klaar. Geen wachttijd.</p>
                    </div>
                </div>
                <div>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                    <div>
                        <strong>Voor je eigen edits</strong>
                        <p>Persoonlijk én commercieel te gebruiken, ook voor klantwerk.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Werk --}}
    <section id="werk">
        <div class="omhulsel">
            <div class="sectiekop">
                <div>
                    <span class="oogje">03 — Werk</span>
                    <h2>Recente edits</h2>
                </div>
                <p class="lead">
                    Waar de presets vandaan komen. Race weekends, cinematische filmedits en
                    voetbal, gecut op de beat.
                </p>
            </div>

            @if ($edits->isNotEmpty())
                {{-- Er staan edits in config/edits.php, dus die tonen we. --}}
                <ul class="reels">
                    @foreach ($edits as $edit)
                        <li class="reel">
                            @if ($edit->label)
                                <span class="reel__label">{{ $edit->label }}</span>
                            @endif
                            <iframe
                                src="{{ $edit->embed }}"
                                title="TikTok-edit van {{ '@' . $edit->account }}"
                                loading="lazy"
                                allow="encrypted-media; fullscreen"
                                referrerpolicy="strict-origin-when-cross-origin"
                                scrolling="no"></iframe>
                        </li>
                    @endforeach
                </ul>

                <div style="margin-top:28px">
                    <a class="knop knop--rand" href="https://www.tiktok.com/@lxrs2004" target="_blank" rel="noopener">
                        Meer op TikTok
                    </a>
                </div>
            @else
            {{-- Nog geen links ingevuld: dan maar de accounts zelf. --}}
            <div class="tegels">
                <a class="tegel" href="https://www.tiktok.com/@lxrs2004" target="_blank" rel="noopener">
                    <strong>@lxrs2004</strong>
                    <span>Race weekends, driver edits en alles rond de grid.</span>
                    <em>Hoofdaccount · F1 →</em>
                </a>
                <a class="tegel" href="https://www.tiktok.com/@lamu.aep" target="_blank" rel="noopener">
                    <strong>@lamu.aep</strong>
                    <span>Cinematische edits van films en series, met focus op sfeer en sound.</span>
                    <em>Film &amp; serie →</em>
                </a>
                <a class="tegel" href="https://www.tiktok.com/@lxrs.ft" target="_blank" rel="noopener">
                    <strong>@lxrs.ft</strong>
                    <span>Goals, skills en spelers — snel gecut op de beat.</span>
                    <em>Voetbal →</em>
                </a>
            </div>
            @endif
        </div>
    </section>

    {{-- Contact --}}
    <section id="contact">
        <div class="omhulsel">
            <div class="sectiekop">
                <div>
                    <span class="oogje">04 — Contact</span>
                    <h2>Een edit, extensie<br>of samenwerking?</h2>
                </div>
                <p class="lead">
                    Stuur me een DM of mail met wat je nodig hebt — deadline, platform en stijl.
                    Ik reageer meestal binnen een dag.
                </p>
            </div>

            <div class="hero__acties" style="margin-top:0">
                <a class="knop knop--vol" href="mailto:hello@lxrs2004.com">Mail me</a>
                <a class="knop knop--rand" href="https://www.tiktok.com/@lxrs2004" target="_blank" rel="noopener">DM op TikTok</a>
            </div>
        </div>
    </section>

@endsection
