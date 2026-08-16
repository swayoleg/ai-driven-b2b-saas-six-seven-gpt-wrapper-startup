<?php

if (! function_exists('loc_url')) {
    /**
     * URL for a site path in the current (or given) locale.
     * English lives at the root, Ukrainian under the /uk prefix.
     */
    function loc_url(string $path = '', ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $path = ltrim($path, '/');

        if ($locale === 'uk') {
            $path = trim('uk/'.$path, '/');
        }

        return url($path === '' ? '/' : $path);
    }
}

if (! function_exists('alt_locale_url')) {
    /** URL of the current page in the other locale (for the EN / УКР switcher). */
    function alt_locale_url(string $targetLocale): string
    {
        $path = request()->path(); // 'uk/blog/foo', 'blog/foo', 'uk' or '/'
        $path = preg_replace('#^uk(/|$)#', '', $path === '/' ? '' : $path);

        return loc_url($path, $targetLocale);
    }
}
