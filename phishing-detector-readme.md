
# 🛡️ Website Phishing Detector with Gemini AI

A PHP-based web application to detect phishing websites using various heuristics and Google Gemini AI.  
Modern user interface with Poppins font and Bootstrap.  
Scan results are classified into **Aman ✅**, **Mencurigakan ⚠️**, and **Bahaya ❌**, and stored in a database.

---

## ✨ Fitur

- 🔎 Form input URL dan cek status keamanan
- 📊 Penilaian berdasarkan beberapa indikator:
  - Umur domain
  - Validitas SSL
  - Redirect mencurigakan
  - Ada form login atau tidak
  - Kemiripan domain dengan situs populer
  - Analisis isi HTML oleh AI
- 🤖 Terintegrasi dengan Gemini API (gratis)
- 🗃️ Hasil scan disimpan ke database MySQL
- 🧠 Analisis isi HTML oleh AI untuk indikasi phishing
- 🛡️ Status akhir: Aman, Mencurigakan, Bahaya
- 💅 UI modern dengan Bootstrap 5 dan font Google Poppins

---

## 📊 Metode Penilaian

| Pemeriksaan                    | Kondisi Mencurigakan         | +Skor |
|-------------------------------|------------------------------|-------|
| Umur domain < 3 tahun         | Ya                           | +1    |
| Mirip dengan domain populer   | Ya                           | +1    |
| SSL tidak valid               | Ya                           | +1    |
| Ada form login                | Ya                           | +1    |
| Ada meta redirect             | Ya                           | +1    |
| AI mendeteksi phishing        | Ya                           | +1    |

**Klasifikasi akhir:**
- **0–1**: ✅ Aman
- **2**: ⚠️ Mencurigakan
- **3 atau lebih**: ❌ Bahaya

---

## 🔑 Integrasi Gemini AI

- Buat API key di [https://makersuite.google.com/app/apikey](https://makersuite.google.com/app/apikey)
- Endpoint:
  ```
  POST https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=YOUR_API_KEY
  ```
- Contoh prompt untuk HTML website:
  > Berikut ini adalah isi HTML dari sebuah website. Tolong analisis dan tentukan apakah website ini kemungkinan phishing atau bukan. Jelaskan secara singkat alasannya.

---

## 🌐 Data Trusted Domain (Tranco)

- Gunakan daftar domain terpercaya otomatis dari:
  - https://tranco-list.eu/top-1m.csv
- Ambil daftar ini menggunakan `cURL` lalu simpan lokal (misal `.json`) untuk digunakan saat membandingkan kemiripan domain.
- Gunakan `similar_text()` atau Levenshtein distance untuk deteksi kemiripan nama domain.

---

## 🔍 Deteksi

- **Umur domain** → Cek via WHOIS
- **Kemiripan domain** → Bandingkan dengan domain populer
- **SSL valid** → Cek pakai `stream_context_create()` atau `curl_getinfo()`
- **Form login** → Deteksi tag `<form>` dengan `<input type="password">`
- **Redirect** → Cek header meta refresh atau JavaScript redirect
- **AI Analisis** → Kirim HTML ke Gemini dan nilai dari respons

---

## 🗃️ Struktur Tabel Database (MySQL)

```sql
CREATE TABLE scan_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  url TEXT,
  domain VARCHAR(255),
  domain_age_years FLOAT,
  ssl_valid BOOLEAN,
  similarity TEXT,
  ai_analysis TEXT,
  score INT,
  status ENUM('aman', 'curiga', 'bahaya'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🖥️ UI Design

- Gunakan **Bootstrap 5**
- Font: [Google Fonts - Poppins](https://fonts.google.com/specimen/Poppins)
- Warna status:
  - Hijau = Aman
  - Kuning = Mencurigakan
  - Merah = Bahaya
- Tampilan minimalis, responsif, dan mobile-friendly

---

## 🧠 AI Tips

- Batasi isi HTML yang dikirim ke AI (misal maksimal 3000 karakter)
- Jangan kirim gambar atau JavaScript
- Tambahkan pertanyaan eksplisit pada prompt agar AI menilai dengan lebih tegas

---

## 🧼 Keamanan

- Validasi input URL (hindari localhost/127.0.0.1)
- Batasi request luar (gunakan allowlist user agent atau rate limit)
- Gunakan HTTPS untuk komunikasi API
- Simpan API Key secara aman (misal di `.env`)

---

## 🚀 Bonus Fitur (Opsional)

- Dashboard statistik
- Export hasil scan
- Pendeteksi otomatis via cron job
- Webhook ke Telegram untuk alert phishing
- Scan massal dari file `.csv`

---

## 📜 Lisensi

Open source untuk tujuan edukasi. Jangan digunakan untuk menyalahgunakan sistem pihak lain.
