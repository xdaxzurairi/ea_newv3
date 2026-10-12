# e-Aduan v3 – Dokumen UAT Dwibahasa / Bilingual UAT Document

> **Versi hidup/boleh isi (BM/EN):** salinan interaktif (penjejakan status, log kecacatan, cetak PDF) ada di:
> https://claude.ai/artifact/2BJNi13Y7GvGmXJbMnw74V
>
> Fail ini (`docs/UAT-ea_newv3.md`) ialah rujukan senarai kes ujian dwibahasa yang disimpan bersama kod untuk rekod/version control.
> *This file is the bilingual test-case reference kept alongside the code for record-keeping/version control.*

**Versi / Version**: 1.0 (Draf — belum disahkan / Draft — not yet approved)
**Disediakan oleh / Prepared by**: Zurairi (zurairi@uitm.edu.my)
**Tarikh / Date**: 12 Oktober 2026 / 12 October 2026
**Jumlah kes ujian / Total test cases**: 60 kes merentasi 10 modul / 60 cases across 10 modules

---

## 1.0 Tujuan & Skop / Purpose & Scope

**Tujuan**: Mengesahkan sistem e-Aduan v3 memenuhi keperluan perniagaan sebenar — Staf, Pelajar dan Pengguna Luar boleh melaporkan kerosakan fasiliti, menyemak status dan memberi maklum balas tanpa halangan — sebelum disahkan untuk kegunaan penuh.
*Purpose: To confirm that the e-Aduan v3 system meets actual business requirements — Staff, Students and External Users can report facility damage, check status and give feedback without obstruction — before it is approved for full use.*

**Dalam skop / In scope**:
- Log masuk & pendaftaran (Google Staf/Pelajar, OTP Pengguna Luar) / *Login & registration (Google Staff/Student, External User OTP)*
- Borang aduan — lokasi, jenis kerosakan, gambar, validasi / *Complaint form — location, damage type, photos, validation*
- Pengesahan, penghantaran & notifikasi e-mel BOM / *Confirmation, submission & BOM e-mail notification*
- Semakan senarai, butiran aduan & maklum balas / *Complaint list review, details & feedback*
- PWA (muat turun aplikasi) & keserasian peranti / *PWA (app download) & device compatibility*
- Kawalan akses & tamat sesi dari perspektif pengguna / *Access control & session expiry from the user's perspective*

**Luar skop / Out of scope**:
- Pemprosesan dalaman arahan kerja di sistem Archibus/BOM selepas e-mel diterima / *Internal work-order processing in Archibus/BOM after the e-mail is received*
- Ujian penembusan & audit header keselamatan terperinci — rujuk `docs/CHECKLIST.md` & `docs/SECURITY-HEADERS.md` / *Penetration testing & detailed security header audit — see `docs/CHECKLIST.md` & `docs/SECURITY-HEADERS.md`*
- Ujian prestasi/beban / *Performance/load testing*

## 2.0 Definisi & Singkatan / Definitions & Abbreviations

| Istilah / Term | Maksud (BM) | Meaning (EN) |
|---|---|---|
| UAT | User Acceptance Testing — ujian penerimaan pengguna oleh wakil perniagaan sebenar | User Acceptance Testing by real business representatives |
| BOM | Pihak pengurusan fasiliti yang menerima notifikasi & menguruskan kerja pembaikan | Facilities management unit that receives notifications & manages repair work |
| OTP | One-Time Password — kod 6 digit sah sementara (10 minit) untuk mengesahkan e-mel Pengguna Luar | Temporary 6-digit code (valid 10 minutes) to verify an External User's e-mail |
| SSO | Single Sign-On — pengesahan identiti bersepadu portal UiTM | UiTM's integrated identity authentication |
| PPF | Kategori khas staf (dp_id tertentu) yang aduannya direkod sebagai "Inisiatif PPF" | Special staff category (specific dp_id) recorded as "Inisiatif PPF" |
| No. Aduan | Nombor rujukan unik aduan (wr_id) selepas berjaya disimpan ke jadual `wr` | Unique complaint reference number (wr_id) after saving to the `wr` table |
| PWA | Progressive Web App — aplikasi web yang boleh dimuat turun/dipasang pada peranti | A web app that can be downloaded/installed on a device |
| Pengguna Luar | Kontraktor/orang awam bukan staf atau pelajar UiTM berdaftar | Contractor/member of the public who is not a registered UiTM staff member or student |

