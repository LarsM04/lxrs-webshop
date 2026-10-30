@extends('layouts.admin')

@section('titel', 'Categorieën')

@section('inhoud')

    <section class="sectie--kop">
        <div class="omhulsel">
            <div class="sectiekop">
                <div>
                    <span class="oogje">Beheer</span>
                    <h1 class="kop-pagina">Categorieën</h1>
                </div>
                <div class="sectiekop__rechts">
                    <p class="lead">De groepen waarin de presets staan. Elke categorie is een filter op de website.</p>
                    <a class="knop knop--vol" href="{{ route('admin.categories.create') }}">Nieuwe categorie</a>
                </div>
            </div>
        </div>
    </section>

    <section style="padding-top:0">
        <div class="omhulsel">
            <div class="tabel-wrap" role="region" aria-label="Categorieën" tabindex="0">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th scope="col"><span class="visueel-verborgen">Kleur</span></th>
                            <th scope="col">Naam</th>
                            <th scope="col">Omschrijving</th>
                            <th scope="col" class="tabel__getal">Presets</th>
                            <th scope="col"><span class="visueel-verborgen">Acties</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categorieen as $categorie)
                            <tr>
                                <td>
                                    <div class="duim plaatshouder" data-cat="{{ $categorie->slug }}" aria-hidden="true">
                                        {{ $categorie->afkorting }}
                                    </div>
                                </td>
                                <th scope="row">
                                    {{ $categorie->name }}
                                    <small class="tabel__sub">{{ $categorie->slug }}</small>
                                </th>
                                <td class="tabel__tekst">{{ $categorie->omschrijving }}</td>
                                <td class="tabel__getal">{{ $categorie->presets_count }}</td>
                                <td class="tabel__acties">
                                    <a href="{{ route('admin.categories.edit', $categorie) }}">Bewerken<span class="visueel-verborgen"> {{ $categorie->name }}</span></a>
                                    <a href="{{ route('presets.index', ['categorie' => $categorie->slug]) }}">Bekijk op de site</a>

                                    {{-- Ook met presets erin staat de knop er; de controller legt dan uit waarom het niet kan. --}}
                                    <form class="tabel__verwijder" method="POST" action="{{ route('admin.categories.destroy', $categorie) }}"
                                          onsubmit="return confirm({{ Js::from('Weet je zeker dat je de categorie ' . $categorie->name . ' wilt verwijderen?') }})">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Verwijderen<span class="visueel-verborgen"> {{ $categorie->name }}</span></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

@endsection
