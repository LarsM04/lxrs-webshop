<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Recente edits op de homepage
    |--------------------------------------------------------------------------
    |
    | Plak hier de links van de TikTok-video's die je wilt uitlichten.
    | Je vindt zo'n link via Delen -> Link kopieren. Hij ziet er zo uit:
    |
    |     https://www.tiktok.com/@lxrs2004/video/7301234567890123456
    |
    | Let op: een korte link (https://vm.tiktok.com/...) werkt niet, omdat
    | daar het videonummer niet in staat. Open die eerst in je browser en
    | kopieer dan de volledige link uit de adresbalk.
    |
    | Staat er ?is_from_webapp=... achter je link? Die mag eraf. Daar zit een
    | web_id in dat aan jouw browser hangt, en dat hoort niet in een publieke
    | repo. Alles t/m het videonummer is genoeg.
    |
    | Staat de lijst leeg, dan toont de homepage in plaats daarvan je drie
    | TikTok-accounts. Je hoeft dus niets te veranderen aan de code.
    |
    */

    'reels' => [
        'https://www.tiktok.com/@lxrs2004/video/7680600960892194070',
        'https://www.tiktok.com/@lxrs2004/video/7681602773791558934',
        'https://www.tiktok.com/@lxrs2004/video/7682129275708886294',
        'https://www.tiktok.com/@lxrs2004/video/7684685214383148310',
    ],

    /*
    | Labeltje linksboven op elke edit, per account. De sectie leest het
    | account uit de link, dus een edit krijgt altijd het juiste label —
    | ook als je vier video's van hetzelfde kanaal achter elkaar zet.
    | Staat een account hier niet bij, dan komt er geen label op.
    */

    'labels' => [
        'lxrs2004' => 'F1',
        'lamu.aep' => 'Film',
        'lxrs.ft' => 'Voetbal',
    ],

];
