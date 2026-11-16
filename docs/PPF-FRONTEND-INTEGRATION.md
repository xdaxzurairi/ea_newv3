# Integrasi frontend Portal PPF

Frontend disediakan sebagai `includes/ppf-dashboard.html`, `assets/css/ppf.css` dan `assets/js/ppf-dashboard.js`. Fragmen kosong tidak mengandungi data sebenar. Halaman PHP dan endpoint mesti disediakan oleh ejen BACKEND sebelum menu diaktifkan.

## Kontrak backend

Lindungi halaman dashboard/butiran dan endpoint dengan sesi sah, intranet yang diluluskan dan dp_id A0140/N00020; semua respons no-store.
GET `ajax/ppf_dashboard.php` memulangkan `{ok,summary,attention,complaints,pagination,filters,updated_at}` mengikut pelan; ralat sesi 401, akses 403, penapis 400.
Butiran: `pages/ppf_detail.php?wr_id=INTEGER`; sahkan akses dan parameter return_to sebagai URL setempat sebelum digunakan.

## Susunan integrasi oleh BACKEND

1. `pages/ppf.php`: jalankan bootstrap dan guard PPF sebelum output; muat header projek.
2. Muat stylesheet `assets/css/ppf.css` melalui URL asas projek.
3. Include `includes/ppf-dashboard.html`.
4. Tetapkan atribut root sebelum JS dimuatkan, menggunakan URL setempat daripada server. Contoh bentuk DOM:

```html
<section id="ppf-dashboard" class="ppf-shell"
  data-endpoint="/eAduanV2/ajax/ppf_dashboard.php"
  data-detail-url="/eAduanV2/pages/ppf_detail.php"
  data-login-url="/eAduanV2/index.php" aria-label="Portal PPF">
```

Fragmen kini mempunyai root tanpa atribut URL. BACKEND boleh menambah atribut URL yang di-escape pada baris root, atau menetapkannya dengan skrip konfigurasi server selepas include. URL mesti same-origin. Jika konfigurasi tiada, UI berhenti dengan mesej belum disambungkan.

5. Muat `assets/js/ppf-dashboard.js` selepas root/config tersedia (atau gunakan defer), kemudian footer projek.
6. BACKEND menambah menu bersyarat berdasarkan keputusan guard. Jangan jadikan atribut DOM sebagai bukti kelayakan.

## Ujian yang telah dijalankan

Chrome headless desktop dan viewport 390px menggunakan fixture sintetik: render, XSS text-only, no-store, pagination, tukar view, collapse penapis, penapis bergantung, kosong, 403, sesi tamat, retry, JSON rosak, reset, overflow dan ralat runtime. Pemeriksaan sintaks Node dijalankan berasingan.

Ujian dengan endpoint sebenar, semakan CIDR, sesi staf sebenar, foto/butiran dan cache offline selepas deployment perlu dijalankan selepas backend disambungkan. Frontend tidak menyediakan data contoh apabila endpoint sebenar gagal.
