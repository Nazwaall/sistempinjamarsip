<!-- Modal Penilaian Kerusakan Fisik Arsip Berbasis CNN (MobileNet-style Depthwise-Separable) -->
<div class="modal fade" id="modalCnnDamage" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white">
                <div>
                    <h5 class="modal-title fw-bold fs-6 mb-0">
                        <i class="bi bi-cpu me-2 text-info"></i>Automated Physical Damage Assessment (CNN)
                    </h5>
                    <small class="text-white-50" id="cnnModalSubtitle">Evaluasi kondisi fisik berkas arsip saat pengembalian &amp; penilaian retensi</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3 p-md-4">
                <div class="row g-3">
                    <!-- Kolom Kiri: Input Foto / Kamera / Contoh Uji + Pratinjau Anotasi -->
                    <div class="col-12 col-lg-7">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <span id="badgeCnnEngine" class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                <span class="spinner-border spinner-border-sm me-1"></span> Menyiapkan Model CNN...
                            </span>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="btnCnnKamera">
                                    <i class="bi bi-camera-video me-1"></i> Kamera
                                </button>
                                <label class="btn btn-sm btn-outline-primary rounded-pill px-3 mb-0" style="cursor: pointer;">
                                    <i class="bi bi-upload me-1"></i> Unggah Foto
                                    <input type="file" id="inputCnnUpload" accept="image/*" class="d-none">
                                </label>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 d-none" id="btnCnnRetake">
                                    <i class="bi bi-arrow-repeat me-1"></i> Ambil Gambar Ulang
                                </button>
                            </div>
                        </div>

                        <!-- Area Kamera Live -->
                        <div id="cnnBoxKamera" class="d-none bg-dark rounded-3 p-2 text-center mb-2">
                            <video id="cnnVideo" autoplay playsinline muted class="w-100 rounded" style="max-height: 310px; object-fit: contain;"></video>
                            <div class="mt-2 d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-success btn-sm rounded-pill px-4 fw-semibold" id="btnCnnJepret">
                                    <i class="bi bi-camera-fill me-1"></i> Jepret &amp; Analisis CNN
                                </button>
                                <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3" id="btnCnnStopKamera">Batal</button>
                            </div>
                        </div>

                        <!-- Placeholder Awal -->
                        <div id="cnnBoxPlaceholder" class="border border-dashed rounded-3 bg-light p-4 text-center">
                            <i class="bi bi-file-earmark-medical fs-1 text-primary d-block mb-2"></i>
                            <h6 class="fw-bold text-slate-800 mb-1">Ambil Foto Lembar Fisik Arsip</h6>
                            <p class="text-muted small mb-3">
                                Model CNN menganalisis bercak jamur / noda air, robekan margin / lubang serangga, dan kertas rapuh / tinta luntur.
                            </p>
                            <div class="d-flex flex-wrap justify-content-center gap-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 btn-cnn-demo" data-sample="bersih">Contoh: Utuh</button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 btn-cnn-demo" data-sample="robek">Contoh: Robek Margin Kiri</button>
                                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2 btn-cnn-demo" data-sample="jamur">Contoh: Jamur / Noda Air</button>
                                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-2 btn-cnn-demo" data-sample="rapuh">Contoh: Kertas Rapuh</button>
                            </div>
                        </div>

                        <!-- Hasil Canvas dengan Bounding Box -->
                        <div id="cnnBoxPreview" class="d-none bg-dark rounded-3 p-2 text-center">
                            <canvas id="cnnPreviewCanvas" class="img-fluid rounded" style="max-height: 360px;"></canvas>
                            <div class="text-white-50 small mt-1" style="font-size: 0.72rem;">
                                Kotak merah: Robekan/Lubang &bull; Kuning: Jamur/Noda Air &bull; Oranye: Kertas Rapuh/Tinta Luntur
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Skor Integritas Fisik & Breakdown Probabilitas CNN -->
                    <div class="col-12 col-lg-5">
                        <div class="card border bg-light h-100 rounded-3">
                            <div class="card-body d-flex flex-column justify-content-between p-3">
                                <div>
                                    <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3">
                                        <i class="bi bi-clipboard2-pulse text-primary me-1"></i> Hasil Evaluasi Degradasi
                                    </h6>

                                    <div id="cnnResultEmpty" class="text-center py-4 text-muted small">
                                        Belum ada citra dianalisis. Nyalakan kamera, unggah foto berkas, atau klik contoh uji di kiri.
                                    </div>

                                    <div id="cnnResultLoading" class="d-none text-center py-4">
                                        <div class="spinner-border text-primary mb-2"></div>
                                        <div class="small fw-semibold text-slate-800">Menjalankan inferensi CNN 9 zona...</div>
                                    </div>

                                    <div id="cnnResultReady" class="d-none">
                                        <div class="bg-white border rounded-3 p-3 mb-3 text-center">
                                            <div class="small text-muted mb-1">Estimasi Skor Integritas Fisik</div>
                                            <div class="display-5 fw-bold mb-1" id="cnnScoreValue">100%</div>
                                            <div class="small fw-semibold" id="cnnVerdictText">Integritas Fisik: 100%</div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="small fw-bold text-secondary mb-2">Probabilitas Kelas CNN (MobileNet-style):</div>
                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between small"><span>Utuh / Baik</span><span id="probUtuhTxt">0%</span></div>
                                                <div class="progress" style="height: 6px;"><div id="probUtuhBar" class="progress-bar bg-success" style="width: 0%;"></div></div>
                                            </div>
                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between small"><span>Bercak Jamur / Noda Air</span><span id="probJamurTxt">0%</span></div>
                                                <div class="progress" style="height: 6px;"><div id="probJamurBar" class="progress-bar bg-warning" style="width: 0%;"></div></div>
                                            </div>
                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between small"><span>Robekan / Lubang Serangga</span><span id="probRobekTxt">0%</span></div>
                                                <div class="progress" style="height: 6px;"><div id="probRobekBar" class="progress-bar bg-danger" style="width: 0%;"></div></div>
                                            </div>
                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between small"><span>Kertas Rapuh / Tinta Luntur</span><span id="probRapuhTxt">0%</span></div>
                                                <div class="progress" style="height: 6px;"><div id="probRapuhBar" class="progress-bar" style="width: 0%; background-color: #ea580c;"></div></div>
                                            </div>
                                        </div>

                                        <div class="alert py-2 px-3 small mb-2" id="cnnRetentionBox">
                                            <strong id="cnnRetentionTitle">Rekomendasi Preservasi:</strong>
                                            <div id="cnnRetentionDesc">-</div>
                                        </div>

                                        <div id="cnnAnomalyListWrap" class="d-none">
                                            <div class="small fw-bold text-secondary mb-1">Area Anomali Terdeteksi:</div>
                                            <ul class="small text-muted ps-3 mb-0" id="cnnAnomalyList"></ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 pt-2 border-top d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                                    <button type="button" class="btn btn-success btn-sm rounded-pill px-4 fw-semibold" id="btnCnnApply" disabled>
                                        <i class="bi bi-check2-circle me-1"></i> Terapkan Hasil Penilaian
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/cnn-damage-detector.js') }}"></script>
<script>
(function () {
    const modalEl = document.getElementById('modalCnnDamage');
    if (!modalEl) return;

    const subtitleEl = document.getElementById('cnnModalSubtitle');
    const badgeEngine = document.getElementById('badgeCnnEngine');
    const boxKamera = document.getElementById('cnnBoxKamera');
    const boxPlaceholder = document.getElementById('cnnBoxPlaceholder');
    const boxPreview = document.getElementById('cnnBoxPreview');
    const videoEl = document.getElementById('cnnVideo');
    const previewCanvas = document.getElementById('cnnPreviewCanvas');
    const btnRetake = document.getElementById('btnCnnRetake');
    const btnApply = document.getElementById('btnCnnApply');
    const resEmpty = document.getElementById('cnnResultEmpty');
    const resLoading = document.getElementById('cnnResultLoading');
    const resReady = document.getElementById('cnnResultReady');

    const sourceCanvas = document.createElement('canvas');
    let activeStream = null;
    let lastSourceKind = 'upload';
    let lastAnalysis = null;
    let activeCallback = null;

    function stopCnnCam() {
        if (activeStream) {
            activeStream.getTracks().forEach((t) => t.stop());
            activeStream = null;
        }
        boxKamera.classList.add('d-none');
    }

    function resetCnnModalState() {
        stopCnnCam();
        lastAnalysis = null;
        btnApply.disabled = true;
        btnRetake.classList.add('d-none');
        boxPreview.classList.add('d-none');
        boxPlaceholder.classList.remove('d-none');
        resLoading.classList.add('d-none');
        resReady.classList.add('d-none');
        resEmpty.classList.remove('d-none');
        const up = document.getElementById('inputCnnUpload');
        if (up) up.value = '';
    }

    modalEl.addEventListener('hidden.bs.modal', resetCnnModalState);

    async function runCnnAnalysis() {
        boxPlaceholder.classList.add('d-none');
        boxPreview.classList.remove('d-none');
        btnRetake.classList.remove('d-none');
        resEmpty.classList.add('d-none');
        resReady.classList.add('d-none');
        resLoading.classList.remove('d-none');
        btnApply.disabled = true;

        try {
            const info = await SipaDamageCnn.ensureReady();
            badgeEngine.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1';
            badgeEngine.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> ${info.backend} Aktif`;

            const res = await SipaDamageCnn.analyzeDocument(sourceCanvas, previewCanvas);
            lastAnalysis = {
                ...res,
                annotatedDataUrl: previewCanvas.toDataURL('image/jpeg', 0.85),
            };

            document.getElementById('cnnScoreValue').textContent = res.integrityScore + '%';
            document.getElementById('cnnScoreValue').className =
                'display-5 fw-bold mb-1 ' + (res.integrityScore >= 86 ? 'text-success' : (res.integrityScore >= 65 ? 'text-warning' : 'text-danger'));
            document.getElementById('cnnVerdictText').textContent = res.summaryText;

            const cp = res.classProbabilities;
            document.getElementById('probUtuhTxt').textContent = cp.utuh + '%';
            document.getElementById('probUtuhBar').style.width = cp.utuh + '%';
            document.getElementById('probJamurTxt').textContent = cp.jamur_noda + '%';
            document.getElementById('probJamurBar').style.width = cp.jamur_noda + '%';
            document.getElementById('probRobekTxt').textContent = cp.robek_lubang + '%';
            document.getElementById('probRobekBar').style.width = cp.robek_lubang + '%';
            document.getElementById('probRapuhTxt').textContent = cp.rapuh_luntur + '%';
            document.getElementById('probRapuhBar').style.width = cp.rapuh_luntur + '%';

            const retBox = document.getElementById('cnnRetentionBox');
            const retDesc = document.getElementById('cnnRetentionDesc');
            if (res.integrityScore >= 86) {
                retBox.className = 'alert alert-success py-2 px-3 small mb-2';
                retDesc.textContent = 'Fisik berkas baik. Layak dikembalikan ke rak/depo penyimpanan sesuai Jadwal Retensi Arsip (JRA).';
            } else if (res.integrityScore >= 65) {
                retBox.className = 'alert alert-warning py-2 px-3 small mb-2';
                retDesc.textContent = 'Terdapat kerusakan fisik ringan-sedang. Catat pada berita acara pengembalian dan jadwalkan enkapsulasi/perbaikan.';
            } else {
                retBox.className = 'alert alert-danger py-2 px-3 small mb-2';
                retDesc.textContent = 'Degradasi signifikan terdeteksi! Segera lakukan konservasi/restorasi dan alih media digital.';
            }

            const listWrap = document.getElementById('cnnAnomalyListWrap');
            const listEl = document.getElementById('cnnAnomalyList');
            listEl.innerHTML = '';
            if (res.detections.length > 0) {
                listWrap.classList.remove('d-none');
                res.detections.forEach((d) => {
                    const li = document.createElement('li');
                    li.textContent = `${d.label} (${d.tingkat}) di ${d.lokasi} (keyakinan CNN ${d.kepercayaan}%)`;
                    listEl.appendChild(li);
                });
            } else {
                listWrap.classList.add('d-none');
            }

            resLoading.classList.add('d-none');
            resReady.classList.remove('d-none');
            btnApply.disabled = false;
        } catch (err) {
            resLoading.classList.add('d-none');
            resEmpty.classList.remove('d-none');
            resEmpty.textContent = 'Gagal memproses citra: ' + err.message;
        }
    }

    document.getElementById('btnCnnKamera').addEventListener('click', () => {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Peramban tidak mendukung kamera. Gunakan Unggah Foto atau Contoh Uji.');
            return;
        }
        lastSourceKind = 'camera';
        SipaDamageCnn.ensureReady();
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } } })
            .then((stream) => {
                activeStream = stream;
                videoEl.srcObject = stream;
                boxPlaceholder.classList.add('d-none');
                boxPreview.classList.add('d-none');
                boxKamera.classList.remove('d-none');
            })
            .catch((err) => alert('Kamera tidak dapat diakses: ' + err.message));
    });

    document.getElementById('btnCnnStopKamera').addEventListener('click', () => {
        stopCnnCam();
        boxPlaceholder.classList.remove('d-none');
    });

    document.getElementById('btnCnnJepret').addEventListener('click', () => {
        if (!videoEl.videoWidth) return;
        sourceCanvas.width = videoEl.videoWidth;
        sourceCanvas.height = videoEl.videoHeight;
        sourceCanvas.getContext('2d').drawImage(videoEl, 0, 0);
        lastSourceKind = 'camera';
        stopCnnCam();
        runCnnAnalysis();
    });

    document.getElementById('inputCnnUpload').addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        stopCnnCam();
        lastSourceKind = 'upload';
        const img = new Image();
        img.onload = () => {
            const maxDim = 1000;
            const scale = Math.min(1, maxDim / Math.max(img.naturalWidth, img.naturalHeight));
            sourceCanvas.width = Math.round(img.naturalWidth * scale);
            sourceCanvas.height = Math.round(img.naturalHeight * scale);
            sourceCanvas.getContext('2d').drawImage(img, 0, 0, sourceCanvas.width, sourceCanvas.height);
            URL.revokeObjectURL(img.src);
            runCnnAnalysis();
        };
        img.src = URL.createObjectURL(file);
    });

    document.querySelectorAll('.btn-cnn-demo').forEach((btn) => {
        btn.addEventListener('click', () => {
            stopCnnCam();
            lastSourceKind = 'demo';
            const demoCanvas = SipaDamageCnn.createSampleTestCanvas(btn.dataset.sample);
            sourceCanvas.width = demoCanvas.width;
            sourceCanvas.height = demoCanvas.height;
            sourceCanvas.getContext('2d').drawImage(demoCanvas, 0, 0);
            runCnnAnalysis();
        });
    });

    btnRetake.addEventListener('click', () => {
        const wasCam = lastSourceKind === 'camera';
        resetCnnModalState();
        if (wasCam) {
            document.getElementById('btnCnnKamera').click();
        }
    });

    btnApply.addEventListener('click', () => {
        if (lastAnalysis && typeof activeCallback === 'function') {
            activeCallback(lastAnalysis);
        }
        bootstrap.Modal.getInstance(modalEl)?.hide();
    });

    window.openDamageAssessmentModal = function (opts) {
        resetCnnModalState();
        subtitleEl.textContent = opts?.subtitle || 'Evaluasi kondisi fisik berkas arsip dengan CNN';
        btnApply.innerHTML = `<i class="bi bi-check2-circle me-1"></i> ${opts?.applyLabel || 'Terapkan Hasil Penilaian'}`;
        activeCallback = opts?.onApply || null;

        SipaDamageCnn.ensureReady().then((info) => {
            badgeEngine.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1';
            badgeEngine.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> ${info.backend} Aktif`;
        }).catch((err) => {
            badgeEngine.className = 'badge bg-danger-subtle text-danger border rounded-pill px-2 py-1';
            badgeEngine.textContent = 'Model CNN gagal dimuat: ' + err.message;
        });

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };
})();
</script>
@endpush
