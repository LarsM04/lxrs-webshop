<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('titel', 'Beheer') — LXRS2004 beheer</title>
    <meta name="theme-color" content="#07060c">
    {{-- De beheeromgeving hoort niet in Google. --}}
    <meta name="robots" content="noindex, nofollow">

    <link rel="preload" href="{{ asset('fonts/manrope-variable.woff2') }}" as="font" type="font/woff2" crossorigin>

    {{-- Zelfde basis als de website, met daarbovenop wat alleen de beheeromgeving nodig heeft. --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

    <script src="{{ asset('js/admin.js') }}" defer></script>
</head>
<body>
    <a class="skiplink" href="#inhoud">Naar de inhoud</a>

    <header class="kop">
        <div class="omhulsel kop__binnen">
            <a class="merk" href="{{ route('admin.dashboard') }}">
                <picture>
                    <source srcset="{{ asset('images/lxrs-logo.webp') }}" type="image/webp">
                    <img src="{{ asset('images/lxrs-logo.jpeg') }}" alt="" width="30" height="30">
                </picture>
                LXRS<span>2004</span>
                <small class="merk__label">Beheer</small>
            </a>

            @auth
                <nav class="beheer-nav" aria-label="Beheer">
                    <a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>Start</a>
                    <a href="{{ route('admin.presets.index') }}" @if(request()->routeIs('admin.presets.*')) aria-current="page" @endif>Presets</a>
                    <a href="{{ route('home') }}">Naar de website</a>

                    {{-- Uitloggen is een POST met CSRF-token, zodat een vreemde
                         site je niet via een link kan uitloggen. --}}
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="beheer-nav__knop">Uitloggen</button>
                    </form>
                </nav>
            @endauth
        </div>
    </header>

    <main id="inhoud">
        {{-- Op de loginpagina staat de melding in het inlogvak zelf. --}}
        @auth
            @if (session('status'))
                <div class="omhulsel">
                    <p class="melding" role="status">{{ session('status') }}</p>
                </div>
            @endif
        @endauth

        @yield('inhoud')
    </main>
</body>
</html>
