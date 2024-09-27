<?php

use App\Http\Controllers\Web\SpellingCorrection2Controller;
use App\Http\Controllers\Web\SpellingCorrectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/upload', function () {
    return view('upload'); // buat form upload di view
});

Route::post('/upload', action: [SpellingCorrection2Controller::class, 'upload'])->name('upload');
// Route::post('/upload', action: [SpellingCorrectionController::class, 'upload'])->name('upload');