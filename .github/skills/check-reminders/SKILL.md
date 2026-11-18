---
name: check-reminders
description: "Semak reminder terbuka dari reminders.md. Guna di awal sesi atau bila Abam kata \"check reminders\" atau \"remind me\"."
disable-model-invocation: true
---
# Check Reminders — DIBA

Baca dan urus reminder dari `C:/Users/BSM/XDIBAX/Project-AI-MemoryCore/main/reminders.md`.

## Mod: Semak

Baca section `## Open`. Senaraikan semua reminder terbuka, sort by priority:
- 🔴 High — flag dulu
- 🟡 Medium
- 🔵 Low

Jika tiada urgent: lapor ringkas sahaja.

## Mod: Tambah Reminder

Bila Abam kata "remind me [X]":
1. Parse apa yang nak diingat
2. Format: `- 🟡 **Tajuk**: Penerangan`
3. Jika ada tarikh: `- 🟡 **Tajuk** (by YYYY-MM-DD): Penerangan`
4. **APPEND** ke section `## Open`
5. Confirm: "Added reminder: [tajuk]"

## Mod: Mark Complete

Bila task yang match reminder selesai:
1. Buang dari `## Open`
2. Tambah ke `## Completed`: `- **Tajuk** (completed YYYY-MM-DD): Outcome`

## Peraturan

- **Jangan delete** — pindah ke Completed sahaja
- **Absolute dates** — tukar "esok", "minggu depan" ke YYYY-MM-DD
- **Silent jika kosong** — jangan announce "no reminders"
