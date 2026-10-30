{{--
    Het formulier voor toevoegen én bewerken van een categorie. Bestaat de
    categorie al, dan gaat het naar update (PUT); anders naar store (POST).
--}}
<form class="formulier formulier--kolommen" method="POST"
      action="{{ $categorie->exists ? route('admin.categories.update', $categorie) : route('admin.categories.store') }}">
    @csrf
    @if ($categorie->exists)
        @method('PUT')
    @endif

    @if ($errors->any())
        <p class="melding melding--fout veld--breed" role="alert">
            Niet opgeslagen: kijk bij de rood omlijnde velden wat er mis is.
        </p>
    @endif

    <x-admin.veld naam="name" label="Naam" :waarde="$categorie->name" required maxlength="255" />

    <x-admin.veld naam="slug" label="Slug" :waarde="$categorie->slug" maxlength="255"
                  hulp="Het stukje in de URL van het filter, zoals shakes. Laat leeg om het van de naam te maken." />

    <x-admin.veld naam="afkorting" label="Afkorting" :waarde="$categorie->afkorting" required maxlength="20"
                  hulp="Staat op het kleurvlak zolang een preset geen afbeelding heeft, zoals CC'S. Maximaal 20 tekens." />

    <x-admin.veld class="veld--breed" naam="omschrijving" label="Omschrijving" :waarde="$categorie->omschrijving"
                  required maxlength="255" hulp="Eén zin voor de tegel op de homepage." />

    <div class="formulier__knoppen veld--breed">
        <button type="submit" class="knop knop--vol">{{ $categorie->exists ? 'Wijzigingen opslaan' : 'Categorie toevoegen' }}</button>
        <a class="knop knop--rand" href="{{ route('admin.categories.index') }}">Annuleren</a>
    </div>
</form>
