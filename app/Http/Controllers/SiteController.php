<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;

class SiteController extends Controller
{
    public function page(string $slug = 'home')
    {
        $page = Page::where('slug', $slug)->where('active', true)->firstOrFail();

        if ($page->template === 'blog') {
            $posts = Post::published()->orderByDesc('published_at')->orderByDesc('id')->get();

            return view('pages.blog', [
                'page' => $page,
                'featured' => $posts->first(),
                'posts' => $posts->slice(1),
            ]);
        }

        return view('pages.default', ['page' => $page]);
    }

    public function post(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        return view('posts.show', [
            'post' => $post,
            'next' => $post->next(),
        ]);
    }
}
