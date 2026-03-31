<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ServiceManager;
use App\Livewire\CustomerManager;
use App\Livewire\UsageManager;


Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // ✅ LAYANAN (Livewire)
    Route::get('/layanan', ServiceManager::class)->name('layanan');

    // ✅ HALAMAN LAIN (sementara view biasa dulu)
    Route::get('/pelanggan', CustomerManager::class)
    ->middleware(['auth'])
    ->name('pelanggan');
    Route::view('/pemakaian', 'pemakaian')->name('pemakaian');
    Route::view('/tagihan', 'tagihan')->name('tagihan');

    // Dummy profile (biar tidak error)
    Route::get('/profile', function () {
        return 'Profile page';
    })->name('profile.edit');

    Route::get('/pemakaian', UsageManager::class)
    ->middleware(['auth'])
    ->name('pemakaian');

});

require __DIR__.'/auth.php';