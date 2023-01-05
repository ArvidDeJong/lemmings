<?php

use Illuminate\Support\Facades\Route;

Route::get('/lemmings', function () {
    return view('darvis-lemmings::lemmings');
});
