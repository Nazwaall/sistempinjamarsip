/**
 * OpenCV.js Smart Document Scanner Module for SIPA
 * Implements: Grayscale -> GaussianBlur -> Morphology -> Canny Edge Detection
 * -> Contour Approximation / Hough Lines / MinAreaRect -> 4-Corner Ordering
 * -> Perspective Transform (Image Warping) + Scan Filter Modes + Pure-JS Fallback.
 */
(function (global) {
    'use strict';

    let cvLoadPromise = null;
    let cvAvailable = false;

    const OPENCV_CDNS = [
        'https://docs.opencv.org/4.9.0/opencv.js',
        'https://cdn.jsdelivr.net/npm/@techstark/opencv-js@4.9.0-release.1/dist/opencv.js',
    ];

    function loadScriptSequential(urls, index = 0) {
        return new Promise((resolve) => {
            if (window.cv && window.cv.Mat) {
                cvAvailable = true;
                return resolve({ ready: true, offline: false });
            }
            if (index >= urls.length) {
                return resolve({ ready: false, offline: true });
            }

            const script = document.createElement('script');
            script.src = urls[index];
            script.async = true;

            const timeoutId = setTimeout(() => {
                script.onerror = null;
                script.onload = null;
                resolve(loadScriptSequential(urls, index + 1));
            }, 12000);

            script.onload = () => {
                clearTimeout(timeoutId);
                waitForCvRuntime()
                    .then((ok) => {
                        cvAvailable = ok;
                        resolve({ ready: ok, offline: !ok });
                    })
                    .catch(() => resolve({ ready: false, offline: true }));
            };

            script.onerror = () => {
                clearTimeout(timeoutId);
                resolve(loadScriptSequential(urls, index + 1));
            };

            document.head.appendChild(script);
        });
    }

    function waitForCvRuntime() {
        return new Promise((resolve) => {
            if (!window.cv) return resolve(false);
            if (typeof window.cv.then === 'function') {
                window.cv.then((resolvedCv) => {
                    window.cv = resolvedCv;
                    resolve(Boolean(window.cv && window.cv.Mat));
                }).catch(() => resolve(false));
                return;
            }
            if (window.cv.Mat) return resolve(true);

            const prevInit = window.cv.onRuntimeInitialized;
            let settled = false;
            const timer = setTimeout(() => {
                if (!settled) {
                    settled = true;
                    resolve(Boolean(window.cv && window.cv.Mat));
                }
            }, 8000);

            window.cv.onRuntimeInitialized = () => {
                if (typeof prevInit === 'function') prevInit();
                if (!settled) {
                    settled = true;
                    clearTimeout(timer);
                    resolve(Boolean(window.cv && window.cv.Mat));
                }
            };
        });
    }

    function ensureLoaded() {
        if (window.cv && window.cv.Mat) {
            cvAvailable = true;
            return Promise.resolve({ ready: true, offline: false });
        }
        if (!cvLoadPromise) {
            cvLoadPromise = loadScriptSequential(OPENCV_CDNS, 0);
        }
        return cvLoadPromise;
    }

    /**
     * Mengurutkan 4 titik sudut dokumen menjadi [Top-Left, Top-Right, Bottom-Right, Bottom-Left].
     */
    function orderCorners(pts) {
        if (!pts || pts.length !== 4) return null;
        const sortedBySum = [...pts].sort((a, b) => (a.x + a.y) - (b.x + b.y));
        const tl = sortedBySum[0];
        const br = sortedBySum[3];
        const remaining = [sortedBySum[1], sortedBySum[2]];
        // Top-Right memiliki nilai (y - x) paling kecil dibanding Bottom-Left
        remaining.sort((a, b) => (a.y - a.x) - (b.y - b.x));
        const tr = remaining[0];
        const bl = remaining[1];
        return [
            { x: tl.x, y: tl.y },
            { x: tr.x, y: tr.y },
            { x: br.x, y: br.y },
            { x: bl.x, y: bl.y },
        ];
    }

    function defaultInsetCorners(width, height, ratio = 0.08) {
        const mx = Math.round(width * ratio);
        const my = Math.round(height * ratio);
        return [
            { x: mx, y: my },
            { x: width - mx, y: my },
            { x: width - mx, y: height - my },
            { x: mx, y: height - my },
        ];
    }

    function isValidQuad(corners, width, height) {
        if (!corners || corners.length !== 4) return false;
        // Shoelace area check
        let area = 0;
        for (let i = 0; i < 4; i++) {
            const j = (i + 1) % 4;
            area += corners[i].x * corners[j].y - corners[j].x * corners[i].y;
        }
        area = Math.abs(area) / 2;
        const totalArea = width * height;
        return area > totalArea * 0.10 && area < totalArea * 0.99;
    }

    /**
     * Mendeteksi 4 sudut dokumen dari canvas sumber memakai OpenCV.js:
     * Grayscale -> GaussianBlur -> Morphological Close -> Canny Edge -> HoughLinesP & findContours + approxPolyDP.
     */
    function detectDocument(sourceCanvas, options = {}) {
        const srcW = sourceCanvas.width || 640;
        const srcH = sourceCanvas.height || 480;

        if (!window.cv || !window.cv.Mat) {
            return {
                detected: false,
                method: 'Mode Penyesuaian Sudut Manual (Offline)',
                corners: defaultInsetCorners(srcW, srcH, 0.08),
            };
        }

        const maxDim = options.workMaxDim || 480;
        const scale = Math.min(1, maxDim / Math.max(srcW, srcH));
        const workW = Math.max(120, Math.round(srcW * scale));
        const workH = Math.max(120, Math.round(srcH * scale));

        const workCanvas = document.createElement('canvas');
        workCanvas.width = workW;
        workCanvas.height = workH;
        const wCtx = workCanvas.getContext('2d', { willReadFrequently: true });
        wCtx.drawImage(sourceCanvas, 0, 0, workW, workH);

        let src = null, gray = null, blurred = null, closed = null, edges = null, dilated = null;
        let kernel = null, contours = null, hierarchy = null, houghLines = null;

        try {
            src = cv.imread(workCanvas);
            gray = new cv.Mat();
            blurred = new cv.Mat();
            closed = new cv.Mat();
            edges = new cv.Mat();
            dilated = new cv.Mat();

            cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);
            cv.GaussianBlur(gray, blurred, new cv.Size(5, 5), 0);

            // Morfologi Closing membantu menghilangkan detail teks di dalam kertas agar tepi kertas menonjol
            kernel = cv.getStructuringElement(cv.MORPH_RECT, new cv.Size(5, 5));
            cv.morphologyEx(blurred, closed, cv.MORPH_CLOSE, kernel);

            // Deteksi tepi Canny
            cv.Canny(closed, edges, 45, 140, 3, false);
            cv.dilate(edges, dilated, kernel);

            // Visualisasi tahap Canny jika diminta (untuk halaman Pemindai)
            if (options.edgePreviewCanvas) {
                options.edgePreviewCanvas.width = workW;
                options.edgePreviewCanvas.height = workH;
                cv.imshow(options.edgePreviewCanvas, dilated);
            }

            // Hough Transform Lines (untuk visualisasi & validasi struktur garis lurus)
            houghLines = new cv.Mat();
            cv.HoughLinesP(edges, houghLines, 1, Math.PI / 180, 45, Math.min(workW, workH) * 0.14, 20);

            if (options.houghPreviewCanvas) {
                const houghVis = src.clone();
                try {
                    for (let i = 0; i < houghLines.rows; i++) {
                        const x1 = houghLines.data32S[i * 4];
                        const y1 = houghLines.data32S[i * 4 + 1];
                        const x2 = houghLines.data32S[i * 4 + 2];
                        const y2 = houghLines.data32S[i * 4 + 3];
                        cv.line(houghVis, new cv.Point(x1, y1), new cv.Point(x2, y2), new cv.Scalar(16, 185, 129, 255), 2);
                    }
                    options.houghPreviewCanvas.width = workW;
                    options.houghPreviewCanvas.height = workH;
                    cv.imshow(options.houghPreviewCanvas, houghVis);
                } finally {
                    houghVis.delete();
                }
            }

            // Ekstraksi Kontur Terluar
            contours = new cv.MatVector();
            hierarchy = new cv.Mat();
            cv.findContours(dilated, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

            const candidates = [];
            for (let i = 0; i < contours.size(); i++) {
                const cnt = contours.get(i);
                const area = cv.contourArea(cnt);
                if (area > workW * workH * 0.10) {
                    candidates.push({ index: i, area });
                }
                cnt.delete();
            }
            candidates.sort((a, b) => b.area - a.area);

            const invScale = 1 / scale;
            let bestQuad = null;
            let method = '';

            for (let k = 0; k < Math.min(5, candidates.length); k++) {
                const cnt = contours.get(candidates[k].index);
                const approx = new cv.Mat();
                try {
                    const peri = cv.arcLength(cnt, true);
                    cv.approxPolyDP(cnt, approx, 0.025 * peri, true);
                    if (approx.rows === 4 && cv.isContourConvex(approx)) {
                        const pts = [];
                        for (let r = 0; r < 4; r++) {
                            pts.push({
                                x: Math.round(approx.data32S[r * 2] * invScale),
                                y: Math.round(approx.data32S[r * 2 + 1] * invScale),
                            });
                        }
                        const ordered = orderCorners(pts);
                        if (isValidQuad(ordered, srcW, srcH)) {
                            bestQuad = ordered;
                            method = 'Canny Edge + Contour (4 Sudut)';
                            break;
                        }
                    }
                } finally {
                    approx.delete();
                    cnt.delete();
                }
            }

            // Fallback ke MinAreaRect pada kontur dokumen terbesar jika poligon terpotong sedikit
            if (!bestQuad && candidates.length > 0) {
                const cnt = contours.get(candidates[0].index);
                try {
                    const rotatedRect = cv.minAreaRect(cnt);
                    const rectPts = cv.RotatedRect.points(rotatedRect);
                    const pts = rectPts.map((p) => ({
                        x: Math.max(0, Math.min(srcW, Math.round(p.x * invScale))),
                        y: Math.max(0, Math.min(srcH, Math.round(p.y * invScale))),
                    }));
                    const ordered = orderCorners(pts);
                    if (isValidQuad(ordered, srcW, srcH)) {
                        bestQuad = ordered;
                        method = 'Canny + MinAreaRect';
                    }
                } catch (_) {
                    // abaikan bila minAreaRect gagal pada kontur tertentu
                } finally {
                    cnt.delete();
                }
            }

            if (bestQuad) {
                return { detected: true, method, corners: bestQuad };
            }

            return {
                detected: false,
                method: 'Tepi Kurang Kontras (Sesuaikan 4 Sudut)',
                corners: defaultInsetCorners(srcW, srcH, 0.08),
            };
        } catch (err) {
            return {
                detected: false,
                method: 'Mode Penyesuaian Manual',
                corners: defaultInsetCorners(srcW, srcH, 0.08),
            };
        } finally {
            if (src) src.delete();
            if (gray) gray.delete();
            if (blurred) blurred.delete();
            if (closed) closed.delete();
            if (edges) edges.delete();
            if (dilated) dilated.delete();
            if (kernel) kernel.delete();
            if (contours) contours.delete();
            if (hierarchy) hierarchy.delete();
            if (houghLines) houghLines.delete();
        }
    }

    /**
     * Menghitung ukuran output target persegi panjang dari 4 sudut dokumen.
     */
    function computeTargetSize(corners, maxDim = 1600) {
        const [tl, tr, br, bl] = corners;
        const wTop = Math.hypot(tr.x - tl.x, tr.y - tl.y);
        const wBottom = Math.hypot(br.x - bl.x, br.y - bl.y);
        const hLeft = Math.hypot(bl.x - tl.x, bl.y - tl.y);
        const hRight = Math.hypot(br.x - tr.x, br.y - tr.y);

        let dstW = Math.max(100, Math.round(Math.max(wTop, wBottom)));
        let dstH = Math.max(100, Math.round(Math.max(hLeft, hRight)));

        if (Math.max(dstW, dstH) > maxDim) {
            const ratio = maxDim / Math.max(dstW, dstH);
            dstW = Math.round(dstW * ratio);
            dstH = Math.round(dstH * ratio);
        }
        return { dstW, dstH };
    }

    /**
     * Melakukan Perspective Transform + Auto-Crop & Filter Scanner menggunakan OpenCV.js
     * filterMode: 'original' | 'enhance' | 'bw'
     */
    function warpDocument(sourceCanvas, rawCorners, filterMode = 'enhance', outCanvas = null, maxDim = 1600) {
        const corners = orderCorners(rawCorners) || defaultInsetCorners(sourceCanvas.width, sourceCanvas.height, 0);
        const { dstW, dstH } = computeTargetSize(corners, maxDim);
        const target = outCanvas || document.createElement('canvas');
        target.width = dstW;
        target.height = dstH;

        if (!window.cv || !window.cv.Mat) {
            return pureJsWarp(sourceCanvas, corners, target, filterMode);
        }

        const [tl, tr, br, bl] = corners;
        let src = null, dst = null, srcTri = null, dstTri = null, M = null;
        let gray = null, bw = null, finalMat = null;

        try {
            src = cv.imread(sourceCanvas);
            dst = new cv.Mat();
            srcTri = cv.matFromArray(4, 1, cv.CV_32FC2, [
                tl.x, tl.y,
                tr.x, tr.y,
                br.x, br.y,
                bl.x, bl.y,
            ]);
            dstTri = cv.matFromArray(4, 1, cv.CV_32FC2, [
                0, 0,
                dstW, 0,
                dstW, dstH,
                0, dstH,
            ]);

            M = cv.getPerspectiveTransform(srcTri, dstTri);
            cv.warpPerspective(
                src,
                dst,
                M,
                new cv.Size(dstW, dstH),
                cv.INTER_LINEAR,
                cv.BORDER_CONSTANT,
                new cv.Scalar(255, 255, 255, 255)
            );

            if (filterMode === 'bw') {
                gray = new cv.Mat();
                bw = new cv.Mat();
                finalMat = new cv.Mat();
                cv.cvtColor(dst, gray, cv.COLOR_RGBA2GRAY);
                cv.adaptiveThreshold(gray, bw, 255, cv.ADAPTIVE_THRESH_GAUSSIAN_C, cv.THRESH_BINARY, 21, 10);
                cv.cvtColor(bw, finalMat, cv.COLOR_GRAY2RGBA);
                cv.imshow(target, finalMat);
            } else if (filterMode === 'enhance') {
                finalMat = new cv.Mat();
                // Tingkatkan kontras & kecerahan khas pemindai dokumen agar kertas cerah
                cv.convertScaleAbs(dst, finalMat, 1.18, 12);
                cv.imshow(target, finalMat);
            } else {
                cv.imshow(target, dst);
            }
            return target;
        } catch (err) {
            return pureJsWarp(sourceCanvas, corners, target, filterMode);
        } finally {
            if (src) src.delete();
            if (dst) dst.delete();
            if (srcTri) srcTri.delete();
            if (dstTri) dstTri.delete();
            if (M) M.delete();
            if (gray) gray.delete();
            if (bw) bw.delete();
            if (finalMat) finalMat.delete();
        }
    }

    /**
     * Pure-JS Inverse Homography Perspective Warp Fallback (jika CDN OpenCV offline).
     */
    function solveHomography(srcPts, dstPts) {
        // Memetakan dstPts -> srcPts agar lookup pixel terbalik mulus tanpa lubang
        const A = [];
        const b = [];
        for (let i = 0; i < 4; i++) {
            const x = dstPts[i].x, y = dstPts[i].y;
            const u = srcPts[i].x, v = srcPts[i].y;
            A.push([x, y, 1, 0, 0, 0, -u * x, -u * y]);
            b.push(u);
            A.push([0, 0, 0, x, y, 1, -v * x, -v * y]);
            b.push(v);
        }
        // Gaussian elimination 8x8
        const n = 8;
        for (let i = 0; i < n; i++) {
            let maxRow = i;
            for (let k = i + 1; k < n; k++) {
                if (Math.abs(A[k][i]) > Math.abs(A[maxRow][i])) maxRow = k;
            }
            [A[i], A[maxRow]] = [A[maxRow], A[i]];
            [b[i], b[maxRow]] = [b[maxRow], b[i]];
            const div = A[i][i] || 1e-10;
            for (let j = i; j < n; j++) A[i][j] /= div;
            b[i] /= div;
            for (let k = 0; k < n; k++) {
                if (k === i) continue;
                const factor = A[k][i];
                for (let j = i; j < n; j++) A[k][j] -= factor * A[i][j];
                b[k] -= factor * b[i];
            }
        }
        return [b[0], b[1], b[2], b[3], b[4], b[5], b[6], b[7], 1];
    }

    function pureJsWarp(sourceCanvas, corners, targetCanvas, filterMode) {
        const w = targetCanvas.width;
        const h = targetCanvas.height;
        const sW = sourceCanvas.width;
        const sH = sourceCanvas.height;

        const srcCtx = sourceCanvas.getContext('2d', { willReadFrequently: true });
        const srcData = srcCtx.getImageData(0, 0, sW, sH).data;
        const dstCtx = targetCanvas.getContext('2d');
        const dstImg = dstCtx.createImageData(w, h);
        const dData = dstImg.data;

        const H = solveHomography(corners, [
            { x: 0, y: 0 },
            { x: w, y: 0 },
            { x: w, y: h },
            { x: 0, y: h },
        ]);

        for (let y = 0; y < h; y++) {
            for (let x = 0; x < w; x++) {
                const denom = H[6] * x + H[7] * y + 1;
                const sx = (H[0] * x + H[1] * y + H[2]) / denom;
                const sy = (H[3] * x + H[4] * y + H[5]) / denom;
                const ix = Math.round(sx);
                const iy = Math.round(sy);
                const idx = (y * w + x) * 4;
                if (ix >= 0 && ix < sW && iy >= 0 && iy < sH) {
                    const sIdx = (iy * sW + ix) * 4;
                    let r = srcData[sIdx];
                    let g = srcData[sIdx + 1];
                    let b = srcData[sIdx + 2];
                    if (filterMode === 'enhance') {
                        r = Math.min(255, Math.max(0, Math.round(r * 1.18 + 12)));
                        g = Math.min(255, Math.max(0, Math.round(g * 1.18 + 12)));
                        b = Math.min(255, Math.max(0, Math.round(b * 1.18 + 12)));
                    } else if (filterMode === 'bw') {
                        const luma = 0.299 * r + 0.587 * g + 0.114 * b;
                        const val = luma > 135 ? 255 : 20;
                        r = g = b = val;
                    }
                    dData[idx] = r;
                    dData[idx + 1] = g;
                    dData[idx + 2] = b;
                    dData[idx + 3] = 255;
                } else {
                    dData[idx] = dData[idx + 1] = dData[idx + 2] = 255;
                    dData[idx + 3] = 255;
                }
            }
        }
        dstCtx.putImageData(dstImg, 0, 0);
        return targetCanvas;
    }

    /**
     * Kanvas interaktif agar pengguna bisa menggeser 4 titik sudut secara manual (mouse & sentuh).
     */
    class CornerEditor {
        constructor(displayCanvas, onChange) {
            this.canvas = displayCanvas;
            this.ctx = displayCanvas.getContext('2d');
            this.sourceCanvas = null;
            this.corners = null;
            this.activeIndex = -1;
            this.onChange = onChange || (() => {});
            this._bindEvents();
        }

        setImageAndCorners(sourceCanvas, corners) {
            this.sourceCanvas = sourceCanvas;
            this.canvas.width = sourceCanvas.width;
            this.canvas.height = sourceCanvas.height;
            this.corners = (corners ? corners.map((p) => ({ ...p })) : defaultInsetCorners(sourceCanvas.width, sourceCanvas.height));
            this.draw();
        }

        getCorners() {
            return this.corners ? this.corners.map((p) => ({ ...p })) : null;
        }

        draw() {
            if (!this.sourceCanvas) return;
            const ctx = this.ctx;
            const w = this.canvas.width;
            const h = this.canvas.height;
            ctx.clearRect(0, 0, w, h);
            ctx.drawImage(this.sourceCanvas, 0, 0, w, h);

            if (!this.corners) return;

            // Arsir area luar dokumen agak gelap agar bingkai dokumen jelas
            ctx.save();
            ctx.fillStyle = 'rgba(15, 23, 42, 0.35)';
            ctx.beginPath();
            ctx.rect(0, 0, w, h);
            ctx.moveTo(this.corners[0].x, this.corners[0].y);
            ctx.lineTo(this.corners[3].x, this.corners[3].y);
            ctx.lineTo(this.corners[2].x, this.corners[2].y);
            ctx.lineTo(this.corners[1].x, this.corners[1].y);
            ctx.closePath();
            ctx.fill();

            // Garis kontur 4 sisi hijau terang
            const lineWidth = Math.max(2, Math.round(Math.max(w, h) / 240));
            ctx.strokeStyle = '#10b981';
            ctx.lineWidth = lineWidth;
            ctx.beginPath();
            ctx.moveTo(this.corners[0].x, this.corners[0].y);
            for (let i = 1; i < 4; i++) ctx.lineTo(this.corners[i].x, this.corners[i].y);
            ctx.closePath();
            ctx.stroke();

            // Lingkaran pegangan 4 sudut
            const radius = Math.max(10, Math.round(Math.max(w, h) / 48));
            this.corners.forEach((pt, idx) => {
                ctx.beginPath();
                ctx.arc(pt.x, pt.y, radius, 0, Math.PI * 2);
                ctx.fillStyle = this.activeIndex === idx ? '#f59e0b' : '#2563eb';
                ctx.fill();
                ctx.lineWidth = Math.max(2, lineWidth);
                ctx.strokeStyle = '#ffffff';
                ctx.stroke();
            });
            ctx.restore();
        }

        _getEventPos(e) {
            const rect = this.canvas.getBoundingClientRect();
            const touch = e.touches && e.touches[0] ? e.touches[0] : e;
            const scaleX = this.canvas.width / (rect.width || 1);
            const scaleY = this.canvas.height / (rect.height || 1);
            return {
                x: Math.max(0, Math.min(this.canvas.width, (touch.clientX - rect.left) * scaleX)),
                y: Math.max(0, Math.min(this.canvas.height, (touch.clientY - rect.top) * scaleY)),
            };
        }

        _bindEvents() {
            const start = (e) => {
                if (!this.corners) return;
                const pos = this._getEventPos(e);
                const hitRadius = Math.max(28, Math.round(Math.max(this.canvas.width, this.canvas.height) / 18));
                let bestIdx = -1;
                let bestDist = hitRadius;
                this.corners.forEach((pt, idx) => {
                    const d = Math.hypot(pt.x - pos.x, pt.y - pos.y);
                    if (d < bestDist) {
                        bestDist = d;
                        bestIdx = idx;
                    }
                });
                if (bestIdx !== -1) {
                    this.activeIndex = bestIdx;
                    if (e.cancelable) e.preventDefault();
                    this.draw();
                }
            };

            const move = (e) => {
                if (this.activeIndex === -1 || !this.corners) return;
                if (e.cancelable) e.preventDefault();
                const pos = this._getEventPos(e);
                this.corners[this.activeIndex] = { x: Math.round(pos.x), y: Math.round(pos.y) };
                this.draw();
            };

            const end = () => {
                if (this.activeIndex !== -1) {
                    this.activeIndex = -1;
                    this.draw();
                    this.onChange(this.getCorners());
                }
            };

            this.canvas.addEventListener('mousedown', start);
            window.addEventListener('mousemove', move);
            window.addEventListener('mouseup', end);

            this.canvas.addEventListener('touchstart', start, { passive: false });
            this.canvas.addEventListener('touchmove', move, { passive: false });
            window.addEventListener('touchend', end);
        }
    }

    /**
     * Menggambar overlay kontur real-time pada canvas di atas elemen video.
     */
    function drawRealtimeOverlay(overlayCanvas, corners, detected) {
        const ctx = overlayCanvas.getContext('2d');
        const w = overlayCanvas.width;
        const h = overlayCanvas.height;
        ctx.clearRect(0, 0, w, h);

        if (!corners || !detected) return;
        ctx.save();
        ctx.strokeStyle = '#10b981';
        ctx.lineWidth = 3;
        ctx.fillStyle = 'rgba(16, 185, 129, 0.14)';
        ctx.beginPath();
        ctx.moveTo(corners[0].x, corners[0].y);
        for (let i = 1; i < 4; i++) ctx.lineTo(corners[i].x, corners[i].y);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        corners.forEach((pt) => {
            ctx.beginPath();
            ctx.arc(pt.x, pt.y, 6, 0, Math.PI * 2);
            ctx.fillStyle = '#34d399';
            ctx.fill();
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 2;
            ctx.stroke();
        });
        ctx.restore();
    }

    /**
     * Kompres canvas hasil scan menjadi Data URL JPEG maksimal <= maxKb KB.
     */
    function toCompressedJpeg(canvas, maxKb = 500) {
        let q = 0.88;
        let dataUrl = canvas.toDataURL('image/jpeg', q);
        while (dataUrl.length * 0.75 > maxKb * 1024 && q > 0.4) {
            q -= 0.08;
            dataUrl = canvas.toDataURL('image/jpeg', q);
        }
        return dataUrl;
    }

    global.SipaDocScanner = {
        ensureLoaded,
        isReady: () => cvAvailable || Boolean(window.cv && window.cv.Mat),
        detectDocument,
        warpDocument,
        orderCorners,
        defaultInsetCorners,
        drawRealtimeOverlay,
        toCompressedJpeg,
        CornerEditor,
    };
})(window);
