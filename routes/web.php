<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\KamusController;
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
    Route::get('/', [SpellingCorrection2Controller::class, 'home'])->name('home');
    Route::get('/training', [SpellingCorrection2Controller::class, 'home'])->name('training');

    Route::get('/setting', function () {
        return view('welcome', ['active_page' => 'setting']); // buat form upload di view
    })->name('setting');


    Route::get('/kamus', function () {
        return view('kamus_page', ['active_page' => 'kamus']); // buat form upload di view
    })->name('kamus');
    Route::get('/list-kamus', [KamusController::class, 'list'])->name('list.kamus');


    Route::post('/upload', action: [SpellingCorrection2Controller::class, 'upload'])->name('upload');


    Route::get('/download/{filename}', function ($filename) {
        $filepath = "results/$filename";
        if (Storage::exists($filepath)) {
            return Storage::download($filepath);
        }
        return abort(404, 'File not found.');
    })->name('download');

    Route::get('/corection-detail/{id}', [SpellingCorrection2Controller::class, 'detail'])->name('correction.detail');
    Route::post('/corection-update', [SpellingCorrection2Controller::class, 'editCorrection'])->name('correction.update');
});