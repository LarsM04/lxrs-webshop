@extends('layouts.admin')

@section('titel', 'Nieuwe preset')

@section('inhoud')

    <section class="sectie--kop">
        <div class="omhulsel">
            <div class="sectiekop">
                <div>
                    <a class="terug" href="{{ route('admin.presets.index') }}">← Alle presets</a>
                    <h1 class="kop-pagina">Nieuwe preset</h1>
                </div>
                <p class="lead">Na opslaan staat hij meteen in de shop.</p>
            </div>
        </div>
    </section>

    <section style="padding-top:0">
        <div class="omhulsel">
            @include('admin.presets._formulier')
        </div>
    </section>

@endsection
