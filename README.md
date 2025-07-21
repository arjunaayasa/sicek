# Website Phishing Detector

Aplikasi web berbasis PHP untuk mendeteksi website phishing menggunakan berbagai heuristik dan Google Gemini AI.

## Fitur

- Deteksi phishing berdasarkan berbagai parameter:
  - Umur domain (domain baru sering digunakan untuk phishing)
  - Validitas sertifikat SSL
  - Keberadaan form login
  - Redirect mencurigakan
  - Kemiripan dengan domain populer
  - Analisis konten HTML menggunakan Google Gemini AI
- Penilaian skor phishing dan klasifikasi status (aman, curiga, bahaya)
- Penyimpanan hasil pemindaian dalam database MySQL
- Tampilan riwayat pemindaian
- Antarmuka pengguna yang modern dan responsif

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Ekstensi PHP: cURL, MySQLi, JSON
- Akses internet untuk API Gemini dan Tranco List

## Instalasi

1. Clone repositori ini ke direktori web server Anda:
   ```
   git clone https://github.com/username/phishing-detector.git
   ```

2. Buat database MySQL baru untuk aplikasi.

3. Edit file konfigurasi database di `config/database.php`:
   ```php
   define('DB_HOST', 'localhost'); // Host database
   define('DB_USER', 'username');   // Username database
   define('DB_PASS', 'password');   // Password database
   define('DB_NAME', 'phishing_detector'); // Nama database
   ```

4. Edit file konfigurasi aplikasi di `config/config.php` dan masukkan API key Gemini Anda:
   ```php
   define('GEMINI_API_KEY', 'YOUR_API_KEY'); // Ganti dengan API key Anda
   ```

5. Buat direktori `data` di root proyek dan pastikan dapat ditulis oleh web server:
   ```
   mkdir data
   chmod 755 data
   ```

6. Akses aplikasi melalui browser web.

## Penggunaan

1. Masukkan URL yang ingin diperiksa pada halaman utama.
2. Klik tombol "Periksa" untuk memulai pemindaian.
3. Hasil pemindaian akan ditampilkan dengan detail berbagai parameter deteksi.
4. Lihat riwayat pemindaian di halaman "Riwayat".

## Struktur Proyek

```
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── img/
├── config/
│   ├── config.php
│   └── database.php
├── data/
│   └── tranco_list.json
├── includes/
│   ├── db.php
│   └── functions.php
├── index.php
├── history.php
├── detail.php
└── README.md
```

## Cara Kerja

1. **Pemeriksaan Umur Domain**: Domain baru (kurang dari 3 tahun) sering digunakan untuk phishing.
2. **Validasi SSL**: Website phishing sering tidak memiliki sertifikat SSL yang valid.
3. **Deteksi Form Login**: Keberadaan form login pada website yang mencurigakan dapat mengindikasikan phishing.
4. **Deteksi Redirect**: Redirect mencurigakan sering digunakan dalam serangan phishing.
5. **Kemiripan Domain**: Memeriksa kemiripan dengan domain populer menggunakan Tranco List.
6. **Analisis AI**: Menggunakan Google Gemini AI untuk menganalisis konten HTML dan mendeteksi pola phishing.

## Pengembangan Lebih Lanjut

- Implementasi deteksi gambar phishing
- Penambahan API untuk integrasi dengan aplikasi lain
- Peningkatan akurasi deteksi dengan algoritma machine learning
- Penambahan fitur laporan dan notifikasi

## Lisensi

MIT License