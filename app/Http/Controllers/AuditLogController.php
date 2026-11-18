<?php

namespace App\Http\Controllers;

use App\Models\LogAudit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AuditLogController extends Controller
{
    private function checkAccess(): void
    {
        $user = Auth::user();
        if (!$user?->isSuperAdmin()) {
            abort(403, 'Akses terbatas untuk Super Admin.');
        }
    }

    private function buildQuery(Request $request)
    {
        $query = LogAudit::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('entitas')) {
            $query->where('entitas', $request->entitas);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $like = $this->likeOperator();
            $query->where(function ($sub) use ($q, $like) {
                $sub->where('aksi', $like, "%{$q}%")
                    ->orWhere('entitas', $like, "%{$q}%");
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $this->checkAccess();

        $query = $this->buildQuery($request);
        $logs = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get();
        $entities = LogAudit::distinct()->pluck('entitas');

        return view('audit.index', compact('logs', 'users', 'entities'));
    }

    /**
     * Download Backup Log Audit dalam format SQL (.sql)
     */
    public function backupSql(Request $request)
    {
        $this->checkAccess();

        $query = $this->buildQuery($request);
        $logs = $query->orderBy('id', 'asc')->get();

        $admin = Auth::user();
        $timestamp = now()->format('Y-m-d_His');
        $fileName = "backup_log_audit_sipa_{$timestamp}.sql";

        $headers = [
            'Content-Type' => 'application/sql; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($logs, $admin) {
            $output = fopen('php://output', 'w');

            fwrite($output, "-- -------------------------------------------------------------\n");
            fwrite($output, "-- CADANGAN DATA LOG AUDIT SISTEM INFORMASI PEMINJAMAN ARSIP\n");
            fwrite($output, "-- DINAS KEARSIPAN DAN PERPUSTAKAAN KABUPATEN CIREBON\n");
            fwrite($output, "-- Waktu Ekspor  : " . now()->translatedFormat('d F Y, H:i:s') . "\n");
            fwrite($output, "-- Dicadangkan   : " . ($admin->name ?? 'Super Admin') . " (" . ($admin->username ?? '-') . ")\n");
            fwrite($output, "-- Total Catatan : " . $logs->count() . " baris data\n");
            fwrite($output, "-- -------------------------------------------------------------\n\n");

            fwrite($output, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
            fwrite($output, "/*!40101 SET NAMES utf8mb4 */;\n");
            fwrite($output, "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n");
            fwrite($output, "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n\n");

            fwrite($output, "CREATE TABLE IF NOT EXISTS `log_audit` (\n");
            fwrite($output, "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n");
            fwrite($output, "  `user_id` bigint unsigned DEFAULT NULL,\n");
            fwrite($output, "  `aksi` varchar(100) NOT NULL,\n");
            fwrite($output, "  `entitas` varchar(100) NOT NULL,\n");
            fwrite($output, "  `entitas_id` bigint unsigned DEFAULT NULL,\n");
            fwrite($output, "  `detail` json DEFAULT NULL,\n");
            fwrite($output, "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n");
            fwrite($output, "  PRIMARY KEY (`id`)\n");
            fwrite($output, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

            if ($logs->isNotEmpty()) {
                fwrite($output, "LOCK TABLES `log_audit` WRITE;\n");
                fwrite($output, "/*!40000 ALTER TABLE `log_audit` DISABLE KEYS */;\n\n");

                $chunks = $logs->chunk(50);
                foreach ($chunks as $chunk) {
                    $rows = [];
                    foreach ($chunk as $log) {
                        $id = (int) $log->id;
                        $userId = $log->user_id ? (int) $log->user_id : 'NULL';
                        $aksi = "'" . addslashes((string) $log->aksi) . "'";
                        $entitas = "'" . addslashes((string) $log->entitas) . "'";
                        $entitasId = $log->entitas_id ? (int) $log->entitas_id : 'NULL';

                        $detailRaw = is_array($log->detail) ? json_encode($log->detail, JSON_UNESCAPED_UNICODE) : (string) $log->detail;
                        $detail = $detailRaw ? "'" . addslashes($detailRaw) . "'" : 'NULL';

                        $createdAt = "'" . addslashes((string) $log->created_at) . "'";

                        $rows[] = "({$id}, {$userId}, {$aksi}, {$entitas}, {$entitasId}, {$detail}, {$createdAt})";
                    }
                    fwrite($output, "INSERT INTO `log_audit` (`id`, `user_id`, `aksi`, `entitas`, `entitas_id`, `detail`, `created_at`) VALUES \n" . implode(",\n", $rows) . ";\n\n");
                }

                fwrite($output, "/*!40000 ALTER TABLE `log_audit` ENABLE KEYS */;\n");
                fwrite($output, "UNLOCK TABLES;\n");
            }

            fwrite($output, "\n/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;\n");
            fwrite($output, "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
            fwrite($output, "-- [Selesai Pencadangan Data Log Audit]\n");

            fclose($output);
        };

        // Rekam aksi backup
        LogAudit::create([
            'user_id' => $admin->id,
            'aksi' => 'Backup Data Log Audit (SQL)',
            'entitas' => 'LogAudit',
            'entitas_id' => null,
            'detail' => [
                'total_baris' => $logs->count(),
                'format' => 'SQL',
            ],
        ]);

        return response()->streamDownload($callback, $fileName, $headers);
    }

    /**
     * Ekspor Data Log Audit dalam format Excel (.xlsx)
     */
    public function exportExcel(Request $request)
    {
        $this->checkAccess();

        $query = $this->buildQuery($request);
        $logs = $query->orderBy('id', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Log Audit SIPA');

        // Header Dokumen
        $sheet->setCellValue('A1', 'REKAPITULASI LOG AUDIT AKTIVITAS SISTEM (SIPA)');
        $sheet->setCellValue('A2', 'DINAS KEARSIPAN DAN PERPUSTAKAAN KABUPATEN CIREBON');
        $sheet->setCellValue('A3', 'Diekspor pada: ' . now()->translatedFormat('d F Y, H:i:s') . ' oleh ' . (Auth::user()?->name ?? 'Super Admin'));

        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->mergeCells('A3:G3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new Color('FF162660'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new Color('FF475569'));
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new Color('FF64748B'));

        // Header Kolom Tabel
        $headers = [
            'A5' => 'No',
            'B5' => 'Waktu Kejadian',
            'C5' => 'Petugas (User)',
            'D5' => 'Peran Petugas',
            'E5' => 'Aksi Dilakukan',
            'F5' => 'Entitas / ID',
            'G5' => 'Rincian Perubahan / Parameter Data',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
            $sheet->getStyle($cell)->getFont()->setBold(true)->setColor(new Color('FFFFFFFF'));
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF162660');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        }
        $sheet->getRowDimension(5)->setRowHeight(26);

        // Isi Data Baris
        $row = 6;
        foreach ($logs as $idx => $log) {
            $waktu = $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-';
            $petugas = $log->user ? $log->user->name : 'Sistem / Tamu';
            $peran = $log->user ? $log->user->peran : '-';
            $aksi = $log->aksi;
            $entitas = $log->entitas . ($log->entitas_id ? " (#{$log->entitas_id})" : '');

            $detailStr = '-';
            if (is_array($log->detail) && !empty($log->detail)) {
                $pairs = [];
                foreach ($log->detail as $k => $v) {
                    $pairs[] = "{$k}: " . (is_array($v) ? json_encode($v) : $v);
                }
                $detailStr = implode(" | ", $pairs);
            } elseif ($log->detail) {
                $detailStr = (string) $log->detail;
            }

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $waktu);
            $sheet->setCellValue('C' . $row, $petugas);
            $sheet->setCellValue('D' . $row, $peran);
            $sheet->setCellValue('E' . $row, $aksi);
            $sheet->setCellValue('F' . $row, $entitas);
            $sheet->setCellValue('G' . $row, $detailStr);

            // Striping zebra
            if ($idx % 2 === 1) {
                $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
            }

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Border Tabel
        $lastRow = max(6, $row - 1);
        $sheet->getStyle("A5:G{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE2E8F0');

        // Auto width kolom
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $dateStr = now()->format('Y-m-d');
        $fileName = "Ekspor_Log_Audit_SIPA_{$dateStr}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
