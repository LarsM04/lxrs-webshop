@extends('layouts.admin')

@section('titel', 'Nieuwe categorie')

@section('inhoud')

    <section class="sectie--kop">
        <div class="omhulsel">
            <div class="sectiekop">
                <div>
                    <a class="terug" href="{{ route('admin.categories.index') }}">← Alle categorieën</a>
                    <h1 class="kop-pagina">Nieuwe categorie</h1>
                </div>
                <p class="lead">Na opslaan staat hij meteen als filter op de website.</p>
            </div>
        </div>
    </section>

    <section style="padding-top:0">
        <div class="omhulsel">
            @include('admin.categories._formulier')
        </div>
    </section>

@endsection
