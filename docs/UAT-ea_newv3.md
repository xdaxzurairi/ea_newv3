# e-Aduan v3: Dokumen Ujian Penerimaan Pengguna (UAT)

> Salinan cetak rasmi (PDF, hitam & putih, petak semak) disimpan sebagai `UAT-ea_newv3.pdf` dalam folder yang sama. Fail ini ialah rujukan senarai kes ujian untuk rekod dan kawalan versi.

**Versi**: 1.0 (Draf, belum disahkan)  
**Disediakan oleh**: Zurairi  
**Tarikh**: 12 Oktober 2026  
**Jumlah kes ujian**: 60 kes merentasi 10 modul

---



## 1.0 Tujuan dan Skop

**Tujuan:** Mengesahkan bahawa sistem e-Aduan v3 memenuhi keperluan perniagaan sebenar, iaitu Staf, Pelajar dan Pengguna Luar dapat melaporkan kerosakan fasiliti, menyemak status aduan dan memberi maklum balas tanpa halangan, sebelum sistem disahkan untuk kegunaan penuh.

**Dalam skop:**

- Log masuk dan pendaftaran (Google Staf/Pelajar, OTP Pengguna Luar)
- Borang aduan: lokasi, jenis kerosakan, gambar dan validasi
- Pengesahan, penghantaran dan notifikasi e-mel BOM
- Semakan senarai aduan, butiran aduan dan maklum balas
- PWA (muat turun aplikasi) dan keserasian peranti
- Kawalan akses dan tamat sesi dari perspektif pengguna

**Luar skop:**

- Pemprosesan dalaman arahan kerja di sistem Archibus/BOM selepas e-mel diterima
- Ujian penembusan dan audit header keselamatan terperinci (dirujuk dalam dokumen senarai semak keselamatan yang berasingan)
- Ujian prestasi dan beban

## 2.0 Definisi dan Singkatan

| Istilah | Maksud |
|---|---|
| UAT | User Acceptance Testing: ujian penerimaan pengguna oleh wakil perniagaan sebenar. |
| BOM | Pihak pengurusan fasiliti yang menerima notifikasi dan menguruskan kerja pembaikan. |
| OTP | One-Time Password: kod 6 digit yang sah sementara (10 minit) bagi mengesahkan e-mel Pengguna Luar. |
| SSO | Single Sign-On: pengesahan identiti bersepadu portal UiTM. |
| PPF | Kategori khas staf (dp_id tertentu) yang aduannya direkodkan sebagai "Inisiatif PPF". |
| No. Aduan | Nombor rujukan unik aduan (wr_id) selepas berjaya disimpan ke jadual wr. |
| PWA | Progressive Web App: aplikasi web yang boleh dimuat turun dan dipasang pada peranti. |
| Pengguna Luar | Kontraktor atau orang awam yang bukan staf atau pelajar UiTM berdaftar. |

## 3.0 Persekitaran dan Keperluan Ujian

- **Persekitaran:** Alamat persekitaran UAT akan dimaklumkan secara bertulis oleh pasukan IT sebelum sesi bermula. Capaian dari luar rangkaian UiTM mungkin disekat oleh WAF.
- **Pelayar dan peranti:** Chrome dan Edge terkini (desktop); Chrome Android dan Safari iOS (mudah alih, wajib bagi ujian PWA dan responsif).
- **Akaun diperlukan:** 1 akaun Google Staf (digalakkan mempunyai lebih daripada 1 bilik berdaftar dalam e-Ruang); 1 akaun Google Pelajar; 1 e-mel belum berdaftar dan 1 e-mel sudah berdaftar sebagai Pengguna Luar; akses kepada peti e-mel BOM.
- **Data ujian:** Sekurang-kurangnya satu bangunan/blok/ruang sedia ada bagi setiap kampus yang diuji, serta kategori infrastruktur luar bangunan.

## 4.0 Peranan dan Tanggungjawab

| Peranan | Tanggungjawab |
|---|---|
| Penyelaras UAT | Menyelaras jadual, mengumpul keputusan, melapor dan menutup isu. |
| Penguji Perniagaan (Staf) | Staf UiTM yang dilantik; menguji alur pelapor Staf. |
| Penguji Perniagaan (Pelajar) | Pelajar UiTM yang dilantik; menguji alur pelapor Pelajar. |
| Penguji Perniagaan (Luar) | Individu luar/kontraktor yang dilantik; menguji alur Pengguna Luar. |
| Pembangun / Sokongan IT | Sedia membantu semasa sesi UAT bagi menjawab soalan teknikal dan membetulkan isu. |
| Pemilik Sistem / BOM | Mengesahkan notifikasi dan aliran kerja; memberi kelulusan akhir. |

