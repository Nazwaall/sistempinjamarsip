<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class Controller
{
    /**
     * Operator pencarian teks case-insensitive yang kompatibel dengan PostgreSQL (ILIKE) dan MySQL (LIKE).
     */
    protected function likeOperator(): string
    {
        return DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
    }

    /**
     * Mengambil nilai kolom jaminan dari pilihan dropdown maupun input manual "Lainnya".
     */
    protected function resolveJaminan(Request $request): ?string
    {
        $opsi = trim((string) $request->input('jaminan_opsi', ''));
        if ($opsi === 'Lainnya') {
            $custom = trim((string) $request->input('jaminan_lainnya', ''));
            return $custom !== '' ? mb_substr($custom, 0, 100) : 'Lainnya';
        }
        if ($opsi !== '') {
            return mb_substr($opsi, 0, 100);
        }
        $direct = trim((string) $request->input('jaminan', ''));
        return $direct !== '' ? mb_substr($direct, 0, 100) : null;
    }

    /**
     * Mendekode data URI gambar base64 (JPEG/PNG) ke disk public Storage.
     */
    protected function saveBase64Image(?string $dataUri, string $dir = 'bukti', string $prefix = 'img'): ?string
    {
        if (!$dataUri || !str_contains($dataUri, 'base64,')) {
            return null;
        }

        $header = substr($dataUri, 0, strpos($dataUri, ','));
        $ext = str_contains(strtolower($header), 'png') ? 'png' : 'jpg';
        $encoded = substr($dataUri, strpos($dataUri, ',') + 1);
        $decoded = base64_decode($encoded, true);

        if ($decoded === false || strlen($decoded) < 100) {
            return null;
        }

        $filename = $prefix . '_' . Str::uuid() . '.' . $ext;
        $relativePath = trim($dir, '/') . '/' . $filename;
        Storage::disk('public')->put($relativePath, $decoded);

        return $relativePath;
    }
}
