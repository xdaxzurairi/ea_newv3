/**
 * e-Aduan v3 – PWA Install Button (Login Page)
 * Handles install prompt for all platforms: Chrome/Edge, Safari/iOS, Firefox
 */
(function () {
    'use strict';

    var btn      = document.getElementById('pwa-install-main-btn');
    var hint     = document.getElementById('pwa-install-hint');
    var section  = document.getElementById('pwa-install-section');
    if (!btn || !section) return;

    var deferredPrompt = null;
    var platform = detectPlatform();

    if (isStandalone()) {
        showInstalled();
        return;
    }

    configureForPlatform();

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        btn.onclick = triggerNativeInstall;
        if (hint) hint.textContent = 'Tekan butang untuk pasang terus ke peranti anda';
    });

    window.addEventListener('appinstalled', function () {
        showInstalled();
    });

    function triggerNativeInstall() {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function (result) {
            if (result.outcome === 'accepted') {
                showInstalled();
            }
            deferredPrompt = null;
        });
    }

    function configureForPlatform() {
        if (platform === 'ios') {
            btn.innerHTML = '<i class="bi bi-phone"></i> Pasang e-Aduan di iPhone/iPad';
            btn.onclick = showIosInstructions;
            if (hint) {
                hint.innerHTML = 'Ketik <i class="bi bi-box-arrow-up"></i> <strong>Share</strong> kemudian <strong>"Add to Home Screen"</strong>';
            }
        } else if (platform === 'android') {
            btn.innerHTML = '<i class="bi bi-phone"></i> Pasang e-Aduan';
            btn.onclick = showAndroidInstructions;
            if (hint) {
                hint.textContent = 'Pasang aplikasi terus ke telefon anda';
            }
        } else {
            btn.innerHTML = '<i class="bi bi-download"></i> Pasang e-Aduan';
            btn.onclick = showDesktopInstructions;
            if (hint) {
                hint.textContent = 'Pasang sebagai aplikasi di desktop anda';
            }
        }
    }

    function showIosInstructions() {
        if (document.getElementById('pwa-ios-overlay')) return;

        var overlay = document.createElement('div');
        overlay.id = 'pwa-ios-overlay';
        overlay.className = 'pwa-ios-overlay';
        overlay.innerHTML =
            '<div class="pwa-ios-modal">' +
                '<h3><i class="bi bi-phone"></i> Pasang e-Aduan</h3>' +
                '<div class="pwa-ios-step">' +
                    '<span class="pwa-ios-step-num">1</span>' +
                    '<span>Ketik ikon <strong>Share</strong> <i class="bi bi-box-arrow-up"></i> di bar bawah Safari</span>' +
                '</div>' +
                '<div class="pwa-ios-step">' +
                    '<span class="pwa-ios-step-num">2</span>' +
                    '<span>Tatal ke bawah dan pilih <strong>"Add to Home Screen"</strong> <i class="bi bi-plus-square"></i></span>' +
                '</div>' +
                '<div class="pwa-ios-step">' +
                    '<span class="pwa-ios-step-num">3</span>' +
                    '<span>Ketik <strong>"Add"</strong> di penjuru kanan atas</span>' +
                '</div>' +
                '<button class="pwa-ios-close" id="pwa-ios-close">Faham</button>' +
            '</div>';

        document.body.appendChild(overlay);

        document.getElementById('pwa-ios-close').addEventListener('click', function () {
            overlay.remove();
        });
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.remove();
        });
    }

    function showAndroidInstructions() {
        if (deferredPrompt) {
            triggerNativeInstall();
            return;
        }

        if (document.getElementById('pwa-ios-overlay')) return;

        var overlay = document.createElement('div');
        overlay.id = 'pwa-ios-overlay';
        overlay.className = 'pwa-ios-overlay';
        overlay.innerHTML =
            '<div class="pwa-ios-modal">' +
                '<h3><i class="bi bi-phone"></i> Pasang e-Aduan</h3>' +
                '<div class="pwa-ios-step">' +
                    '<span class="pwa-ios-step-num">1</span>' +
                    '<span>Ketik ikon <strong>Menu</strong> <i class="bi bi-three-dots-vertical"></i> di penjuru kanan atas Chrome</span>' +
                '</div>' +
                '<div class="pwa-ios-step">' +
                    '<span class="pwa-ios-step-num">2</span>' +
                    '<span>Pilih <strong>"Install app"</strong> atau <strong>"Add to Home screen"</strong></span>' +
                '</div>' +
                '<div class="pwa-ios-step">' +
                    '<span class="pwa-ios-step-num">3</span>' +
                    '<span>Ketik <strong>"Install"</strong> untuk sahkan</span>' +
                '</div>' +
                '<button class="pwa-ios-close" id="pwa-ios-close">Faham</button>' +
            '</div>';

        document.body.appendChild(overlay);

        document.getElementById('pwa-ios-close').addEventListener('click', function () {
            overlay.remove();
        });
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.remove();
        });
    }

    function showDesktopInstructions() {
        if (deferredPrompt) {
            triggerNativeInstall();
            return;
        }

        if (document.getElementById('pwa-ios-overlay')) return;

        var browser = detectBrowser();
        var steps;

        if (browser === 'chrome' || browser === 'edge') {
            steps =
                '<div class="pwa-ios-step">' +
                    '<span class="pwa-ios-step-num">1</span>' +
                    '<span>Klik ikon <strong>Install</strong> <i class="bi bi-download"></i> di bar alamat (address bar)</span>' +
                '</div>' +
                '<div class="pwa-ios-step">' +
                    '<span class="pwa-ios-step-num">2</span>' +
                    '<span>Atau klik <strong>Menu</strong> <i class="bi bi-three-dots-vertical"></i> &rarr; <strong>"Install e-Aduan"</strong></span>' +
                '</div>';
        } else {
            steps =
                '<div class="pwa-ios-step">' +
                    '<span class="pwa-ios-step-num">1</span>' +
                    '<span>Buka laman ini menggunakan <strong>Google Chrome</strong> atau <strong>Microsoft Edge</strong></span>' +
                '</div>' +
                '<div class="pwa-ios-step">' +
                    '<span class="pwa-ios-step-num">2</span>' +
                    '<span>Klik ikon <strong>Install</strong> <i class="bi bi-download"></i> di bar alamat</span>' +
                '</div>';
        }

        var overlay = document.createElement('div');
        overlay.id = 'pwa-ios-overlay';
        overlay.className = 'pwa-ios-overlay';
        overlay.innerHTML =
            '<div class="pwa-ios-modal">' +
                '<h3><i class="bi bi-laptop"></i> Pasang e-Aduan</h3>' +
                steps +
                '<button class="pwa-ios-close" id="pwa-ios-close">Faham</button>' +
            '</div>';

        document.body.appendChild(overlay);

        document.getElementById('pwa-ios-close').addEventListener('click', function () {
            overlay.remove();
        });
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.remove();
        });
    }

    function showInstalled() {
        section.innerHTML =
            '<div class="pwa-installed-badge">' +
                '<i class="bi bi-check-circle-fill"></i> e-Aduan telah dipasang' +
            '</div>';
    }

    function isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true;
    }

    function detectPlatform() {
        var ua = navigator.userAgent || '';
        if (/iPad|iPhone|iPod/.test(ua) && !window.MSStream) return 'ios';
        if (/android/i.test(ua)) return 'android';
        return 'desktop';
    }

    function detectBrowser() {
        var ua = navigator.userAgent || '';
        if (/Edg\//i.test(ua)) return 'edge';
        if (/Chrome/i.test(ua) && !/Edg/i.test(ua)) return 'chrome';
        if (/Firefox/i.test(ua)) return 'firefox';
        if (/Safari/i.test(ua)) return 'safari';
        return 'other';
    }
})();
