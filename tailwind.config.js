/**
 * Ported verbatim from the inline `tailwind.config` that used to sit next to
 * the cdn.tailwindcss.com script tag in resources/views/layouts/site.blade.php.
 * Tailwind 3.4.x, because that config is v3 syntax and the CDN served 3.4.17.
 *
 * preflight is off on purpose: the Nocturne design-system sheet
 * (the styles.css under public/_ds/) supplies the reset and the base element
 * styles, and Tailwind's own reset fights it.
 *
 * CONTENT GLOBS — read before editing.
 * Page and Field Note bodies are raw HTML stored in the database, so most of
 * the utility classes on this site never appear in a Blade file. Three of the
 * four globs below exist to catch them:
 *   - markup/**: the original hand-built mockup the CMS was seeded from, which
 *     covers nearly every class the design uses;
 *   - storage/app/tailwind/content.html: a dump of the *live* database in every
 *     locale, written by `php artisan tailwind:content` (run by `gulp styles`),
 *     which catches anything authored in the admin since the seed;
 *   - public/assets/**.js: classes toggled from JavaScript.
 * Dropping any of them silently purges classes and breaks the design.
 */
module.exports = {
    content: [
        './resources/views/**/*.blade.php',
        './markup/**/*.html',
        './public/assets/**/*.js',
        './storage/app/tailwind/content.html',
    ],
    corePlugins: {
        preflight: false,
    },
    theme: {
        extend: {
            colors: {
                bg:      'var(--color-bg)',
                surface: 'var(--color-surface)',
                ink:     'var(--color-text)',
                accent:  'var(--color-accent)',
                divider: 'var(--color-divider)',
                section: 'var(--color-section)',
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
            borderRadius: {
                sm: 'var(--radius-sm)',
                md: 'var(--radius-md)',
                lg: 'var(--radius-lg)',
            },
        },
    },
    plugins: [],
};
