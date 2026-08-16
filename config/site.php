<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ukrainian site
    |--------------------------------------------------------------------------
    |
    | Hides the EN / УКР switcher and makes every /uk/* URL 404 while the
    | translations are being reworked. The translated content stays in the
    | database and is still editable in the admin panel, so flipping this
    | back to true is all that's needed to bring the language back.
    |
    */

    'uk_enabled' => (bool) env('SITE_UK_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Google Analytics
    |--------------------------------------------------------------------------
    |
    | GA4 measurement ID. Leave it empty and no analytics markup is rendered at
    | all — which is what you want locally, so your own browsing does not end up
    | in the production property.
    |
    */

    'analytics_id' => env('GOOGLE_ANALYTICS_ID'),

];
