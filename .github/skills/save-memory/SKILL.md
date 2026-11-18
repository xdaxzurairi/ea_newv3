---
name: save-memory
description: "Simpan maklumat penting ke memory files. Guna bila Abam kata \"ingat\", \"save\", \"simpan\", atau bila ada maklumat worth preserving."
disable-model-invocation: true
---
# Save Memory — DIBA

Simpan maklumat penting ke fail memory yang berkaitan.

## Fail Memory

| Fail | Kandungan |
|------|-----------|
| `main/current-session.md` | Recap sesi semasa (topik, keputusan, fail terakhir) |
| `main/decisions.md` | Keputusan penting (guna /log-decision) |
| `main/reminders.md` | Reminder terbuka (guna /check-reminders) |

## Protokol

### Langkah 1: Kenal pasti apa nak simpan

Kategori memory:
- **Preference baru**: cara Abam suka buat sesuatu
- **Keputusan projek**: pilihan yang dibuat untuk projek
- **Context user**: maklumat tentang Abam yang membantu kolaborasi
- **Rujukan external**: di mana jumpa sesuatu (Linear, Grafana, dll)

### Langkah 2: Update fail yang betul

- **Append** ke fail yang ada — jangan overwrite
- Update `current-session.md` dengan recap ringkas:
  - Topik terakhir
  - Keputusan terakhir
  - Fail terakhir diubah

### Langkah 3: Confirm

Lapor apa yang disimpan dan ke fail mana.

## Peraturan

- Simpan hanya yang genuinely penting — bukan setiap detail perbualan
- Jangan simpan: detail task sementara, maklumat yang boleh derived dari kod/git
- Preserve content sedia ada — append atau update, jangan overwrite
