<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ServiceManager;
use App\Livewire\CustomerManager;
use App\Livewire\UsageManager;
use App\Livewire\BillManager;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DashboardController;



Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Layanan
    Route::get('/layanan', ServiceManager::class)->name('layanan');

    // Pelanggan
    Route::get('/pelanggan', CustomerManager::class)
        ->name('pelanggan');

    // Pemakaian
    Route::get('/pemakaian', UsageManager::class)
        ->name('pemakaian');

    // Tagihan (Bill Manager)
    Route::get('/tagihan', BillManager::class)
        ->name('tagihan');

    Route::get('/tagihan/{id}', [InvoiceController::class, 'show'])
    ->name('invoice.show');

    Route::get('/tagihan/{id}/pdf', [InvoiceController::class, 'download'])
    ->name('invoice.pdf');

    // Profile dummy
    Route::get('/profile', function () {
        return 'Profile page';
    })->name('profile.edit');

    Route::get('/laporan/bulanan', [ReportController::class, 'monthly'])->name('report.monthly');
    Route::get('/laporan/bulanan/pdf', [ReportController::class, 'monthlyPdf'])->name('report.monthly.pdf');

    

});

require __DIR__.'/auth.php';