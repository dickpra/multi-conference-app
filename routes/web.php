<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicController;

// Bungkus rute untuk menampilkan dan memproses login dengan middleware 'guest'
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'authenticate'])->name('login.authenticate');
});

// Rute logout tetap di luar karena hanya bisa diakses oleh user yang sudah login
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/conference/{conference:slug}', [PublicController::class, 'show'])->name('conference.show');

Route::get('/', [PublicController::class, 'index'])->name('home');


// Route::get('/', function () {
//     return view('welcome');
// });
