---
name: log-decision
description: "Log keputusan penting ke decisions.md. Guna bila keputusan non-obvious dibuat atau bila Abam kata \"log decision\"."
disable-model-invocation: true
---
# Log Decision — DIBA

Simpan keputusan penting secara append-only ke `C:/Users/BSM/XDIBAX/Project-AI-MemoryCore/main/decisions.md`.

## Bila Perlu Log

Log jika keputusan adalah **non-obvious**, contoh:
- Pilih tech/library/approach tertentu atas sebab spesifik
- Tolak cara biasa demi sebab khas
- Ada dua pilihan valid — pilih satu
- Ubah rancangan asal semasa sesi

**Skip** jika: typo fixes, formatting biasa, obvious defaults.

## Format Entry

```markdown
## YYYY-MM-DD — [Tajuk Pendek]
**Context**: Situasi yang mendesak keputusan ini
**Decision**: Apa yang dipilih (dan apa yang ditolak)
**Rationale**: Kenapa — trade-off, kekangan, bukti
```

## Protokol

1. Baca `main/decisions.md` untuk semak jika sudah dilog
2. Tulis entry baru dengan format di atas
3. **APPEND** ke hujung fail — jangan edit entry lama
4. Confirm: "Logged decision: [tajuk]"

**Jika membalikkan keputusan lama:** Append entry baru dengan prefix `Reversed:` — jangan edit original.
