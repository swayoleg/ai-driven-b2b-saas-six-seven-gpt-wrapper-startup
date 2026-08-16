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

];
