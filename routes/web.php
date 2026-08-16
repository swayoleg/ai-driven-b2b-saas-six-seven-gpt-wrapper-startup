<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

// Ukrainian site — /uk prefix. Registered first so /uk is never swallowed by
// the English page catch-all below. Gated behind SITE_UK_ENABLED: while the
// flag is off these routes do not exist at all, so /uk/* returns a 404 rather
// than serving half-finished translations. The content stays in the database.
if (config('site.uk_enabled')) {
    Route::prefix('uk')->name('uk.')->group(function () {
        Route::get('/', [SiteController::class, 'page'])->name('home');
        Route::get('/blog', [SiteController::class, 'page'])->defaults('slug', 'blog')->name('blog');
        Route::get('/blog/{slug}', [SiteController::class, 'post'])->name('post');
        Route::get('/{slug}', [SiteController::class, 'page'])->name('page')
            ->where('slug', '[a-z0-9-]+');
    });
}

// English site — root.
Route::get('/', [SiteController::class, 'page'])->name('home');
Route::get('/blog', [SiteController::class, 'page'])->defaults('slug', 'blog')->name('blog');
Route::get('/blog/{slug}', [SiteController::class, 'post'])->name('post');

// The two public forms. Not locale-prefixed: the markup posts here from every
// language, and the locale is read from the request by the SetLocale middleware.
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/waitlist', [FormController::class, 'waitlist'])->name('waitlist.store');
    Route::post('/newsletter', [FormController::class, 'subscribe'])->name('newsletter.store');
});

// Keep last: this matches any single-segment slug.
Route::get('/{slug}', [SiteController::class, 'page'])->name('page')
    ->where('slug', '^(?!admin$|uk$)[a-z0-9-]+$');
