/**
 * SIPA Automated Physical Damage & Degradation Assessment Module
 * Runs Depthwise-Separable CNN (MobileNet-style, 96x96 RGB) trained in TensorFlow/Keras
 * using TensorFlow.js (when online) with exact Float32 pure-JS tensor fallback (offline Laragon),
 * combined with 3x3 spatial patch localization for bounding-box highlighting & integrity scoring.
 */
(function (global) {
    'use strict';

    const MODEL_URL = '/models/archive-damage-cnn/weights.json';
    const TFJS_CDN = 'https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.20.0/dist/tf.min.js';

    const ZONE_NAMES = [
        'sudut kiri atas', 'tepi atas', 'sudut kanan atas',
        'margin kiri', 'bagian tengah', 'margin kanan',
        'sudut kiri bawah', 'tepi bawah', 'sudut kanan bawah',
    ];

    const CLASS_META = {
        utuh: { label: 'Kondisi Utuh', short: 'Utuh', color: '#16a34a', fill: 'rgba(22, 163, 74, 0.14)' },
        jamur_noda: { label: 'Bercak Jamur / Noda Air', short: 'Jamur / Noda Air', color: '#d97706', fill: 'rgba(245, 158, 11, 0.24)' },
        robek_lubang: { label: 'Robekan / Lubang', short: 'Robekan / Lubang', color: '#dc2626', fill: 'rgba(220, 38, 38, 0.24)' },
        rapuh_luntur: { label: 'Kertas Rapuh / Tinta Luntur', short: 'Rapuh / Luntur', color: '#ea580c', fill: 'rgba(234, 88, 12, 0.22)' },
    };

    let modelPromise = null;
    let cachedWeights = null;
    let tfAttempted = false;

    function decodeFloat32B64(b64) {
        const bin = atob(b64);
        const bytes = new Uint8Array(bin.length);
        for (let i = 0; i < bin.length; i++) {
            bytes[i] = bin.charCodeAt(i);
        }
        return new Float32Array(bytes.buffer);
    }

    function tryLoadTfjs() {
        if (window.tf && typeof window.tf.conv2d === 'function') {
            return Promise.resolve(true);
        }
        if (tfAttempted) return Promise.resolve(Boolean(window.tf && window.tf.conv2d));
        tfAttempted = true;

        return new Promise((resolve) => {
            const script = document.createElement('script');
            script.src = TFJS_CDN;
            script.async = true;
            const timer = setTimeout(() => resolve(false), 6500);
            script.onload = () => {
                clearTimeout(timer);
                resolve(Boolean(window.tf && typeof window.tf.conv2d === 'function'));
            };
            script.onerror = () => {
                clearTimeout(timer);
                resolve(false);
            };
            document.head.appendChild(script);
        });
    }

    async function ensureReady() {
        if (cachedWeights) {
            return {
                ready: true,
                backend: window.tf && window.tf.conv2d ? 'TensorFlow.js + CNN' : 'Pure-JS CNN Engine',
                meta: cachedWeights,
            };
        }

        if (!modelPromise) {
            modelPromise = (async () => {
                const [resp] = await Promise.all([
                    fetch(MODEL_URL, { cache: 'force-cache' }),
                    tryLoadTfjs().catch(() => false),
                ]);
                if (!resp.ok) {
                    throw new Error('Gagal memuat bobot CNN (' + resp.status + ')');
                }
                const json = await resp.json();
                json.decodedLayers = json.layers.map((layer) => ({
                    ...layer,
                    kernel: decodeFloat32B64(layer.kernelB64),
                    bias: decodeFloat32B64(layer.biasB64),
                }));
                cachedWeights = json;
                return json;
            })();
        }

        await modelPromise;
        return {
            ready: true,
            backend: window.tf && window.tf.conv2d ? 'TensorFlow.js + CNN' : 'Pure-JS CNN Engine',
            meta: cachedWeights,
        };
    }

    // Pure-JS exact TensorFlow SAME-padding Conv2D / DepthwiseConv2D / MaxPool / GAP / Dense
    function conv2dSamePure(input, inH, inW, inC, kernel, kShape, bias, stride) {
        const [kh, kw, , outC] = kShape;
        const outH = Math.ceil(inH / stride);
        const outW = Math.ceil(inW / stride);
        const padH = Math.max((outH - 1) * stride + kh - inH, 0);
        const padW = Math.max((outW - 1) * stride + kw - inW, 0);
        const padTop = Math.floor(padH / 2);
        const padLeft = Math.floor(padW / 2);
        const out = new Float32Array(outH * outW * outC);

        for (let oy = 0; oy < outH; oy++) {
            const sy0 = oy * stride - padTop;
            for (let ox = 0; ox < outW; ox++) {
                const sx0 = ox * stride - padLeft;
                const outBase = (oy * outW + ox) * outC;
                for (let oc = 0; oc < outC; oc++) {
                    out[outBase + oc] = bias[oc];
                }
                for (let ky = 0; ky < kh; ky++) {
                    const iy = sy0 + ky;
                    if (iy < 0 || iy >= inH) continue;
                    for (let kx = 0; kx < kw; kx++) {
                        const ix = sx0 + kx;
                        if (ix < 0 || ix >= inW) continue;
                        const inBase = (iy * inW + ix) * inC;
                        const kBase = ((ky * kw + kx) * inC) * outC;
                        for (let ic = 0; ic < inC; ic++) {
                            const iv = input[inBase + ic];
                            const kIcBase = kBase + ic * outC;
                            for (let oc = 0; oc < outC; oc++) {
                                out[outBase + oc] += iv * kernel[kIcBase + oc];
                            }
                        }
                    }
                }
                for (let oc = 0; oc < outC; oc++) {
                    const v = out[outBase + oc];
                    out[outBase + oc] = v <= 0 ? 0 : (v >= 6 ? 6 : v);
                }
            }
        }
        return { data: out, h: outH, w: outW, c: outC };
    }

    function depthwiseSamePure(input, inH, inW, inC, kernel, kShape, bias, stride) {
        const [kh, kw] = kShape;
        const outH = Math.ceil(inH / stride);
        const outW = Math.ceil(inW / stride);
        const padH = Math.max((outH - 1) * stride + kh - inH, 0);
        const padW = Math.max((outW - 1) * stride + kw - inW, 0);
        const padTop = Math.floor(padH / 2);
        const padLeft = Math.floor(padW / 2);
        const out = new Float32Array(outH * outW * inC);

        for (let oy = 0; oy < outH; oy++) {
            const sy0 = oy * stride - padTop;
            for (let ox = 0; ox < outW; ox++) {
                const sx0 = ox * stride - padLeft;
                const outBase = (oy * outW + ox) * inC;
                for (let c = 0; c < inC; c++) {
                    out[outBase + c] = bias[c];
                }
                for (let ky = 0; ky < kh; ky++) {
                    const iy = sy0 + ky;
                    if (iy < 0 || iy >= inH) continue;
                    for (let kx = 0; kx < kw; kx++) {
                        const ix = sx0 + kx;
                        if (ix < 0 || ix >= inW) continue;
                        const inBase = (iy * inW + ix) * inC;
                        const kBase = (ky * kw + kx) * inC;
                        for (let c = 0; c < inC; c++) {
                            out[outBase + c] += input[inBase + c] * kernel[kBase + c];
                        }
                    }
                }
                for (let c = 0; c < inC; c++) {
                    const v = out[outBase + c];
                    out[outBase + c] = v <= 0 ? 0 : (v >= 6 ? 6 : v);
                }
            }
        }
        return { data: out, h: outH, w: outW, c: inC };
    }

    function maxPool2Pure(input, inH, inW, inC) {
        const outH = Math.floor(inH / 2);
        const outW = Math.floor(inW / 2);
        const out = new Float32Array(outH * outW * inC);
        for (let oy = 0; oy < outH; oy++) {
            for (let ox = 0; ox < outW; ox++) {
                const outBase = (oy * outW + ox) * inC;
                const y0 = oy * 2;
                const x0 = ox * 2;
                for (let c = 0; c < inC; c++) {
                    let m = -1e9;
                    for (let dy = 0; dy < 2; dy++) {
                        for (let dx = 0; dx < 2; dx++) {
                            const v = input[((y0 + dy) * inW + (x0 + dx)) * inC + c];
                            if (v > m) m = v;
                        }
                    }
                    out[outBase + c] = m;
                }
            }
        }
        return { data: out, h: outH, w: outW, c: inC };
    }

    function inferPure(rgbFloat, size) {
        let cur = { data: rgbFloat, h: size, w: size, c: 3 };
        for (const layer of cachedWeights.decodedLayers) {
            if (layer.type === 'conv2d') {
                cur = conv2dSamePure(cur.data, cur.h, cur.w, cur.c, layer.kernel, layer.kernelShape, layer.bias, layer.stride);
            } else if (layer.type === 'depthwise') {
                cur = depthwiseSamePure(cur.data, cur.h, cur.w, cur.c, layer.kernel, layer.kernelShape, layer.bias, layer.stride);
            } else if (layer.type === 'dense') {
                const hw = cur.h * cur.w;
                const gap = new Float32Array(cur.c);
                for (let p = 0; p < hw; p++) {
                    const pBase = p * cur.c;
                    for (let c = 0; c < cur.c; c++) gap[c] += cur.data[pBase + c];
                }
                for (let c = 0; c < cur.c; c++) gap[c] /= hw;

                const outC = layer.bias.length;
                const logits = new Float32Array(outC);
                let maxLogit = -1e9;
                for (let oc = 0; oc < outC; oc++) {
                    let sum = layer.bias[oc];
                    for (let ic = 0; ic < cur.c; ic++) {
                        sum += gap[ic] * layer.kernel[ic * outC + oc];
                    }
                    logits[oc] = sum;
                    if (sum > maxLogit) maxLogit = sum;
                }
                let expSum = 0;
                const probs = new Float32Array(outC);
                for (let oc = 0; oc < outC; oc++) {
                    probs[oc] = Math.exp(logits[oc] - maxLogit);
                    expSum += probs[oc];
                }
                for (let oc = 0; oc < outC; oc++) probs[oc] /= expSum;
                return Array.from(probs);
            }
            if (layer.pool2) {
                cur = maxPool2Pure(cur.data, cur.h, cur.w, cur.c);
            }
        }
        return [1, 0, 0, 0];
    }

    function inferTfjs(rgbFloat, size) {
        const tf = window.tf;
        return tf.tidy(() => {
            let x = tf.tensor4d(rgbFloat, [1, size, size, 3]);
            for (const layer of cachedWeights.decodedLayers) {
                if (layer.type === 'conv2d') {
                    const k = tf.tensor4d(layer.kernel, layer.kernelShape);
                    const b = tf.tensor1d(layer.bias);
                    x = tf.clipByValue(tf.add(tf.conv2d(x, k, layer.stride, 'same'), b), 0, 6);
                } else if (layer.type === 'depthwise') {
                    const k = tf.tensor4d(layer.kernel, layer.kernelShape);
                    const b = tf.tensor1d(layer.bias);
                    x = tf.clipByValue(tf.add(tf.depthwiseConv2d(x, k, [layer.stride, layer.stride], 'same'), b), 0, 6);
                } else if (layer.type === 'dense') {
                    const gap = tf.mean(x, [1, 2]);
                    const k = tf.tensor2d(layer.kernel, layer.kernelShape);
                    const b = tf.tensor1d(layer.bias);
                    return Array.from(tf.softmax(tf.add(tf.matMul(gap, k), b)).dataSync());
                }
                if (layer.pool2) {
                    x = tf.maxPool(x, 2, 2, 'valid');
                }
            }
            return [1, 0, 0, 0];
        });
    }

    function inferPatch(canvas) {
        const size = cachedWeights.inputSize || 96;
        const patchCanvas = document.createElement('canvas');
        patchCanvas.width = size;
        patchCanvas.height = size;
        const ctx = patchCanvas.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(canvas, 0, 0, size, size);
        const rgba = ctx.getImageData(0, 0, size, size).data;
        const rgb = new Float32Array(size * size * 3);
        for (let i = 0, j = 0; i < rgba.length; i += 4, j += 3) {
            rgb[j] = rgba[i] / 255.0;
            rgb[j + 1] = rgba[i + 1] / 255.0;
            rgb[j + 2] = rgba[i + 2] / 255.0;
        }
        try {
            if (window.tf && typeof window.tf.conv2d === 'function') {
                return inferTfjs(rgb, size);
            }
        } catch (_) {}
        return inferPure(rgb, size);
    }

    function localizeAnomalyInZone(workCanvas, rx, ry, rw, rh, clsName) {
        const ctx = workCanvas.getContext('2d', { willReadFrequently: true });
        const img = ctx.getImageData(rx, ry, rw, rh);
        const d = img.data;

        // Estimate local paper reference luminance from top 75th percentile (background paper)
        const hist = new Uint32Array(256);
        const totalPx = rw * rh;
        for (let i = 0; i < d.length; i += 4) {
            const luma = Math.min(255, Math.round(0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2]));
            hist[luma]++;
        }
        let cum = 0;
        let refLuma = 215;
        const target = Math.floor(totalPx * 0.72);
        for (let v = 0; v < 256; v++) {
            cum += hist[v];
            if (cum >= target) {
                refLuma = Math.max(150, v);
                break;
            }
        }

        let minX = rw, minY = rh, maxX = -1, maxY = -1, hitCount = 0;

        for (let y = 0; y < rh; y++) {
            for (let x = 0; x < rw; x++) {
                const idx = (y * rw + x) * 4;
                const r = d[idx], g = d[idx + 1], b = d[idx + 2];
                const luma = 0.299 * r + 0.587 * g + 0.114 * b;
                const maxC = Math.max(r, g, b);
                const minC = Math.min(r, g, b);
                const chroma = maxC - minC;

                let isDefect = false;
                if (clsName === 'robek_lubang') {
                    // Missing paper / desk shadow void distinctly darker than surrounding paper
                    isDefect = luma < Math.min(82, refLuma * 0.42) && r < 96 && g < 96 && b < 96;
                } else if (clsName === 'jamur_noda') {
                    // Discolored brownish / olive damp stain spots (exclude neutral dark ink text)
                    isDefect = luma >= 40 && luma <= refLuma - 28 && chroma >= 26 && (r - b >= 24 || g - b >= 22);
                } else if (clsName === 'rapuh_luntur') {
                    // Acid yellowing / foxing oxidation or wet ink bleed
                    isDefect = (r - b >= 34 && luma < 210) || (chroma >= 26 && luma < refLuma - 28);
                }

                if (isDefect) {
                    hitCount++;
                    if (x < minX) minX = x;
                    if (x > maxX) maxX = x;
                    if (y < minY) minY = y;
                    if (y > maxY) maxY = y;
                }
            }
        }

        const minRatio = clsName === 'robek_lubang' ? 0.022 : 0.03;
        if (hitCount >= Math.max(35, totalPx * minRatio) && maxX > minX + 5 && maxY > minY + 5) {
            return {
                x: rx + Math.max(0, minX - 4),
                y: ry + Math.max(0, minY - 4),
                w: Math.min(rw - Math.max(0, minX - 4), maxX - minX + 10),
                h: Math.min(rh - Math.max(0, minY - 4), maxY - minY + 10),
                areaRatio: hitCount / Math.max(1, totalPx),
            };
        }

        return null;
    }

    function describeSeverity(areaRatio) {
        if (areaRatio < 0.08) return 'kecil';
        if (areaRatio < 0.22) return 'sedang';
        return 'luas';
    }

    async function analyzeDocument(sourceCanvas, previewCanvas) {
        const initInfo = await ensureReady();

        // Standardize working canvas max 640px for localization & annotation
        const maxDim = 640;
        const scale = Math.min(1, maxDim / Math.max(sourceCanvas.width, sourceCanvas.height));
        const w = Math.max(96, Math.round(sourceCanvas.width * scale));
        const h = Math.max(96, Math.round(sourceCanvas.height * scale));

        const workCanvas = document.createElement('canvas');
        workCanvas.width = w;
        workCanvas.height = h;
        const wCtx = workCanvas.getContext('2d', { willReadFrequently: true });
        wCtx.drawImage(sourceCanvas, 0, 0, w, h);

        // 1. Global CNN evaluation on entire archive sheet
        const globalProbs = inferPatch(workCanvas);

        // 2. 3x3 Overlapping spatial grid CNN evaluation (96x96 patch per zone)
        const patchW = Math.round(w * 0.38);
        const patchH = Math.round(h * 0.38);
        const xs = [0, Math.round((w - patchW) / 2), Math.max(0, w - patchW)];
        const ys = [0, Math.round((h - patchH) / 2), Math.max(0, h - patchH)];

        const patchCanvas = document.createElement('canvas');
        patchCanvas.width = 96;
        patchCanvas.height = 96;
        const pCtx = patchCanvas.getContext('2d');

        const detections = [];
        const zoneScores = [];
        const classPeak = { utuh: globalProbs[0], jamur_noda: globalProbs[1], robek_lubang: globalProbs[2], rapuh_luntur: globalProbs[3] };

        if (w >= 180 && h >= 180) {
            let zoneIdx = 0;
            for (let row = 0; row < 3; row++) {
                for (let col = 0; col < 3; col++) {
                    const zx = xs[col];
                    const zy = ys[row];
                    pCtx.clearRect(0, 0, 96, 96);
                    pCtx.drawImage(workCanvas, zx, zy, patchW, patchH, 0, 0, 96, 96);
                    const probs = inferPatch(patchCanvas);
                    zoneScores.push({ zone: ZONE_NAMES[zoneIdx], probs });

                    let bestDmgIdx = 1;
                    if (probs[2] > probs[bestDmgIdx]) bestDmgIdx = 2;
                    if (probs[3] > probs[bestDmgIdx]) bestDmgIdx = 3;

                    const dmgProb = 1.0 - probs[0];
                    const bestClsName = cachedWeights.classes[bestDmgIdx];

                    if (dmgProb >= 0.52 && probs[bestDmgIdx] >= 0.40) {
                        const box = localizeAnomalyInZone(workCanvas, zx, zy, patchW, patchH, bestClsName);
                        if (box) {
                            if (probs[1] > classPeak.jamur_noda) classPeak.jamur_noda = probs[1];
                            if (probs[2] > classPeak.robek_lubang) classPeak.robek_lubang = probs[2];
                            if (probs[3] > classPeak.rapuh_luntur) classPeak.rapuh_luntur = probs[3];
                            detections.push({
                                classKey: bestClsName,
                                classLabel: CLASS_META[bestClsName].short,
                                zone: ZONE_NAMES[zoneIdx],
                                confidence: Math.round(probs[bestDmgIdx] * 100),
                                severity: describeSeverity(box.areaRatio),
                                areaRatio: box.areaRatio,
                                box,
                            });
                        }
                    }
                    zoneIdx++;
                }
            }
        } else if (1.0 - globalProbs[0] >= 0.52) {
            let bestDmgIdx = 1;
            if (globalProbs[2] > globalProbs[bestDmgIdx]) bestDmgIdx = 2;
            if (globalProbs[3] > globalProbs[bestDmgIdx]) bestDmgIdx = 3;
            const bestClsName = cachedWeights.classes[bestDmgIdx];
            const box = localizeAnomalyInZone(workCanvas, 0, 0, w, h, bestClsName) || { x: 4, y: 4, w: w - 8, h: h - 8, areaRatio: 0.12 };
            detections.push({
                classKey: bestClsName,
                classLabel: CLASS_META[bestClsName].short,
                zone: 'lembar dokumen',
                confidence: Math.round(globalProbs[bestDmgIdx] * 100),
                severity: describeSeverity(box.areaRatio),
                areaRatio: box.areaRatio,
                box,
            });
        }

        // Compute calibrated Physical Integrity Score (0..100%)
        let penalty = 0;
        if (detections.length === 0) {
            const globalDmg = 1.0 - globalProbs[0];
            penalty = globalDmg > 0.45 ? Math.round(globalDmg * 18) : Math.round(globalDmg * 6);
        } else {
            const avgConf = detections.reduce((acc, d) => acc + d.confidence / 100, 0) / detections.length;
            const totalAreaFrac = Math.min(0.6, detections.reduce((acc, d) => acc + d.areaRatio / 9, 0));
            const basePenalty = 8 + detections.length * 4.5 + avgConf * 6 + totalAreaFrac * 35;
            penalty = Math.min(72, Math.max(8, Math.round(basePenalty)));
        }

        const integrityScore = Math.max(15, Math.min(100, 100 - penalty));

        // Merge & summarize findings into natural Indonesian verdict
        let findingsText = 'Tidak ditemukan anomali kerusakan signifikan';
        if (detections.length > 0) {
            // Sort strongest detections first, keep top 2-3 clearest zones
            const top = [...detections].sort((a, b) => b.confidence - a.confidence).slice(0, 2);
            const phrases = top.map((d) => {
                if (d.classKey === 'robek_lubang') return `robekan ${d.severity} di ${d.zone}`;
                if (d.classKey === 'jamur_noda') return `bercak jamur / noda air (${d.severity}) di ${d.zone}`;
                return `kertas rapuh / tinta luntur di ${d.zone}`;
            });
            const uniquePhrases = [...new Set(phrases)];
            findingsText = 'Terdeteksi ' + uniquePhrases.join(' dan ');
        } else if (globalProbs[0] < 0.55) {
            let maxG = 1;
            if (globalProbs[2] > globalProbs[maxG]) maxG = 2;
            if (globalProbs[3] > globalProbs[maxG]) maxG = 3;
            findingsText = 'Indikasi awal ' + CLASS_META[cachedWeights.classes[maxG]].label.toLowerCase();
        }

        const summaryText = `Integritas Fisik: ${integrityScore}% (${findingsText})`;
        const kondisiSaran = integrityScore >= 86 ? 'Baik' : 'Rusak';

        // Render annotated preview onto target canvas if provided
        if (previewCanvas) {
            previewCanvas.width = w;
            previewCanvas.height = h;
            const pCtxOut = previewCanvas.getContext('2d');
            pCtxOut.drawImage(workCanvas, 0, 0);

            detections.forEach((d) => {
                const meta = CLASS_META[d.classKey];
                pCtxOut.fillStyle = meta.fill;
                pCtxOut.fillRect(d.box.x, d.box.y, d.box.w, d.box.h);
                pCtxOut.strokeStyle = meta.color;
                pCtxOut.lineWidth = 2.5;
                pCtxOut.strokeRect(d.box.x, d.box.y, d.box.w, d.box.h);

                const tag = `${meta.short} (${d.confidence}%)`;
                pCtxOut.font = 'bold 11px sans-serif';
                const tw = pCtxOut.measureText(tag).width + 10;
                const ty = Math.max(16, d.box.y - 2);
                pCtxOut.fillStyle = meta.color;
                pCtxOut.fillRect(d.box.x, ty - 14, tw, 16);
                pCtxOut.fillStyle = '#ffffff';
                pCtxOut.fillText(tag, d.box.x + 5, ty - 2);
            });
        }

        return {
            backend: initInfo.backend,
            modelName: cachedWeights.modelName,
            integrityScore,
            summaryText,
            findingsText,
            kondisiSaran,
            detections: detections.map((d) => ({
                jenis: d.classKey,
                label: d.classLabel,
                lokasi: d.zone,
                kepercayaan: d.confidence,
                tingkat: d.severity,
            })),
            classProbabilities: {
                utuh: Math.round(globalProbs[0] * 100),
                jamur_noda: Math.round(Math.max(globalProbs[1], classPeak.jamur_noda) * 100),
                robek_lubang: Math.round(Math.max(globalProbs[2], classPeak.robek_lubang) * 100),
                rapuh_luntur: Math.round(Math.max(globalProbs[3], classPeak.rapuh_luntur) * 100),
            },
        };
    }

    function createSampleTestCanvas(kind) {
        const w = 640, h = 480;
        const c = document.createElement('canvas');
        c.width = w;
        c.height = h;
        const ctx = c.getContext('2d');

        // Paper background
        ctx.fillStyle = kind === 'rapuh' ? '#ebd4a2' : '#f7f5ef';
        ctx.fillRect(0, 0, w, h);

        // Simulated official archive text
        ctx.fillStyle = '#1e293b';
        ctx.font = 'bold 18px serif';
        ctx.fillText('BERKAS ARSIP DINAMIS KABUPATEN CIREBON', 85, 58);
        ctx.fillRect(75, 72, 490, 2);
        ctx.font = '13px monospace';
        for (let y = 105; y <= 410; y += 26) {
            ctx.fillStyle = kind === 'rapuh' ? '#57422a' : '#334155';
            ctx.fillRect(80, y, 220 + ((y * 13) % 210), 4);
            ctx.fillRect(80, y + 9, 150 + ((y * 7) % 260), 3);
        }

        if (kind === 'robek') {
            // Left margin tear exposing dark desk background
            ctx.fillStyle = '#241b18';
            ctx.beginPath();
            ctx.moveTo(0, 155);
            ctx.lineTo(135, 195);
            ctx.lineTo(95, 232);
            ctx.lineTo(148, 265);
            ctx.lineTo(75, 305);
            ctx.lineTo(0, 340);
            ctx.closePath();
            ctx.fill();
            ctx.strokeStyle = '#9b8b72';
            ctx.lineWidth = 3;
            ctx.stroke();
        } else if (kind === 'jamur') {
            // Water stain rings & dark olive mold spots at bottom-right
            const grad = ctx.createRadialGradient(485, 355, 18, 485, 355, 105);
            grad.addColorStop(0, 'rgba(92, 86, 44, 0.72)');
            grad.addColorStop(0.65, 'rgba(138, 104, 54, 0.48)');
            grad.addColorStop(0.88, 'rgba(96, 66, 30, 0.65)');
            grad.addColorStop(1, 'rgba(138, 104, 54, 0)');
            ctx.fillStyle = grad;
            ctx.beginPath();
            ctx.arc(485, 355, 105, 0, Math.PI * 2);
            ctx.fill();
            for (let i = 0; i < 40; i++) {
                ctx.fillStyle = i % 2 === 0 ? 'rgba(56, 58, 32, 0.75)' : 'rgba(72, 48, 22, 0.65)';
                const ax = 415 + ((i * 37) % 130);
                const ay = 285 + ((i * 53) % 130);
                ctx.beginPath();
                ctx.arc(ax, ay, (i % 4) + 1.5, 0, Math.PI * 2);
                ctx.fill();
            }
        } else if (kind === 'rapuh') {
            // Foxing speckles + wet ink smear in center
            for (let i = 0; i < 70; i++) {
                ctx.fillStyle = 'rgba(165, 96, 32, 0.55)';
                ctx.beginPath();
                ctx.arc((i * 73) % w, (i * 97) % h, (i % 3) + 1.2, 0, Math.PI * 2);
                ctx.fill();
            }
            const g2 = ctx.createRadialGradient(320, 240, 10, 320, 240, 85);
            g2.addColorStop(0, 'rgba(78, 86, 128, 0.52)');
            g2.addColorStop(1, 'rgba(78, 86, 128, 0)');
            ctx.fillStyle = g2;
            ctx.beginPath();
            ctx.arc(320, 240, 85, 0, Math.PI * 2);
            ctx.fill();
        }
        return c;
    }

    global.SipaDamageCnn = {
        ensureReady,
        analyzeDocument,
        createSampleTestCanvas,
        CLASS_META,
    };
})(window);
