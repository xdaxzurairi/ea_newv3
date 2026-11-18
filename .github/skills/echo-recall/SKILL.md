---
name: echo-recall
description: "Cari dan recall maklumat dari diary lepas. Guna bila Abam tanya \"Do you remember...\", \"recall...\", \"last time we...\", atau \"ingat semula\"."
disable-model-invocation: true
---
# Echo Memory Recall — DIBA

Cari dalam diary dan jawab dalam bahasa semula jadi.

## Protokol

### 1. Terima Query

Kenal pasti kata kunci dari soalan Abam. Contoh: nama projek, nama fungsi, tarikh, atau topik.

### 2. Cari Dalam Diary

Cari kata kunci merentasi:
- `C:/Users/BSM/XDIBAX/daily-diary/current/*.md`
- `C:/Users/BSM/XDIBAX/daily-diary/archived/**/*.md`
- `C:/Users/BSM/XDIBAX/Project-AI-MemoryCore/main/decisions.md`

### 3. Bina Naratif

Jawab dalam bahasa semula jadi dengan struktur:
- **Bila**: tarikh sesi
- **Apa**: ringkasan yang berkaitan
- **Keputusan**: keputusan yang dibuat (jika ada)

### Peraturan Wajib

- **JANGAN fabricate** — kalau tak jumpa, kata tak jumpa
- Paparkan sebagai perbualan biasa, bukan dump mentah
- Kalau pelbagai hasil: senaraikan ringkas, tanya Abam mana yang dimaksudkan
- Kalau tiada hasil: "Saya tak jumpa catatan tentang itu. Ingat projek atau tarikh mana?"
