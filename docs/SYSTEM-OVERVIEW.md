## e-Aduan v3 – Dokumentasi Sistem (Ringkasan)

### 1. Tujuan Sistem

- **Nama sistem**: e-Aduan v3  
- **Objektif**: Mengurus aduan kerosakan fasiliti UiTM (dalam & luar bangunan) oleh:
  - **Staf UiTM**
  - **Pelajar UiTM**
  - **Pengguna luar** (kontraktor / orang awam tertentu)
- **Ciri utama**:
  - Log masuk berasaskan SSO / Google / pengguna luar
  - Borang aduan dengan lokasi terperinci (negeri, kampus, bangunan, blok, aras/ruang atau kategori infra luar)
  - Pemetaan jenis kerosakan (seksyen, elemen, masalah)
  - Rekod aduan ke jadual `wr` (mengikut sistem lama)
  - Notifikasi e-mel BOM
  - Semakan senarai aduan & butiran + maklum balas pengguna

### 2. Senibina & Teknologi

- **Backend**: PHP (struktur berasaskan fail, tanpa framework besar)
- **Pangkalan Data**: RDBMS (mengikut sistem eAduan lama), jadual penting:
  - `wr` – work request (aduan utama)
  - `em`, `emstd`, `dp` – maklumat staf & jabatan
  - `stu`, `fakulti`, `site` – maklumat pelajar & kampus
  - `afm_user_osd` – pengguna luar
  - `state`, `site`, `bldg`, `bl`, `fl`, `rm` – hierarki lokasi dalaman
  - `wr_kat_infra`, `WR_SUB_INFRA` – kategori & sub kategori infra luar bangunan
  - `section`, `prob_group`, `probtype` – klasifikasi kerosakan
- **Frontend**:
  - Bootstrap 5.3, Bootstrap Icons
  - Font `Inter`
  - CSS tersuai di `assets/css/style.css`
- **PWA**:
  - `manifest.json`
  - Skrip `assets/js/pwa-register.js`, `assets/js/pwa-install.js`
- **Peta**:
  - Leaflet + Google Maps tile (`lyrs=m`)

### 3. Struktur Fail Utama

- **Akar projek**
  - `index.php` – Laman log masuk / landing page PWA
  - `config/database.php` – Kembalikan instance `PDO` (sambungan DB)
  - `config/auth.php` – Konfigurasi autentikasi & `base_url`
  - `includes/functions.php` – Fungsi utiliti global (DB helper, sanitasi, label medium, dll.)
  - `includes/header.php` – Template `<head>`, navbar, pembuka `<main>`
  - `includes/footer.php` – Penutup layout + include JS global
  - `includes/auth_guard.php` – Semak sesi SSO & tamat tempoh token
- **Modul Aduan**
  - `pages/borang.php` – Borang utama aduan (Langkah 1 – isi maklumat)
  - `pages/info_aduan.php` – Paparan pengesahan aduan (Langkah 2 – semak)
  - `pages/simpan.php` – Simpan ke jadual `wr` & hantar e-mel (Langkah 3 – hantar)
  - `pages/semak.php` – Senarai aduan milik pengguna yang log masuk
  - `pages/detail.php` – Butiran lengkap satu aduan (`wr_id`)
  - `pages/maklumbalas.php` – Popup pemberian maklum balas lepas kerja siap
  - `pages/bilik_sendiri.php` – Popup pemilihan bilik sendiri untuk staf (jika banyak bilik)
  - `pages/thanx.php` – Skrin tamat / ucapan terima kasih
- **Autentikasi**
  - `auth/google_callback.php` – Callback Google (staf / pelajar)
  - `auth/sso_callback.php` – Callback SSO portal UiTM (jika digunakan)
  - `auth/logout.php` – Log keluar + tamat sesi
  - `pages/daftar_luar.php` – Pendaftaran & log masuk pengguna luar (jadual `afm_user_osd`)
- **AJAX JSON API**
  - `ajax/get_negeri.php` – Senarai negeri yang boleh dilapor (`state`)
  - `ajax/get_kampus.php` – Kampus mengikut negeri (`site`)
  - `ajax/get_bangunan.php` – Bangunan mengikut kampus (`bldg`)
  - `ajax/get_blok.php` – Blok mengikut bangunan (`bl`)
  - `ajax/get_aras.php` – Aras mengikut blok (`fl`)
  - `ajax/get_ruang.php` – Ruang mengikut aras & blok (`rm`)
  - `ajax/get_kat_infra.php` – Kategori infra luar bangunan (`wr_kat_infra`)
  - `ajax/get_sub_infra.php` – Sub kategori (`WR_SUB_INFRA`)
  - `ajax/get_seksyen.php` – Seksyen kerosakan ikut kampus & infra (`section`)
  - `ajax/get_elemen.php` – Elemen kerosakan (`prob_group`)
  - `ajax/get_masalah.php` – Masalah kerosakan (`probtype`)
- **Lain-lain**
  - `lib/email_embom.php` – Fungsi hantar e-mel BOM selepas aduan disimpan
  - `daily-diary/` – Diari teknikal harian (log sesi & keputusan teknikal)
  - `docs/` – Dokumentasi sistem (fail ini dan lain-lain)

### 4. Kitar Hayat Aduan (Ringkas)

1. **Log masuk**
   - Pengguna melayari `index.php`.
   - Jika sudah ada `$_SESSION['sso_authenticated'] === true` → redirect ke `pages/borang.php`.
   - Jika belum:
     - Boleh guna Google / SSO (staf/pelajar).
     - Atau daftar sebagai pengguna luar (`pages/daftar_luar.php`).
