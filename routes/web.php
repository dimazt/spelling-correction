<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\SpellingCorrection2Controller;
use App\Http\Controllers\Web\SpellingCorrectionController;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'guest'], function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login.page');
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register.page');

    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});


Route::group(['middleware' => 'auth'], function () {
    Route::get('/', function () {
        return view('home_page', ['active_page' => 'home']);
    })->name('home');
    Route::get('/training', function () {
        return view('training_page', ['active_page' => 'training']); // buat form upload di view
    })->name('training');
    Route::get('/setting', function () {
        return view('welcome', ['active_page' => 'setting']); // buat form upload di view
    })->name('setting');

    Route::post('/upload', action: [SpellingCorrection2Controller::class, 'upload'])->name('upload');
});
// Route::post('/upload', action: [SpellingCorrectionController::class, 'upload'])->name('upload');