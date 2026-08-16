<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Post;
use Illuminate\Console\Command;

/**
 * Writes public/sitemap.xml from the CMS rather than a hardcoded list, so a
 * page added in the admin shows up on the next run. Ukrainian URLs are only
 * included while SITE_UK_ENABLED is on — listing URLs that 404 is worse than
 * listing nothing.
 */
class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate {--path= : Write somewhere other than public/sitemap.xml}';

    protected $description = 'Generate sitemap.xml from the CMS pages and Field Notes';

    /** Slug => [priority, changefreq]. Anything not listed gets the default. */
    protected const HINTS = [
        'home' => ['1.0', 'weekly'],
        'blog' => ['0.9', 'weekly'],
        'pricing' => ['0.8', 'monthly'],
    ];

    public function handle(): int
    {
        $locales = config('site.uk_enabled') ? ['en', 'uk'] : ['en'];
        $urls = [];

        foreach (Page::where('active', true)->get() as $page) {
            [$priority, $freq] = self::HINTS[$page->slug] ?? ['0.7', 'monthly'];
            $path = $page->slug === 'home' ? '/' : $page->slug;

            foreach ($locales as $locale) {
                $urls[] = [
                    'loc' => loc_url($path, $locale),
                    'lastmod' => $page->updated_at?->toAtomString(),
                    'changefreq' => $freq,
                    'priority' => $priority,
                    'alternates' => $locales,
                    'path' => $path,
                ];
            }
        }

        foreach (Post::published()->orderByDesc('published_at')->get() as $post) {
            foreach ($locales as $locale) {
                $urls[] = [
                    'loc' => loc_url('blog/'.$post->slug, $locale),
                    'lastmod' => $post->updated_at?->toAtomString(),
                    'changefreq' => 'yearly',
                    'priority' => '0.6',
                    'alternates' => $locales,
                    'path' => 'blog/'.$post->slug,
                ];
            }
        }

        $path = $this->option('path') ?: public_path('sitemap.xml');
        file_put_contents($path, $this->toXml($urls, count($locales) > 1));

        $this->info(count($urls).' URLs written to '.$path);

        if (! config('site.uk_enabled')) {
            $this->line('Ukrainian URLs skipped: SITE_UK_ENABLED is off.');
        }

        return self::SUCCESS;
    }

    protected function toXml(array $urls, bool $withAlternates): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
            .($withAlternates ? ' xmlns:xhtml="http://www.w3.org/1999/xhtml"' : '').">\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($url['loc'])."</loc>\n";

            if ($url['lastmod']) {
                $xml .= '    <lastmod>'.$url['lastmod']."</lastmod>\n";
            }

            // hreflang pairs, so the two language versions are not read as duplicates
            if ($withAlternates) {
                foreach ($url['alternates'] as $locale) {
                    $xml .= '    <xhtml:link rel="alternate" hreflang="'.$locale
                        .'" href="'.e(loc_url($url['path'], $locale))."\"/>\n";
                }
            }

            $xml .= '    <changefreq>'.$url['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$url['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }

        return $xml.'</urlset>'."\n";
    }
}