## 3.0 Persekitaran & Keperluan Ujian / Test Environment & Requirements

- **URL dalaman/rangkaian UiTM** *(Internal/UiTM network)*: `http://10.0.36.127/ea_newv3`
- **URL staging**: `https://fmsdev.uitm.edu.my/ea_newv3`
- ⚠️ Sahkan URL persekitaran sebenar dengan pasukan IT sebelum sesi UAT — WAF UiTM menyekat sesetengah akses dari luar rangkaian.
  *Confirm the actual environment URL with the IT team before the UAT session — UiTM's WAF blocks some access from outside the network.*
- **Pelayar/peranti** *(Browsers/devices)*: Chrome & Edge terkini (desktop); Chrome Android & Safari iOS (mudah alih, wajib untuk ujian PWA / mobile, required for PWA testing)
- **Akaun diperlukan** *(Accounts required)*: 1 akaun Google Staf (digalakkan >1 bilik berdaftar e-Ruang), 1 akaun Google Pelajar, 1 e-mel belum berdaftar + 1 e-mel sudah berdaftar sebagai Pengguna Luar, akses peti e-mel BOM
  *1 Staff Google account (preferably >1 room registered in e-Ruang), 1 Student Google account, 1 unregistered + 1 already-registered e-mail for External User, access to the BOM mailbox*
- **Data ujian** *(Test data)*: sekurang-kurangnya satu bangunan/blok/ruang sedia ada bagi setiap kampus yang diuji, serta kategori infra luar bangunan
  *At least one existing building/block/room per campus under test, plus outdoor infrastructure categories*

## 4.0 Peranan & Tanggungjawab / Roles & Responsibilities

| Peranan / Role | Tanggungjawab (BM) | Responsibility (EN) |
|---|---|---|
| Penyelaras UAT / *UAT Coordinator* | Zurairi — menyelaras jadual, mengumpul keputusan, melapor & menutup isu | Schedules sessions, collects results, reports & closes issues |
| Penguji Perniagaan — Staf / *Business Tester — Staff* | Staf UiTM dilantik — menguji alur pelapor Staf | Appointed UiTM staff — tests the Staff reporter flow |
| Penguji Perniagaan — Pelajar / *Business Tester — Student* | Pelajar UiTM dilantik — menguji alur pelapor Pelajar | Appointed UiTM student — tests the Student reporter flow |
| Penguji Perniagaan — Luar / *Business Tester — External* | Individu luar/kontraktor dilantik — menguji alur Pengguna Luar | Appointed external individual/contractor — tests the External User flow |
| Pembangun / IT Support | Sedia semasa sesi UAT untuk menjawab soalan teknikal & membetulkan isu | Available during UAT sessions to answer technical questions & fix issues |
| Pemilik Sistem / BOM / *System Owner / BOM* | Sahkan notifikasi & aliran kerja; beri kelulusan akhir | Confirms notifications & workflow; gives final approval |

## 5.0 Kriteria Kemasukan & Keluar / Entry & Exit Criteria

**Kemasukan / Entry**: build dideploy ke persekitaran UAT; bug kritikal SIT/dev ditutup; akaun & data ujian sedia; dokumen diedarkan.
*The build is deployed to a confirmed UAT environment; critical SIT/dev bugs are closed; accounts & test data are ready; this document has been distributed.*

**Keluar / Exit**: semua kes keutamaan tinggi (log masuk, hantar aduan, semakan) Lulus; kecacatan terbuka hanya kosmetik/rendah risiko & dipersetujui; Seksyen 9.0 & 10.0 lengkap ditandatangani.
*All high-priority cases (login, submission, review) Pass; remaining open defects are cosmetic/low-risk only and agreed; Sections 9.0 & 10.0 are fully signed.*

## 6.0 Jadual UAT / UAT Schedule (templat — kemas kini tarikh sebenar / template — update with actual dates)

