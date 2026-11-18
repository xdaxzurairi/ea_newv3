---
name: forge-skill
description: "Cipta atau upgrade prompt skill baru. Guna bila Abam kata \"buat skill baru\", \"forge this\", atau bila pattern berulang 3+ kali."
disable-model-invocation: true
---
# Forge Skill — DIBA

Cipta skill baru atau upgrade skill sedia ada berdasarkan pattern yang dikesan.

## Detect Opportunity

Forge aktif bila:
1. **Pattern berulang** — perkara yang sama dibuat ad-hoc 3+ kali
2. **Kesilapan boleh dicegah** — kesilapan yang permanent rule boleh elak
3. **Manual trigger** — Abam kata "buat skill", "forge this", "upgrade skill"

## Protokol

### Langkah 1: Analisis

```
TYPE: [New Skill / Level-Up / New Rule]
TARGET: [nama skill]
TRIGGER: [pattern/kesilapan yang dikesan]
EVIDENCE: [2 contoh konkrit]
IMPACT: [apa yang improve]
```

### Langkah 2: Propose

Bentangkan proposal kepada Abam sebelum buat apa-apa:

```
Forge Detected an Opportunity
==============================
Type: [New / Level-Up]
Name: [nama]
Purpose: [apa yang dibuat]
Trigger: [apa yang activate]
Evidence:
  1. [contoh pertama]
  2. [contoh kedua]
Impact: [apa yang improve]

Approve untuk forge?
```

### Langkah 3: Buat (Selepas Approval)

Buat fail baru di `C:/Users/BSM/.copilot/prompts/[skill-name].prompt.md` dengan format:

```markdown
---
mode: agent | ask | edit
description: [satu ayat description]
tools:
  - codebase
---

# [Nama Skill] — DIBA

[Kandungan skill]
```

### Langkah 4: Confirm

```
Forge Complete!
===============
[New/Level-Up]: [nama] 
Location: .copilot/prompts/[nama].prompt.md
Capability: [apa yang ditambah]
```

## Rules

- **Human-in-the-loop** — propose dulu, buat selepas approval
- **Evidence-based** — minimum 2 contoh konkrit sebelum propose
- **Jangan over-engineer** — start simple, evolve organically