## 5.0 Kriteria Kemasukan dan Keluar

**Kriteria kemasukan:** Binaan (build) telah dideploy ke persekitaran UAT yang disahkan; pepijat kritikal peringkat SIT/pembangunan telah ditutup; akaun dan data ujian telah disediakan; dokumen ini telah diedarkan kepada semua penguji.

**Kriteria keluar:** Semua kes berkeutamaan tinggi (log masuk, penghantaran aduan, semakan aduan) berstatus Lulus; kecacatan terbuka yang berbaki hanya berkategori kosmetik atau berisiko rendah dan telah dipersetujui; Seksyen 9.0 dan 10.0 telah dilengkapkan dan ditandatangani.

## 6.0 Jadual UAT

| Aktiviti | Tarikh Cadangan | Tarikh Sebenar |
|---|---|---|
| Taklimat dan edaran dokumen |  |  |
| Sesi UAT: Modul 1 hingga 5 |  |  |
| Sesi UAT: Modul 6 hingga 10 |  |  |
| Semakan dan penutupan kecacatan |  |  |
| Pengesahan dan tandatangan akhir |  |  |

**Arahan mengisi:** Bagi setiap kes ujian, tandakan satu (1) petak sahaja pada lajur Keputusan: **Lulus** jika keputusan sebenar menepati keputusan dijangka; **Gagal** jika tidak menepati (rekodkan dalam Log Kecacatan, Seksyen 8.0); **Disekat** jika kes tidak dapat dilaksanakan kerana halangan persekitaran atau kebergantungan. Catat rujukan kecacatan (cth. D-01) pada lajur Catatan.



## 7.0 Senario dan Kes Ujian

### Modul 1 — Log Masuk & Pendaftaran Pengguna

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan | Keputusan |
|---|---|---|---|---|---|
| M1-01 | Log masuk Staf melalui Google | Klik Google di bawah label "Staf & Pelajar" → log masuk dgn akaun staf sah | Berjaya log masuk & dibawa ke borang aduan; sesi staf aktif | Staf | ☐ Lulus ☐ Gagal ☐ Disekat |
| M1-02 | Log masuk Pelajar melalui Google | Sama seperti di atas dgn akaun Google pelajar yang sah | Berjaya log masuk; sistem kenali sebagai pelajar | Pelajar | ☐ Lulus ☐ Gagal ☐ Disekat |
| M1-03 | Label log masuk papar "Staf & Pelajar" | Semak label teks di atas butang Google | Label betul "Staf & Pelajar" (bukan label berbahasa Inggeris) | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M1-04 | Mohon kod OTP sebagai Pengguna Luar | Klik "Pengguna Luar" → masukkan e-mel sah → hantar | Makluman kod dihantar; e-mel OTP diterima dlm beberapa minit, sah 10 minit | Pengguna Luar | ☐ Lulus ☐ Gagal ☐ Disekat |
| M1-05 | Mohon OTP dgn e-mel tidak sah | Masukkan teks bukan e-mel → hantar | Ralat "Sila masukkan alamat e-mel yang sah." | Pengguna Luar | ☐ Lulus ☐ Gagal ☐ Disekat |
| M1-06 | Sahkan OTP — 1 akaun sedia ada | Masukkan kod sah utk e-mel berdaftar 1 akaun | Terus log masuk ke borang aduan | Pengguna Luar | ☐ Lulus ☐ Gagal ☐ Disekat |
| M1-07 | Sahkan OTP — e-mel belum berdaftar | Masukkan kod sah utk e-mel baharu | Skrin "Pendaftaran Pengguna Baru" dipaparkan | Pengguna Luar | ☐ Lulus ☐ Gagal ☐ Disekat |
| M1-08 | Lengkapkan pendaftaran Luar baharu | Isi Nama (≤100 aksara)/No. K/P (6–20 aksara alfanumerik+sempang)/Telefon (pilihan) → "Daftar & Teruskan" | Rekod disimpan; terus log masuk | Pengguna Luar | ☐ Lulus ☐ Gagal ☐ Disekat |
| M1-09 | Sahkan OTP — e-mel dgn >1 akaun | Masukkan kod sah utk e-mel berkaitan >1 No. K/P | Skrin "Pilih Akaun" dipaparkan; pilih satu → log masuk | Pengguna Luar | ☐ Lulus ☐ Gagal ☐ Disekat |
| M1-10 | Kod OTP salah/tamat tempoh | Masukkan kod salah atau tunggu lebih 10 minit | Ralat dipaparkan; boleh "Hantar Semula" | Pengguna Luar | ☐ Lulus ☐ Gagal ☐ Disekat |
| M1-11 | Daftar No. K/P sudah wujud dgn e-mel lain | Masukkan No. K/P yg berkaitan e-mel lain semasa daftar | Ralat "No. K/P telah berdaftar dengan e-mel lain..."; disekat | Pengguna Luar | ☐ Lulus ☐ Gagal ☐ Disekat |

