<?php

/*
 * Nederlandse foutmeldingen voor de validatieregels die dit project gebruikt.
 * Staat een regel hier niet in, dan valt Laravel terug op de Engelse melding
 * (APP_FALLBACK_LOCALE=en).
 *
 * :attribute wordt vervangen door de naam onderaan bij 'attributes',
 * zodat er "Prijs moet een getal zijn" staat in plaats van "price".
 */

return [

    'alpha_dash' => ':Attribute mag alleen letters, cijfers, streepjes en lage streepjes bevatten.',
    'array' => ':Attribute moet een lijst zijn.',
    'between' => [
        'numeric' => ':Attribute moet tussen :min en :max liggen.',
    ],
    'boolean' => ':Attribute moet ja of nee zijn.',
    'decimal' => ':Attribute mag maximaal :decimal cijfers achter de komma hebben.',
    'email' => ':Attribute moet een geldig e-mailadres zijn.',
    'exists' => 'De gekozen :attribute bestaat niet.',
    'image' => ':Attribute moet een afbeelding zijn.',
    'in' => 'De gekozen :attribute is ongeldig.',
    'integer' => ':Attribute moet een heel getal zijn.',
    'max' => [
        'array' => ':Attribute mag niet meer dan :max items hebben.',
        'file' => ':Attribute mag niet groter zijn dan :max kilobyte.',
        'numeric' => ':Attribute mag niet hoger zijn dan :max.',
        'string' => ':Attribute mag niet langer zijn dan :max tekens.',
    ],
    'mimes' => ':Attribute moet een bestand zijn van het type: :values.',
    'min' => [
        'array' => ':Attribute moet minstens :min items hebben.',
        'numeric' => ':Attribute moet minstens :min zijn.',
        'string' => ':Attribute moet minstens :min tekens lang zijn.',
    ],
    'numeric' => ':Attribute moet een getal zijn.',
    'required' => ':Attribute is verplicht.',
    'string' => ':Attribute moet tekst zijn.',
    'unique' => 'Deze :attribute is al in gebruik.',
    'uploaded' => ':Attribute kon niet worden geüpload.',

    'attributes' => [
        'category_id' => 'categorie',
        'name' => 'naam',
        'slug' => 'slug',
        'soort' => 'soort',
        'aantal' => 'aantal',
        'price' => 'prijs',
        'tagline' => 'tagline',
        'description' => 'omschrijving',
        'includes' => 'wat je krijgt',
        'includes.*' => 'elke regel bij wat je krijgt',
        'bestandsgrootte' => 'bestandsgrootte',
        'ae_version' => 'After Effects-versie',
        'is_featured' => 'uitgelicht',
        'afbeelding' => 'afbeelding',
        'afbeelding_x' => 'positie links-rechts',
        'afbeelding_y' => 'positie boven-onder',
        'afbeelding_zoom' => 'zoom',
        'afbeelding_passend' => 'hele afbeelding tonen',
        'email' => 'e-mailadres',
        'password' => 'wachtwoord',
    ],

];
