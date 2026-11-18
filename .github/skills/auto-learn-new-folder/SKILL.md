---
name: auto-learn-new-folder
description: Kaji projek atau folder baru sebelum buat sebarang perubahan. Guna bila folder baru didetect atau Abam minta edit projek yang belum dikaji.
disable-model-invocation: true
---
# Auto-Learn New Folder — DIBA

Kaji dahulu, ubah kemudian. 5 fasa wajib sebelum sentuh mana-mana fail.

## Fasa 1: Pemetaan Struktur

Lihat struktur folder 2-3 tahap. Kenal pasti:
- Jenis projek (PWA, Laravel, plain PHP, Node, dll)
- Bahasa utama dan framework

## Fasa 2: Fail Konfigurasi & Manifes

Baca mana yang ada:
- `package.json`, `composer.json`, `requirements.txt`
- `.env.example`, `docker-compose.yml`, `README.md`
- `web.config`, `.htaccess`

## Fasa 3: Arkitektur & Konvensyen

- Cari fail konfigurasi framework
- Fahami pola: layout, penamaan, import style, API layer

## Fasa 4: Baca Contoh Kod

Baca 3-5 fail kod utama. Perhatikan:
- Indentation dan quote style
- Naming convention
- Error handling pattern
- Cara buat query DB

## Fasa 5: Ringkasan & Laporan

Sebelum buat perubahan, lapor kepada Abam:

```
=== Projek Dikaji: [nama] ===

Jenis: [PHP legacy / PWA / Node / dll]
Stack: [bahasa, framework, DB]
Struktur: [ringkasan folder utama]
Konvensyen: [indentation, quote, naming]
Penting: [sesuatu yang perlu diketahui]

Sedia untuk ubah.
```

## Peraturan Mutlak

- **JANGAN buat sebarang perubahan** sebelum Fasa 1-4 selesai
- **JANGAN assume** framework atau konvensyen — semak fail sebenar
- Ikut gaya projek yang ada, bukan "best practice" luar