### Modul 2 — Borang Aduan: Maklumat Pelapor

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan | Keputusan |
|---|---|---|---|---|---|
| M2-01 | Maklumat pelapor Staf auto-isi | Log masuk Staf → lihat "1.0 Maklumat Pelapor" | Nama/jawatan/jabatan/bilik/telefon/e-mel tepat, read-only | Staf | ☐ Lulus ☐ Gagal ☐ Disekat |
| M2-02 | Maklumat pelapor Pelajar auto-isi | Log masuk Pelajar → lihat seksyen sama | Nama/No. Matrik/fakulti/kampus tepat | Pelajar | ☐ Lulus ☐ Gagal ☐ Disekat |
| M2-03 | Maklumat pelapor Luar auto-isi | Log masuk Luar → lihat seksyen sama | Nama/No. K/P/telefon tepat | Pengguna Luar | ☐ Lulus ☐ Gagal ☐ Disekat |

### Modul 3 — Borang Aduan: Lokasi Kerosakan

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan | Keputusan |
|---|---|---|---|---|---|
| M3-01 | "Bilik Sendiri" — staf 1 bilik | Pilih radio "Bilik Sendiri" | Lokasi auto-isi tanpa pilihan tambahan | Staf | ☐ Lulus ☐ Gagal ☐ Disekat |
| M3-02 | "Bilik Sendiri" — staf >1 bilik | Pilih "Bilik Sendiri" → "Pilih Bilik Sendiri" | Popup senarai bilik; pilih satu → lokasi dikemaskini | Staf | ☐ Lulus ☐ Gagal ☐ Disekat |
| M3-03 | "Bilik Sendiri" tiada utk Pelajar/Luar | Log masuk Pelajar/Luar → lihat seksyen Lokasi | Hanya "Lokasi Lain" tersedia | Pelajar | ☐ Lulus ☐ Gagal ☐ Disekat |
| M3-04 | Lokasi Lain — Dalam Bangunan berjenjang | Pilih Negeri→Kampus→Bangunan→Blok→Aras→Ruang | Setiap dropdown aktif berturutan tanpa ralat | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M3-05 | Lokasi Lain — Luar Bangunan berjenjang | Pilih Negeri→Kampus→Bangunan→Blok→Kategori→Sub Kategori | Dropdown infra terisi & berfungsi berjenjang | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M3-06 | Peta Leaflet papar & tanda lokasi | Pilih "Luar Bangunan" → klik titik di peta | Peta papar penuh tanpa bertabur; koordinat auto-isi | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M3-07 | Seret marker peta | Seret marker ke lokasi lain | Koordinat dikemaskini ikut marker baharu | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M3-08 | Keterangan Lokasi wajib | Tinggalkan kosong → cuba seterusnya | Validasi halang & papar ralat | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |

### Modul 4 — Borang Aduan: Jenis Kerosakan & Validasi

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan | Keputusan |
|---|---|---|---|---|---|
| M4-01 | Seksyen→Elemen→Masalah berjenjang | Pilih Seksyen → semak Elemen/Masalah | Dropdown berfungsi berjenjang ikut kampus/infra | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M4-02 | Keterangan Kerosakan wajib | Tinggalkan kosong → hantar | Validasi halang & papar ralat | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M4-03 | Validasi lokasi belum dipilih | Jangan pilih Bilik Sendiri/Lokasi Lain → hantar | Ralat "Sila pilih Lokasi Kerosakan..." | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M4-04 | Validasi jenis infra belum dipilih | Pilih Lokasi Lain tanpa Dalam/Luar Bangunan → hantar | Ralat "Sila pilih jenis infrastruktur..." | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M4-05 | Validasi Luar Bangunan tanpa tanda peta | Pilih Luar Bangunan tanpa klik peta → hantar | Sistem paksa tanda peta dahulu | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M4-06 | Borang lengkap → skrin pengesahan | Lengkapkan semua wajib → seterusnya | Dibawa ke skrin Pengesahan Maklumat | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |

