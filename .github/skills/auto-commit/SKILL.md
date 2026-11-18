---
name: auto-commit
description: "Commit perubahan kod dengan message berstruktur. Guna bila Abam kata commit, save changes, atau selepas selesai task."
disable-model-invocation: true
---
# Auto-Commit — DIBA

Jalankan commit dengan protokol berikut:

## Langkah 0: Pre-Flight Check

```bash
git status
```

Jika tiada perubahan: lapor "Nothing to commit — working tree is clean" dan berhenti.

## Langkah 1: Analyze Changes

```bash
git diff --staged
git diff
git log --oneline -5
```

Kenal pasti jenis perubahan: feature, bug fix, security, ui, refactor, dll.

## Langkah 2: Draft Commit Message

Format standard:
```
type: ringkasan pendek dalam BM

- Fail: perubahan spesifik
- Fail: perubahan spesifik
```

Contoh type: `fix:`, `feat:`, `security:`, `ui:`, `chore:`

**Rules:**
- Tiada emoji dalam commit message
- Ikut gaya commit terkini dalam repo (`git log`)
- Bahasa: BM untuk description, Inggeris untuk nama fail/fungsi

## Langkah 3: Execute Commit

```bash
git add [fail-spesifik]   # JANGAN git add -A
git commit -m "type: message"
```

**Warn dulu jika ada fail sensitif:** `.env`, credentials, API keys.

## Langkah 4: Confirm

Lapor: short hash, tajuk commit, bilangan fail berubah. Tanya Abam jika nak push.

**JANGAN auto-push** — push hanya bila Abam minta eksplisit.
