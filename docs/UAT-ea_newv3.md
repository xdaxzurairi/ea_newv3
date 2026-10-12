# e-Aduan v3 – Dokumen UAT (User Acceptance Testing)

> **Versi dokumen hidup/boleh isi:** salinan interaktif (penjejakan status, log kecacatan, cetak PDF) ada di:
> https://claude.ai/artifact/2BJNi13Y7GvGmXJbMnw74V
>
> Fail ini (`docs/UAT-ea_newv3.md`) ialah rujukan senarai kes ujian yang disimpan bersama kod untuk rekod/version control.

**Versi**: 1.0 (Draf — belum disahkan)
**Disediakan oleh**: Zurairi (zurairi@uitm.edu.my)
**Tarikh disediakan**: 12 Oktober 2026
**Jumlah kes ujian**: 60 kes merentasi 10 modul

---

## 1.0 Tujuan & Skop

**Tujuan**: Mengesahkan sistem e-Aduan v3 memenuhi keperluan perniagaan sebenar — Staf, Pelajar dan Pengguna Luar boleh melaporkan kerosakan fasiliti, menyemak status dan memberi maklum balas tanpa halangan — sebelum disahkan untuk kegunaan penuh.

**Dalam skop**:
- Log masuk & pendaftaran (Google Staf/Pelajar, OTP Pengguna Luar)
- Borang aduan — lokasi, jenis kerosakan, gambar, validasi
- Pengesahan, penghantaran & notifikasi e-mel BOM
- Semakan senarai, butiran aduan & maklum balas
- PWA (muat turun aplikasi) & keserasian peranti
- Kawalan akses & tamat sesi (dari perspektif pengguna)

**Luar skop**:
- Pemprosesan dalaman arahan kerja di sistem Archibus/BOM selepas e-mel diterima
- Ujian penembusan & audit header keselamatan terperinci (rujuk `docs/CHECKLIST.md` & `docs/SECURITY-HEADERS.md`)
- Ujian prestasi/beban (load testing)

## 2.0 Definisi & Singkatan

| Istilah | Maksud |
|---|---|
| UAT | User Acceptance Testing — ujian penerimaan pengguna oleh wakil perniagaan sebenar |
| BOM | Pihak pengurusan fasiliti yang menerima notifikasi & menguruskan kerja pembaikan |
| OTP | One-Time Password — kod 6 digit sah sementara untuk mengesahkan e-mel Pengguna Luar |
| SSO | Single Sign-On — pengesahan identiti bersepadu portal UiTM |
| PPF | Kategori khas staf (dp_id tertentu) yang aduannya direkod sebagai "Inisiatif PPF" |
| No. Aduan / wr_id | Nombor rujukan unik aduan selepas berjaya disimpan ke jadual `wr` |
| PWA | Progressive Web App — aplikasi web yang boleh dimuat turun/dipasang pada peranti |
| Pengguna Luar | Kontraktor/orang awam bukan staf atau pelajar UiTM berdaftar |

## 3.0 Persekitaran & Keperluan Ujian

- **URL dalaman/rangkaian UiTM**: `http://10.0.36.127/ea_newv3`
- **URL staging**: `https://fmsdev.uitm.edu.my/ea_newv3`
- ⚠️ Sahkan URL persekitaran sebenar dengan pasukan IT sebelum sesi UAT — WAF UiTM menyekat sesetengah akses dari luar rangkaian.
- **Pelayar/peranti**: Chrome & Edge (desktop, terkini); Chrome Android & Safari iOS (mudah alih, wajib untuk ujian PWA)
- **Akaun diperlukan**: 1 akaun Google Staf (digalakkan dengan >1 bilik berdaftar e-Ruang), 1 akaun Google Pelajar, 1 e-mel belum berdaftar + 1 e-mel sudah berdaftar sebagai Pengguna Luar, akses peti e-mel BOM
- **Data ujian**: sekurang-kurangnya satu bangunan/blok/ruang sedia ada bagi setiap kampus yang diuji, serta kategori infra luar bangunan

## 4.0 Peranan & Tanggungjawab

| Peranan | Tanggungjawab |
|---|---|
| Penyelaras UAT | Zurairi — menyelaras jadual, mengumpul keputusan, melapor & menutup isu |
| Penguji Perniagaan (Staf) | Menguji alur pelapor Staf |
| Penguji Perniagaan (Pelajar) | Menguji alur pelapor Pelajar |
| Penguji Perniagaan (Luar) | Menguji alur pendaftaran & aduan Pengguna Luar |
| Pembangun/IT Support | Sedia menjawab soalan teknikal & membetulkan isu |
| Pemilik Sistem/BOM | Sahkan notifikasi & aliran kerja; kelulusan akhir |

## 5.0 Kriteria Kemasukan & Keluar

