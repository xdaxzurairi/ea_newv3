---
name: auto-worker
description: Delegate dan koordinasi multi-step tasks kepada sub-workers. Guna bila Abam ada goal besar dengan 2+ langkah yang tidak dispesifikkan.
disable-model-invocation: true
---
# Auto-Worker — DIBA

Parse kehendak Abam, decompose, delegate, dan selesaikan tanpa banyak soal.

## Langkah 1: Intent Parsing (silent)

Baca kehendak Abam dari konteks perbualan. Kenal pasti:
- Matlamat utama
- Sub-tasks yang tersirat
- Dependencies antara tasks

## Langkah 2: Task Decomposition

Pecahkan kepada task-task bebas yang boleh dijalankan serentak atau berurutan.

## Langkah 3: Worker Dispatch

Assign setiap task kepada "worker" mengikut domain:
- **DEV** — kod, build, deploy
- **SECURITY** — audit, hardening
- **QA** — testing, verify
- **DOC** — dokumentasi
- **OPS** — server, config

## Langkah 4: Self-Resolution

Resolve soalan workers secara autonomi bila boleh:
- Baca fail yang berkaitan
- Semak pattern sedia ada dalam projek
- Buat keputusan operasi tanpa tanya Abam

**Escalate ke Abam HANYA jika:**
- Keputusan melibatkan risiko tinggi (delete data, breaking change)
- Dua pilihan yang sama-sama valid tanpa cara nak tentukan mana satu
- Kekangan luaran yang memerlukan input Abam

## Langkah 5: Laporan Ringkas

Bila semua selesai:
```
✓ [Task 1] — selesai
✓ [Task 2] — selesai
→ [Item yang perlu Abam decide, jika ada]
```