| Aktiviti / Activity | Tarikh Cadangan / Proposed Date |
|---|---|
| Taklimat & edaran dokumen / *Briefing & document distribution* | |
| Sesi UAT — Modul 1–5 / *UAT session — Modules 1–5* | |
| Sesi UAT — Modul 6–10 / *UAT session — Modules 6–10* | |
| Semakan & penutupan kecacatan / *Defect review & closure* | |
| Pengesahan & tandatangan akhir / *Final sign-off* | |

## 7.0 Senario & Kes Ujian / Test Scenarios & Test Cases

### Modul 1 — Log Masuk & Pendaftaran Pengguna / Login & User Registration

| ID | Senario / Scenario | Langkah Ujian / Test Steps | Keputusan Dijangka / Expected Result | Peranan / Role |
|---|---|---|---|---|
| M1-01 | Log masuk Staf melalui Google<br>*Staff login via Google* | Klik Google di bawah label "Staf & Pelajar" → log masuk dgn akaun staf sah<br>*Click Google below the "Staf & Pelajar" label → sign in with a valid staff account* | Berjaya log masuk & dibawa ke borang aduan; sesi staf aktif<br>*Login succeeds; taken to the complaint form with an active staff session* | Staf / Staff |
| M1-02 | Log masuk Pelajar melalui Google<br>*Student login via Google* | Sama seperti di atas dgn akaun Google pelajar yang sah<br>*Same as above, with a valid student Google account* | Berjaya log masuk; sistem kenali sebagai pelajar<br>*Login succeeds; the system identifies the user as a student* | Pelajar / Student |
| M1-03 | Label log masuk papar "Staf & Pelajar"<br>*Login label reads "Staf & Pelajar"* | Semak label teks di atas butang Google<br>*Inspect the text label above the Google button* | Label betul "Staf & Pelajar" (bukan "Staf & Student")<br>*Label correctly reads "Staf & Pelajar" (not "Staf & Student")* | Semua / All |
| M1-04 | Mohon kod OTP sebagai Pengguna Luar<br>*Request OTP as an External User* | Klik "Pengguna Luar" → masukkan e-mel sah → hantar<br>*Click "Pengguna Luar" → enter a valid e-mail → send* | Makluman kod dihantar; e-mel OTP diterima dlm beberapa minit, sah 10 minit<br>*"Code sent" notice shown; OTP e-mail arrives within minutes, valid 10 minutes* | Luar / External |
| M1-05 | Mohon OTP dgn e-mel tidak sah<br>*Request OTP with invalid e-mail* | Masukkan teks bukan e-mel → hantar<br>*Enter non-e-mail text → send* | Ralat "Sila masukkan alamat e-mel yang sah."<br>*Error "Sila masukkan alamat e-mel yang sah." shown* | Luar / External |
| M1-06 | Sahkan OTP — 1 akaun sedia ada<br>*Verify OTP — one existing account* | Masukkan kod sah utk e-mel berdaftar 1 akaun<br>*Enter the valid code for an e-mail registered to one account* | Terus log masuk ke borang aduan<br>*Logs straight into the complaint form* | Luar / External |
| M1-07 | Sahkan OTP — e-mel belum berdaftar<br>*Verify OTP — unregistered e-mail* | Masukkan kod sah utk e-mel baharu<br>*Enter the valid code for a new e-mail* | Skrin "Pendaftaran Pengguna Baru" dipaparkan<br>*"New User Registration" screen is shown* | Luar / External |
| M1-08 | Lengkapkan pendaftaran Luar baharu<br>*Complete new External registration* | Isi Nama (≤100 aksara)/No. K/P (6–20 aksara alfanumerik+sempang)/Telefon (pilihan) → "Daftar & Teruskan"<br>*Fill Name (≤100 chars)/IC (6–20 alphanumeric+hyphen)/Phone (optional) → "Daftar & Teruskan"* | Rekod disimpan; terus log masuk<br>*Record saved; logs straight in* | Luar / External |
| M1-09 | Sahkan OTP — e-mel dgn >1 akaun<br>*Verify OTP — e-mail with >1 account* | Masukkan kod sah utk e-mel berkaitan >1 No. K/P<br>*Enter the valid code for an e-mail linked to >1 IC number* | Skrin "Pilih Akaun" dipaparkan; pilih satu → log masuk<br>*"Select Account" screen shown; selecting one logs in* | Luar / External |
| M1-10 | Kod OTP salah/tamat tempoh<br>*Incorrect/expired OTP* | Masukkan kod salah atau tunggu lebih 10 minit<br>*Enter a wrong code, or wait more than 10 minutes* | Ralat dipaparkan; boleh "Hantar Semula"<br>*Error shown; "Resend" option available* | Luar / External |
| M1-11 | Daftar No. K/P sudah wujud dgn e-mel lain<br>*Register an IC already linked to another e-mail* | Masukkan No. K/P yg berkaitan e-mel lain semasa daftar<br>*Enter an IC already linked to a different e-mail during registration* | Ralat "No. K/P telah berdaftar dengan e-mel lain..."; disekat<br>*Error "No. K/P ini telah berdaftar dengan e-mel lain..." shown; blocked* | Luar / External |

