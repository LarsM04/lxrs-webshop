@extends('layouts.admin')

@section('titel', $preset->name . ' bewerken')

@section('inhoud')

    <section class="sectie--kop">
        <div class="omhulsel">
            <div class="sectiekop">
                <div>
                    <a class="terug" href="{{ route('admin.presets.index') }}">← Alle presets</a>
                    <h1 class="kop-pagina">{{ $preset->name }}</h1>
                </div>
                <p class="lead">
                    Wijzigingen zijn na opslaan meteen te zien op
                    <a href="{{ route('presets.show', $preset->slug) }}">de productpagina</a>.
                </p>
            </div>
        </div>
    </section>

    <section style="padding-top:0">
        <div class="omhulsel">
            @include('admin.presets._formulier')
        </div>
    </section>

@endsection
