@extends('layouts.app')

@section('title', 'Pemindai Dokumen Cerdas (OpenCV.js)')

@section('content')
<div class="row g-3">
    <!-- Hero Header Halaman -->
    <div class="col-12">
        <div class="sipa-page-hero d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="sipa-kicker">PEMINDAIAN DOKUMEN OTOMATIS</div>
                <h3 class="fw-bold mb-1 text-slate-800">Pemindai Dokumen Cerdas (OpenCV.js)</h3>
                <p class="text-muted small mb-0">Pindai foto KTP jaminan, surat pengantar, atau berkas fisik agar otomatis terpotong rapi dan lurus seperti hasil mesin scanner.</p>
            </div>
            <div class="flex-shrink-0">
                <span id="badgeCvStatus" class="badge bg-white text-secondary border rounded-pill px-3 py-2 shadow-sm">
                    <span class="spinner-border spinner-border-sm me-1"></span> Memuat Modul OpenCV.js...
                </span>
            </div>
        </div>
    </div>

    <!-- Penjelasan Alur Pemindai -> Bukti Foto Peminjaman -->
    <div class="col-12">
        <div class="alert alert-info border-0 shadow-sm rounded-3 py-2 px-3 mb-0 small d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <div>
                <i class="bi bi-link-45deg me-1 fs-6"></i>
                <strong>Terhubung ke Bukti Foto Peminjaman:</strong>
                Gunakan pemindai ini untuk memfoto KTP jaminan, surat pengantar bertanda tangan, atau bukti serah terima agar terpotong lurus, lalu langsung kirim ke <strong>Peminjaman Baru</strong> atau lampirkan ke surat yang sudah ada.
            </div>
            <a href="{{ route('peminjaman.create') }}#buktiSection" class="btn btn-sm btn-outline-primary rounded-pill px-3 flex-shrink-0">
                <i class="bi bi-arrow-right-circle me-1"></i> Ke Form Peminjaman
            </a>
        </div>
    </div>

    <!-- Panel Kiri: Kamera Realtime / Unggah / Editor 4 Sudut -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-camera-video me-1 text-primary"></i> 1. Tangkap / Pilih Foto Dokumen</h6>
                <div class="d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="btnStartKamera">
                        <i class="bi bi-webcam me-1"></i> Kamera
                    </button>
                    <label class="btn btn-sm btn-outline-primary rounded-pill px-3 mb-0" style="cursor: pointer;">
                        <i class="bi bi-upload me-1"></i> Unggah Foto
                        <input type="file" id="inputUploadDokumen" accept="image/*" class="d-none">
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2" id="btnDemoSampel" title="Buat contoh foto dokumen miring untuk uji coba">
                        <i class="bi bi-magic me-1"></i> Contoh Uji
                    </button>
                </div>
            </div>

            <div class="card-body">
                <!-- Mode Kamera Live -->
                <div id="boxKameraLive" class="d-none text-center">
                    <div class="position-relative bg-dark rounded-3 overflow-hidden mx-auto" style="max-width: 100%; aspect-ratio: 4 / 3;">
                        <video id="videoScanner" autoplay playsinline muted class="w-100 h-100" style="object-fit: contain;"></video>
                        <canvas id="overlayRealtime" class="position-absolute top-0 start-0 w-100 h-100" style="pointer-events: none; object-fit: contain;"></canvas>
                        <div class="position-absolute top-0 start-0 m-2">
                            <span id="badgeLiveDetect" class="badge bg-dark bg-opacity-75 text-white rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                Mencari tepi kertas...
                            </span>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold" id="btnSnapScan">
                            <i class="bi bi-camera-fill me-1"></i> Jepret &amp; Luruskan Otomatis
                        </button>
                        <button type="button" class="btn btn-outline-danger rounded-pill px-3" id="btnStopKamera">
                            <i class="bi bi-stop-circle me-1"></i> Tutup
                        </button>
                    </div>
                </div>

                <!-- Placeholder Awal -->
                <div id="boxPlaceholder" class="border border-dashed rounded-3 bg-light p-5 text-center">
                    <i class="bi bi-file-earmark-richtext fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="fw-bold text-slate-800">Siap Memindai Dokumen / Berkas</h6>
                    <p class="text-muted small mb-0">
                        Nyalakan kamera HP/webcam untuk mendeteksi 4 sudut kertas secara real-time, unggah foto dokumen/KTP yang miring, atau klik <strong>Contoh Uji</strong> untuk simulasi instan.
                    </p>
                </div>

                <!-- Mode Editor 4 Sudut Interaktif -->
                <div id="boxEditorSudut" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                        <span id="badgeMethodDeteksi" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 0.74rem;">
                            4 Sudut Terdeteksi
                        </span>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" id="btnRetakeEditor">
                                <i class="bi bi-arrow-repeat me-1"></i> Ambil Gambar Ulang
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" id="btnRedetect">
                                <i class="bi bi-crosshair me-1"></i> Deteksi Ulang
                            </button>
                        </div>
                    </div>
                    <div class="bg-dark rounded-3 overflow-hidden text-center position-relative">
                        <canvas id="editorCanvas" class="w-100" style="max-height: 390px; object-fit: contain; touch-action: none; cursor: crosshair;"></canvas>
                    </div>
                    <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                        <i class="bi bi-hand-index-thumb me-1"></i> Geser 4 titik biru pada sudut gambar jika ingin menyesuaikan area potong secara manual.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Kanan: Hasil Perspective Transform + Visualisasi Canny & Hough -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-aspect-ratio me-1 text-success"></i> 2. Hasil Scan Lurus &amp; Tahapan CV</h6>
                <div class="btn-group btn-group-sm" role="group" aria-label="Filter Hasil Scan">
                    <input type="radio" class="btn-check" name="scanFilter" id="filterEnhance" value="enhance" checked>
                    <label class="btn btn-outline-primary" for="filterEnhance">Cerah</label>

                    <input type="radio" class="btn-check" name="scanFilter" id="filterOriginal" value="original">
                    <label class="btn btn-outline-primary" for="filterOriginal">Warna Asli</label>

                    <input type="radio" class="btn-check" name="scanFilter" id="filterBw" value="bw">
                    <label class="btn btn-outline-primary" for="filterBw">Hitam-Putih</label>
                </div>
            </div>

            <div class="card-body d-flex flex-column">
                <!-- Tab Visualisasi Tahapan Algoritma -->
                <ul class="nav nav-pills nav-fill small mb-3 bg-light p-1 rounded-3" id="cvStageTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-1" data-bs-toggle="pill" data-bs-target="#tabHasilWarp" type="button" role="tab">
                            <i class="bi bi-file-check me-1"></i> Hasil Lurus
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1" data-bs-toggle="pill" data-bs-target="#tabCannyEdge" type="button" role="tab">
                            <i class="bi bi-border-outer me-1"></i> Canny Edge
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1" data-bs-toggle="pill" data-bs-target="#tabHoughLine" type="button" role="tab">
                            <i class="bi bi-slash-lg me-1"></i> Hough Transform
                        </button>
                    </li>
                </ul>

                <div class="tab-content flex-grow-1">
                    <!-- Tab 1: Hasil Flatbed Warp -->
                    <div class="tab-pane fade show active" id="tabHasilWarp" role="tabpanel">
                        <div id="resultPlaceholder" class="border border-dashed rounded-3 bg-light p-5 text-center text-muted small">
                            Hasil dokumen yang sudah terpotong otomatis dan diluruskan (<em>Perspective Correction</em>) akan tampil di sini.
                        </div>
                        <div id="resultContainer" class="d-none text-center bg-light border rounded-3 p-2">
                            <canvas id="outputCanvas" class="img-fluid rounded shadow-sm" style="max-height: 360px;"></canvas>
                        </div>
                    </div>

                    <!-- Tab 2: Canny Edge Detection -->
                    <div class="tab-pane fade" id="tabCannyEdge" role="tabpanel">
                        <div class="bg-dark rounded-3 p-2 text-center">
                            <canvas id="edgePreviewCanvas" class="img-fluid rounded" style="max-height: 360px;"></canvas>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">
                            Tahap <em>Grayscale &rarr; Gaussian Blur &rarr; Morphological Close &rarr; Canny Edge Detection</em> untuk memisahkan tepi kertas dari latar meja.
                        </small>
                    </div>

                    <!-- Tab 3: Hough Transform -->
                    <div class="tab-pane fade" id="tabHoughLine" role="tabpanel">
                        <div class="bg-dark rounded-3 p-2 text-center">
                            <canvas id="houghPreviewCanvas" class="img-fluid rounded" style="max-height: 360px;"></canvas>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">
                            Tahap <em>Probabilistic Hough Transform (HoughLinesP)</em> mendeteksi segmen garis lurus tepi dokumen (garis hijau).
                        </small>
                    </div>
                </div>

                <!-- Panel Aksi Simpan / Unduh -->
                <div id="panelAksiScan" class="d-none border-top pt-3 mt-3">
                    <div class="mb-2">
                        <label for="selectJenisBukti" class="form-label small fw-semibold text-slate-800 mb-1">Jenis Bukti Foto (Disimpan ke `bukti_foto`):</label>
                        <select id="selectJenisBukti" class="form-select form-select-sm">
                            <option value="Identitas">Foto KTP / Kartu Identitas Jaminan</option>
                            <option value="Surat Bertanda Tangan">Foto Surat Pengantar / Bertanda Tangan</option>
                            <option value="Serah Terima">Bukti Foto Serah Terima Berkas</option>
                        </select>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold" id="btnRetakeScan">
                            <i class="bi bi-arrow-repeat me-1"></i> Ambil Gambar Ulang
                        </button>
                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold" id="btnDownloadScan">
                            <i class="bi bi-download me-1"></i> Unduh JPG
                        </button>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" id="btnPakaiDiPinjamBaru" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                            <i class="bi bi-file-earmark-plus me-1"></i> Jadikan Bukti Peminjaman Baru
                        </button>
                    </div>

                    @if ($daftarSurat->isNotEmpty())
                        <form action="{{ route('pemindai.simpan-bukti') }}" method="POST" id="formLampirkanSurat" class="bg-light p-2 rounded-3 border">
                            @csrf
                            <input type="hidden" name="scanned_image_data" id="hiddenScannedData">
                            <input type="hidden" name="sumber" id="hiddenScannedSumber" value="Kamera CV">
                            <input type="hidden" name="jenis" id="hiddenScannedJenis" value="Identitas">
                            <label class="form-label small fw-semibold text-slate-800 mb-1">Atau Lampirkan ke Surat Peminjaman yang Sudah Ada:</label>
                            <div class="input-group input-group-sm">
                                <select name="surat_id" class="form-select" required>
                                    <option value="">-- Pilih Nomor Surat --</option>
                                    @foreach ($daftarSurat as $s)
                                        <option value="{{ $s->id }}" {{ (string) ($selectedSuratId ?? '') === (string) $s->id ? 'selected' : '' }}>
                                            {{ $s->nomor_surat }} - {{ $s->peminjam->nama }} ({{ $s->status }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-outline-primary fw-semibold">
                                    <i class="bi bi-paperclip me-1"></i> Lampirkan ke Bukti Foto
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/opencv-doc-scanner.js') }}"></script>
<script>
    const badgeCvStatus = document.getElementById('badgeCvStatus');
    const videoScanner = document.getElementById('videoScanner');
    const overlayRealtime = document.getElementById('overlayRealtime');
    const badgeLiveDetect = document.getElementById('badgeLiveDetect');

    const boxKameraLive = document.getElementById('boxKameraLive');
    const boxPlaceholder = document.getElementById('boxPlaceholder');
    const boxEditorSudut = document.getElementById('boxEditorSudut');
    const badgeMethodDeteksi = document.getElementById('badgeMethodDeteksi');

    const editorCanvas = document.getElementById('editorCanvas');
    const outputCanvas = document.getElementById('outputCanvas');
    const edgePreviewCanvas = document.getElementById('edgePreviewCanvas');
    const houghPreviewCanvas = document.getElementById('houghPreviewCanvas');
    const resultPlaceholder = document.getElementById('resultPlaceholder');
    const resultContainer = document.getElementById('resultContainer');
    const panelAksiScan = document.getElementById('panelAksiScan');

    let camStream = null;
    let liveLoopTimer = null;
    const rawSourceCanvas = document.createElement('canvas');
    const tempFrameCanvas = document.createElement('canvas');
    let currentFilterMode = 'enhance';
    let currentSumber = 'Kamera CV';

    // Muat OpenCV.js
    SipaDocScanner.ensureLoaded().then((res) => {
        if (res.ready) {
            badgeCvStatus.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2';
            badgeCvStatus.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> OpenCV.js Aktif (Canny + Hough + Warp)';
        } else {
            badgeCvStatus.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2';
            badgeCvStatus.innerHTML = '<i class="bi bi-wifi-off me-1"></i> Mode Offline (Perspective Warp Aktif)';
        }
    });

    const cornerEditor = new SipaDocScanner.CornerEditor(editorCanvas, (newCorners) => {
        renderWarpedOutput(newCorners);
    });

    function stopKamera() {
        if (liveLoopTimer) {
            clearInterval(liveLoopTimer);
            liveLoopTimer = null;
        }
        if (camStream) {
            camStream.getTracks().forEach(t => t.stop());
            camStream = null;
        }
        boxKameraLive.classList.add('d-none');
    }

    document.getElementById('btnStartKamera').addEventListener('click', () => {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Peramban tidak mendukung akses kamera. Gunakan Unggah Foto.');
            return;
        }
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } } })
            .then((stream) => {
                camStream = stream;
                videoScanner.srcObject = stream;
                boxPlaceholder.classList.add('d-none');
                boxEditorSudut.classList.add('d-none');
                boxKameraLive.classList.remove('d-none');

                liveLoopTimer = setInterval(() => {
                    if (!videoScanner.videoWidth) return;
                    const w = videoScanner.videoWidth;
                    const h = videoScanner.videoHeight;
                    if (overlayRealtime.width !== w || overlayRealtime.height !== h) {
                        overlayRealtime.width = w;
                        overlayRealtime.height = h;
                    }
                    tempFrameCanvas.width = w;
                    tempFrameCanvas.height = h;
                    tempFrameCanvas.getContext('2d').drawImage(videoScanner, 0, 0, w, h);

                    const res = SipaDocScanner.detectDocument(tempFrameCanvas);
                    SipaDocScanner.drawRealtimeOverlay(overlayRealtime, res.corners, res.detected);
                    if (res.detected) {
                        badgeLiveDetect.className = 'badge bg-success text-white rounded-pill px-2 py-1';
                        badgeLiveDetect.textContent = '4 Sudut Terdeteksi - Siap Jepret';
                    } else {
                        badgeLiveDetect.className = 'badge bg-dark bg-opacity-75 text-white rounded-pill px-2 py-1';
                        badgeLiveDetect.textContent = 'Arahkan ke dokumen dengan latar kontras...';
                    }
                }, 220);
            })
            .catch((err) => {
                alert('Kamera tidak dapat diakses: ' + err.message);
            });
    });

    document.getElementById('btnStopKamera').addEventListener('click', () => {
        stopKamera();
        if (rawSourceCanvas.width > 0) {
            boxEditorSudut.classList.remove('d-none');
        } else {
            boxPlaceholder.classList.remove('d-none');
        }
    });

    document.getElementById('btnSnapScan').addEventListener('click', () => {
        if (!videoScanner.videoWidth) return;
        rawSourceCanvas.width = videoScanner.videoWidth;
        rawSourceCanvas.height = videoScanner.videoHeight;
        rawSourceCanvas.getContext('2d').drawImage(videoScanner, 0, 0);
        currentSumber = 'Kamera CV';
        stopKamera();
        processSourceCanvas();
    });

    document.getElementById('inputUploadDokumen').addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        stopKamera();
        const img = new Image();
        img.onload = () => {
            // Batasi resolusi input maksimal 1800px agar pemrosesan cepat dan tajam
            const maxDim = 1800;
            const scale = Math.min(1, maxDim / Math.max(img.naturalWidth, img.naturalHeight));
            rawSourceCanvas.width = Math.round(img.naturalWidth * scale);
            rawSourceCanvas.height = Math.round(img.naturalHeight * scale);
            rawSourceCanvas.getContext('2d').drawImage(img, 0, 0, rawSourceCanvas.width, rawSourceCanvas.height);
            currentSumber = 'Unggah';
            URL.revokeObjectURL(img.src);
            processSourceCanvas();
        };
        img.src = URL.createObjectURL(file);
    });

    // Tombol Contoh Uji: membuat simulasi foto dokumen miring di atas meja gelap
    document.getElementById('btnDemoSampel').addEventListener('click', () => {
        stopKamera();
        const w = 800, h = 600;
        rawSourceCanvas.width = w;
        rawSourceCanvas.height = h;
        const ctx = rawSourceCanvas.getContext('2d');

        // Latar meja kayu gelap bertekstur
        ctx.fillStyle = '#3b2f2f';
        ctx.fillRect(0, 0, w, h);
        ctx.fillStyle = '#4a3b3b';
        for (let i = 0; i < w; i += 40) ctx.fillRect(i, 0, 2, h);

        // Gambar kertas dokumen miring (trapesium perspektif)
        ctx.save();
        ctx.fillStyle = '#f8fafc';
        ctx.beginPath();
        ctx.moveTo(190, 80);   // TL
        ctx.lineTo(660, 125);  // TR
        ctx.lineTo(710, 520);  // BR
        ctx.lineTo(120, 470);  // BL
        ctx.closePath();
        ctx.fill();
        ctx.strokeStyle = '#cbd5e1';
        ctx.lineWidth = 3;
        ctx.stroke();

        // Teks contoh surat dinas miring di dalam kertas
        ctx.translate(415, 290);
        ctx.rotate(0.06);
        ctx.fillStyle = '#0f172a';
        ctx.font = 'bold 20px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('PEMERINTAH KABUPATEN CIREBON', 0, -140);
        ctx.font = 'bold 17px sans-serif';
        ctx.fillText('DINAS KEARSIPAN DAN PERPUSTAKAAN', 0, -112);
        ctx.fillRect(-190, -96, 380, 3);
        ctx.font = '15px sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText('Nomor Arsip   : ARS-2026-0014', -170, -50);
        ctx.fillText('Perihal       : Surat Pertanggungjawaban Keuangan', -170, -20);
        ctx.fillText('Lokasi Simpan : Depo 1 / Rak B-02 / Boks 04', -170, 10);
        ctx.fillText('Keterangan    : Contoh simulasi koreksi perspektif', -170, 50);
        ctx.fillRect(-170, 90, 340, 2);
        ctx.fillRect(-170, 115, 290, 2);
        ctx.fillRect(-170, 140, 310, 2);
        ctx.restore();

        currentSumber = 'Unggah';
        processSourceCanvas();
    });

    document.getElementById('btnRedetect').addEventListener('click', () => {
        if (rawSourceCanvas.width > 0) processSourceCanvas();
    });

    function retakeGambar() {
        const wasCamera = currentSumber === 'Kamera CV';
        stopKamera();
        rawSourceCanvas.width = 0;
        rawSourceCanvas.height = 0;
        outputCanvas.width = 0;
        outputCanvas.height = 0;
        const uploadInput = document.getElementById('inputUploadDokumen');
        if (uploadInput) uploadInput.value = '';
        const hiddenInput = document.getElementById('hiddenScannedData');
        if (hiddenInput) hiddenInput.value = '';

        boxEditorSudut.classList.add('d-none');
        resultContainer.classList.add('d-none');
        panelAksiScan.classList.add('d-none');
        resultPlaceholder.classList.remove('d-none');
        boxPlaceholder.classList.remove('d-none');

        if (wasCamera && navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            document.getElementById('btnStartKamera').click();
        }
    }

    document.getElementById('btnRetakeEditor').addEventListener('click', retakeGambar);
    document.getElementById('btnRetakeScan').addEventListener('click', retakeGambar);

    const selectJenisBukti = document.getElementById('selectJenisBukti');
    if (selectJenisBukti) {
        selectJenisBukti.addEventListener('change', () => {
            const hiddenJenis = document.getElementById('hiddenScannedJenis');
            if (hiddenJenis) hiddenJenis.value = selectJenisBukti.value;
        });
    }

    function processSourceCanvas() {
        boxPlaceholder.classList.add('d-none');
        boxEditorSudut.classList.remove('d-none');

        const result = SipaDocScanner.detectDocument(rawSourceCanvas, {
            edgePreviewCanvas: edgePreviewCanvas,
            houghPreviewCanvas: houghPreviewCanvas,
        });

        badgeMethodDeteksi.textContent = result.method;
        badgeMethodDeteksi.className = result.detected
            ? 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1'
            : 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1';

        cornerEditor.setImageAndCorners(rawSourceCanvas, result.corners);
        renderWarpedOutput(result.corners);
    }

    function renderWarpedOutput(corners) {
        const activeCorners = corners || cornerEditor.getCorners();
        if (!activeCorners || rawSourceCanvas.width === 0) return;

        SipaDocScanner.warpDocument(rawSourceCanvas, activeCorners, currentFilterMode, outputCanvas, 1600);
        resultPlaceholder.classList.add('d-none');
        resultContainer.classList.remove('d-none');
        panelAksiScan.classList.remove('d-none');

        const jpegData = SipaDocScanner.toCompressedJpeg(outputCanvas, 500);
        const hiddenInput = document.getElementById('hiddenScannedData');
        const hiddenSumber = document.getElementById('hiddenScannedSumber');
        if (hiddenInput) hiddenInput.value = jpegData;
        if (hiddenSumber) hiddenSumber.value = currentSumber;
    }

    document.querySelectorAll('input[name="scanFilter"]').forEach((radio) => {
        radio.addEventListener('change', (e) => {
            currentFilterMode = e.target.value;
            renderWarpedOutput(cornerEditor.getCorners());
        });
    });

    document.getElementById('btnDownloadScan').addEventListener('click', () => {
        const link = document.createElement('a');
        link.download = 'Hasil_Scan_Dokumen_SIPA_' + Date.now() + '.jpg';
        link.href = SipaDocScanner.toCompressedJpeg(outputCanvas, 800);
        link.click();
    });

    document.getElementById('btnPakaiDiPinjamBaru').addEventListener('click', () => {
        try {
            const jpegData = SipaDocScanner.toCompressedJpeg(outputCanvas, 450);
            const jenisVal = selectJenisBukti ? selectJenisBukti.value : 'Identitas';
            sessionStorage.setItem('sipa_scanned_dokumen', jpegData);
            sessionStorage.setItem('sipa_scanned_sumber', currentSumber);
            sessionStorage.setItem('sipa_scanned_jenis', jenisVal);
            window.location.href = "{{ route('peminjaman.create') }}#buktiSection";
        } catch (err) {
            alert('Gagal menyimpan sementara ke sesi browser: ' + err.message);
        }
    });
</script>
@endpush
