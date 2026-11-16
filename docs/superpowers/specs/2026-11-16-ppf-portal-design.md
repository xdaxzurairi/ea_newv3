# Reka Bentuk Portal PPF eAduanV2

Tarikh: 2026-11-16

Status: Diluluskan untuk perancangan

Pendekatan: Portal PPF modular dalam eAduanV2

## Latar belakang

eAduan lama mempunyai portal operasi khas PPF di `eAduan/ppf/` dengan paparan aduan keseluruhan, aduan baharu, aduan siap, maklum balas dan graf. eAduanV2 pada masa ini hanya mengelaskan staf PPF melalui `dp_id` tertentu dan belum mempunyai portal operasi tersebut.

Portal baharu akan membawa semula keupayaan PPF secara berperingkat, menggunakan reka bentuk eAduanV2 dan kawalan akses sisi server. Fasa pertama ialah paparan baca-sahaja supaya fungsi boleh diuji tanpa mengubah aliran tindakan aduan.

## Matlamat

- Menyediakan dashboard operasi moden untuk staf PPF.
- Mengekalkan skop semua kampus dan penapis utama daripada eAduan lama.
- Mengehadkan portal kepada staf PPF yang berada dalam intranet.
- Memastikan halaman dan data PPF tidak tersedia secara offline.
- Menyediakan asas modular untuk fungsi tindakan, maklum balas dan laporan pada fasa seterusnya.

## Di luar skop fasa pertama

- Mengubah status atau butiran aduan.
- Menambah catatan tindakan atau maklum balas.
- Menghantar e-mel atau notifikasi baharu.
- Mengubah SQL, skema, sesi, autentikasi atau konfigurasi server melalui kerja frontend.
- Membawa kod PHP lama secara terus ke eAduanV2.

## Pengguna dan akses

Pengguna sasaran ialah staf PPF sahaja. Untuk fasa pertama, staf PPF dikenal pasti melalui `dp_id` bernilai `A0140` atau `N00020`.

Akses diluluskan hanya apabila ketiga-tiga syarat dipenuhi pada server:

1. Pengguna mempunyai sesi eAduanV2 yang sah.
2. Permintaan datang daripada rangkaian intranet yang diluluskan.
3. `dp_id` pengguna ialah `A0140` atau `N00020`.

Semakan yang sama mesti digunakan pada halaman dan setiap endpoint data. Menyembunyikan menu atau menggunakan JavaScript bukan kawalan keselamatan. Permintaan yang gagal mesti menerima HTTP 403 tanpa kandungan aduan.

## Seni bina frontend

- `pages/ppf.php`: rangka dashboard dan keadaan asas paparan.
- `assets/css/ppf.css`: token, susun atur responsif dan komponen khusus portal.
- `assets/js/ppf-dashboard.js`: muatan data, penapis, carian, pagination serta keadaan loading, kosong dan gagal.
- `includes/header.php`: pautan `Portal PPF` yang hanya dirender apabila server memberikan kelayakan PPF.
- `sw.js`: mengecualikan halaman dan endpoint PPF daripada cache serta respons offline.

Reka bentuk tidak menambah dependency frontend baharu. Ia menggunakan corak HTML, CSS dan JavaScript sedia ada dalam eAduanV2.

## Struktur maklumat

Navigasi Portal PPF mempunyai empat destinasi fasa pertama:

1. Ringkasan
2. Semua Aduan
3. Aduan Baharu
4. Aduan Siap

Menu PPF ditambah kepada navigasi eAduanV2. Ia tidak menggantikan paparan pengguna biasa supaya staf PPF masih boleh membuat dan menyemak aduan peribadi.

## Susun atur yang dipilih

Pilihan A, `Dashboard operasi`, telah diluluskan.

Urutan visual:

1. Kepala halaman `Pusat Operasi PPF` dan lencana `Intranet`.
2. Kad ringkasan untuk Semua Aduan, Baharu dan Siap.
3. Senarai `Perlu Perhatian`, disusun mengikut aduan Baharu paling lama.
4. Penapis dan carian.
5. Jadual keputusan serta pagination.

Pada desktop, portal menggunakan navigasi sisi dan jadual. Pada skrin kecil, navigasi menjadi bar ringkas, penapis dibuka sebagai panel, dan setiap rekod dipaparkan sebagai kad. Semua sasaran sentuh minimum 44 piksel.

## Penapis

Fasa pertama mengekalkan penapis kerja portal lama:

- Negeri
- Kampus
- Zon
- Seksyen
- Elemen atau kumpulan masalah
- Julat tarikh
- Nombor aduan
- ID staf
- ID pelajar
- Status melalui destinasi navigasi

Nilai penapis disimpan dalam parameter URL. Muat semula pelayar dan pautan yang dikongsi akan menghasilkan keadaan penapis yang sama selepas semakan akses berjaya.

## Paparan rekod

Senarai aduan hanya memerlukan medan berikut:

- `wr_id`
- `date_requested`
- `requestor`
- `requestor_id`
- `state_name`
- `site_name`
- `location_label`
- `section_label`
- `problem_group_label`
- `status_code`
- `status_label`
- `age_days`

