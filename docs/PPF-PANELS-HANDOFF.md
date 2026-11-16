# Serahan panel PPF kepada BACKEND

Frontend menambah navigasi Terima Aduan & Inisiatif Jabatan, Semak Maklum Balas dan Graf Maklum Balas dalam fragmen `includes/ppf-dashboard.html`. Ketiga-tiganya menggunakan panel berasaskan hash pada halaman `pages/ppf.php` yang sudah dilindungi guard.

## Status semasa

Borang ialah semakan tempatan sahaja. Butang Semak Maklumat tidak menghantar permintaan simpan. Tiada data peribadi disimpan dalam localStorage, sessionStorage atau fixture aplikasi. Panel maklum balas/graf memaparkan status sambungan belum tersedia; tiada statistik rekaan dipaparkan.

## Kod medium warisan

| Kumpulan | Melalui | Nilai medium / type_wr |
| --- | --- | --- |
| Lisan | Telefon | 3 |
| Bertulis | Surat | 2 |
| Bertulis | Borang Aduan | 5 |
| Bertulis | Memo | 6 |
| Bertulis | E-mel | 7 |
| Bertulis | Minit Mesyuarat | 8 |
| Inisiatif Jabatan | Inisiatif PPF | 4 |

## Kontrak backend yang dicadangkan — perlu disahkan sebelum sambungan

`POST ajax/ppf_create.php`: medan `requestor`, `requestor_id`, `medium`, `phone`, `description` serta kod lokasi/seksyen/elemen yang disahkan server; balas `{ok:true,wr_id}` atau `{ok:false,error,fields?}`. Bootstrap PPF mesti menyediakan token CSRF; backend mesti menyelesaikan identiti pengadu dan kod lokasi, bukan mempercayai nama/lokasi teks daripada borang draf.

`GET ajax/ppf_feedback.php`: penapis kampus/tarikh/page; balas `{ok:true,feedback:[{wr_id,date,rating,comment}],pagination}`; guna guard PPF yang sama.

`GET ajax/ppf_feedback_summary.php`: penapis kampus/tarikh; balas `{ok:true,total,ratings:[{label,count}]}`; guna guard dan no-store yang sama. Frontend akan memaparkan graf serta jadual nilai boleh akses apabila kontrak disahkan.

Sebelum penghantaran sebenar diaktifkan, borang draf perlu disambung kepada pemilih kod lokasi dan kategori yang disokong backend. Jangan terus menggunakan aliran simpan aduan pengguna biasa untuk mencipta aduan bagi pihak pengadu lain.

## Pengesahan frontend

Chrome desktop 1440px dan telefon 390px: 17 semakan regresi dashboard pada setiap saiz. Tambahan 12 semakan panel: buka borang, kod medium bertulis, Telefon untuk lisan, semakan maklumat, teks tanpa HTML injection, tiada permintaan simpan, kembali edit, reset medium, buka maklum balas, buka graf, tiada overflow mobile dan tiada ralat runtime.
