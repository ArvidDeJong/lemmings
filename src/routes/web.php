<?php

use Darvis\Lemmings\App\Http\Livewire\Lemmings;
use Illuminate\Support\Facades\Route;


// Route::get('/lemmings', Lemmings::class)->name('darvis.lemmings');
Route::get('/lemmings', function () {
    return view('darvis-lemmings::layouts.website');
});