### Modul 5 — Muat Naik Gambar Kerosakan

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan | Keputusan |
|---|---|---|---|---|---|
| M5-01 | Muat naik via Kamera | Klik "Kamera" slot 1 → ambil gambar | Gambar dipratonton dalam slot | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M5-02 | Muat naik via Galeri | Klik "Galeri" slot 2 → pilih gambar | Gambar dipratonton dalam slot | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M5-03 | Had maksimum 3 gambar | Muat naik ketiga-tiga slot | Tiada slot ke-4; maksimum 3 gambar | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M5-04 | Had saiz fail 5MB | Muat naik gambar >5MB | Ditolak dgn mesej ralat jelas | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M5-05 | Buang gambar dimuat naik | Klik "✕" pada slot berisi | Slot kembali kosong | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M5-06 | Gambar kekal selepas hantar | Muat naik 1–3 gambar → hantar aduan | Gambar sama terpapar di pengesahan & butiran | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |

### Modul 6 — Pengesahan & Penghantaran Aduan

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan | Keputusan |
|---|---|---|---|---|---|
| M6-01 | Skrin pengesahan tepat | Semak skrin "Pengesahan Maklumat" | Semua maklumat sepadan dgn yg diisi | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M6-02 | Hantar & jana No. Aduan | Klik "Hantar" | Aduan disimpan; No. Aduan dipaparkan | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M6-03 | E-mel notifikasi BOM diterima | Semak peti e-mel BOM | E-mel diterima dgn maklumat betul & lengkap | IT/BOM | ☐ Lulus ☐ Gagal ☐ Disekat |
| M6-04 | Staf PPF → Inisiatif PPF | Log masuk staf dp_id PPF (cth A0140/N00020) & hantar | Direkod sbg "Inisiatif PPF", bukan Staf biasa | IT | ☐ Lulus ☐ Gagal ☐ Disekat |
| M6-05 | Kategori pelapor betul utk semua jenis | Hantar aduan bagi Staf/Pelajar/Luar | Setiap direkod dgn kategori pelapor yg betul | IT | ☐ Lulus ☐ Gagal ☐ Disekat |
| M6-06 | Butang "Aduan Baru"/"Semak Aduan" | Klik kedua-dua butang (percubaan berasingan) | Navigasi ke borang kosong / senarai aduan | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |

### Modul 7 — Semakan Senarai & Butiran Aduan

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan | Keputusan |
|---|---|---|---|---|---|
| M7-01 | Senarai papar aduan sendiri sahaja | Buka "Semak Aduan" | Hanya aduan pengguna semasa kelihatan | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M7-02 | Pagination senarai | Guna akaun dgn >10 aduan | 10 rekod/muka surat, navigasi berfungsi | IT | ☐ Lulus ☐ Gagal ☐ Disekat |
| M7-03 | Status papar label & warna betul | Semak aduan pelbagai status | Label/badge sepadan status sebenar | IT | ☐ Lulus ☐ Gagal ☐ Disekat |
| M7-04 | Medium papar label betul | Semak lajur Medium aduan online | Label "Online" betul | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M7-05 | Klik No. Aduan → butiran tepat | Klik No. Aduan dalam senarai | Butiran penuh & tepat dipaparkan | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M7-06 | Aduan orang lain tak boleh diakses | Tukar rujukan aduan pada URL butiran | Disekat / "Aduan Tidak Dijumpai" | IT | ☐ Lulus ☐ Gagal ☐ Disekat |
| M7-07 | Kategori pengadu kosong papar "-" | Semak aduan dgn kategori kosong/null | Papar "-", bukan tersilap "Staf UiTM" | IT | ☐ Lulus ☐ Gagal ☐ Disekat |
| M7-08 | Lokasi luar bangunan papar kategori infra | Buka butiran aduan luar bangunan | Nama kategori/sub-kategori infra dipaparkan | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |

### Modul 8 — Maklum Balas Selepas Siap Kerja

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan | Keputusan |
|---|---|---|---|---|---|
| M8-01 | Butang Maklumbalas muncul bila layak | Semak aduan status SIAP belum dinilai | Butang dipaparkan hanya bila layak | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M8-02 | Maklum balas "Memuaskan" | Pilih "Memuaskan" → hantar | Disimpan; papar ikon "Memuaskan" | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M8-03 | Maklum balas "Tidak Memuaskan" | Pilih "Tidak Memuaskan" → hantar | Disimpan; papar ikon "Tidak Memuaskan" | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M8-04 | Validasi tiada pilihan ditanda | Hantar tanpa tanda pilihan | Validasi halang penghantaran | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |

