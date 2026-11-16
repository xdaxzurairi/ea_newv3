# Portal PPF eAduanV2 Implementation Plan

> **For agentic workers:** Gunakan executing-plans untuk melaksanakan pelan ini mengikut tugas dalam sesi yang sama. Kerja frontend dan backend mempunyai pemilik berasingan.

**Goal:** Menyediakan dashboard PPF pilihan A, baca-sahaja, semua kampus seperti portal lama, dengan akses intranet dan staf PPF sahaja.

**Architecture:** PHP menyediakan halaman yang telah dilindungi sebelum sebarang output. JavaScript mendapatkan JSON daripada endpoint yang mengulangi semakan akses; CSS menggunakan token UiTM sedia ada. Backend memiliki guard, SQL, laluan butiran dan foto; frontend memiliki HTML paparan, CSS, interaksi dan polisi service worker.

**Tech Stack:** PHP procedural sedia ada, Bootstrap 5.3.3, Bootstrap Icons 1.11.3, vanilla JavaScript, CSS, service worker.

**Spec:** `docs/superpowers/specs/2026-11-16-ppf-portal-design.md`

## Penemuan kod dan sempadan

- Halaman diakses terus di `pages/*.php`; tiada whitelist route dalam `index.php`.
- `includes/header.php` menyediakan navigasi dan token paparan melalui `assets/css/style.css`.
- `pages/detail.php` menggunakan `fetchOwnWr()`; akses semua kampus memerlukan halaman butiran PPF berasingan. Jangan melonggarkan pemilikan halaman biasa.
- Foto pada butiran menggunakan `ajax/foto.php`; backend perlu menyediakan akses foto PPF yang mengulangi guard, jika foto dimasukkan.
- `sw.js` sudah mengecualikan `/eAduanV2/ajax/`, tetapi permintaan halaman tanpa Accept HTML boleh masuk strategi stale-while-revalidate. Tambah pengecualian berdasarkan pathname PPF sebelum strategi lain.
- Perubahan sedia ada `docs/UAT-ea_newv3.md`, fail PDF dan fail `.bak_v2rename` ialah kerja lain. Stage hanya fail tugas sendiri.
- Ruflo tidak tersedia dalam senarai alat sesi ini; pelaksanaan menggunakan pemeriksaan fail dan Git.

## Pemilikan fail

| Fail | Pemilik | Tanggungjawab |
| --- | --- | --- |
| `includes/ppf_guard.php` | Backend | Sesi, keahlian PPF, rangkaian intranet, no-store |
| `pages/ppf.php` blok bootstrap | Backend | Guard sebelum output, URL asas |
| `pages/ppf.php` blok HTML | Frontend | Dashboard A, penapis, senarai, keadaan paparan |
| `assets/css/ppf.css` | Frontend | Susun atur desktop/mobile dan aksesibiliti |
| `assets/js/ppf-dashboard.js` | Frontend | JSON, URL, penapis, pagination, loading/error |
| `ajax/ppf_dashboard.php` | Backend | Query berparameter dan validasi penapis |
| `pages/ppf_detail.php` | Backend + frontend pada blok masing-masing | Butiran baca-sahaja dengan guard PPF |
| `ajax/ppf_foto.php` jika foto disertakan | Backend | Semakan akses foto setiap permintaan |
| `includes/header.php` | Backend untuk kelayakan; frontend untuk markup | Menu bersyarat |
| `sw.js` | Frontend | Network-only PPF dan pembersihan cache |

## Tugas 1 — Sediakan kontrak backend dan kawalan akses

- [ ] Backend bina `includes/ppf_guard.php` berdasarkan corak sesi projek: sesi sah dan belum tamat; staf sahaja; `dp_id` dibaca daripada data staf server dan dalam `{A0140,N00020}`.
- [ ] Backend tentukan CIDR intranet daripada konfigurasi rangkaian yang diluluskan organisasi. Jangan menganggap semua IP private ialah intranet. Header forwarded hanya dipercayai jika proxy asal dipercayai.
- [ ] Halaman dan endpoint terus menguatkuasakan guard. Respons tanpa sesi ialah 401 untuk JSON; akses ditolak ialah 403. HTML sesi tamat menghala ke login sedia ada.
- [ ] Backend gunakan `Cache-Control: no-store, private` pada semua respons PPF, termasuk ralat.
- [ ] Backend sediakan kelayakan menu melalui boolean server `$canAccessPpf`; nilai tiada bermaksud false. Jangan bina kelayakan daripada localStorage atau data atribut pelayar.
- [ ] Uji matriks sesi sah/tamat, staf PPF/bukan PPF, intranet/luar. Setiap gabungan selain sesi sah + PPF + intranet mesti menolak data.

