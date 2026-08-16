<?php

namespace App\Support;

use App\Models\Page;
use App\Models\Post;
use Illuminate\Support\Collection;

/**
 * Builds the JSON-LD graphs for the public site.
 *
 * The company is fictional, so nothing here asserts that it trades: no Product,
 * no Offer, no AggregateRating, no Review. Search engines treat invented offers
 * and ratings as spam markup, and rightly. What is described is what actually
 * exists — a website, its pages, and articles written by a real author.
 */
class Schema
{
    public static function author(): array
    {
        return [
            '@type' => 'Person',
            'name' => 'Oleg',
            'url' => 'https://github.com/swayoleg',
        ];
    }

    public static function publisher(): array
    {
        return [
            '@type' => 'Organization',
            '@id' => url('/').'#publisher',
            'name' => config('app.name'),
            'url' => url('/'),
            'description' => 'A satirical, fictional enterprise AI vendor. SIXSEVEN is not a real company and sells no product.',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('icon-512.png'),
                'width' => 512,
                'height' => 512,
            ],
        ];
    }

    /** Site-wide graph, emitted on every page. */
    public static function site(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebSite',
                    '@id' => url('/').'#website',
                    'url' => url('/'),
                    'name' => config('app.name'),
                    'description' => 'Satire of enterprise AI marketing: a fictional vendor whose product is one model call, wrapped carefully.',
                    'inLanguage' => app()->getLocale() === 'uk' ? 'uk-UA' : 'en-GB',
                    'publisher' => ['@id' => url('/').'#publisher'],
                ],
                self::publisher(),
            ],
        ];
    }

    /** Slugs that map onto a more specific schema.org page type. */
    protected const PAGE_TYPES = [
        'about' => 'AboutPage',
        'contact' => 'ContactPage',
    ];

    public static function webPage(Page $page): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => self::PAGE_TYPES[$page->slug] ?? 'WebPage',
            'url' => url()->current(),
            'name' => $page->meta_title ?: $page->title,
            'description' => (string) $page->meta_description,
            'isPartOf' => ['@id' => url('/').'#website'],
            'inLanguage' => app()->getLocale() === 'uk' ? 'uk-UA' : 'en-GB',
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url' => asset('uploads/og-image.jpg'),
            ],
        ];
    }

    /** The Field Notes index: a Blog whose posts are listed in order. */
    public static function blog(Page $page, Collection $posts): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Blog',
            '@id' => url()->current().'#blog',
            'url' => url()->current(),
            'name' => $page->meta_title ?: $page->title,
            'description' => (string) ($page->meta_description ?: $page->lead),
            'isPartOf' => ['@id' => url('/').'#website'],
            'publisher' => ['@id' => url('/').'#publisher'],
            'inLanguage' => app()->getLocale() === 'uk' ? 'uk-UA' : 'en-GB',
            'blogPost' => $posts->map(fn (Post $post) => [
                '@type' => 'BlogPosting',
                '@id' => loc_url('blog/'.$post->slug).'#post',
                'headline' => $post->title,
                'url' => loc_url('blog/'.$post->slug),
                'datePublished' => $post->published_at?->toDateString(),
                'author' => self::author(),
            ])->values()->all(),
        ];
    }

    public static function post(Post $post): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            '@id' => url()->current().'#post',
            'url' => url()->current(),
            'mainEntityOfPage' => url()->current(),
            'headline' => $post->title,
            'description' => (string) $post->excerpt,
            'articleSection' => (string) $post->category,
            'datePublished' => $post->published_at?->toDateString(),
            'dateModified' => $post->updated_at?->toDateString(),
            'timeRequired' => 'PT'.$post->read_minutes.'M',
            'wordCount' => str_word_count(strip_tags((string) $post->body)),
            'inLanguage' => app()->getLocale() === 'uk' ? 'uk-UA' : 'en-GB',
            'author' => self::author(),
            'publisher' => ['@id' => url('/').'#publisher'],
            'isPartOf' => ['@id' => url('/').'#website'],
            'image' => asset('uploads/og-image.jpg'),
        ];
    }

    public static function breadcrumbs(array $crumbs): array
    {
        $items = [];
        $position = 1;

        foreach ($crumbs as $name => $url) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $name,
                'item' => $url,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /** Render one or more graphs as script tags. */
    public static function render(array ...$graphs): string
    {
        return collect($graphs)
            ->map(fn (array $graph) => '<script type="application/ld+json">'
                .json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                .'</script>')
            ->implode("\n");
    }
}