### Modul 2 — Borang Aduan: Maklumat Pelapor / Complaint Form: Reporter Information

| ID | Senario / Scenario | Langkah Ujian / Test Steps | Keputusan Dijangka / Expected Result | Peranan / Role |
|---|---|---|---|---|
| M2-01 | Maklumat pelapor Staf auto-isi<br>*Staff reporter info auto-fills* | Log masuk Staf → lihat "1.0 Maklumat Pelapor"<br>*Log in as Staff → view "1.0 Maklumat Pelapor"* | Nama/jawatan/jabatan/bilik/telefon/e-mel tepat, read-only<br>*Name/position/department/room/phone/e-mail accurate, read-only* | Staf / Staff |
| M2-02 | Maklumat pelapor Pelajar auto-isi<br>*Student reporter info auto-fills* | Log masuk Pelajar → lihat seksyen sama<br>*Log in as Student → view the same section* | Nama/No. Matrik/fakulti/kampus tepat<br>*Name/matric number/faculty/campus accurate* | Pelajar / Student |
| M2-03 | Maklumat pelapor Luar auto-isi<br>*External reporter info auto-fills* | Log masuk Luar → lihat seksyen sama<br>*Log in as External → view the same section* | Nama/No. K/P/telefon tepat<br>*Name/IC number/phone accurate* | Luar / External |

### Modul 3 — Borang Aduan: Lokasi Kerosakan / Complaint Form: Damage Location

| ID | Senario / Scenario | Langkah Ujian / Test Steps | Keputusan Dijangka / Expected Result | Peranan / Role |
|---|---|---|---|---|
| M3-01 | "Bilik Sendiri" — staf 1 bilik<br>*"Bilik Sendiri" — staff with 1 room* | Pilih radio "Bilik Sendiri"<br>*Select the "Bilik Sendiri" radio* | Lokasi auto-isi tanpa pilihan tambahan<br>*Location auto-fills, no further selection* | Staf / Staff |
| M3-02 | "Bilik Sendiri" — staf >1 bilik<br>*"Bilik Sendiri" — staff with >1 room* | Pilih "Bilik Sendiri" → "Pilih Bilik Sendiri"<br>*Select "Bilik Sendiri" → "Pilih Bilik Sendiri"* | Popup senarai bilik; pilih satu → lokasi dikemaskini<br>*Room list popup; selecting one updates the location* | Staf / Staff |
| M3-03 | "Bilik Sendiri" tiada utk Pelajar/Luar<br>*"Bilik Sendiri" absent for Student/External* | Log masuk Pelajar/Luar → lihat seksyen Lokasi<br>*Log in as Student/External → view the Location section* | Hanya "Lokasi Lain" tersedia<br>*Only "Lokasi Lain" is available* | Pelajar/Luar / Student/External |
| M3-04 | Lokasi Lain — Dalam Bangunan berjenjang<br>*Other Location — Indoor cascade* | Pilih Negeri→Kampus→Bangunan→Blok→Aras→Ruang<br>*Select State→Campus→Building→Block→Floor→Room* | Setiap dropdown aktif berturutan tanpa ralat<br>*Each dropdown activates in sequence with no errors* | Semua / All |
| M3-05 | Lokasi Lain — Luar Bangunan berjenjang<br>*Other Location — Outdoor cascade* | Pilih Negeri→Kampus→Bangunan→Blok→Kategori→Sub Kategori<br>*Select State→Campus→Building→Block→Category→Sub-category* | Dropdown infra terisi & berfungsi berjenjang<br>*Infrastructure dropdowns populate & cascade correctly* | Semua / All |
| M3-06 | Peta Leaflet papar & tanda lokasi<br>*Leaflet map renders & pins location* | Pilih "Luar Bangunan" → klik titik di peta<br>*Select "Luar Bangunan" → click a point on the map* | Peta papar penuh tanpa bertabur; koordinat auto-isi<br>*Map renders fully without scattering; coordinates auto-fill* | Semua / All |
| M3-07 | Seret marker peta<br>*Drag the map marker* | Seret marker ke lokasi lain<br>*Drag the marker to a different location* | Koordinat dikemaskini ikut marker baharu<br>*Coordinates update to match the new marker position* | Semua / All |
| M3-08 | Keterangan Lokasi wajib<br>*"Keterangan Lokasi" is mandatory* | Tinggalkan kosong → cuba seterusnya<br>*Leave empty → try to proceed* | Validasi halang & papar ralat<br>*Validation blocks and shows an error* | Semua / All |

