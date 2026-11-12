/**
 * eAduan v3 – JavaScript Utama
 * Cascade dropdown (AJAX) + utiliti borang
 */
(function () {
    'use strict';

    const BASE = document.querySelector('meta[name="base-url"]')?.content
              || window.EA_BASE
              || '/ea_newv3';

    /**
     * Generic cascade loader:
     * Apabila <select> parent bertukar, muat semula child dari endpoint AJAX.
     */
    window.cascadeLoad = function (parentId, childId, ajaxUrl, paramName) {
        const parent = document.getElementById(parentId);
        const child  = document.getElementById(childId);
        if (!parent || !child) return;

        parent.addEventListener('change', function () {
            const val = this.value;
            child.innerHTML = '<option value="">Memuat...</option>';
            if (!val) {
                child.innerHTML = '<option value="">-- Sila Pilih --</option>';
                child.dispatchEvent(new Event('change'));
                return;
            }
            fetch(BASE + '/ajax/' + ajaxUrl + '?' + paramName + '=' + encodeURIComponent(val))
                .then(r => r.json())
                .then(data => {
                    let html = '<option value="">-- Sila Pilih --</option>';
                    data.forEach(item => {
                        html += '<option value="' + escHtml(item.id) + '">'
                              + escHtml(optionLabel(item)) + '</option>';
                    });
                    child.innerHTML = html;
                    child.dispatchEvent(new Event('change'));
                })
                .catch(() => {
                    child.innerHTML = '<option value="">Ralat memuatkan</option>';
                });
        });
    };

    function escHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function optionLabel(item) {
        const id = (item.id || '').trim();
        const name = (item.name || '').trim();
        if (id && name && id.toUpperCase() !== name.toUpperCase()) {
            return id + ' - ' + name;
        }
        return name || id;
    }

    /**
     * Confirmation dialog sebelum simpan
     */
    window.confirmSimpan = function (formId) {
        const ok = confirm("Adakah anda pasti?\n[OK] = Data disimpan\n[Cancel] = Kembali");
        if (ok) {
            document.getElementById(formId).submit();
        }
    };

})();
