<?php

use App\Http\Controllers\Web\SpellingCorrection2Controller;
use App\Http\Controllers\Web\SpellingCorrectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home_page', ['active_page' => 'home']);
})->name('home');
Route::get('/training', function () {
    return view('training_page', ['active_page' => 'training']); // buat form upload di view
})->name('training');

Route::post('/upload', action: [SpellingCorrection2Controller::class, 'upload'])->name('upload');
// Route::post('/upload', action: [SpellingCorrectionController::class, 'upload'])->name('upload');