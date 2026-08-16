<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

// Ukrainian site — /uk prefix. Registered first so /uk is never
// swallowed by the English page catch-all below.
Route::prefix('uk')->name('uk.')->group(function () {
    Route::get('/', [SiteController::class, 'page'])->name('home');
    Route::get('/blog', [SiteController::class, 'page'])->defaults('slug', 'blog')->name('blog');
    Route::get('/blog/{slug}', [SiteController::class, 'post'])->name('post');
    Route::get('/{slug}', [SiteController::class, 'page'])->name('page')
        ->where('slug', '[a-z0-9-]+');
});

// English site — root.
Route::get('/', [SiteController::class, 'page'])->name('home');
Route::get('/blog', [SiteController::class, 'page'])->defaults('slug', 'blog')->name('blog');
Route::get('/blog/{slug}', [SiteController::class, 'post'])->name('post');
Route::get('/{slug}', [SiteController::class, 'page'])->name('page')
    ->where('slug', '^(?!admin$|uk$)[a-z0-9-]+$');
