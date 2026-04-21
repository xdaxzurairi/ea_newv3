# e-Aduan v3 – Checklist

Checklist untuk audit keselamatan, kesediaan production dan penyelenggaraan sistem.

---

## 1. Security Headers (Production)

- [ ] Semua response production mengandungi 6 header:
  - [ ] `Strict-Transport-Security` (max-age ≥ 31536000)
  - [ ] `Content-Security-Policy`
  - [ ] `X-Frame-Options: SAMEORIGIN`
  - [ ] `X-Content-Type-Options: nosniff`
  - [ ] `Referrer-Policy: strict-origin-when-cross-origin`
  - [ ] `Permissions-Policy`
- [ ] HSTS hanya digunakan apabila laman dilayan melalui **HTTPS**
- [ ] Domain luar (CDN, Google Fonts, Leaflet, tile peta) ada dalam direktif CSP yang betul
- [ ] CSP tiada `'unsafe-eval'`
- [ ] `object-src 'none'` kekal
- [ ] Headers **tidak** dipaksa dalam environment local/dev (jika mahu debug senang)

**Rujukan:** `docs/SECURITY-HEADERS.md`

---

## 2. Konfigurasi & Persekitaran

- [ ] `config/database.php` – maklumat DB production betul dan selamat
- [ ] `config/auth.php` – `base_url` dan tetapan SSO/OAuth production betul
- [ ] Tiada credential atau URL dev tertinggal dalam kod yang di-deploy
- [ ] Sesi & token expiry (`token_exp`) sesuai dengan polisi keselamatan

---

## 3. Aplikasi & UX

- [ ] Borang aduan – validasi client-side (semua medan wajib) berfungsi sebelum ke `info_aduan.php`
- [ ] Halaman utama (login) – tiada banner PWA yang mengganggu (install/update disembunyikan jika dikehendaki)
- [ ] PWA – Service Worker daftar betul; `manifest.json` dan scope path konsisten
- [ ] Peta (Luar Bangunan) – Leaflet & tile peta load tanpa ralat CSP

---

## 4. Dokumentasi

- [ ] `docs/SYSTEM-OVERVIEW.md` – dikemas kini jika ada perubahan struktur
- [ ] `docs/FLOW-ADUAN.md` – aliran proses aduan tepat dengan kod semasa
- [ ] `docs/FLOW-ADUAN-CHART.md` – rajah Mermaid render tanpa ralat
- [ ] `docs/SECURITY-HEADERS.md` – polisi header selari dengan implementasi dalam `includes/functions.php`

---

## 5. Ujian & Pemantauan

- [ ] Semak header dengan `curl -I https://yourdomain.com` atau securityheaders.com
- [ ] Uji aliran penuh: login → isi borang → pengesahan → simpan → semak aduan
- [ ] Log ralat PHP & web server disemak berkala
- [ ] Backup pangkalan data dan fail konfigurasi mengikut jadual

---

## 6. Penyelenggaraan Berkala

- [ ] Kemas kini dependency (Bootstrap, Leaflet) mengikut kesesuaian
- [ ] Semak gred security (securityheaders.com / Mozilla Observatory) secara berkala
- [ ] Ulas semula senarai domain dalam CSP apabila tambah perkhidmatan baharu

---

*Kemas kini checklist ini selepas setiap audit atau sebelum go-live production.*