Klik nombor aduan membuka butiran baca-sahaja yang dilindungi oleh kawalan akses PPF yang sama.

## Kontrak backend

Endpoint dicadangkan:

`GET /ajax/ppf_dashboard.php`

Parameter pilihan:

- `view`: `all`, `requested`, atau `completed`
- `state_id`, `site_id`, `zone_id`, `section`, `prob_group`
- `date_from`, `date_to`, `wr_id`, `staff_id`, `student_id`
- `page` dan `per_page`

Respons berjaya:

```json
{
  "ok": true,
  "summary": {
    "all": 1284,
    "requested": 48,
    "completed": 1036
  },
  "attention": [],
  "complaints": [],
  "pagination": {
    "page": 1,
    "per_page": 10,
    "total": 1284,
    "pages": 129
  },
  "filters": {},
  "updated_at": "2026-11-16T10:42:00+08:00"
}
```

Respons gagal menggunakan status HTTP yang sesuai dan bentuk minimum:

```json
{
  "ok": false,
  "error": "access_denied"
}
```

## Aliran data

1. Pelayar meminta halaman Portal PPF.
2. Server mengesahkan sesi, intranet dan `dp_id`.
3. Halaman memuatkan rangka dashboard tanpa data sensitif yang disisipkan dalam HTML awam.
4. JavaScript meminta endpoint menggunakan parameter URL semasa.
5. Endpoint mengulangi semakan akses sebelum membaca data.
6. Frontend merender ringkasan, perhatian dan senarai.
7. Perubahan penapis mengemas kini URL dan meminta halaman data yang sepadan.

Permintaan bertindih akan dibatalkan supaya respons lama tidak menggantikan keputusan penapis terkini.

## Keadaan paparan dan ralat

- Loading: skeleton ringkas dengan teks status yang boleh dibaca pembaca skrin.
- Kosong: mesej `Tiada aduan sepadan` dan tindakan reset penapis.
- Rangkaian gagal: mesej ringkas serta butang `Cuba semula`.
- Sesi tamat: aliran kembali ke halaman log masuk.
- Bukan staf PPF: halaman 403 tanpa data.
- Di luar intranet: halaman `Akses rangkaian dalaman diperlukan`.
- Input penapis tidak sah: mesej inline dan tiada permintaan dihantar sehingga diperbetulkan.

## Aksesibiliti dan interaksi

- Hierarki tajuk semantik dan landmark navigasi utama.
- Label nyata untuk semua input penapis.
- Fokus papan kekunci yang jelas.
- Status tidak disampaikan melalui warna sahaja.
- Kawasan sentuh sekurang-kurangnya 44 piksel.
- Jadual mempunyai tajuk kolum dan kapsyen yang sesuai.
- Paparan kad mudah alih mengekalkan label bagi setiap nilai.
- Animasi mematuhi `prefers-reduced-motion`.

## Polisi cache

Service worker tidak boleh menyimpan:

- `/pages/ppf.php`
- `/ajax/ppf_dashboard.php`
- Sebarang respons yang mengandungi data PPF

Jika peranti offline, portal memaparkan bahawa sambungan intranet diperlukan dan tidak menunjukkan salinan data terdahulu.

## Kriteria penerimaan

- Staf PPF dalam intranet boleh melihat menu dan membuka portal.
- Staf bukan PPF tidak melihat menu dan menerima 403 apabila menggunakan URL langsung.
- Staf PPF di luar intranet menerima 403.
- Endpoint langsung dilindungi dengan syarat yang sama.
- Ringkasan, senarai perhatian dan senarai aduan menunjukkan data yang konsisten.
- Semua penapis, carian, pagination dan pautan butiran berfungsi.
- Paparan sesuai pada desktop dan telefon.
- Portal boleh digunakan dengan papan kekunci dan pembaca skrin asas.
- Tiada respons PPF boleh diperoleh daripada cache ketika offline.
- Tiada perubahan data dilakukan dalam fasa pertama.

## Pelan berperingkat

### Fasa 1 — Baca sahaja

Dashboard operasi, ringkasan, senarai semua/baharu/siap, penapis, carian, pagination dan butiran baca-sahaja.

### Fasa 2 — Operasi

Kemas kini tindakan dan status, catatan kerja serta jejak audit. Fasa ini memerlukan reka bentuk dan semakan backend berasingan.

### Fasa 3 — Maklum balas dan analitik

Semak maklum balas, laporan dan graf moden yang menggantikan komponen Flash portal lama.

## Kontrak serahan kepada ejen backend

Lindungi `pages/ppf.php` dan `GET /ajax/ppf_dashboard.php` menggunakan sesi sah, IP intranet dan `dp_id` dalam `{A0140,N00020}`; gagal mesti HTTP 403.

Endpoint menerima parameter penapis yang disenaraikan dan memulangkan `{ok, summary, attention, complaints, pagination, filters, updated_at}` tanpa HTML.

Jangan cache respons; tambah `Cache-Control: no-store, private` dan sahkan semula akses pada setiap permintaan.