## Tugas 2 — Kunci kontrak JSON yang boleh dirender

- [ ] `GET /ajax/ppf_dashboard.php` menerima `view=all|requested|completed`, `state_id`, `site_id`, `zone_id`, `section`, `prob_group`, `date_from`, `date_to`, `wr_id`, `staff_id`, `student_id`, `page`, `per_page`.
- [ ] Tarikh input berbentuk YYYY-MM-DD; `page` minimum 1; `per_page` tetap 10 untuk fasa pertama. Backend mengapit nombor halaman pada halaman sah terakhir.
- [ ] Semua penapis diparameterkan. Makna status, hubungan zon/elemen dan lalai tarikh disemak dengan query portal lama sebelum dipindahkan. Jangan anggap Requested hanya satu kod status tanpa semakan.
- [ ] Ringkasan mengikut penapis aktif kecuali `view`; perhatian mengikut penapis sama dan aduan Requested paling lama dahulu. Senarai utama mengikut `view`, terbaru dahulu.
- [ ] Tetapkan bentuk respons berikut; nombor mesti JSON number, senarai mesti array, nilai tiada mesti null atau string kosong yang konsisten:

```json
{
  "ok": true,
  "summary": {"all": 1284, "requested": 48, "completed": 1036},
  "attention": [{"wr_id": 24119, "date_requested": "2026-11-14", "requestor": "Nama pengadu", "requestor_id": "12345", "state_name": "Selangor", "site_name": "Shah Alam", "location_label": "Blok A", "section_label": "Mekanikal", "problem_group_label": "Penghawa dingin", "status_code": "R", "status_label": "Baharu", "age_days": 2}],
  "complaints": [],
  "pagination": {"page": 1, "per_page": 10, "total": 1284, "pages": 129},
  "filters": {"states": [], "sites": [], "zones": [], "sections": [], "problem_groups": []},
  "updated_at": "2026-11-16T10:42:00+08:00"
}
```

- [ ] Item pilihan penapis berbentuk `{"value":"kod","label":"Nama"}`. Senarai kampus mengikut negeri, zon mengikut kampus, elemen mengikut seksyen. Semua senarai perlu dipulangkan semula pada setiap respons.
- [ ] Ralat berbentuk `{"ok":false,"error":"access_denied","reason":"intranet_required"}` untuk luar intranet; `reason=ppf_required` bagi bukan PPF. Gunakan `session_expired` untuk 401 dan `invalid_filters` untuk 400. Ralat dalaman menggunakan 500 tanpa SQL atau stack trace.

## Tugas 3 — Bina rangka HTML dan gaya pilihan A

- [ ] Semak `git status` dan diff fail sasaran sebelum edit bersama backend.
- [ ] Dalam blok HTML `pages/ppf.php`, bina root `#ppf-dashboard` dengan URL endpoint dan URL butiran yang diberikan server. Bootstrap PHP serta guard dimiliki backend.
- [ ] Masukkan sidebar empat pautan, tajuk, lencana intranet, tiga kad ringkasan, senarai perhatian dan form penapis. Gunakan ID penapis yang sama dengan nama parameter JSON.
- [ ] Bina jadual dengan caption, scope col, sepuluh rekod setiap halaman dan butang sebelumnya/seterusnya. Jadual diubah kepada kad berlabel pada skrin kecil tanpa menggandakan rekod dalam DOM.
- [ ] CSS dihadkan kepada `.ppf-*`; guna `--uitm-purple`, `--uitm-gold`, `--bg-body`, `--bg-surface` sedia ada. Tambah skala ruang 4/8/12/16/24/32 dan fokus kelihatan.
- [ ] Hadkan setiap input kepada label nyata; panel penapis mobile menggunakan button dengan `aria-expanded` dan `aria-controls`. Tiada pautan maklum balas/laporan yang belum berfungsi.
- [ ] Muat CSS dan JS khusus hanya pada halaman PPF. Backend menyambung header dan footer projek selepas guard berjaya.

## Tugas 4 — Sambung JavaScript dan keadaan paparan

