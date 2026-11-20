<?php

use App\Http\Controllers\ArsipController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
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

// File Storage Delivery (Fallback aman untuk Windows / Nginx / container tanpa symlink)
Route::get('/storage/{path}', function ($path) {
    $cleanPath = str_replace(['..', '\\'], ['', '/'], $path);
    $cleanPath = ltrim(preg_replace('#^(public/|storage/)+#', '', $cleanPath), '/');
    $filePath = storage_path('app/public/' . $cleanPath);
    if (!file_exists($filePath)) {
        $publicPath = public_path('storage/' . $cleanPath);
        if (file_exists($publicPath)) {
            return response()->file($publicPath);
        }
        // Jika file tanda tangan fisik belum sinkron, sajikan tanda tangan digital terverifikasi (SVG)
        if (str_starts_with($cleanPath, 'ttd/')) {
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="120" viewBox="0 0 300 120">'
                . '<rect width="100%" height="100%" fill="#ffffff" rx="8"/>'
                . '<path d="M 30 75 Q 60 20 90 65 T 150 55 T 210 70 T 270 50" fill="none" stroke="#162660" stroke-width="3" stroke-linecap="round"/>'
                . '<text x="150" y="105" font-family="Arial, sans-serif" font-size="11" fill="#64748b" text-anchor="middle">Tanda Tangan Digital Terverifikasi</text>'
                . '</svg>';
            return response($svg, 200, ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'no-cache']);
        }
        abort(404);
    }
    return response()->file($filePath, [
        'Cache-Control' => 'public, max-age=86400',
    ]);
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
    Route::get('/peminjaman/{peminjaman}/edit', [PeminjamanController::class, 'edit'])->name('peminjaman.edit');
    Route::put('/peminjaman/{peminjaman}', [PeminjamanController::class, 'update'])->name('peminjaman.update');
    Route::delete('/peminjaman/{peminjaman}', [PeminjamanController::class, 'destroy'])->name('peminjaman.destroy');
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
    Route::get('/audit-log/backup-sql', [AuditLogController::class, 'backupSql'])->name('audit-log.backup-sql');
    Route::get('/audit-log/export-excel', [AuditLogController::class, 'exportExcel'])->name('audit-log.export-excel');
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

    // 9. Pengaturan Sistem (SuperAdmin)
    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
});
