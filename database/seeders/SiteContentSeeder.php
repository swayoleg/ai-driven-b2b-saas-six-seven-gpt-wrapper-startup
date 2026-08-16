<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Imports the original static markup (markup/Joke AI startup satire site)
 * into the CMS: one Page per static page (EN + UK translations) and one
 * Post per "Field Note". Internal .html links are rewritten to app URLs.
 *
 * DESTRUCTIVE. Every row is written with updateOrCreate, so running this
 * overwrites whatever has been edited in the admin panel with the contents of
 * markup/. That is what you want after breaking a page's HTML, and what you
 * very much do not want on a live site. Note that `php artisan db:seed` runs
 * it as part of DatabaseSeeder — on production, always name the seeder you
 * actually mean, e.g. `db:seed --class=AdminUserSeeder`.
 */
class SiteContentSeeder extends Seeder
{
    protected string $dir;

    protected const PAGES = [
        'home' => 'index',
        'pricing' => 'pricing',
        'about' => 'about',
        'contact' => 'contact',
        'support' => 'support',
    ];

    protected const POSTS = [
        'suing-the-model',
        'ten-thousand',
        'it-fired-the-ceo',
        'summaries-of-summaries',
    ];

    public function run(): void
    {
        $this->dir = base_path('markup/Joke AI startup satire site');

        foreach (self::PAGES as $slug => $file) {
            $this->seedPage($slug, $file);
        }

        $this->seedBlogPage();

        foreach (self::POSTS as $slug) {
            $this->seedPost($slug);
        }
    }

    protected function seedPage(string $slug, string $file): void
    {
        $en = $this->html("$file.html");
        $uk = $this->html("$file-uk.html");

        Page::updateOrCreate(['slug' => $slug], [
            'template' => 'default',
            'title' => $this->perLocale($en, $uk, fn ($h) => $this->metaTitle($h)),
            'content' => $this->perLocale($en, $uk, fn ($h) => $this->mainElement($h)),
            'meta_title' => $this->perLocale($en, $uk, fn ($h) => $this->headTitle($h)),
            'meta_description' => $this->perLocale($en, $uk, fn ($h) => $this->metaDescription($h)),
            'active' => true,
        ]);
    }

    protected function seedBlogPage(): void
    {
        $en = $this->html('blog.html');
        $uk = $this->html('blog-uk.html');

        Page::updateOrCreate(['slug' => 'blog'], [
            'template' => 'blog',
            'eyebrow' => $this->perLocale($en, $uk, fn ($h) => $this->match('#<span class="eyebrow"[^>]*>(.*?)</span>#s', $h)),
            'title' => $this->perLocale($en, $uk, fn ($h) => $this->match('#<h1[^>]*>(.*?)</h1>#s', $h)),
            'lead' => $this->perLocale($en, $uk, fn ($h) => $this->match('#<p class="text-muted mt-4"[^>]*>(.*?)</p>#s', $h)),
            // Everything after the post cards (the newsletter card) is kept as raw HTML.
            'content' => $this->perLocale($en, $uk, fn ($h) => trim($this->match('#(<div class="card elev-sm mt-6".*?)</main>#s', $h))),
            'meta_title' => $this->perLocale($en, $uk, fn ($h) => $this->headTitle($h)),
            'meta_description' => $this->perLocale($en, $uk, fn ($h) => $this->metaDescription($h)),
            'active' => true,
        ]);
    }

    protected function seedPost(string $slug): void
    {
        $en = $this->html("post-$slug.html");
        $uk = $this->html("post-$slug-uk.html");

        $meta = $this->match('#<span class="text-muted mono" style="font-size:12px">(.*?)</span>#s', $en);
        [$date, $minutes] = $this->parseDateAndMinutes($meta);

        Post::updateOrCreate(['slug' => $slug], [
            'title' => $this->perLocale($en, $uk, fn ($h) => $this->match('#<h1[^>]*>(.*?)</h1>#s', $h)),
            'excerpt' => $this->perLocale($en, $uk, fn ($h) => $this->match('#<p class="text-muted mt-4"[^>]*>(.*?)</p>#s', $h)),
            'body' => $this->perLocale($en, $uk, fn ($h) => trim($this->match('#<div class="prose">(.*?)</div>\s*<hr#s', $h))),
            'category' => $this->perLocale($en, $uk, fn ($h) => $this->match('#<span class="tag tag-accent">(.*?)</span>#s', $h)),
            'read_minutes' => $minutes,
            'published_at' => $date,
            'active' => true,
        ]);
    }

    // ---------------------------------------------------------------- helpers

    protected function html(string $file): string
    {
        return file_get_contents($this->dir.'/'.$file);
    }

    /** Build the ['en' => ..., 'uk' => ...] translation array with links rewritten per locale. */
    protected function perLocale(string $en, string $uk, callable $extract): array
    {
        return [
            'en' => $this->rewriteLinks($extract($en)),
            'uk' => $this->rewriteLinks($extract($uk)),
        ];
    }

    protected function match(string $pattern, string $html): string
    {
        preg_match($pattern, $html, $m);

        return $m[1] ?? '';
    }

    /** The whole <main ...>...</main> element, attributes included. */
    protected function mainElement(string $html): string
    {
        preg_match('#<main[^>]*>.*</main>#s', $html, $m);

        return $m[0] ?? '';
    }

    protected function headTitle(string $html): string
    {
        return html_entity_decode($this->match('#<title>(.*?)</title>#s', $html), ENT_QUOTES | ENT_HTML5);
    }

    /** Page title without the site-name suffix ("Foo — AI driven..." -> "Foo"). */
    protected function metaTitle(string $html): string
    {
        return trim(preg_split('/\s+—\s+/u', $this->headTitle($html))[0]);
    }

    protected function metaDescription(string $html): string
    {
        return html_entity_decode(
            $this->match('#<meta name="description" content="(.*?)">#s', $html),
            ENT_QUOTES | ENT_HTML5
        );
    }

    /** "4 August 2026 · 5 min" / "4 серпня 2026 · 5 хв" -> [Y-m-d, 5] */
    protected function parseDateAndMinutes(string $meta): array
    {
        [$dateStr, $minStr] = array_map('trim', explode('·', html_entity_decode($meta, ENT_QUOTES | ENT_HTML5)));
        $date = \DateTime::createFromFormat('j F Y', $dateStr);

        return [$date ? $date->format('Y-m-d') : null, (int) $minStr];
    }

    /** Rewrite static .html links to app routes (EN at root, UK under /uk). */
    protected function rewriteLinks(string $html): string
    {
        $map = [];

        foreach (self::POSTS as $slug) {
            $map["post-$slug-uk.html"] = "/uk/blog/$slug";
            $map["post-$slug.html"] = "/blog/$slug";
        }

        foreach (['index' => '', 'pricing' => 'pricing', 'blog' => 'blog', 'about' => 'about', 'contact' => 'contact', 'support' => 'support'] as $file => $path) {
            $map["$file-uk.html"] = '/uk'.($path === '' ? '' : "/$path");
            $map["$file.html"] = $path === '' ? '/' : "/$path";
        }

        return str_replace(array_keys($map), array_values($map), $html);
    }
}
