---
name: save-diary
description: Simpan sesi semasa ke diary harian DIBA. Guna selepas setiap perubahan kod atau bila selesai sesi kerja.
disable-model-invocation: true
---
# Save Diary — DIBA

Simpan sesi semasa ke daily diary. Ikut protokol ini:

## Langkah 1: Auto-Archive Bulan Lepas

Baca semua fail dalam `C:/Users/BSM/XDIBAX/daily-diary/current/`. Jika ada fail dengan bulan berbeza dari bulan semasa (April 2026), pindah ke `C:/Users/BSM/XDIBAX/daily-diary/archived/YYYY-MM/`.

## Langkah 2: Tulis Entry

Append entry baru ke `C:/Users/BSM/XDIBAX/daily-diary/current/YYYY-MM-DD.md` (cipta fail jika belum ada).

**Format entry WAJIB:**

```markdown
---

## YYYY-MM-DD (HH:mm) - [Tajuk Sesi]

### Session summary
1-3 ayat ringkasan apa yang dibuat.

### Key decisions
- Keputusan atau pilihan penting yang dibuat
- Masalah yang ditemui dan cara diselesaikan

### Fail yang diubah
- `path/to/file.php` — Apa yang diubah dan kenapa

### Follow-ups
- Item terbuka atau langkah seterusnya

### Tags
#tag1 #tag2 #projek-name
```

**Peraturan:**
- Append sahaja — jangan overwrite entry lama
- Guna Bahasa Melayu dengan istilah teknikal Inggeris
- Log hanya fakta sebenar — jangan reka

## Langkah 3: DIBA Hub Sync

Selepas tulis entry, jalankan di terminal:
```bash
node C:/Users/BSM/XDIBAX/diba-hub/cli/diary-sync.js
```

## Langkah 4: Confirm

Lapor kepada Abam: tarikh fail, tajuk sesi, bilangan keputusan dilog.
