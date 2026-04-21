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
- `.env` (buat baru di server prod)
- `uploads/temp/*` (fail sementara)

---

### 2. Cipta fail `.env` di server production

Salin `.env.example` sebagai `.env` dan isi nilai production:

```env
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
```

> **PENTING:** JWT_SECRET untuk production MESTI berbeza dari dev. Jana secret baru:
> ```php
> php -r "echo bin2hex(random_bytes(48));"
> ```

---

### 3. Sahkan Google OAuth redirect URI

Pergi ke [Google Cloud Console](https://console.cloud.google.com/) → Credentials:

- Tambah `https://fms.uitm.edu.my/ea_newv3/auth/google_callback.php` sebagai Authorized redirect URI
- Pastikan production `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` digunakan (bukan dev credentials)

---

### 4. Verify web.config aktif

Pastikan IIS memuatkan `web.config` di root folder. Test bahawa `.env` tidak boleh diakses:

```
https://fms.uitm.edu.my/ea_newv3/.env  →  patut dapat 404 atau 403
```

---

### 5. Semak permissions folder

| Folder | Permission |
|--------|-----------|
| `uploads/temp/` | IIS App Pool user: Write |
| `uploads/work_request/` | IIS App Pool user: Write |
| `.env` | Read only, bukan public |

---

### 6. Test smoke selepas deploy

- [ ] Login SSO Portal Warga berjaya
- [ ] Login Google OAuth berjaya
- [ ] Submit aduan (staf) berjaya — email BOM dihantar
- [ ] Submit aduan (pelajar) berjaya
- [ ] Upload gambar berjaya
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

---

*Dokumen ini dikemaskini: 2026-04-21*
