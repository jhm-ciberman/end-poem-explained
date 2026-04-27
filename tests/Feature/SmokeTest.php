<?php

test('landing renders', function () {
    $this->get('/')->assertSuccessful();
});

test('full poem renders', function () {
    $this->get('/poem')->assertSuccessful();
});

test('passage renders', function (string $slug) {
    $this->get(route('reader', ['slug' => $slug]))->assertSuccessful();
})->with(function () {
    return collect(glob(__DIR__.'/../../resources/pages/*.md'))
        ->map(fn (string $path) => basename($path, '.md'))
        ->all();
});

test('unknown slug 404s', function () {
    $this->get('/p/999-does-not-exist')->assertNotFound();
});