2. **Isi borang aduan (`pages/borang.php`)**
   - Auto-isi maklumat pelapor berdasarkan jenis:
     - Staf (`em`)
     - Pelajar (`stu`)
     - Luar (`afm_user_osd`)
   - Pilih lokasi kerosakan:
     - **Bilik sendiri** (staf sahaja; `location = 8`) – auto dari jadual `em` dan `bl`/`fl`/`rm`.
     - **Lokasi lain** (`location = 9`) – pilih negeri → kampus → bangunan → blok → aras/ruang atau kategori/sub-infra.
   - Tentukan jenis kerosakan:
     - Seksyen → Elemen → Masalah (data dari `section`, `prob_group`, `probtype`).
   - Bagi luar bangunan (`infra = 2`), pengguna tandakan lokasi di peta Leaflet (mengisi `koordinat_lokasi`).
   - Validasi JavaScript memastikan semua medan wajib dipilih/diisi sebelum ke langkah seterusnya.
3. **Pengesahan maklumat (`pages/info_aduan.php`)**
   - Terima `POST` dari borang.
   - Resolve ID kepada nama (negeri, kampus, bangunan, blok, aras/ruang atau kategori/sub infra, nama seksyen/elemen/masalah).
   - Papar ringkasan bertingkat:
     - Maklumat Pelapor
     - Lokasi Kerosakan
     - Jenis Kerosakan + Medium aduan
   - Papar peta lokasi (jika ada `koordinat_lokasi`).
   - Pengguna sahkan dan tekan “Hantar” → data digaungkan semula ke `pages/simpan.php`.
4. **Simpan aduan (`pages/simpan.php`)**
   - Menentukan:
     - `bl_id`, `fl_id`, `rm_id`, `state_id`, `site_id` berdasarkan:
       - Bilik sendiri vs lokasi lain.
       - Infra dalam vs luar bangunan.
     - `zone_id` dari `bl`.
     - `type_req`:
       - `1` – Staf UiTM
       - `5` – Inisiatif PPF (staf dengan `dp_id` tertentu, contohnya `A0140`, `N00020`)
       - `2` – Pelajar
       - `3` – Pengguna luar
     - `type_wr` – Medium aduan (online, telefon, surat, dsb.).
     - `latitude`, `longitude`, `koordinat_lokasi` untuk infra luar.
   - Lakukan `INSERT` ke jadual `wr`.
   - Ambil `wr_id` terakhir dan panggil `email_embom($wr_id, $pdo)` untuk notifikasi.
   - Papar skrin pengesahan dengan No. Aduan.
5. **Semak & pantau (`pages/semak.php`, `pages/detail.php`, `pages/maklumbalas.php`)**
   - `semak.php`:
     - Tapis `wr` berdasarkan:
       - Staf → `em_number`
       - Pelajar → `stu_id`
       - Luar → `ic_no`
     - Papar senarai dengan status / jenis kerosakan / medium.
   - `detail.php`:
     - Papar butiran penuh satu aduan (`wr_id`).
   - `maklumbalas.php`:
     - Pengguna isi maklum balas (memuaskan / tidak memuaskan) lepas kerja ditanda SIAP.

### 5. Fungsi Utiliti Penting

- `includes/functions.php`:
  - `getPdo()` – Singleton `PDO` dari `config/database.php`.
  - `getAuthConfig()` – Konfigurasi autentikasi dari `config/auth.php`.
  - `baseUrl()` – Pulangkan `base_url` (diguna dalam semua pautan & aset).
  - `dbFetchAll()`, `dbFetchOne()`, `dbFetchColumn()`, `dbExecute()` – Helper DB generik.
  - `e()` – `htmlspecialchars` berserta `ENT_QUOTES`, untuk output selamat.
  - `req()`, `post()` – Ambil parameter request dengan `trim`.
  - `sess()` – Ambil nilai dari `$_SESSION`.
  - `getMediumLabels()` – Mapping `type_wr` → label (Online / Surat / Telefon / dsb.).
  - `descSlashToDash()` – Menukar `/` kepada ` - ` pada deskripsi (lebih mesra paparan).

### 6. Keselamatan & Kawalan Akses

- Semua halaman utama selepas log masuk (`pages/*.php` kecuali `daftar_luar.php`, `thanx.php`) memerlukan:
  - `$_SESSION['sso_authenticated'] === true`
  - Jika token tamat (`token_exp < time()`) → `session_destroy()` dan redirect dengan `?expired=1`.
- Input pengguna dibaca melalui `post()` / `req()` dan digunakan dalam prepared statement (`$pdo->prepare()`), meminimumkan risiko SQL injection.
- Output HTML menggunakan `e()` untuk elakkan XSS asas.

### 7. PWA & Pengalaman Pengguna

- `index.php` menyediakan:
  - UI split-screen (panel gambar + panel login).
  - Meta tag PWA, ikon, dan manifest.
  - Seksyen “Pasang Aplikasi” dengan butang `Pasang e-Aduan` yang disokong oleh:
    - `assets/js/pwa-register.js`
    - `assets/js/pwa-install.js`
- Pada peranti mudah alih, layout responsif (sembunyikan panel gambar, fokus pada borang log masuk).

### 8. Diari Teknikal (`daily-diary/`)

- Folder `daily-diary/` menyimpan log harian / keputusan teknikal:
  - Fail mengikut tarikh: `YYYY-MM-DD.md`.
  - Mengandungi ringkasan sesi, perubahan kod penting, keputusan design & follow-up.
- Digunakan bersama skill **Save Diary** dan **Echo Recall** untuk “ingat” evolusi sistem.

