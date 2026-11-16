# Serahan panel PPF kepada BACKEND

Frontend menambah navigasi Terima Aduan & Inisiatif Jabatan, Semak Maklum Balas dan Graf Maklum Balas dalam fragmen `includes/ppf-dashboard.html`. Ketiga-tiganya menggunakan panel berasaskan hash pada halaman `pages/ppf.php` yang sudah dilindungi guard.

## Status semasa

Borang Terima Aduan bermula dengan No. Pekerja, pilihan Lisan/Bertulis dan Melalui. Semua aduan melalui aliran ini ialah inisiatif; pilihan Inisiatif telah dibuang daripada medium. Tiada borang ringkas nama/lokasi/keterangan atau simpanan pelayar. Butang Teruskan membuka URL setempat daripada atribut root `data-entry-url`; tanpa atribut tersebut UI memaparkan sambungan borang penuh belum tersedia.

Panel maklum balas dan graf sudah menggunakan endpoint JSON yang disahkan di bawah, dengan penapis, ringkasan, pagination, graf dan jadual nilai.

## Kod medium warisan

| Kumpulan | Melalui | Nilai medium / type_wr |
| --- | --- | --- |
| Lisan | Telefon | 3 |
| Bertulis | Surat | 2 |
| Bertulis | Borang Aduan | 5 |
| Bertulis | Memo | 6 |
| Bertulis | E-mel | 7 |
| Bertulis | Minit Mesyuarat | 8 |

## Kontrak backend yang dicadangkan — perlu disahkan sebelum sambungan

BACKEND menyediakan laluan borang penuh PPF melalui `data-entry-url` pada root halaman. Frontend menghantar GET `txt_emnumber` dan `medium` ke laluan tersebut selepas validasi input.

Laluan borang penuh mesti mengesahkan pekerja, guard intranet + PPF/DFMS dan memaparkan borang eAduanV2 menggunakan pekerja terpilih, tanpa menukar identiti/sesi operator. Backend menentukan kategori inisiatif serta mengesahkan medium sebelum simpan.

`GET ajax/ppf_feedback.php`: `feedback=all|ada|memuaskan|tidak_memuaskan|tiada`, penapis negeri/kampus/zon/seksyen/elemen/tarikh, wr_id, page. Respons `{ok,summary,complaints,pagination,filters,updated_at}`. Rekod mengandungi `feedback_code,feedback_label,feedback_notes,feedback_date` serta medan dashboard.

`GET ajax/ppf_feedback_chart.php`: `group=site|month` dan penapis negeri/kampus/zon/seksyen/elemen/tarikh. Respons `{ok,group,totals,series,filters,updated_at}`. Jumlah dan setiap item siri menggunakan `memuaskan,tidak_memuaskan,tiada,total`.

Semua endpoint mengulangi guard dan no-store. Foto serta butiran menggunakan laluan PPF sedia ada. Endpoint biasa yang menganggap operator sebagai pengadu tidak sesuai untuk aliran pekerja terpilih ini.

## Pengesahan frontend

Chrome desktop 1440px dan telefon 390px: 18 semakan kontrak maklum balas/graf pada setiap saiz, termasuk 403/500, retry, no-store dan teks tanpa HTML injection. Menu unik dan tiada sasaran bertindih disahkan. Sembilan semakan aliran No. Pekerja lulus: medan wajib, Lisan/Bertulis sahaja, kod medium, tiada borang ringkas lama, laluan belum dikonfigurasi gagal dengan mesej jelas, reset serta tiada ralat runtime. Ujian data sebenar memerlukan akaun intranet dan sambungan backend.
