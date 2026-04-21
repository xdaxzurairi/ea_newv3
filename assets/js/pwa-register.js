/**
 * e-Aduan v3 – PWA Service Worker Registration & Install Prompt
 */
(function () {
    'use strict';

    if (!('serviceWorker' in navigator)) return;

    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/ea_newv3/sw.js', { scope: '/ea_newv3/' })
            .then((reg) => {
                reg.addEventListener('updatefound', () => {
                    const newWorker = reg.installing;
                    if (!newWorker) return;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'activated' && navigator.serviceWorker.controller) {
                            showUpdateBanner();
                        }
                    });
                });
            })
            .catch(() => {});
    });

    let deferredPrompt = null;

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        showInstallBanner();
    });

    function showInstallBanner() {
        if (document.getElementById('pwa-install-banner')) return;

        const banner = document.createElement('div');
        banner.id = 'pwa-install-banner';
        banner.innerHTML =
            '<div class="pwa-banner-content">' +
                '<span class="pwa-banner-icon">📱</span>' +
                '<div class="pwa-banner-text">' +
                    '<strong>Pasang e-Aduan</strong>' +
                    '<small>Akses pantas terus dari skrin utama</small>' +
                '</div>' +
                '<div class="pwa-banner-actions">' +
                    '<button id="pwa-install-btn" class="pwa-btn-install">Pasang</button>' +
                    '<button id="pwa-dismiss-btn" class="pwa-btn-dismiss">&times;</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(banner);

        requestAnimationFrame(() => {
            banner.classList.add('pwa-banner-show');
        });

        document.getElementById('pwa-install-btn').addEventListener('click', () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(() => {
                deferredPrompt = null;
                removeBanner(banner);
            });
        });

        document.getElementById('pwa-dismiss-btn').addEventListener('click', () => {
            removeBanner(banner);
        });
    }

    function showUpdateBanner() {
        if (document.getElementById('pwa-update-banner')) return;

        const banner = document.createElement('div');
        banner.id = 'pwa-update-banner';
        banner.innerHTML =
            '<div class="pwa-banner-content">' +
                '<span class="pwa-banner-icon">🔄</span>' +
                '<div class="pwa-banner-text">' +
                    '<strong>Kemas kini tersedia</strong>' +
                    '<small>Versi baharu e-Aduan sedia dimuat</small>' +
                '</div>' +
                '<div class="pwa-banner-actions">' +
                    '<button id="pwa-refresh-btn" class="pwa-btn-install">Muat Semula</button>' +
                    '<button id="pwa-update-dismiss" class="pwa-btn-dismiss">&times;</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(banner);

        requestAnimationFrame(() => {
            banner.classList.add('pwa-banner-show');
        });

        document.getElementById('pwa-refresh-btn').addEventListener('click', () => {
            window.location.reload();
        });

        document.getElementById('pwa-update-dismiss').addEventListener('click', () => {
            removeBanner(banner);
        });
    }

    function removeBanner(el) {
        el.classList.remove('pwa-banner-show');
        setTimeout(() => el.remove(), 350);
    }
})();
