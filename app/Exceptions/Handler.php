<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Penanganan ramah pengguna saat sesi CSRF kedaluwarsa (419 Page Expired)
        $this->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('DELETE')) {
                return redirect()->back()
                    ->withInput($request->except(['password', '_token', 'password_confirmation']))
                    ->with('error', 'Sesi formulir kedaluwarsa karena server baru saja diperbarui. Token keamanan telah disegarkan, silakan klik simpan kembali.');
            }
        });
    }
}
