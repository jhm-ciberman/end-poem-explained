<?php

use App\Data\Poem;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('landing', ['firstSlug' => Poem::firstSlug()]))->name('landing');

Route::get('/p/{slug}', function (string $slug) {
    abort_unless(Poem::passage($slug), 404);

    return view('reader', ['slug' => $slug]);
})->where('slug', '[0-9]{3}-[a-z0-9-]+')->name('reader');

Route::get('/poem', fn () => view('full-poem', [
    'paragraphs' => Poem::paragraphs(),
    'firstSlug' => Poem::firstSlug(),
]))->name('full-poem');
