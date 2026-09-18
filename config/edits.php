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
    | Staat de lijst leeg, dan toont de homepage in plaats daarvan je drie
    | TikTok-accounts. Je hoeft dus niets te veranderen aan de code.
    |
    */

    'reels' => [
        // 'https://www.tiktok.com/@lxrs2004/video/0000000000000000000',
        // 'https://www.tiktok.com/@lamu.aep/video/0000000000000000000',
        // 'https://www.tiktok.com/@lxrs.ft/video/0000000000000000000',
        // 'https://www.tiktok.com/@lxrs2004/video/0000000000000000000',
    ],

    /*
    | Labeltje linksboven op elke edit. Wordt op volgorde gebruikt; zijn er
    | meer edits dan labels, dan begint hij weer vooraan.
    */

    'labels' => ['F1', 'Film', 'Voetbal'],

];