**Kemasukan**: build dideploy ke persekitaran UAT; bug kritikal SIT/dev ditutup; akaun & data ujian sedia; dokumen diedarkan.

**Keluar**: semua kes keutamaan tinggi (log masuk, hantar aduan, semakan) Lulus; kecacatan terbuka hanya kosmetik/rendah risiko & dipersetujui; Seksyen 9.0 & 10.0 lengkap ditandatangani.

## 6.0 Jadual UAT (templat — kemas kini tarikh sebenar)

| Aktiviti | Tarikh Cadangan |
|---|---|
| Taklimat & edaran dokumen | |
| Sesi UAT — Modul 1–5 | |
| Sesi UAT — Modul 6–10 | |
| Semakan & penutupan kecacatan | |
| Pengesahan & tandatangan akhir | |

## 7.0 Senario & Kes Ujian

### Modul 1 — Log Masuk & Pendaftaran Pengguna

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan |
|---|---|---|---|---|
| M1-01 | Log masuk Staf UiTM melalui Google | Klik Google di bawah label "Staf & Pelajar" → log masuk dgn akaun staf sah | Berjaya log masuk & dibawa ke borang aduan; sesi staf aktif | Staf |
| M1-02 | Log masuk Pelajar UiTM melalui Google | Sama seperti di atas dgn akaun Google pelajar | Berjaya log masuk; sistem kenali sebagai pelajar | Pelajar |
| M1-03 | Label log masuk papar "Staf & Pelajar" | Lihat skrin log masuk | Label betul "Staf & Pelajar" (bukan "Staf & Student") | Semua |
| M1-04 | Mohon kod OTP sebagai Pengguna Luar | Klik "Pengguna Luar" → masukkan e-mel sah → hantar | Makluman kod dihantar; e-mel OTP diterima | Luar |
| M1-05 | Mohon OTP dgn e-mel tidak sah | Masukkan teks bukan e-mel → hantar | Ralat "Sila masukkan alamat e-mel yang sah." | Luar |
| M1-06 | Sahkan OTP — 1 akaun sedia ada | Masukkan kod sah utk e-mel berdaftar 1 akaun | Terus log masuk ke borang aduan | Luar |
| M1-07 | Sahkan OTP — e-mel belum berdaftar | Masukkan kod sah utk e-mel baharu | Skrin "Pendaftaran Pengguna Baru" dipaparkan | Luar |
| M1-08 | Lengkapkan pendaftaran Luar baharu | Isi Nama/No. K/P/Telefon → "Daftar & Teruskan" | Rekod disimpan; terus log masuk | Luar |
| M1-09 | Sahkan OTP — e-mel dgn >1 akaun | Masukkan kod sah utk e-mel berkaitan >1 No. K/P | Skrin "Pilih Akaun" dipaparkan; pilih satu → log masuk | Luar |
| M1-10 | Kod OTP salah/tamat tempoh | Masukkan kod salah atau tunggu lepas tempoh | Ralat dipaparkan; boleh "Hantar Semula" | Luar |
| M1-11 | Daftar No. K/P sudah wujud dgn e-mel lain | Masukkan No. K/P yg berkaitan e-mel lain semasa daftar | Ralat "No. K/P telah berdaftar dengan e-mel lain..." | Luar |

### Modul 2 — Borang Aduan: Maklumat Pelapor

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan |
|---|---|---|---|---|
| M2-01 | Maklumat pelapor Staf auto-isi | Log masuk Staf → lihat "1.0 Maklumat Pelapor" | Nama/jawatan/jabatan/bilik/telefon/e-mel tepat (read-only) | Staf |
| M2-02 | Maklumat pelapor Pelajar auto-isi | Log masuk Pelajar → lihat seksyen sama | Nama/No. Matrik/fakulti/kampus tepat | Pelajar |
| M2-03 | Maklumat pelapor Luar auto-isi | Log masuk Luar → lihat seksyen sama | Nama/No. K/P/telefon tepat | Luar |

