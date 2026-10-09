@extends('layouts.admin')

@section('titel', 'Inloggen')

@section('inhoud')

    <section class="inlog">
        <div class="omhulsel">
            <div class="inlog__kaart">
                <span class="oogje">Beheer</span>
                <h1 class="kop-klein">Inloggen</h1>
                <p class="dim">Alleen voor de beheerder van de shop.</p>

                @if (session('status'))
                    <p class="melding" role="status">{{ session('status') }}</p>
                @endif

                <form class="formulier" method="POST" action="{{ route('admin.login.store') }}">
                    @csrf

                    <div class="veld">
                        <label for="email">E-mailadres</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                               required autofocus autocomplete="username"
                               @error('email') aria-invalid="true" aria-describedby="email-fout" @enderror>
                        @error('email')
                            <p class="veld__fout" id="email-fout">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="veld">
                        <label for="password">Wachtwoord</label>
                        <input id="password" name="password" type="password"
                               required autocomplete="current-password"
                               @error('password') aria-invalid="true" aria-describedby="password-fout" @enderror>
                        @error('password')
                            <p class="veld__fout" id="password-fout">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="knop knop--vol knop--breed">Inloggen</button>
                </form>
            </div>

            <a class="inlog__terug" href="{{ route('home') }}">← Terug naar de website</a>
        </div>
    </section>

@endsection
