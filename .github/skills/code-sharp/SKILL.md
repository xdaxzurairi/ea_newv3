---
name: code-sharp
description: "Semak dan pastikan kod yang akan ditulis ikut piawaian DIBA — laju, bersih, konsisten, tepat. Jalankan sebelum tulis atau edit kod."
disable-model-invocation: true
---
# Code-Sharp — Piawaian Kod DIBA

Sebelum tulis atau edit sebarang kod, ikut 4 prinsip ini:

## 1. LAJU — Terus ke kod

- Buat dulu, explain kalau perlu
- Jangan tanya benda yang boleh diinfer dari konteks
- Satu round-trip: baca → faham → tulis → hantar

## 2. BERSIH — Minimum, tidak lebih

- Tulis hanya apa yang diminta — tiada feature bonus
- Tiada comment yang tidak perlu
- Tiada dead code, tiada `console.log` debug tertinggal
- Tiada import yang tidak digunakan

## 3. KONSISTEN — Ikut corak fail semasa

Sebelum tulis, scan fail yang akan diedit:

| Perkara | Cara Semak |
|---------|-----------|
| Indentation | Tab atau space? Berapa? |
| Quote style | Single `'` atau double `"`? |
| Naming | camelCase / snake_case / PascalCase? |
| Function style | `function foo()` atau arrow `const foo = () =>`? |
| Error handling | Try-catch, return false, atau die()? |

**Ikut corak sedia ada** — jangan bawa style baru masuk fail lama.

## 4. TEPAT — Ubah apa yang diminta sahaja

- Jangan sentuh baris yang tidak berkaitan
- Kalau nampak masalah lain, **report** — jangan auto-fix
- Edit paling kecil yang boleh, bukan rewrite keseluruhan blok

## Hierarki Keputusan

```
1. Arahan Abam (eksplisit)
2. Corak sedia ada dalam fail
3. Standard bahasa/framework
4. Penilaian sendiri (last resort)
```

## Checklist Sebelum Submit

```
[ ] Kod ikut corak/style fail semasa?
[ ] Tiada baris tidak berkaitan diubah?
[ ] Tiada import/variable/fungsi tak guna?
[ ] Tiada debug artifact tertinggal?
[ ] Logic betul — bukan sekadar nampak betul?
```
