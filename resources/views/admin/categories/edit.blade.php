@extends('layouts.admin')

@section('titel', $categorie->name . ' bewerken')

@section('inhoud')

    <section class="sectie--kop">
        <div class="omhulsel">
            <div class="sectiekop">
                <div>
                    <a class="terug" href="{{ route('admin.categories.index') }}">← Alle categorieën</a>
                    <h1 class="kop-pagina">{{ $categorie->name }}</h1>
                </div>
                <p class="lead">
                    Wijzigingen zijn na opslaan meteen te zien op
                    <a href="{{ route('presets.index', ['categorie' => $categorie->slug]) }}">de website</a>.
                </p>
            </div>
        </div>
    </section>

    <section style="padding-top:0">
        <div class="omhulsel">
            @include('admin.categories._formulier')
        </div>
    </section>

@endsection