### Modul 4 — Borang Aduan: Jenis Kerosakan & Validasi / Complaint Form: Damage Type & Validation

| ID | Senario / Scenario | Langkah Ujian / Test Steps | Keputusan Dijangka / Expected Result | Peranan / Role |
|---|---|---|---|---|
| M4-01 | Seksyen→Elemen→Masalah berjenjang<br>*Section→Element→Problem cascade* | Pilih Seksyen → semak Elemen/Masalah<br>*Select Section → check Element/Problem* | Dropdown berfungsi berjenjang ikut kampus/infra<br>*Dropdowns cascade correctly per campus/infrastructure* | Semua / All |
| M4-02 | Keterangan Kerosakan wajib<br>*"Keterangan Kerosakan" is mandatory* | Tinggalkan kosong → hantar<br>*Leave empty → submit* | Validasi halang & papar ralat<br>*Validation blocks and shows an error* | Semua / All |
| M4-03 | Validasi lokasi belum dipilih<br>*Validation — location not selected* | Jangan pilih Bilik Sendiri/Lokasi Lain → hantar<br>*Don't select either option → submit* | Ralat "Sila pilih Lokasi Kerosakan..."<br>*Error "Sila pilih Lokasi Kerosakan..." shown* | Semua / All |
| M4-04 | Validasi jenis infra belum dipilih<br>*Validation — infrastructure type not selected* | Pilih Lokasi Lain tanpa Dalam/Luar Bangunan → hantar<br>*Select Lokasi Lain without Indoor/Outdoor → submit* | Ralat "Sila pilih jenis infrastruktur..."<br>*Error "Sila pilih jenis infrastruktur..." shown* | Semua / All |
| M4-05 | Validasi Luar Bangunan tanpa tanda peta<br>*Validation — Outdoor without a map pin* | Pilih Luar Bangunan tanpa klik peta → hantar<br>*Select Outdoor without clicking the map → submit* | Sistem paksa tanda peta dahulu<br>*System requires a map pin first* | Semua / All |
| M4-06 | Borang lengkap → skrin pengesahan<br>*Complete form → confirmation screen* | Lengkapkan semua wajib → seterusnya<br>*Complete all mandatory fields → next* | Dibawa ke skrin Pengesahan Maklumat<br>*Taken to the Confirmation screen* | Semua / All |

### Modul 5 — Muat Naik Gambar Kerosakan / Damage Photo Upload

