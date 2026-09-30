# SOP: Switch Dev → Production

## e-Aduan v3 — UiTM Facility Management System

---

## Prasyarat

- [ ] Semua action items dalam meeting 2026-04-21 telah resolved
- [ ] Testing di dev server (`fmsdev.uitm.edu.my`) berjaya
- [ ] Backup database FMSDEV dibuat
- [ ] Akses ke server production `fms.uitm.edu.my` tersedia

---

## Langkah-langkah

### 1. Salin fail ke server production

```
\\10.0.36.127\webs\ea_newv3\  →  production server
```

Jangan salin fail berikut:
- `.env.php` / `.env` (buat baru di server prod)
- `uploads/temp/*`, `uploads/wr/*` (fail sementara / gambar dev)
- `daily-diary/`, `memories/`, `.claude/`, `.cursor/` (nota pembangunan — jangan letak dalam web root prod)

Pastikan `config/cacert.pem` IKUT disalin (bundle CA untuk sambungan MinIO).

---

### 2. Cipta fail `.env.php` di server production

> **PENTING:** Pelayan ialah **Apache** yang mengabaikan `.htaccess`/`web.config`
> (`AllowOverride None`). Fail `.env` biasa BOLEH DIMUAT TURUN dari browser.
> Konfigurasi MESTI disimpan sebagai `.env.php` — baris pertama `<?php exit; ?>`
> menyebabkan PHP pulangkan kosong jika fail diminta terus.

Salin `.env.example` sebagai `.env.php`, tambah `<?php exit; ?>` sebagai **baris pertama**, dan isi nilai production:

```env
<?php exit; ?>
DB_HOST=<IP server prod>
DB_DATABASE=FMS
DB_USER=afm
DB_PASSWORD=<password prod>

AUTH_MODE=portal_warga
JWT_SECRET=<secret baru untuk prod>
JWT_ALGORITHM=HS512
PORTAL_URL=https://portal.uitm.edu.my/login

GOOGLE_CLIENT_ID=<prod client ID>
GOOGLE_CLIENT_SECRET=<prod client secret>
GOOGLE_REDIRECT_URI=https://fms.uitm.edu.my/ea_newv3/auth/google_callback.php

BASE_URL=https://fms.uitm.edu.my/ea_newv3

MINIO_ENDPOINT=<endpoint MinIO prod>
MINIO_ACCESS_KEY=<access key prod>
MINIO_SECRET_KEY=<secret key prod>
MINIO_REGION=us-east-1
MINIO_BUCKET=<bucket prod>
MINIO_USE_PATH_STYLE=true
# MINIO_CA_BUNDLE=   (pilihan — lalai guna config/cacert.pem)
```

> **PENTING:** `JWT_SECRET` MESTI sama dengan secret yang digunakan Portal Warga untuk
> menandatangani token prod (dapatkan dari pasukan Portal Warga), dan berbeza dari dev.
> Jika kosong, SSO akan ditolak (fail-closed).

---

### 3. Sahkan Google OAuth redirect URI

Pergi ke [Google Cloud Console](https://console.cloud.google.com/) → Credentials:

- Tambah `https://fms.uitm.edu.my/ea_newv3/auth/google_callback.php` sebagai Authorized redirect URI
- Pastikan production `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` digunakan (bukan dev credentials)

---

### 4. Sahkan MinIO

Jalankan di server prod (CLI):

```
php tools/minio_check.php          → mesti "OK: upload + baca semula berjaya"
php tools/migrate_foto_minio.php --dry-run   → semak gambar lokal (jika ada)
php tools/migrate_foto_minio.php             → pindah gambar lokal ke MinIO
```

Jika gagal dengan ralat SSL, kemas kini `config/cacert.pem` dari https://curl.se/ca/cacert.pem.
Jika MinIO gagal semasa runtime, gambar disimpan lokal dalam `uploads/wr/` (nama rawak) dan
`error_log` merekod `simpanFotoDB: MinIO gagal`.

---

### 5. Konfigurasi Apache (admin server) & semakan pendedahan

`.htaccess` diabaikan, jadi perlindungan berikut perlu ditambah dalam konfigurasi Apache
(httpd.conf / vhost) oleh admin server:

```apache
<Directory "C:/path/to/webs/ea_newv3">
    Options -Indexes
    <FilesMatch "^\.(env|git)|\.(bak|md|mdc|pem|log)$">
        Require all denied
    </FilesMatch>
</Directory>
<Directory "C:/path/to/webs/ea_newv3/uploads">
    php_admin_flag engine off
    <FilesMatch "\.(php|phtml|phar)$">
        Require all denied
    </FilesMatch>
</Directory>
<DirectoryMatch "/ea_newv3/(\.git|\.claude|\.cursor|daily-diary|memories|docs|tools)/">
    Require all denied
</DirectoryMatch>
```

Semak dari browser — semua mesti 403/404 atau kosong:

```
https://fms.uitm.edu.my/ea_newv3/.env
https://fms.uitm.edu.my/ea_newv3/.env.php      (200 dengan 0 bait = OK)
https://fms.uitm.edu.my/ea_newv3/uploads/
https://fms.uitm.edu.my/ea_newv3/docs/SOP-dev-to-prod.md
```

| Folder | Permission |
|--------|-----------|
| `uploads/temp/` | User Apache: Write |
| `uploads/wr/` | User Apache: Write (fallback bila MinIO gagal) |
| `.env.php` | Read only |

---

### 6. Test smoke selepas deploy

- [ ] Login SSO Portal Warga berjaya
- [ ] Login Google OAuth berjaya
- [ ] Submit aduan (staf) berjaya — email BOM dihantar
- [ ] Submit aduan (pelajar) berjaya
- [ ] Log masuk Pengguna Luar dengan e-mel + OTP berjaya (e-mel kod sampai)
- [ ] Upload gambar berjaya — `wr.foto1` bermula `ea_newv3/` (MinIO, bukan nama fail lokal)
- [ ] Butiran aduan papar gambar
- [ ] Semak aduan berjaya
- [ ] Akses `.env` dari browser dapat 403/404

---

## Checklist Perbezaan Dev vs Prod

| Setting | Dev | Production |
|---------|-----|-----------|
| `BASE_URL` | `https://fmsdev.uitm.edu.my/ea_newv3` | `https://fms.uitm.edu.my/ea_newv3` |
| `GOOGLE_REDIRECT_URI` | `...fmsdev.uitm.edu.my...` | `...fms.uitm.edu.my...` |
| `JWT_SECRET` | dev secret | **secret baru** |
| `DB_HOST` | `10.0.36.97` | IP server prod |
| `DB_DATABASE` | `FMSDEV` | `FMS` |
| `MINIO_ENDPOINT` / `MINIO_BUCKET` | `minio.uitm.edu.my:8443` / `907-fms` | nilai prod |

---

*Dokumen ini dikemaskini: 2026-09-30 (Apache, .env.php, MinIO, OTP Pengguna Luar)*
