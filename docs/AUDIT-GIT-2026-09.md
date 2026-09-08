# Audit Git & Kawalan Perubahan — e-Aduan v3

**Tarikh audit:** 8 September 2026
**Skop:** repositori `xdaxzurairi/ea_newv3` (16 commit, 97 fail, 1.9 MB)
**Tujuan:** menilai risiko cara kod sistem ini disimpan, disemak dan dihantar ke production

---

## Ringkasan untuk pengurusan

Sistem e-Aduan v3 sudah berjalan di production, tetapi kod sumbernya diuruskan pada tahap projek peribadi, bukan tahap sistem operasi universiti. Tiga perkara paling perlu perhatian:

1. **Gambar aduan sebenar pengguna berada dalam repositori GitHub** — 12 fail JPEG dalam `uploads/wr/`. Ini data orang awam, bukan aset sistem, dan tidak boleh dipadam sepenuhnya dari sejarah git tanpa tindakan khusus. Ini isu PDPA 2010, bukan sekadar kekemasan.
2. **Tiga fail `.bak` dihantar ke web root production** dan IIS akan menghidangkannya sebagai teks biasa. `pages/semak.php.bak` mengandungi logik pertanyaan pangkalan data. Sesiapa yang teka nama fail boleh baca kod sumber.
3. **Tiada rekod apa yang sedang berjalan di production.** Deployment dibuat dengan salin fail secara manual ke `\\10.0.36.127\webs\ea_newv3\`. Git tidak tahu versi mana yang live, dan tiada tag atau release. Bila berlaku masalah, tiada titik rujukan untuk rollback.

Punca ketiga-tiganya sama: git digunakan sebagai tempat simpan salinan, bukan sebagai kawalan perubahan.

---

## Penemuan terperinci

### A. Data dan keselamatan

| # | Penemuan | Bukti | Risiko |
|---|----------|-------|--------|
| A1 | Gambar aduan pengguna dalam version control | `uploads/wr/wr_*.jpg` (12 fail, 1.5 MB), masuk melalui commit `16d8cdf`, `98dea8f`, `05400b0` | Tinggi — PDPA |
| A2 | Fail `.bak` dalam web root | `index.php.bak`, `pages/semak.php.bak`, `assets/css/style.css.bak` | Tinggi — pendedahan kod sumber |
| A3 | `web.config` hanya sekat `.env` | Tiada sekatan untuk `.bak`, `.old`, `.sql`, `.zip` | Tinggi |
| A4 | `uploads/.htaccess` tidak berfungsi | Fail Apache pada pelayan IIS. Kawalan "block PHP execution in uploads" sebenarnya tidak wujud | Tinggi |
| A5 | Alamat IP dalaman dalam repo | `10.0.36.97` (DB), `10.0.36.127` (file server), endpoint MinIO — termasuk dalam `.env.example` yang sepatutnya kosong | Sederhana |
| A6 | Konfigurasi mesin peribadi di-commit | `.claude/settings.local.json` mengandungi laluan pelayan dalaman dan kebenaran `Bash(git push *)` | Rendah |

Perkara yang **betul** dan patut dikekalkan: tiada satu pun kata laluan atau kunci API ditemui dalam keseluruhan sejarah git. `.env` diabaikan dengan betul, dan `config/env.php` memuatkan nilai dari persekitaran seperti sepatutnya. Asas pengurusan rahsia sudah kukuh.

### B. Proses dan kawalan

| # | Penemuan | Bukti |
|---|----------|-------|
| B1 | Semua kerja terus ke `master` | 16 commit, 0 merge commit, tiada branch kerja, tiada pull request |
| B2 | Tiada tag atau release | `git tag` kosong — tiada penanda versi production |
| B3 | Tiada CI/CD | Tiada `.github/workflows`. Deployment manual mengikut `docs/SOP-dev-to-prod.md` |
| B4 | Tiada ujian langsung | Tiada PHPUnit, tiada smoke test automatik. Pengesahan bergantung pada checklist manual 7 item |
| B5 | Bus factor = 1 | Zurairi satu-satunya penyumbang (15 commit + 1 di bawah identiti `nnkmz`, e-mel sama) |
| B6 | Tiada README | Ada dokumentasi baik dalam `docs/` (SOP, SYSTEM-OVERVIEW, FLOW-ADUAN), tetapi tiada titik masuk untuk orang baru |

### C. Disiplin commit

Format conventional commit (`feat:`, `fix:`, `refactor:`) digunakan pada 13 daripada 16 commit — ini di atas purata. Tetapi:

- Commit `04895cb` bernama "commit" sahaja
- Commit `1e1cf5c` menggabungkan tiga kerja berbeza dalam 1,631 baris: entri diari, revamp login, dan susun atur semak aduan
- Commit awal `472fda0` memasukkan 64 fail / 6,520 baris sekaligus — tiada sejarah sebelum April 2026
- Fail sesi AI dan diari peribadi di-commit ke repo sistem production: `daily-diary/`, `memories/session/latest-session-recap.txt` (mengandungi laluan `C:/Users/BSM/...`), `.cursor/rules/`

Corak commit menunjukkan kerja berlaku di luar git: 7 commit pada 3 Jun, kemudian kosong dua bulan, satu commit pada 5 Ogos. Ini konsisten dengan penyuntingan terus pada file share dan commit dibuat sekali-sekala sebagai salinan.

---

## Cadangan

### Segera (minggu ini, kos: masa sahaja)

1. **Keluarkan gambar aduan dari repo.** Padam `uploads/wr/` dari tracking, tambah `uploads/wr/*` ke `.gitignore`. Tulis semula sejarah dengan `git filter-repo` untuk buang jejaknya sepenuhnya, atau — jika repo ini private dan tiada clone luar — dokumenkan sebagai risiko diterima dan pastikan repo kekal private.
2. **Padam ketiga-tiga fail `.bak`** dari repo dan dari pelayan production. Tambah `*.bak`, `*.old`, `*.sql`, `*.zip` ke `.gitignore`.
3. **Kemas kini `web.config`** untuk sekat `.bak`, `.old`, `.sql`, `.md`, `.json` selain `.env`, dan pindahkan kawalan `uploads/.htaccess` ke `web.config` (`<handlers><clear/>` dalam folder uploads) supaya ia benar-benar berkuat kuasa di IIS.
4. **Sahkan repo GitHub ini private.** Jika public, ia perlu ditukar hari ini juga — IP dalaman dan struktur sistem terdedah.
5. **Kosongkan `.env.example`** dari nilai sebenar (`DB_HOST`, `DB_DATABASE`, endpoint MinIO). Fail contoh sepatutnya kosong.

### 30 hari (proses)

6. **Tag setiap deployment.** Sebelum salin fail ke production, buat `git tag v3.x.y` dan push. Ini memberi jawapan kepada soalan "apa yang live sekarang" dalam satu arahan, dan membolehkan rollback.
7. **Branch untuk setiap perubahan.** Kerja dalam `feature/...` atau `fix/...`, buka pull request, baca semula diff sendiri, kemudian merge. Walaupun seorang pembangun, PR memaksa semakan diri dan meninggalkan rekod sebab sesuatu perubahan dibuat.
8. **Lindungi `master`.** Aktifkan branch protection di GitHub: tiada push terus, perlu PR.
9. **Alih fail bukan-kod keluar dari repo.** `daily-diary/`, `memories/`, `.claude/settings.local.json` bukan sebahagian sistem e-Aduan.
10. **Tulis README** dengan tiga perkara sahaja: apa sistem ini, cara sediakan persekitaran dev, dan pautan ke SOP deployment.

### 90 hari (keupayaan)

11. **Automasi deployment.** Ganti salinan manual ke `\\10.0.36.127` dengan skrip deploy yang tarik dari tag git. Selagi punca kebenaran ialah file share dan bukan git, dev dan production akan terus terpisah tanpa disedari.
12. **Smoke test automatik.** Enam item pertama dalam checklist SOP boleh dijadikan skrip: login SSO, submit aduan, upload gambar, semak aduan, dan ujian bahawa `.env` memulangkan 403. Jalankan selepas setiap deploy.
13. **Selesaikan bus factor.** Sekurang-kurangnya seorang lagi kakitangan perlu ada akses repo, boleh deploy, dan pernah lakukannya sekali di bawah pemerhatian. Ini keputusan pengurusan, bukan keputusan teknikal — sistem aduan fasiliti yang bergantung pada seorang individu adalah risiko operasi.
14. **Log audit deployment.** Satu fail `CHANGELOG.md` yang mencatat tarikh, tag, dan siapa deploy. Diperlukan bila juruaudit dalaman bertanya.

---

## Keutamaan jika sumber terhad

Jika hanya satu perkara boleh dibuat: **buang fail `.bak` dan tambah sekatannya dalam `web.config`** (cadangan 2 dan 3). Ia mengambil masa 20 minit dan menutup pendedahan kod sumber yang aktif pada sistem production hari ini.

Jika dua: tambah **keluarkan gambar aduan pengguna** (cadangan 1), kerana ia melibatkan liabiliti undang-undang, bukan sekadar risiko teknikal.
