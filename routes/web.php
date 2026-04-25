<?php

use App\Data\Poem;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing', ['firstSlug' => Poem::firstSlug()])->name('landing');

Route::get('/p/{slug}', function (string $slug) {
    abort_unless(Poem::passage($slug), 404);

    return view('reader', ['slug' => $slug]);
})->where('slug', '[0-9]{4}-[a-z0-9-]+')->name('reader');
