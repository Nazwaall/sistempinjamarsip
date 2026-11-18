<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\LogAudit;
use App\Models\Peminjam;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    private function checkAccess(): void
    {
        $user = Auth::user();
        if (!$user?->isSuperAdmin()) {
            abort(403, 'Akses terbatas untuk Super Admin.');
        }
    }

    public function index()
    {
        $this->checkAccess();

        $dbConnection = config('database.default');
        $dbDriver = config("database.connections.{$dbConnection}.driver", 'mysql');
        $dbDatabase = config("database.connections.{$dbConnection}.database", 'sipa');

        $stats = [
            'driver' => $dbDriver,
            'database' => $dbDatabase,
            'total_arsip' => Arsip::count(),
            'total_peminjaman' => Peminjaman::count(),
            'total_peminjam' => Peminjam::count(),
            'total_users' => User::count(),
            'total_logs' => LogAudit::count(),
        ];

        // 10 riwayat unduhan backup terakhir dari LogAudit
        $recentBackups = LogAudit::whereIn('aksi', [
            'Unduh Cadangan Database (.sql)',
            'Unduh Cadangan Log Audit (.csv)',
        ])->latest()->take(10)->get();

        return view('backup.index', compact('stats', 'recentBackups'));
    }

    public function downloadDatabase(): StreamedResponse
    {
        $this->checkAccess();

        $filename = 'sipa_backup_database_' . date('Y-m-d_His') . '.sql';

        LogAudit::create([
            'user_id' => Auth::id(),
            'aksi' => 'Unduh Cadangan Database (.sql)',
            'entitas' => 'Database',
            'entitas_id' => null,
            'detail' => [
                'filename' => $filename,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
        ]);

        $headers = [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "-- ============================================================\n");
            fwrite($handle, "-- SIPA (Sistem Informasi Peminjaman Arsip)\n");
            fwrite($handle, "-- Dinas Kearsipan dan Perpustakaan Kabupaten Cirebon\n");
            fwrite($handle, "-- Cadangan Resmi Database Sistem (.sql)\n");
            fwrite($handle, "-- Tanggal Pembuatan: " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "-- Operator / Petugas: " . (Auth::user()?->name ?? 'SuperAdmin') . "\n");
            fwrite($handle, "-- ============================================================\n\n");

            $connection = DB::connection();
            $driver = $connection->getDriverName();
            $pdo = $connection->getPdo();

            if ($driver === 'mysql') {
                fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
                fwrite($handle, "/*!40101 SET NAMES utf8mb4 */;\n");
                fwrite($handle, "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n");
                fwrite($handle, "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n\n");

                $tables = $connection->select('SHOW TABLES');
                $dbName = $connection->getDatabaseName();
                $prop = "Tables_in_" . $dbName;

                foreach ($tables as $tableRow) {
                    $table = $tableRow->$prop ?? array_values((array)$tableRow)[0];

                    if (in_array($table, ['telescope_entries', 'telescope_entries_tags', 'telescope_monitoring'])) {
                        continue;
                    }

                    fwrite($handle, "\n-- ------------------------------------------------------------\n");
                    fwrite($handle, "-- Struktur Tabel `{$table}`\n");
                    fwrite($handle, "-- ------------------------------------------------------------\n");
                    fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

                    $createTableStmt = $connection->select("SHOW CREATE TABLE `{$table}`");
                    if (!empty($createTableStmt)) {
                        $createSql = $createTableStmt[0]->{'Create Table'} ?? array_values((array)$createTableStmt[0])[1];
                        fwrite($handle, $createSql . ";\n\n");
                    }

                    $count = $connection->table($table)->count();
                    if ($count > 0) {
                        fwrite($handle, "-- Dumping data untuk tabel `{$table}` ({$count} baris)\n");
                        fwrite($handle, "LOCK TABLES `{$table}` WRITE;\n");
                        fwrite($handle, "/*!40000 ALTER TABLE `{$table}` DISABLE KEYS */;\n");

                        $hasId = false;
                        try {
                            $hasId = $connection->getSchemaBuilder()->hasColumn($table, 'id');
                        } catch (\Throwable $e) {
                            $hasId = false;
                        }

                        $processRows = function ($rows) use ($handle, $table, $pdo) {
                            foreach ($rows as $row) {
                                $fields = [];
                                foreach ((array)$row as $val) {
                                    if (is_null($val)) {
                                        $fields[] = 'NULL';
                                    } elseif (is_numeric($val) && !is_string($val)) {
                                        $fields[] = $val;
                                    } else {
                                        $fields[] = $pdo->quote($val);
                                    }
                                }
                                fwrite($handle, "INSERT INTO `{$table}` VALUES (" . implode(', ', $fields) . ");\n");
                            }
                        };

                        if ($hasId) {
                            $connection->table($table)->orderBy('id')->chunk(250, $processRows);
                        } else {
                            $rows = $connection->table($table)->get();
                            $processRows($rows);
                        }

                        fwrite($handle, "/*!40000 ALTER TABLE `{$table}` ENABLE KEYS */;\n");
                        fwrite($handle, "UNLOCK TABLES;\n\n");
                    }
                }

                fwrite($handle, "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;\n");
                fwrite($handle, "-- Cadangan database selesai.\n");

            } elseif ($driver === 'sqlite') {
                fwrite($handle, "PRAGMA foreign_keys = OFF;\n\n");

                $tables = $connection->select("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                foreach ($tables as $t) {
                    $table = $t->name;
                    fwrite($handle, "\n-- ------------------------------------------------------------\n");
                    fwrite($handle, "-- Tabel `{$table}`\n");
                    fwrite($handle, "-- ------------------------------------------------------------\n");
                    fwrite($handle, "DROP TABLE IF EXISTS \"{$table}\";\n");
                    fwrite($handle, $t->sql . ";\n\n");

                    $rows = $connection->table($table)->get();
                    foreach ($rows as $row) {
                        $fields = [];
                        foreach ((array)$row as $val) {
                            if (is_null($val)) {
                                $fields[] = 'NULL';
                            } else {
                                $fields[] = $pdo->quote((string)$val);
                            }
                        }
                        fwrite($handle, "INSERT INTO \"{$table}\" VALUES (" . implode(', ', $fields) . ");\n");
                    }
                }

                fwrite($handle, "\nPRAGMA foreign_keys = ON;\n");
            }

            fclose($handle);
        }, $filename, $headers);
    }

    public function downloadAuditLog(): StreamedResponse
    {
        $this->checkAccess();

        $filename = 'sipa_backup_log_audit_' . date('Y-m-d_His') . '.csv';

        LogAudit::create([
            'user_id' => Auth::id(),
            'aksi' => 'Unduh Cadangan Log Audit (.csv)',
            'entitas' => 'LogAudit',
            'entitas_id' => null,
            'detail' => [
                'filename' => $filename,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
        ]);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM untuk kompatibilitas Microsoft Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID Log',
                'Waktu Kejadian (WIB)',
                'Nama Petugas',
                'Username',
                'Peran Akses',
                'Aksi Dilakukan',
                'Entitas Terkait',
                'ID Entitas',
                'Detail / Perubahan Data',
            ]);

            LogAudit::with('user')->orderBy('id', 'desc')->chunk(300, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    $detailString = '';
                    if (is_array($log->detail)) {
                        $parts = [];
                        foreach ($log->detail as $k => $v) {
                            $parts[] = "{$k}: " . (is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v);
                        }
                        $detailString = implode(' | ', $parts);
                    }

                    fputcsv($handle, [
                        $log->id,
                        $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-',
                        $log->user?->name ?? 'Sistem / Tamu',
                        $log->user?->username ?? '-',
                        $log->user?->peran ?? '-',
                        $log->aksi,
                        $log->entitas,
                        $log->entitas_id ?? '-',
                        $detailString,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, $headers);
    }
}
