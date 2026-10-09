# Sistem Informasi Pembangunan & Manajemen Gereja

Aplikasi berbasis web sederhana yang dirancang untuk mengelola pendataan donasi, laporan keuangan, publikasi informasi/berita, serta galeri kegiatan pembangunan gereja secara transparan dan terstruktur.

---

## 🚀 Fitur Utama

### 🌐 **Halaman Publik (User)**
* **Beranda (`index.php`)**: Menampilkan ringkasan informasi, progres pembangunan, dan donasi terbaru.
* **Form Donasi (`donasi.php`)**: Memudahkan jemaat/donatur untuk melakukan donasi dan mengunggah bukti pembayaran.
* **Total Donasi (`total_donasi.php`)**: Menampilkan statistik serta daftar donasi yang telah terverifikasi secara transparan.
* **Progres Pembangunan (`pembangunan.php`)**: Informasi rinci mengenai tahapan dan perkembangan pembangunan gereja.
* **Informasi & Berita (`informasi.php`)**: Pengumuman atau artikel terkait kegiatan gereja.
* **Galeri Foto (`galeri.php`)**: Dokumentasi foto kegiatan dan fisik pembangunan.

### 🔐 **Panel Admin (`/admin/`)**
* **Dashboard Admin (`dashboard.php`)**: Ringkasan statistik data donasi, konten galeri, dan informasi.
* **Kelola Donasi (`admin/donasi.php`)**: Memverifikasi dan mengonfirmasi bukti transfer donasi yang masuk dari pengguna.
* **Manajemen Konten (CRUD)**:
  * Kelola Informasi & Pengumuman (`admin/informasi.php`)
  * Kelola Galeri Kegiatan (`admin/galeri.php`)
  * Kelola Data Pembangunan (`admin/pembangunan.php`)
* **Laporan Keuangan & Export (`admin/laporan_keuangan.php`)**:
  * Fitur Export Laporan ke format **Excel (`export_excel.php`)**
  * Fitur Export Laporan ke format **PDF (`export_pdf.php`)**

---

## 🛠️ Teknologi yang Digunakan

* **Language**: PHP (Native)
* **Database**: MySQL / MariaDB
* **Frontend Framework**: Bootstrap / CSS Custom & HTML5
* **Library Additional**: 
  * FPDF / Dompdf (Export PDF)
  * PhpSpreadsheet / Excel Exporter (Export Excel)

---

## 📁 Struktur Direktori Utama

```text
gereja_pembangunan/
├── admin/                     # Panel Administrator
│   ├── dashboard.php          # Halaman utama admin
│   ├── donasi.php             # Verifikasi & kelola donasi
│   ├── export_excel.php       # Script export data ke Excel
│   ├── export_pdf.php         # Script export data ke PDF
│   ├── galeri.php             # Kelola foto galeri
│   ├── informasi.php          # Kelola berita/informasi
│   ├── laporan_keuangan.php   # Rekapitulasi laporan keuangan
│   ├── login.php              # Halaman login admin
│   ├── logout.php             # Logout session
│   └── pembangunan.php        # Kelola status pembangunan
├── config/
│   └── database.php           # Konfigurasi koneksi database MySQL
├── core/
│   ├── auth.php               # Middleware / helper autentikasi admin
│   └── functions.php          # Fungsi-fungsi helper aplikasi
├── uploads/                   # Folder penampung berkas (bukti transfer, gambar)
├── donasi.php                 # Halaman form donasi user
├── galeri.php                 # Halaman galeri publik
├── index.php                  # Halaman utama aplikasi
├── informasi.php              # Halaman informasi publik
├── pembangunan.php            # Halaman detail pembangunan
├── total_donasi.php           # Halaman statistik donasi publik
└── README.md                  # Dokumentasi proyek
```

---

## 💻 Panduan Instalasi & Penggunaan

### 1. **Prasyarat**
* Web Server (XAMPP, WAMP, Laragon, atau LAMP stack).
* PHP versi **7.4** atau **8.x**.
* Database Server MySQL / MariaDB.

### 2. **Langkah-Langkah Instalasi**

1. **Clone / Download Repository**
   Unduh atau salin folder proyek ini ke dalam direktori server lokal Anda (contoh: `htdocs` pada XAMPP):
   ```bash
   htdocs/
   └── gereja_pembangunan/
   ```

2. **Pengaturan Database**
   * Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
   * Buat database baru, misalnya dengan nama: `db_gereja` (atau sesuaikan dengan file `.sql` yang Anda miliki).
   * Import file database `.sql` proyek Anda ke dalam database tersebut.

3. **Konfigurasi Koneksi**
   * Buka file `config/database.php`.
   * Sesuaikan kredensial koneksi database dengan server lokal Anda:
     ```php
     $host     = "localhost";
     $username = "root";      // Sesuaikan username MySQL Anda
     $password = "";          // Sesuaikan password MySQL Anda
     $database = "db_gereja"; // Sesuaikan nama database Anda
     ```

4. **Menjalankan Aplikasi**
   * Buka browser lalu akses:
     * **Halaman Publik**: `http://localhost/gereja_pembangunan/`
     * **Halaman Admin**: `http://localhost/gereja_pembangunan/admin/login.php`

---

## 📝 Catatan Penting
* Pastikan folder `uploads/` memiliki hak akses tulis (*write permission*) agar proses pengunggahan bukti transfer dan gambar berjalan dengan lancar.

---

## 📄 Lisensi
Proyek ini dibuat untuk keperluan manajemen informasi dan transparansi dana pembangunan gereja.