### Modul 3 — Borang Aduan: Lokasi Kerosakan

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan |
|---|---|---|---|---|
| M3-01 | "Bilik Sendiri" — staf 1 bilik | Pilih radio "Bilik Sendiri" | Lokasi auto-isi tanpa pilihan tambahan | Staf |
| M3-02 | "Bilik Sendiri" — staf >1 bilik | Pilih "Bilik Sendiri" → "Pilih Bilik Sendiri" | Popup senarai bilik; pilih satu → lokasi dikemaskini | Staf |
| M3-03 | Opsyen "Bilik Sendiri" tiada utk Pelajar/Luar | Log masuk Pelajar/Luar → lihat seksyen Lokasi | Hanya "Lokasi Lain" tersedia | Pelajar/Luar |
| M3-04 | Lokasi Lain — Dalam Bangunan berjenjang | Pilih Negeri→Kampus→Bangunan→Blok→Aras→Ruang | Setiap dropdown aktif berturutan tanpa ralat | Semua |
| M3-05 | Lokasi Lain — Luar Bangunan berjenjang | Pilih Negeri→Kampus→Bangunan→Blok→Kategori→Sub Kategori | Dropdown infra terisi & berfungsi berjenjang | Semua |
| M3-06 | Peta Leaflet papar & tanda lokasi | Pilih "Luar Bangunan" → klik titik di peta | Peta papar penuh tanpa bertabur; koordinat auto-isi | Semua |
| M3-07 | Seret marker peta | Seret marker ke lokasi lain | Koordinat dikemaskini ikut marker baharu | Semua |
| M3-08 | Keterangan Lokasi wajib | Tinggalkan kosong → cuba seterusnya | Validasi halang & papar ralat | Semua |

### Modul 4 — Borang Aduan: Jenis Kerosakan & Validasi

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan |
|---|---|---|---|---|
| M4-01 | Seksyen→Elemen→Masalah berjenjang | Pilih Seksyen → semak Elemen/Masalah | Dropdown berfungsi berjenjang ikut kampus/infra | Semua |
| M4-02 | Keterangan Kerosakan wajib | Tinggalkan kosong → hantar | Validasi halang & papar ralat | Semua |
| M4-03 | Validasi lokasi belum dipilih | Jangan pilih Bilik Sendiri/Lokasi Lain → hantar | Ralat "Sila pilih Lokasi Kerosakan..." | Semua |
| M4-04 | Validasi jenis infra belum dipilih | Pilih Lokasi Lain tanpa Dalam/Luar Bangunan → hantar | Ralat "Sila pilih jenis infrastruktur..." | Semua |
| M4-05 | Validasi Luar Bangunan tanpa tanda peta | Pilih Luar Bangunan tanpa klik peta → hantar | Sistem paksa tanda peta dahulu | Semua |
| M4-06 | Borang lengkap → skrin pengesahan | Lengkapkan semua wajib → seterusnya | Dibawa ke skrin Pengesahan Maklumat | Semua |

### Modul 5 — Muat Naik Gambar Kerosakan

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan |
|---|---|---|---|---|
| M5-01 | Muat naik via Kamera | Klik "Kamera" slot 1 → ambil gambar | Gambar dipratonton dalam slot | Semua |
| M5-02 | Muat naik via Galeri | Klik "Galeri" slot 2 → pilih gambar | Gambar dipratonton dalam slot | Semua |
| M5-03 | Had maksimum 3 gambar | Muat naik ketiga-tiga slot | Tiada slot ke-4; maksimum 3 gambar | Semua |
| M5-04 | Had saiz fail 5MB | Muat naik gambar >5MB | Ditolak dgn mesej ralat jelas | Semua |
| M5-05 | Buang gambar dimuat naik | Klik "X" pada slot berisi | Slot kembali kosong | Semua |
| M5-06 | Gambar kekal selepas hantar | Muat naik 1–3 gambar → hantar aduan | Gambar sama terpapar di pengesahan & butiran | Semua |

### Modul 6 — Pengesahan & Penghantaran Aduan

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan |
|---|---|---|---|---|
| M6-01 | Skrin pengesahan tepat | Semak skrin "Pengesahan Maklumat" | Semua maklumat sepadan dgn yg diisi | Semua |
| M6-02 | Hantar & jana No. Aduan | Klik "Hantar" | Aduan disimpan; No. Aduan dipaparkan | Semua |
| M6-03 | E-mel notifikasi BOM diterima | Semak peti e-mel BOM | E-mel diterima dgn maklumat betul & lengkap | IT/BOM |
| M6-04 | Staf PPF → Inisiatif PPF | Log masuk staf dp_id PPF (cth A0140/N00020) & hantar | Direkod sbg "Inisiatif PPF", bukan Staf biasa | IT |
| M6-05 | Kategori pelapor betul utk semua jenis | Hantar aduan bagi Staf/Pelajar/Luar | Setiap direkod dgn kategori pelapor yg betul | IT |
| M6-06 | Butang "Aduan Baru"/"Semak Aduan" berfungsi | Klik kedua-dua butang (percubaan berasingan) | Navigasi ke borang kosong / senarai aduan | Semua |

