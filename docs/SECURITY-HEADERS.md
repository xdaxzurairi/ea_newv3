## e-Aduan v3 – HTTP Security Headers

Dokumen ini menerangkan polisi security headers yang kini dihantar oleh aplikasi e-Aduan v3 melalui fungsi `sendSecurityHeaders()` dalam `includes/functions.php`.

---

### 1. Senarai Header Yang Digunakan

- **Strict-Transport-Security**
  - Nilai: `max-age=31536000; includeSubDomains`
  - Tujuan: Memaksa pelayar menggunakan HTTPS dan mengurangkan risiko SSL stripping.
  - Nota: Hanya berkuatkuasa apabila aplikasi diakses melalui HTTPS.

- **Content-Security-Policy (CSP)**
  - Dibina oleh fungsi `buildContentSecurityPolicy()`.
  - Nilai (ringkas):
    - `default-src 'self'`
    - `script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com`
    - `style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com`
    - `font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net`
    - `img-src 'self' data: blob: https://*.google.com`
    - `connect-src 'self'`
    - `frame-ancestors 'self'`
    - `form-action 'self'`
    - `base-uri 'self'`
    - `object-src 'none'`
  - Tujuan:
    - Menghadkan sumber skrip, CSS, font, imej dan sambungan AJAX hanya kepada domain yang dipercayai (aplikasi sendiri, CDN yang digunakan, Google Maps / Leaflet).
    - Menghalang framing oleh domain lain (`frame-ancestors 'self'`).
    - Menghalang penggunaan plugin legacy (`object-src 'none'`).

- **X-Frame-Options**
  - Nilai: `SAMEORIGIN`
  - Tujuan: Menghalang clickjacking dengan mengehadkan iframe hanya dari origin yang sama.

- **X-Content-Type-Options**
  - Nilai: `nosniff`
  - Tujuan: Menghalang MIME type sniffing oleh pelayar.

- **Referrer-Policy**
  - Nilai: `strict-origin-when-cross-origin`
  - Tujuan: Menghadkan maklumat `Referer` yang dihantar ke domain lain dan mengurangkan kebocoran URL sensitif.

- **Permissions-Policy**
  - Nilai: `geolocation=(), microphone=(), camera=()`
  - Tujuan: Menyahaktifkan feature pelayar yang tidak diperlukan (lokasi, mikrofon, kamera) untuk aplikasi ini.

---

### 2. Lokasi Kod

- Fail: `includes/functions.php`
- Fungsi berkaitan:
  - `sendSecurityHeaders()`
  - `buildContentSecurityPolicy()`
- `sendSecurityHeaders()` dipanggil secara automatik di bahagian bawah fail:

```php
// Panggil secara automatik untuk semua request web
sendSecurityHeaders();
```

Ini memastikan semua permintaan yang memuatkan `includes/functions.php` akan menerima security headers yang sama.

---

### 3. Cara Ubahsuai Untuk Persekitaran Berbeza

Jika mahu **melonggarkan** atau **mematikan** headers ketika pembangunan tempatan (contoh: `localhost`), boleh kemaskini `sendSecurityHeaders()` seperti berikut:

```php
function sendSecurityHeaders(): void
{
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }

    // Contoh pengecualian untuk environment pembangunan
    if (!empty($_SERVER['APP_ENV']) && $_SERVER['APP_ENV'] === 'local') {
        return;
    }

    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header('Content-Security-Policy: ' . buildContentSecurityPolicy());
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}
```

Gunakan pembolehubah environment atau konfigurasi lain mengikut infra anda.

---

### 4. Checklist Audit Ringkas

- [ ] Semua response pengeluaran (production) mengandungi:
  - [ ] `Strict-Transport-Security`
  - [ ] `Content-Security-Policy`
  - [ ] `X-Frame-Options`
  - [ ] `X-Content-Type-Options`
  - [ ] `Referrer-Policy`
  - [ ] `Permissions-Policy`
- [ ] HSTS hanya digunakan di environment HTTPS.
- [ ] Semua domain luar (CDN, Google Maps, dll.) telah dimasukkan dalam direktif CSP yang betul.
- [ ] Tiada penggunaan `object-src` selain `none`.
- [ ] Tiada `'unsafe-eval'` dalam `script-src`.
- [ ] `'unsafe-inline'` dikekalkan hanya jika benar-benar perlu (sekarang diperlukan untuk inline script sedia ada).