- [ ] Parse whitelist parameter melalui URLSearchParams; abaikan parameter lain. Navigasi Ringkasan menggunakan view all dan anchor ringkasan; tiga senarai menggunakan view masing-masing.
- [ ] Hantar melalui `fetch(endpoint, {credentials:'same-origin', cache:'no-store', signal})`. AbortController membatalkan permintaan terdahulu.
- [ ] Validasi julat tarikh sebelum submit, kosongkan page apabila penapis/view berubah, dan gunakan history.pushState. Event popstate memulihkan form dan meminta data semula.
- [ ] Render teks dengan textContent/createElement. Nombor aduan untuk pautan butiran mesti integer positif; gunakan URL setempat yang diberikan server. Jangan masukkan HTML daripada JSON.
- [ ] Apabila loading, tandakan region `aria-busy=true`; umum status melalui region aria-live. Apabila gagal, kosongkan rekod dan ringkasan lama sebelum memaparkan ralat.
- [ ] 401: pergi ke login dengan URL setempat projek. 403: paparkan keadaan akses ditolak berdasarkan reason tanpa retry automatik. 400: minta pembetulan penapis. Ralat rangkaian/500: butang Cuba semula.
- [ ] Jika JSON tidak lengkap atau ok bukan true, tunjuk ralat respons; jangan menggantikan data hilang dengan angka 0 seolah-olah query berjaya.
- [ ] Sediakan reset penapis yang memulihkan view all/page 1. Keadaan kosong menunjukkan Tiada aduan sepadan serta butang reset.

## Tugas 5 — Butiran, menu dan cache

- [ ] Backend menyediakan `pages/ppf_detail.php?wr_id=INTEGER`, mengulangi guard PPF dan memulangkan rekod baca-sahaja. Jangan gunakan halaman detail biasa yang memanggil fetchOwnWr untuk akses rentas pemilik.
- [ ] Frontend guna gaya butiran projek dan pautan kembali ke URL dashboard dengan penapis terpelihara. Foto hanya disambung selepas endpoint foto PPF dilindungi.
- [ ] Backend menyediakan `$canAccessPpf`; frontend menambah markup menu dengan syarat boolean yang disediakan. Nilai undefined gagal tertutup.
- [ ] Dalam fetch handler `sw.js`, pintas pathname `/eAduanV2/pages/ppf.php`, `/eAduanV2/pages/ppf_detail.php` dan semua endpoint PPF sebelum strategi cache. Permintaan PPF terus menggunakan rangkaian dan tiada fallback rekod lama.
- [ ] Naikkan CACHE_VERSION daripada nilai semasa supaya cache lama dibersihkan ketika activate. Jangan precache halaman atau endpoint PPF.

## Tugas 6 — Pengesahan dan serahan

- [ ] Jalankan `node --check assets/js/ppf-dashboard.js` dan `node --check sw.js`.
- [ ] Backend jalankan `php -l` pada semua fail PHP yang disentuh.
- [ ] Uji browser 1440px dan 390px: navigasi, semua penapis, reset, pagination, pautan butiran dan Back/Forward; periksa console tanpa ralat baharu.
- [ ] Uji tiga respons berbeza: array kosong, HTTP 403, dan gangguan rangkaian. Pastikan data terdahulu hilang apabila akses ditolak.
- [ ] Uji keyboard: capai menu, buka/tutup penapis, submit, reset dan pagination; fokus jelas. Semak reduced-motion serta kontras teks >=4.5:1.
- [ ] Banding sampel query lama dan baharu untuk kampus, zon, seksyen serta status sama; catat jumlah dan lima wr_id pertama. Bezakan pembetulan bug warisan daripada kelakuan yang mesti dikekalkan.
- [ ] Dengan staf PPF intranet, muat dashboard/butiran, kemudian offline. Pastikan Cache Storage tiada respons PPF dan data tidak boleh dimuat semula. Uji semula selepas logout pada peranti sama.
- [ ] Uji URL halaman, JSON dan foto secara terus sebagai staf bukan PPF serta dari luar intranet. Sekatan frontend tidak dikira bukti akses.
- [ ] Jalankan `git diff --check`, semak diff penuh dan stage fail tugas sendiri. Commit frontend berasingan daripada backend; jangan push tanpa arahan.

## Serahan ringkas untuk Claude Code / backend

Lindungi halaman `pages/ppf.php`, `pages/ppf_detail.php` dan endpoint PPF dengan sesi sah, staf PPF dp_id A0140/N00020 serta CIDR intranet yang disahkan; sediakan boolean `$canAccessPpf` untuk menu.

Sediakan `GET ajax/ppf_dashboard.php` dengan JSON `{ok,summary,attention,complaints,pagination,filters,updated_at}` mengikut kontrak Tugas 2; data semua kampus dan semantik penapis/status mesti dipadankan dengan portal lama.

Semua respons PPF menggunakan no-store; JSON sesi tamat 401, akses ditolak 403, penapis tidak sah 400. Halaman butiran dan foto perlu guard yang sama tanpa melonggarkan pemilikan laluan aduan biasa.

## Urutan pelepasan

Frontend boleh dibina dan diuji dengan fixture tempatan tanpa data sebenar. Menu dan halaman portal hanya diaktifkan selepas guard, endpoint serta butiran backend tersedia dan matriks akses lulus. Senarai CIDR intranet perlu datang daripada konfigurasi organisasi yang disahkan backend; pelan ini tidak mereka senarai IP tersebut.
