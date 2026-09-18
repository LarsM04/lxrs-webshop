<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('titel', 'Presets en tools voor editors') — LXRS2004</title>
    <meta name="description" content="@yield('omschrijving', 'Edit presets, After Effects extensies en clip packs van LXRS2004. Gemaakt voor snelle short-form edits in F1, film en voetbal.')">
    <meta name="theme-color" content="#07060c">

    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('titel', 'LXRS2004')">
    <meta property="og:description" content="@yield('omschrijving', 'Edit presets en tools voor short-form editors.')">
    <meta property="og:url" content="{{ url()->current() }}">

    {{-- Font wordt lokaal geserveerd, dus vast inladen in plaats van wachten
         tot de CSS geparsed is. --}}
    <link rel="preload" href="{{ asset('fonts/manrope-variable.woff2') }}" as="font" type="font/woff2" crossorigin>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <a class="skiplink" href="#inhoud">Naar de inhoud</a>

    <header class="kop">
        <div class="omhulsel kop__binnen">
            <a class="merk" href="{{ route('home') }}">
                <picture>
                    <source srcset="{{ asset('images/lxrs-logo.webp') }}" type="image/webp">
                    <img src="{{ asset('images/lxrs-logo.jpeg') }}" alt="" width="30" height="30">
                </picture>
                LXRS<span>2004</span>
            </a>

            <button class="nav-knop" type="button" aria-expanded="false" aria-controls="hoofdnav" data-nav-knop>
                <span class="visueel-verborgen">Menu openen</span>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M3 6h18M3 12h18M3 18h18"/>
                </svg>
            </button>

            <nav class="nav" id="hoofdnav" aria-label="Hoofdnavigatie">
                <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
                <a href="{{ route('presets.index') }}" @if(request()->routeIs('presets.*')) aria-current="page" @endif>Presets</a>
                <a href="{{ route('home') }}#werk">Werk</a>
                <a href="{{ route('home') }}#contact">Contact</a>
            </nav>
        </div>
    </header>

    <main id="inhoud">
        @yield('inhoud')
    </main>

    <footer class="voet">
        <div class="omhulsel">
            <div class="voet__raster">
                <div>
                    <a class="merk" href="{{ route('home') }}">LXRS<span>2004</span></a>
                    <p class="dim" style="font-size:14px; margin-top:12px; max-width:32ch">
                        Presets, extensies en clip packs die ik zelf elke dag gebruik.
                    </p>
                </div>

                <div>
                    <h2 class="kop-mini">Shop</h2>
                    <ul>
                        <li><a href="{{ route('presets.index') }}">Alle presets</a></li>
                        @foreach (\App\Support\PresetCatalog::categories() as $categorie)
                            <li><a href="{{ route('presets.index', ['categorie' => $categorie->slug]) }}">{{ $categorie->name }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h2 class="kop-mini">Kanalen</h2>
                    <ul>
                        <li><a href="https://www.tiktok.com/@lxrs2004" target="_blank" rel="noopener">@lxrs2004 — F1</a></li>
                        <li><a href="https://www.tiktok.com/@lamu.aep" target="_blank" rel="noopener">@lamu.aep — Film</a></li>
                        <li><a href="https://www.tiktok.com/@lxrs.ft" target="_blank" rel="noopener">@lxrs.ft — Voetbal</a></li>
                    </ul>
                </div>

                <div>
                    <h2 class="kop-mini">Contact</h2>
                    <ul>
                        <li><a href="mailto:hello@lxrs2004.com">hello@lxrs2004.com</a></li>
                        <li><a href="{{ route('home') }}#contact">Samenwerken</a></li>
                    </ul>
                </div>
            </div>

            <div class="voet__onder">
                <span>&copy; {{ date('Y') }} LXRS2004 — Editor</span>
                <span>Alle presets zijn voor persoonlijk en commercieel gebruik in je eigen edits.</span>
            </div>
        </div>
    </footer>

    <div class="cursor cursor--ring" aria-hidden="true"></div>
    <div class="cursor cursor--stip" aria-hidden="true"></div>

    <script src="{{ asset('js/main.js') }}" defer></script>
</body>
</html>