### Modul 9 — PWA & Keserasian Peranti

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan | Keputusan |
|---|---|---|---|---|---|
| M9-01 | Label "Muat Turun e-Aduan" betul | Lihat seksyen muat turun di skrin log masuk | Papar "Muat Turun..." (bukan "Pasang") | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M9-02 | Pemasangan PWA berfungsi | Klik Muat Turun pd peranti menyokong | Gesaan pemasangan terpapar & berjaya | IT | ☐ Lulus ☐ Gagal ☐ Disekat |
| M9-03 | Ikon & nama aplikasi betul | Buka app yg dipasang dari skrin utama | Ikon & nama "e-Aduan" betul | IT | ☐ Lulus ☐ Gagal ☐ Disekat |
| M9-04 | Responsif mudah alih | Lengkapkan aduan di skrin <768px | Borang/dropdown/peta boleh guna, tiada terpotong | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |
| M9-05 | Responsif tablet/desktop | Buka sistem di skrin lebih besar | Susun atur kemas & konsisten | IT | ☐ Lulus ☐ Gagal ☐ Disekat |

### Modul 10 — Kawalan Akses & Sesi

| ID | Senario | Langkah Ujian | Keputusan Dijangka | Peranan | Keputusan |
|---|---|---|---|---|---|
| M10-01 | Sesi tamat selepas 2 jam tidak aktif | Biar sesi tanpa aktiviti >2 jam → hantar borang | Dilog keluar automatik dgn makluman jelas | IT | ☐ Lulus ☐ Gagal ☐ Disekat |
| M10-02 | Akses terus tanpa log masuk disekat | Taip URL halaman dalaman terus | Disekat & diarah ke skrin log masuk | IT | ☐ Lulus ☐ Gagal ☐ Disekat |
| M10-03 | Log keluar tamatkan sesi sepenuhnya | Log keluar → cuba "Back"/akses semula | Tidak boleh akses tanpa log masuk semula | Semua | ☐ Lulus ☐ Gagal ☐ Disekat |



## 8.0 Log Kecacatan

| Rujukan | Kes Ujian | Keterangan Isu | Keterukan | Status | Diberi Kepada | Tarikh Lapor |
|---|---|---|---|---|---|---|
| D-01 |  |  | Kritikal  Major  Minor  Kosmetik | Baru  Dalam Proses  Selesai  Ditutup |  |  || D-02 |  |  | Kritikal  Major  Minor  Kosmetik | Baru  Dalam Proses  Selesai  Ditutup |  |  || D-03 |  |  | Kritikal  Major  Minor  Kosmetik | Baru  Dalam Proses  Selesai  Ditutup |  |  || D-04 |  |  | Kritikal  Major  Minor  Kosmetik | Baru  Dalam Proses  Selesai  Ditutup |  |  || D-05 |  |  | Kritikal  Major  Minor  Kosmetik | Baru  Dalam Proses  Selesai  Ditutup |  |  |

**Takrifan keterukan:** **Kritikal** (sistem tidak boleh digunakan atau data rosak); **Major** (fungsi utama gagal); **Minor** (isu kecil, ada penyelesaian sementara); **Kosmetik** (isu paparan visual sahaja).

## 9.0 Ringkasan Keputusan UAT

| Jumlah Kes | Lulus | Gagal | Disekat | Kadar Kelulusan |
|---|---|---|---|---|
| 60 |  |  |  |  |

**Isu kritikal yang dibangkitkan:**

**Cadangan / catatan keseluruhan:**

**Keputusan akhir (tandakan satu):**

☐ Lulus Sepenuhnya     ☐ Lulus Bersyarat     ☐ Gagal / Perlu Ujian Semula

## 10.0 Pengesahan dan Kelulusan

| Peranan | Nama | Jawatan | Tandatangan | Tarikh |
|---|---|---|---|---|
| Disediakan oleh (Penyelaras UAT) | Zurairi |  |  |  || Disahkan oleh (Wakil Pengguna: Staf) |  |  |  |  || Disahkan oleh (Wakil Pengguna: Pelajar) |  |  |  |  || Disahkan oleh (Wakil Pengguna: Luar) |  |  |  |  || Disahkan oleh (Pegawai IT / Pembangun) |  |  |  |  || Diluluskan oleh (Pemilik Sistem / BOM) |  |  |  |  |