| ID | Senario / Scenario | Langkah Ujian / Test Steps | Keputusan Dijangka / Expected Result | Peranan / Role |
|---|---|---|---|---|
| M5-01 | Muat naik via Kamera<br>*Upload via Camera* | Klik "Kamera" slot 1 → ambil gambar<br>*Click "Kamera" on slot 1 → capture a photo* | Gambar dipratonton dalam slot<br>*Photo is previewed in the slot* | Semua / All |
| M5-02 | Muat naik via Galeri<br>*Upload via Gallery* | Klik "Galeri" slot 2 → pilih gambar<br>*Click "Galeri" on slot 2 → pick a photo* | Gambar dipratonton dalam slot<br>*Photo is previewed in the slot* | Semua / All |
| M5-03 | Had maksimum 3 gambar<br>*Maximum of 3 photos* | Muat naik ketiga-tiga slot<br>*Fill all three slots* | Tiada slot ke-4; maksimum 3 gambar<br>*No 4th slot; maximum 3 photos enforced* | Semua / All |
| M5-04 | Had saiz fail 5MB<br>*5MB file size limit* | Muat naik gambar >5MB<br>*Upload a photo larger than 5MB* | Ditolak dgn mesej ralat jelas<br>*Rejected with a clear error message* | Semua / All |
| M5-05 | Buang gambar dimuat naik<br>*Remove an uploaded photo* | Klik "✕" pada slot berisi<br>*Click "✕" on a filled slot* | Slot kembali kosong<br>*Slot becomes empty again* | Semua / All |
| M5-06 | Gambar kekal selepas hantar<br>*Photos persist after submission* | Muat naik 1–3 gambar → hantar aduan<br>*Upload 1–3 photos → submit the complaint* | Gambar sama terpapar di pengesahan & butiran<br>*Same photos appear on the confirmation & detail screens* | Semua / All |

### Modul 6 — Pengesahan & Penghantaran Aduan / Complaint Confirmation & Submission

| ID | Senario / Scenario | Langkah Ujian / Test Steps | Keputusan Dijangka / Expected Result | Peranan / Role |
|---|---|---|---|---|
| M6-01 | Skrin pengesahan tepat<br>*Confirmation screen is accurate* | Semak skrin "Pengesahan Maklumat"<br>*Review the "Pengesahan Maklumat" screen* | Semua maklumat sepadan dgn yg diisi<br>*All information matches what was entered* | Semua / All |
| M6-02 | Hantar & jana No. Aduan<br>*Submit & generate complaint number* | Klik "Hantar"<br>*Click "Hantar"* | Aduan disimpan; No. Aduan dipaparkan<br>*Complaint saved; complaint number displayed* | Semua / All |
| M6-03 | E-mel notifikasi BOM diterima<br>*BOM notification e-mail received* | Semak peti e-mel BOM<br>*Check the BOM mailbox* | E-mel diterima dgn maklumat betul & lengkap<br>*E-mail received with correct, complete information* | IT/BOM |
| M6-04 | Staf PPF → Inisiatif PPF<br>*PPF staff → PPF Initiative* | Log masuk staf dp_id PPF (cth A0140/N00020) & hantar<br>*Log in with a PPF dp_id (e.g. A0140/N00020) & submit* | Direkod sbg "Inisiatif PPF", bukan Staf biasa<br>*Recorded as "Inisiatif PPF", not regular Staff* | IT |
| M6-05 | Kategori pelapor betul utk semua jenis<br>*Correct reporter category for all types* | Hantar aduan bagi Staf/Pelajar/Luar<br>*Submit a complaint for Staff/Student/External* | Setiap direkod dgn kategori pelapor yg betul<br>*Each is recorded with the correct reporter category* | IT |
| M6-06 | Butang "Aduan Baru"/"Semak Aduan"<br>*"Aduan Baru"/"Semak Aduan" buttons* | Klik kedua-dua butang (percubaan berasingan)<br>*Click both buttons (separate attempts)* | Navigasi ke borang kosong / senarai aduan<br>*Navigates to an empty form / the complaint list* | Semua / All |

### Modul 7 — Semakan Senarai & Butiran Aduan / Complaint List & Detail Review

