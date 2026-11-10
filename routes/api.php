<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InstansiController;
use App\Http\Controllers\Api\KendaraanDinasController;
use App\Http\Controllers\Api\MasterKendaraanController;
use App\Http\Controllers\Api\PegawaiController;
use App\Http\Controllers\Api\PerangkatDaerahController;
use App\Http\Controllers\Api\ReferensiController;
use App\Http\Controllers\Api\WarnaKendaraanController;
use Illuminate\Support\Facades\Route;

/*
| REST API JakAsset BPAD — prefix /api/v1. Dokumentasi lengkap: API.md.
| Semua endpoint kecuali login memakai header "Authorization: Bearer {token}".
*/

Route::prefix('v1')->name('api.')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('auth.login');

    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::get('referensi', ReferensiController::class)->name('referensi');

        // Asset > Kendaraan Dinas
        Route::post('kendaraan-dinas/hapus-massal', [KendaraanDinasController::class, 'hapusMassal'])->name('kendaraan-dinas.hapus-massal');
        Route::post('kendaraan-dinas/label', [KendaraanDinasController::class, 'labelBatch'])->name('kendaraan-dinas.label-batch');
        Route::get('kendaraan-dinas/{kendaraanDinas}/label', [KendaraanDinasController::class, 'label'])->name('kendaraan-dinas.label');
        Route::apiResource('kendaraan-dinas', KendaraanDinasController::class)
            ->parameters(['kendaraan-dinas' => 'kendaraanDinas']);

        // Pegawai
        Route::post('pegawai/hapus-massal', [PegawaiController::class, 'hapusMassal'])->name('pegawai.hapus-massal');
        Route::apiResource('pegawai', PegawaiController::class)
            ->parameters(['pegawai' => 'pegawai']);

        // Master Data > Kendaraan
        Route::post('master-kendaraan/hapus-massal', [MasterKendaraanController::class, 'hapusMassal'])->name('master-kendaraan.hapus-massal');
        Route::apiResource('master-kendaraan', MasterKendaraanController::class)
            ->parameters(['master-kendaraan' => 'masterKendaraan']);

        // Warna kendaraan (Warna Tersedia di Master Data > Kendaraan)
        Route::get('warna-kendaraan', [WarnaKendaraanController::class, 'index'])->name('warna-kendaraan.index');
        Route::post('warna-kendaraan', [WarnaKendaraanController::class, 'store'])->name('warna-kendaraan.store');

        // Master Data > Instansi Pemilik
        Route::post('instansi/hapus-massal', [InstansiController::class, 'hapusMassal'])->name('instansi.hapus-massal');
        Route::apiResource('instansi', InstansiController::class)
            ->parameters(['instansi' => 'instansi']);

        // Perangkat Daerah (read-only)
        Route::get('perangkat-daerah', [PerangkatDaerahController::class, 'index'])->name('perangkat-daerah.index');
        Route::get('perangkat-daerah/{instansi}', [PerangkatDaerahController::class, 'show'])->name('perangkat-daerah.show');
        Route::get('perangkat-daerah/{instansi}/kendaraan-dinas', [PerangkatDaerahController::class, 'kendaraanDinas'])->name('perangkat-daerah.kendaraan-dinas');
        Route::get('perangkat-daerah/{instansi}/pegawai', [PerangkatDaerahController::class, 'pegawai'])->name('perangkat-daerah.pegawai');
    });
});
