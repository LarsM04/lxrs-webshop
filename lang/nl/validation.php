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
    'boolean' => ':Attribute moet ja of nee zijn.',
    'decimal' => ':Attribute mag maximaal :decimal cijfers achter de komma hebben.',
    'email' => ':Attribute moet een geldig e-mailadres zijn.',
    'exists' => 'De gekozen :attribute bestaat niet.',
    'in' => 'De gekozen :attribute is ongeldig.',
    'integer' => ':Attribute moet een heel getal zijn.',
    'max' => [
        'array' => ':Attribute mag niet meer dan :max items hebben.',
        'numeric' => ':Attribute mag niet hoger zijn dan :max.',
        'string' => ':Attribute mag niet langer zijn dan :max tekens.',
    ],
    'min' => [
        'array' => ':Attribute moet minstens :min items hebben.',
        'numeric' => ':Attribute moet minstens :min zijn.',
        'string' => ':Attribute moet minstens :min tekens lang zijn.',
    ],
    'numeric' => ':Attribute moet een getal zijn.',
    'required' => ':Attribute is verplicht.',
    'string' => ':Attribute moet tekst zijn.',
    'unique' => 'Deze :attribute is al in gebruik.',

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
        'email' => 'e-mailadres',
        'password' => 'wachtwoord',
    ],

];
