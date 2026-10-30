{{--
    Het formulier voor toevoegen én bewerken. Bestaat de preset al, dan gaat
    het naar update (PUT); anders naar store (POST).
--}}
{{-- multipart/form-data is nodig om een bestand (de afbeelding) mee te sturen. --}}
<form class="formulier formulier--preset" method="POST" enctype="multipart/form-data"
      action="{{ $preset->exists ? route('admin.presets.update', $preset) : route('admin.presets.store') }}">
    @csrf
    @if ($preset->exists)
        @method('PUT')
    @endif

    @if ($errors->any())
        <p class="melding melding--fout veld--breed" role="alert">
            Niet opgeslagen: kijk bij de rood omlijnde velden wat er mis is.
        </p>
    @endif

    <div class="veld">
        <label for="veld-category_id">Categorie</label>
        <select id="veld-category_id" name="category_id" required
                @error('category_id') aria-invalid="true" aria-describedby="veld-category_id-fout" @enderror>
            <option value="">Kies een categorie</option>
            @foreach ($categorieen as $categorie)
                <option value="{{ $categorie->id }}" @selected((string) old('category_id', $preset->category_id) === (string) $categorie->id)>
                    {{ $categorie->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <p class="veld__fout" id="veld-category_id-fout">{{ $message }}</p>
        @enderror
    </div>

    <div class="veld">
        <label for="veld-soort">Soort</label>
        <select id="veld-soort" name="soort" required
                @error('soort') aria-invalid="true" aria-describedby="veld-soort-fout" @enderror>
            @foreach ($soorten as $soort)
                <option value="{{ $soort->slug }}" @selected(old('soort', $preset->soort ?? 'los') === $soort->slug)>
                    {{ $soort->naam }}
                </option>
            @endforeach
        </select>
        @error('soort')
            <p class="veld__fout" id="veld-soort-fout">{{ $message }}</p>
        @enderror
    </div>

    <x-admin.veld naam="name" label="Naam" :waarde="$preset->name" required maxlength="255" />

    <x-admin.veld naam="slug" label="Slug" :waarde="$preset->slug" maxlength="255"
                  hulp="Het stukje in de URL, zoals midnight-cc. Laat leeg om het van de naam te maken." />

    <x-admin.veld naam="price" label="Prijs (€)" type="number" :waarde="$preset->price"
                  required min="0" max="9999" step="0.01" />

    <x-admin.veld naam="aantal" label="Aantal presets" type="number" :waarde="$preset->aantal"
                  required min="1" max="1000" hulp="Bij een losse preset is dit 1." />

    <x-admin.veld class="veld--breed" naam="tagline" label="Tagline" :waarde="$preset->tagline"
                  required maxlength="255" hulp="De korte regel op de productkaart." />

    <x-admin.veld class="veld--breed" naam="description" label="Omschrijving" type="textarea"
                  :waarde="$preset->description" required maxlength="5000" />

    <x-admin.veld class="veld--breed" naam="includes" label="Wat je krijgt" type="textarea"
                  :waarde="implode(PHP_EOL, $preset->includes ?? [])"
                  hulp="Eén item per regel, bijvoorbeeld: 1 preset als .ffx" />
    @error('includes.*')
        <p class="veld__fout veld--breed">{{ $message }}</p>
    @enderror

    <div class="veld veld--breed">
        <label for="veld-afbeelding">Afbeelding</label>

        @if ($preset->image_path)
            <div class="afbeelding-nu">
                <img src="{{ asset('storage/' . $preset->image_path) }}" alt="Huidige afbeelding van {{ $preset->name }}"
                     width="160" height="100">
                <label class="vinkje">
                    <input type="checkbox" name="afbeelding_weghalen" value="1" @checked(old('afbeelding_weghalen'))>
                    Afbeelding weghalen
                </label>
            </div>
        @endif

        <input id="veld-afbeelding" name="afbeelding" type="file" accept=".jpg,.jpeg,.png,.webp"
               aria-describedby="veld-afbeelding-hulp @error('afbeelding') veld-afbeelding-fout @enderror"
               @error('afbeelding') aria-invalid="true" @enderror>
        <p class="veld__hulp" id="veld-afbeelding-hulp">
            jpg, png of webp, maximaal 2 MB.
            @if ($preset->image_path) Kies een nieuwe om de huidige te vervangen. @endif
        </p>
        @error('afbeelding')
            <p class="veld__fout" id="veld-afbeelding-fout">{{ $message }}</p>
        @enderror
    </div>

    <x-admin.veld naam="bestandsgrootte" label="Bestandsgrootte" :waarde="$preset->bestandsgrootte"
                  maxlength="20" hulp="Bijvoorbeeld 120 KB. Mag leeg blijven." />

    <x-admin.veld naam="ae_version" label="After Effects-versie" :waarde="$preset->ae_version"
                  maxlength="255" hulp="Bijvoorbeeld After Effects 2020 of nieuwer. Mag leeg blijven." />

    {{-- Zonder vinkje stuurt de browser dit veld niet mee; PresetFormRequest maakt er dan false van.
         Na een fout tonen we wat er net was aangevinkt, anders wat er in de database staat. --}}
    <label class="vinkje veld--breed">
        <input type="checkbox" name="is_featured" value="1"
               @checked($errors->any() ? old('is_featured') : $preset->is_featured)>
        Uitgelicht op de homepage
    </label>

    <div class="formulier__knoppen veld--breed">
        <button type="submit" class="knop knop--vol">{{ $preset->exists ? 'Wijzigingen opslaan' : 'Preset toevoegen' }}</button>
        <a class="knop knop--rand" href="{{ route('admin.presets.index') }}">Annuleren</a>
    </div>
</form>
