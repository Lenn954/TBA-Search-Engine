<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;

Route::get('/', function () {
    return view('index');
});
Route::post('/search', [SearchController::class, 'search'])->name('search');