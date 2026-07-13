# BiblioCloud
Sistem Manajemen Perpustakaan Digital Berbasis Amazon S3

Aplikasi web untuk mengelola koleksi buku digital (e-book): upload, katalog, cari,
lihat detail, download, dan hapus buku. File fisik (PDF/EPUB + cover) disimpan sebagai
object di Amazon S3; metadata (judul, penulis, genre) disimpan di database SQLite lokal.

## Struktur Folder

```
BiblioCloud/
├── public/                  # Root web server (arahkan XAMPP/Laragon ke sini)
│   ├── index.php            # Halaman katalog utama
│   ├── upload.php           # Form + handler upload buku
│   ├── detail.php           # Halaman detail satu buku
│   ├── delete.php           # Handler hapus buku
│   ├── download.php         # Handler download e-book dari S3
│   ├── cover.php            # Streaming thumbnail cover dari S3
│   └── assets/
│       ├── css/style.css
│       └── img/placeholder-cover.png   # (tambahkan gambar placeholder sendiri)
├── src/                      # Logic PHP
│   ├── config.php           # Konfigurasi (path python, DB)
│   └── functions.php        # Fungsi panggil s3_helper.py + query metadata
├── python/                   # Semua yang berhubungan dengan boto3/S3
│   ├── s3_helper.py          # Wrapper boto3: list, upload, delete, detail
│   ├── download_helper.py    # Download object dari S3 ke file lokal
│   ├── start_local_s3.py      # Menjalankan moto server (simulator S3)
│   └── requirements.txt
├── database/
│   └── schema.sql            # Skema tabel metadata buku
└── README.md
```

## Cara Menjalankan (Local Mode / Simulator S3)

1. **Install dependency Python**
   ```
   cd python
   pip install -r requirements.txt
   ```

2. **Jalankan simulator S3 (moto)** — biarkan terminal ini tetap terbuka
   ```
   python3 start_local_s3.py
   ```

3. **Buat database SQLite** (sekali saja, dari folder database/)
   ```
   sqlite3 bibliocloud.sqlite < schema.sql
   ```

4. **Jalankan web server** — arahkan XAMPP/Laragon ke folder `public/`
   Pastikan ekstensi PHP `pdo_sqlite` aktif di php.ini.

5. Buka `http://localhost/` (atau sesuai port XAMPP/Laragon kamu) dan mulai upload buku.

## Beralih ke AWS S3 Asli

Ubah dua baris di `python/s3_helper.py`:
```python
LOCAL_MODE = False   # dari True
AWS_REGION = "ap-southeast-1"   # sesuaikan region
```
Lalu pastikan kredensial AWS sudah dikonfigurasi di `~/.aws/credentials`
(lewat `aws configure` atau environment variable). Tidak ada perubahan lain
yang diperlukan karena seluruh kode boto3 sudah pakai fungsi generik yang sama.

## Catatan untuk Dosen

Aplikasi ini di-demo menggunakan simulator lokal (moto) untuk mereplikasi
layanan Amazon S3, dikarenakan kendala verifikasi kartu untuk pembuatan akun
AWS Free Tier. Seluruh logika S3 (list, upload, delete, detail object) ditulis
memakai boto3 dengan API call yang identik dengan AWS asli, dan sudah disiapkan
toggle `LOCAL_MODE` untuk berpindah ke akun AWS sungguhan tanpa mengubah struktur kode.