| ID | Senario / Scenario | Langkah Ujian / Test Steps | Keputusan Dijangka / Expected Result | Peranan / Role |
|---|---|---|---|---|
| M7-01 | Senarai papar aduan sendiri sahaja<br>*List shows own complaints only* | Buka "Semak Aduan"<br>*Open "Semak Aduan"* | Hanya aduan pengguna semasa kelihatan<br>*Only the current user's complaints are visible* | Semua / All |
| M7-02 | Pagination senarai<br>*List pagination* | Guna akaun dgn >10 aduan<br>*Use an account with >10 complaints* | 10 rekod/muka surat, navigasi berfungsi<br>*10 records/page, navigation works* | IT |
| M7-03 | Status papar label & warna betul<br>*Status shows correct label & colour* | Semak aduan pelbagai status<br>*Review complaints with varying statuses* | Label/badge sepadan status sebenar<br>*Label/badge match the actual status* | IT |
| M7-04 | Medium papar label betul<br>*Medium shows the correct label* | Semak lajur Medium aduan online<br>*Check the Medium column for an online complaint* | Label "Online" betul<br>*"Online" label is correct* | Semua / All |
| M7-05 | Klik No. Aduan → butiran tepat<br>*Click complaint number → accurate details* | Klik No. Aduan dalam senarai<br>*Click a complaint number in the list* | Butiran penuh & tepat dipaparkan<br>*Full, accurate details are displayed* | Semua / All |
| M7-06 | Aduan orang lain tak boleh diakses<br>*Other users' complaints inaccessible* | Tukar rujukan aduan pada URL butiran<br>*Change the complaint reference in the detail URL* | Disekat / "Aduan Tidak Dijumpai"<br>*Blocked / "Aduan Tidak Dijumpai" shown* | IT |
| M7-07 | Kategori pengadu kosong papar "-"<br>*Empty reporter category shows "-"* | Semak aduan dgn kategori kosong/null<br>*Review a complaint with an empty/null category* | Papar "-", bukan tersilap "Staf UiTM"<br>*Shows "-", not incorrectly "Staf UiTM"* | IT |
| M7-08 | Lokasi luar bangunan papar kategori infra<br>*Outdoor location shows infra category* | Buka butiran aduan luar bangunan<br>*Open an outdoor complaint's details* | Nama kategori/sub-kategori infra dipaparkan<br>*Infrastructure category/sub-category name is shown* | Semua / All |

### Modul 8 — Maklum Balas Selepas Siap Kerja / Post-Completion Feedback

| ID | Senario / Scenario | Langkah Ujian / Test Steps | Keputusan Dijangka / Expected Result | Peranan / Role |
|---|---|---|---|---|
| M8-01 | Butang Maklumbalas muncul bila layak<br>*"Maklumbalas" button appears when eligible* | Semak aduan status SIAP belum dinilai<br>*Review a completed, unrated complaint* | Butang dipaparkan hanya bila layak<br>*Button shown only when eligible* | Semua / All |
| M8-02 | Maklum balas "Memuaskan"<br>*"Memuaskan" (Satisfied) feedback* | Pilih "Memuaskan" → hantar<br>*Select "Memuaskan" → submit* | Disimpan; papar ikon "Memuaskan"<br>*Saved; "Memuaskan" icon is shown* | Semua / All |
| M8-03 | Maklum balas "Tidak Memuaskan"<br>*"Tidak Memuaskan" (Unsatisfied) feedback* | Pilih "Tidak Memuaskan" → hantar<br>*Select "Tidak Memuaskan" → submit* | Disimpan; papar ikon "Tidak Memuaskan"<br>*Saved; "Tidak Memuaskan" icon is shown* | Semua / All |
| M8-04 | Validasi tiada pilihan ditanda<br>*Validation — no option selected* | Hantar tanpa tanda pilihan<br>*Submit without selecting an option* | Validasi halang penghantaran<br>*Validation blocks submission* | Semua / All |

### Modul 9 — PWA & Keserasian Peranti / PWA & Device Compatibility

| ID | Senario / Scenario | Langkah Ujian / Test Steps | Keputusan Dijangka / Expected Result | Peranan / Role |
|---|---|---|---|---|
| M9-01 | Label "Muat Turun e-Aduan" betul<br>*"Muat Turun e-Aduan" label is correct* | Lihat seksyen muat turun di skrin log masuk<br>*View the download section on the login screen* | Papar "Muat Turun..." (bukan "Pasang")<br>*Shows "Muat Turun..." (not "Pasang")* | Semua / All |
| M9-02 | Pemasangan PWA berfungsi<br>*PWA installation works* | Klik Muat Turun pd peranti menyokong<br>*Click Muat Turun on a supporting device* | Gesaan pemasangan terpapar & berjaya<br>*Install prompt appears and succeeds* | IT |
| M9-03 | Ikon & nama aplikasi betul<br>*App icon & name are correct* | Buka app yg dipasang dari skrin utama<br>*Open the installed app from the home screen* | Ikon & nama "e-Aduan" betul<br>*"e-Aduan" icon & name are correct* | IT |
| M9-04 | Responsif mudah alih<br>*Responsive on mobile* | Lengkapkan aduan di skrin <768px<br>*Complete a complaint on a screen <768px wide* | Borang/dropdown/peta boleh guna, tiada terpotong<br>*Form/dropdowns/map fully usable, nothing clipped* | Semua / All |
| M9-05 | Responsif tablet/desktop<br>*Responsive on tablet/desktop* | Buka sistem di skrin lebih besar<br>*Open the system on a larger screen* | Susun atur kemas & konsisten<br>*Layout stays tidy and consistent* | IT |

