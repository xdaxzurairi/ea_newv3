---
name: session-briefing
description: "Brief ringkas dari sesi lepas, reminder terbuka, dan inbox DIBA Hub. Guna di awal sesi atau bila Abam kata \"brief\"."
disable-model-invocation: true
---
# Session Briefing — DIBA

Baca dan paparkan brief sesi dalam maksimum 12 baris. Ikut urutan:

## 1. Recap Sesi Lepas

Baca `C:/Users/BSM/XDIBAX/Project-AI-MemoryCore/main/current-session.md`.
Ekstrak 1-2 baris: topik terakhir + keputusan terakhir.

## 2. Reminder Terbuka

Baca `C:/Users/BSM/XDIBAX/Project-AI-MemoryCore/main/reminders.md`.
Senaraikan hanya item bertanda 🔴 (urgent/overdue). Skip jika tiada.

## 3. DIBA Hub Inbox

Baca `C:/Users/BSM/XDIBAX/diba-hub/data/inbox.json`.
Jika ada items, tanya Abam: "Mana satu nak buat dahulu?" — jangan auto-mula tanpa Abam pilih.

## Output Format

```
=== DIBA Session Brief ===

Sesi lepas: [topik + keputusan ringkas]

Reminder 🔴: [item jika ada]

Inbox: [tasks jika ada]
```

Skip sebarang section yang kosong/tiada item. Jangan announce "no reminders" jika kosong.
