<?php

use App\Http\Controllers\ArsipController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PeminjamController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PemindaiController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\UnitPengolahController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dasbor');
});

// Autentikasi
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/captcha/refresh', [AuthController::class, 'refreshCaptcha'])->name('captcha.refresh');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Lupa & Reset Password
Route::get('/lupa-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/lupa-password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/lupa-password/ajukan', [AuthController::class, 'submitAdminResetRequest'])->name('password.admin-request');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// File Storage Delivery (Fallback aman untuk Windows/tanpa symlink)
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) {
        abort(404);
    }
    return response()->file($filePath);
})->where('path', '.*')->name('storage.local');

// Halaman dengan proteksi login
Route::middleware('auth')->group(function () {
    Route::get('/dasbor', [DasborController::class, 'index'])->name('dasbor');
    Route::get('/panduan', [DasborController::class, 'panduan'])->name('panduan');
    Route::post('/profil/ganti-password', [AuthController::class, 'updateOwnPassword'])->name('profil.password.update');

    // 1. Data Arsip & Impor Excel
    Route::get('/arsip/import', [ArsipController::class, 'showImport'])->name('arsip.import');
    Route::post('/arsip/import', [ArsipController::class, 'processImport'])->name('arsip.import.process');
    Route::post('/arsip/hapus-massal', [ArsipController::class, 'bulkDestroy'])->name('arsip.bulk-destroy');
    Route::post('/arsip/{arsip}/nilai-kondisi', [ArsipController::class, 'nilaiKondisi'])->name('arsip.nilai-kondisi');
    Route::resource('arsip', ArsipController::class);

    // API Pencarian OCR Label Berkas (FR-09, FR-35)
    Route::get('/api/arsip/cari-label', [ArsipController::class, 'cariLabel'])->name('api.arsip.cari-label');

    // 2. Data Peminjam
    Route::get('/peminjam/pdf', [PeminjamController::class, 'exportPdf'])->name('peminjam.pdf');
    Route::get('/peminjam/{peminjam}/pdf', [PeminjamController::class, 'exportShowPdf'])->name('peminjam.show.pdf');
    Route::resource('peminjam', PeminjamController::class);

    // 3. Peminjaman Arsip
    Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::get('/peminjaman/baru', [PeminjamanController::class, 'create'])->name('peminjaman.create');
    Route::post('/peminjaman/baru', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show'])->name('peminjaman.show');
    Route::post('/peminjaman/{peminjaman}/setujui', [PeminjamanController::class, 'setujui'])->name('peminjaman.setujui');
    Route::post('/peminjaman/{peminjaman}/tolak', [PeminjamanController::class, 'tolak'])->name('peminjaman.tolak');
    Route::post('/peminjaman/{peminjaman}/serah-terima', [PeminjamanController::class, 'serahTerimakan'])->name('peminjaman.serah-terima');
    Route::post('/peminjaman/{peminjaman}/batal-janjian', [PeminjamanController::class, 'batalkanJanjian'])->name('peminjaman.batal-janjian');
    Route::post('/peminjaman/{peminjaman}/perpanjang', [PeminjamanController::class, 'perpanjang'])->name('peminjaman.perpanjang');
    Route::get('/peminjaman/{peminjaman}/cetak-pdf', [PeminjamanController::class, 'cetakPdf'])->name('peminjaman.pdf');

    // 4. Pengembalian Arsip
    Route::get('/pengembalian', [PengembalianController::class, 'index'])->name('pengembalian.index');
    Route::post('/pengembalian/{peminjaman}/proses', [PengembalianController::class, 'proses'])->name('pengembalian.proses');

    // 4b. Pemindai Dokumen Cerdas (Computer Vision: OpenCV.js)
    Route::get('/pemindai', [PemindaiController::class, 'index'])->name('pemindai.index');
    Route::post('/pemindai/simpan-bukti', [PemindaiController::class, 'simpanBukti'])->name('pemindai.simpan-bukti');

    // 5. Laporan & Rekapitulasi Tahunan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');
    Route::get('/laporan/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');

    // 6. Master Unit Pengolah / Bidang (SuperAdmin)
    Route::resource('unit-pengolah', UnitPengolahController::class);

    // 7. Manajemen Pengguna / Petugas (SuperAdmin)
    Route::post('/pengguna/reset-request/{permintaan}', [PenggunaController::class, 'processResetRequest'])->name('pengguna.reset-request.process');
    Route::resource('pengguna', PenggunaController::class);

    // 8. Log Audit (SuperAdmin)
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

    // 9. Pengaturan Sistem (SuperAdmin)
    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');

    // 10. Cadangan Data & Backup (SuperAdmin)
    Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::get('/backup/database', [BackupController::class, 'downloadDatabase'])->name('backup.database');
    Route::get('/backup/audit-log', [BackupController::class, 'downloadAuditLog'])->name('backup.audit-log');
});