### Modul 10 — Kawalan Akses & Sesi / Access Control & Session

| ID | Senario / Scenario | Langkah Ujian / Test Steps | Keputusan Dijangka / Expected Result | Peranan / Role |
|---|---|---|---|---|
| M10-01 | Sesi tamat selepas 2 jam tidak aktif<br>*Session expires after 2 hours idle* | Biar sesi tanpa aktiviti >2 jam → hantar borang<br>*Leave the session idle >2 hours → submit the form* | Dilog keluar automatik dgn makluman jelas<br>*Automatically logged out with a clear notice* | IT |
| M10-02 | Akses terus tanpa log masuk disekat<br>*Direct access without login is blocked* | Taip URL halaman dalaman terus<br>*Type an internal page URL directly* | Disekat & diarah ke skrin log masuk<br>*Blocked and redirected to the login screen* | IT |
| M10-03 | Log keluar tamatkan sesi sepenuhnya<br>*Logout fully ends the session* | Log keluar → cuba "Back"/akses semula<br>*Log out → try "Back" / re-accessing a page* | Tidak boleh akses tanpa log masuk semula<br>*Cannot access without logging in again* | Semua / All |

## 8.0 Log Kecacatan / Defect Log

| Ref | Kes Ujian / Test Case | Keterangan Isu / Issue Description | Keterukan / Severity | Status | Diberi Kepada / Assigned To | Tarikh Lapor / Date Raised |
|---|---|---|---|---|---|---|
| D-01 | | | | Baru / New | | |
| D-02 | | | | Baru / New | | |
| D-03 | | | | Baru / New | | |

*Keterukan/Severity: **Kritikal/Critical** (sistem tak boleh guna/data rosak — system unusable/data corrupted) · **Major** (fungsi utama gagal — key function fails) · **Minor** (isu kecil — small issue) · **Kosmetik/Cosmetic** (visual sahaja — visual only).*

## 9.0 Ringkasan Keputusan UAT / UAT Results Summary

| Jumlah Kes / Total | Lulus / Pass | Gagal / Fail | Disekat / Blocked | Kadar Kelulusan / Pass Rate |
|---|---|---|---|---|
| 60 | | | | |

**Isu kritikal dibangkitkan / Critical issues raised**:

**Cadangan/catatan keseluruhan / Overall recommendations/notes**:

**Keputusan akhir / Final decision**: ☐ Lulus Sepenuhnya / *Fully Passed* ☐ Lulus Bersyarat / *Conditionally Passed* ☐ Gagal — Perlu Ulang Uji / *Failed — Retest Required*

## 10.0 Pengesahan & Kelulusan / Sign-off & Approval

| Peranan / Role | Nama / Name | Jawatan / Position | Tandatangan / Signature | Tarikh / Date |
|---|---|---|---|---|
| Disediakan Oleh (Penyelaras UAT) / *Prepared By (UAT Coordinator)* | Zurairi | | | |
| Disahkan Oleh (Wakil Pengguna — Staf) / *Confirmed By (Staff Rep.)* | | | | |
| Disahkan Oleh (Wakil Pengguna — Pelajar) / *Confirmed By (Student Rep.)* | | | | |
| Disahkan Oleh (Wakil Pengguna — Luar) / *Confirmed By (External Rep.)* | | | | |
| Disahkan Oleh (Pegawai IT/Pembangun) / *Confirmed By (IT/Developer)* | | | | |
| Diluluskan Oleh (Pemilik Sistem/BOM) / *Approved By (System Owner/BOM)* | | | | |
