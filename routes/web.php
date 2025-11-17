<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PanelSwitchController;

// Bungkus rute untuk menampilkan dan memproses login dengan middleware 'guest'
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'authenticate'])->name('login.authenticate');

    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->name('register.store');

});

Route::middleware('auth:web,chair,admin')->group(function () {
    
    Route::get('/switch-to-chair/{conference:id}', [PanelSwitchController::class, 'switchToChair'])
        ->name('switch.chair');

    Route::get('/switch-to-general', [PanelSwitchController::class, 'switchToGeneral'])
        ->name('switch.general');
});

// Rute logout tetap di luar karena hanya bisa diakses oleh user yang sudah login
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/conference/{conference:slug}', [PublicController::class, 'show'])->name('conference.show');

Route::get('/', [PublicController::class, 'index'])->name('home');


// Route::get('/', function () {
//     return view('welcome');
// });
