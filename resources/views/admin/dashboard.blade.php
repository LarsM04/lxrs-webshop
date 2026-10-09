@extends('layouts.admin')

@section('titel', 'Start')

@section('inhoud')

    <section class="sectie--kop">
        <div class="omhulsel">
            <div class="sectiekop">
                <div>
                    <span class="oogje">Beheer</span>
                    <h1 class="kop-pagina">Welkom, {{ auth()->user()->name }}</h1>
                </div>
                <p class="lead">Hier pas je het aanbod van de shop aan, zonder de code aan te raken.</p>
            </div>
        </div>
    </section>

    <section style="padding-top:0">
        <div class="omhulsel">
            <dl class="tellers">
                <div class="teller">
                    <dt>Presets in de shop</dt>
                    <dd>{{ $aantalPresets }}</dd>
                </div>
                <div class="teller">
                    <dt>Categorieën</dt>
                    <dd>{{ $aantalCategorieen }}</dd>
                </div>
                <div class="teller">
                    <dt>Uitgelicht op de homepage</dt>
                    <dd>{{ $aantalUitgelicht }}</dd>
                </div>
            </dl>
        </div>
    </section>

@endsection
