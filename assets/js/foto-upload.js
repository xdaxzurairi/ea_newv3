/**
 * eAduan v3 – Muat naik gambar kerosakan (Kamera / Galeri)
 */
(function () {
    'use strict';

    var BASE = (document.querySelector('meta[name="base-url"]') && document.querySelector('meta[name="base-url"]').content)
        || window.EA_BASE
        || '';

    window.eaFotoUploading = false;

    function escHtml(str) {
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    window.eaTriggerFotoCam = function (index) {
        if (window.eaFotoUploading) return;
        var el = document.getElementById('file-cam-' + index);
        if (el) el.click();
    };

    window.eaTriggerFotoGal = function (index) {
        if (window.eaFotoUploading) return;
        var el = document.getElementById('file-gal-' + index);
        if (el) el.click();
    };

    window.eaClearFotoInputs = function (index) {
        var cam = document.getElementById('file-cam-' + index);
        var gal = document.getElementById('file-gal-' + index);
        if (cam) cam.value = '';
        if (gal) gal.value = '';
    };

    window.eaShowFotoPreview = function (index, src) {
        var img = document.getElementById('preview-' + index);
        var icon = document.getElementById('icon-' + index);
        var lbl = document.getElementById('lbl-' + index);
        var remove = document.getElementById('remove-' + index);
        if (!img) return;
        img.src = src;
        img.classList.remove('d-none');
        if (icon) icon.classList.add('d-none');
        if (lbl) lbl.classList.add('d-none');
        if (remove) remove.classList.remove('d-none');
    };

    window.eaHideFotoPreview = function (index) {
        var img = document.getElementById('preview-' + index);
        var icon = document.getElementById('icon-' + index);
        var lbl = document.getElementById('lbl-' + index);
        var remove = document.getElementById('remove-' + index);
        if (img) {
            img.src = '';
            img.classList.add('d-none');
        }
        if (icon) icon.classList.remove('d-none');
        if (lbl) lbl.classList.remove('d-none');
        if (remove) remove.classList.add('d-none');
    };

    window.eaHandleFoto = function (index, source) {
        var fileInput = document.getElementById('file-' + (source === 'cam' ? 'cam' : 'gal') + '-' + index);
        if (!fileInput || !fileInput.files[0]) return;

        var file = fileInput.files[0];
        window.eaFotoUploading = true;
        var statusEl = document.getElementById('foto-status');
        if (statusEl) {
            statusEl.innerHTML = '<span class="text-muted"><i class="bi bi-hourglass-split"></i> Memuat naik Gambar ' + (index + 1) + '...</span>';
        }

        var formData = new FormData();
        formData.append('foto', file);
        formData.append('index', index);

        fetch(BASE + '/ajax/upload_foto.php', { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                window.eaFotoUploading = false;
                if (data.success) {
                    var h = document.getElementById('hfoto_' + index);
                    if (h) h.value = data.filename;
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        window.eaShowFotoPreview(index, e.target.result);
                    };
                    reader.readAsDataURL(file);
                    if (statusEl) {
                        var kb = data.size_kb ? ' (' + data.size_kb + ' KB)' : '';
                        statusEl.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Gambar ' + (index + 1) + ' berjaya dimuat naik' + kb + '.</span>';
                    }
                } else {
                    if (statusEl) {
                        statusEl.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> ' + escHtml(data.error || 'Ralat') + '</span>';
                    }
                    window.eaClearFotoInputs(index);
                }
            })
            .catch(function () {
                window.eaFotoUploading = false;
                if (statusEl) {
                    statusEl.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Ralat sambungan. Sila cuba lagi.</span>';
                }
                window.eaClearFotoInputs(index);
            });
    };

    window.eaRemoveFoto = function (event, index) {
        if (event) event.stopPropagation();
        var h = document.getElementById('hfoto_' + index);
        if (h) h.value = '';
        window.eaClearFotoInputs(index);
        window.eaHideFotoPreview(index);
        var statusEl = document.getElementById('foto-status');
        if (statusEl) statusEl.innerHTML = '';
    };

    window.eaIsFotoUploading = function () {
        return !!window.eaFotoUploading;
    };

    window.eaInitFotoFromSaved = function () {
        for (var i = 0; i < 3; i++) {
            var h = document.getElementById('hfoto_' + i);
            if (h && h.value) {
                window.eaShowFotoPreview(
                    i,
                    BASE + '/ajax/foto_temp.php?f=' + encodeURIComponent(h.value)
                );
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('foto-preview-row')) {
            window.eaInitFotoFromSaved();
        }
    });
})();