### Modul 7 — Semakan Senarai & Butiran Aduan

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan |
|---|---|---|---|---|
| M7-01 | Senarai papar aduan sendiri sahaja | Buka "Semak Aduan" | Hanya aduan pengguna semasa kelihatan | Semua |
| M7-02 | Pagination senarai | Guna akaun dgn >10 aduan | 10 rekod/muka surat, navigasi berfungsi | IT |
| M7-03 | Status papar label & warna betul | Semak aduan pelbagai status | Label/badge sepadan status sebenar | IT |
| M7-04 | Medium papar label betul | Semak lajur Medium aduan online | Label "Online" (atau setara) betul | Semua |
| M7-05 | Klik No. Aduan → butiran tepat | Klik No. Aduan dalam senarai | Butiran penuh & tepat dipaparkan | Semua |
| M7-06 | Aduan orang lain tak boleh diakses | Tukar rujukan aduan pada URL butiran | Disekat / "Aduan Tidak Dijumpai" | IT |
| M7-07 | Kategori pengadu kosong papar "-" | Semak aduan dgn kategori kosong/null | Papar "-", bukan tersilap "Staf UiTM" | IT |
| M7-08 | Lokasi luar bangunan papar kategori infra | Buka butiran aduan luar bangunan | Nama kategori/sub-kategori infra dipaparkan | Semua |

### Modul 8 — Maklum Balas Selepas Siap Kerja

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan |
|---|---|---|---|---|
| M8-01 | Butang Maklumbalas muncul bila layak | Semak aduan status SIAP belum dinilai | Butang dipaparkan hanya bila layak | Semua |
| M8-02 | Maklum balas "Memuaskan" | Pilih "Memuaskan" → hantar | Disimpan; papar ikon "Memuaskan" | Semua |
| M8-03 | Maklum balas "Tidak Memuaskan" | Pilih "Tidak Memuaskan" → hantar | Disimpan; papar ikon "Tidak Memuaskan" | Semua |
| M8-04 | Validasi tiada pilihan ditanda | Hantar tanpa tanda pilihan | Validasi halang penghantaran | Semua |

### Modul 9 — PWA & Keserasian Peranti

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan |
|---|---|---|---|---|
| M9-01 | Label "Muat Turun e-Aduan" betul | Lihat seksyen muat turun di skrin log masuk | Papar "Muat Turun..." (bukan "Pasang") | Semua |
| M9-02 | Pemasangan PWA berfungsi | Klik Muat Turun pd peranti menyokong | Gesaan pemasangan terpapar & berjaya | IT |
| M9-03 | Ikon & nama aplikasi betul | Buka app yg dipasang dari skrin utama | Ikon & nama "e-Aduan" betul | IT |
| M9-04 | Responsif mudah alih | Lengkapkan aduan di skrin <768px | Borang/dropdown/peta boleh guna, tiada terpotong | Semua |
| M9-05 | Responsif tablet/desktop | Buka sistem di skrin lebih besar | Susun atur kemas & konsisten | IT |

### Modul 10 — Kawalan Akses & Sesi

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan |
|---|---|---|---|---|
| M10-01 | Sesi tamat semasa guna sistem | Biar sesi tamat tanpa aktiviti → hantar borang | Dilog keluar automatik dgn makluman jelas | IT |
| M10-02 | Akses terus tanpa log masuk disekat | Taip URL halaman dalaman terus | Disekat & diarah ke skrin log masuk | IT |
| M10-03 | Log keluar tamatkan sesi sepenuhnya | Log keluar → cuba "Back"/akses semula | Tidak boleh akses tanpa log masuk semula | Semua |

## 8.0 Log Kecacatan (Defect Log)

| Ref | Kes Ujian | Keterangan Isu | Keterukan | Status | Diberi Kepada | Tarikh Lapor |
|---|---|---|---|---|---|---|
| D-01 | | | | Baru | | |
| D-02 | | | | Baru | | |
| D-03 | | | | Baru | | |

*Keterukan: Kritikal (sistem tak boleh guna/data rosak) · Major (fungsi utama gagal) · Minor (isu kecil) · Kosmetik (visual sahaja).*

## 9.0 Ringkasan Keputusan UAT

| Jumlah Kes | Lulus | Gagal | Disekat | Kadar Kelulusan |
|---|---|---|---|---|
| 60 | | | | |

**Isu kritikal dibangkitkan**:

**Cadangan/catatan keseluruhan**:

**Keputusan akhir**: ☐ Lulus Sepenuhnya ☐ Lulus Bersyarat ☐ Gagal — Perlu Ulang Uji

## 10.0 Pengesahan & Kelulusan

| Peranan | Nama | Jawatan | Tandatangan | Tarikh |
|---|---|---|---|---|
| Disediakan Oleh (Penyelaras UAT) | Zurairi | | | |
| Disahkan Oleh (Wakil Pengguna — Staf) | | | | |
| Disahkan Oleh (Wakil Pengguna — Pelajar) | | | | |
| Disahkan Oleh (Wakil Pengguna — Luar) | | | | |
| Disahkan Oleh (Pegawai IT/Pembangun) | | | | |
| Diluluskan Oleh (Pemilik Sistem/BOM) | | | | |
