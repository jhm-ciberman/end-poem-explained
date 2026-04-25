<?php

use App\Data\Poem;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::landing')->name('landing');

Route::get('/p/{slug}', function (string $slug) {
    $passage = Poem::passage($slug);
    abort_unless($passage, 404);

    $name = request()->cookie('epx_name');
    if (! $name) {
        return redirect()->route('landing');
    }

    return view('reader', [
        'slug' => $slug,
        'name' => $name,
    ]);
})->where('slug', '[0-9]{4}-[a-z0-9-]+')->name('reader');
