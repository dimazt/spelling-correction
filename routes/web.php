<?php

use App\Http\Controllers\Web\SpellingCorrection2Controller;
use App\Http\Controllers\Web\SpellingCorrectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index', ['active_page' => 'home']);
})->name('home');
Route::get('/upload', function () {
    return view('welcome', ['active_page' => 'upload']); // buat form upload di view
})->name('upload');

Route::post('/upload', action: [SpellingCorrection2Controller::class, 'upload'])->name('upload');
// Route::post('/upload', action: [SpellingCorrectionController::class, 'upload'])->name('upload');