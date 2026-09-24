# Website Profil SMK Bangun Nusa Bangsa & Dashboard Manajemen Artikel

Website profil sekolah modern, elegan, dan profesional untuk **SMK Bangun Nusa Bangsa** yang dibangun menggunakan **PHP Native (PDO)**, **MySQL**, dan **Bootstrap 5.3**. Sistem ini memiliki fitur utama **Portal Berita & Artikel Sekolah** serta **Sistem Komentar Fleksibel (Opsi Anonim atau Wajib Nama & Email)** yang terintegrasi dengan **Web Dashboard Admin**.

---

## 🚀 Fitur Unggulan

### 1. Portal Publik Sekolah (Frontend)
- **Beranda Interaktif**:
  - Banner Hero modern dengan badge akreditasi, statistik serapan kerja, dan call to actions.
  - Sambutan resmi Kepala Sekolah lengkap dengan foto dan kutipan visi misi.
  - Katalog 3 Program Keahlian Unggulan (AKL, TKJ, TKR).
  - Seksi khusus Warta & Artikel Berita terkini.
  - Banner konsultasi dan pendaftaran PPDB via WhatsApp.
- **Profil Sekolah (`profil.php`)**:
  - Sejarah berdirinya sekolah.
  - Visi & Misi resmi.
  - Fasilitas laboratorium 4.0 dan sarana kejuruan modern.
- **Program Keahlian (`jurusan.php`)**:
  - Rincian kompetensi unggulan setiap jurusan.
  - Peluang dan prospek karir lulusan.
  - Tombol konsultasi cepat jurusan via WhatsApp.
- **Portal Artikel & Berita (`artikel.php`) - *Fitur Utama***:
  - Pencarian artikel instan berdasarkan kata kunci judul/isi.
  - Filter kategori artikel dengan counter dinamis.
  - Pengurutan artikel (Terbaru, Terpopuler / Views tertinggi, Terlama).
  - Navigasi pagination halaman yang rapi.
  - Sidebar: Pencarian, daftar kategori, artikel terpopuler, dan info PPDB.
- **Detail Artikel & Sistem Komentar (`artikel-detail.php`)**:
  - Tampilan artikel lengkap dengan format teks, tanggal format Indonesia, jumlah views (otomatis bertambah setiap kunjungan), dan share button (WhatsApp, Facebook, X).
  - Rekomendasi artikel terkait dalam kategori yang sama.
  - **Sistem Komentar Fleksibel**:
    - **Opsi 1 (Anonim)**: Pengunjung dapat mengaktifkan opsi *"Kirim Komentar sebagai Anonim"*. Nama otomatis menjadi "Anonim", email tidak diwajibkan / dirahasiakan, dan komentar diberi badge khusus *"Anonim"*.
    - **Opsi 2 (Identitas Terverifikasi)**: Jika opsi anonim tidak dicentang, pengunjung **wajib** mengisi Nama Lengkap dan Email valid, serta mendapatkan badge *"Terverifikasi"*.
    - Dukungan membalas komentar (Nested Reply / Diskusi bersarang).
    - Dilengkapi honeypot anti-spam dan proteksi token CSRF.
- **Kontak & Lokasi (`kontak.php`)**:
  - Formulir kirim pesan langsung yang tersimpan ke database.
  - Informasi kontak WhatsApp, Telepon, Email, dan Alamat Kampus.

---

### 2. Web Dashboard Management (Admin Panel)
- **Keamanan & Autentikasi**:
  - Login aman dengan hashing `password_verify()` (BCRYPT) dan proteksi CSRF.
  - Proteksi seluruh halaman admin melalui `auth-check.php`.
- **Dashboard Overview (`admin/index.php`)**:
  - Statistik real-time: Total Artikel, Total Views (Pembaca), Total Komentar, Komentar Pending Review, dan Total Kategori.
  - Ringkasan tabel 5 artikel terbaru dan 5 komentar terkini.
- **Kelola Artikel (`admin/articles.php`)**:
  - Full CRUD Artikel: Tambah, Edit, Hapus, dan Pratinjau langsung ke web.
  - Upload gambar thumbnail/sampul dengan validasi MIME type (`jpg, png, webp`), batas ukuran 3MB, dan pembersihan file otomatis saat artikel dihapus.
  - Auto-generate URL Slug dari judul artikel.
  - Pilihan status: *Published* (Terbit) atau *Draft* (Konsep).
  - Toggle izin komentar: Admin dapat mengaktifkan atau menonaktifkan kolom komentar per artikel.
  - Filter pencarian, filter kategori, dan filter status artikel.
- **Kelola Kategori (`admin/categories.php`)**:
  - Tambah, edit, dan hapus kategori artikel.
  - Proteksi penghapusan jika kategori masih berisi artikel aktif.
- **Moderasi Komentar (`admin/comments.php`) - *Fitur Utama***:
  - Daftar seluruh komentar dari seluruh artikel.
  - Filter status: *Semua*, *Pending*, *Approved*, *Spam*.
  - Filter tipe pengirim: *Semua*, *Komentar Anonim*, *Komentar Terverifikasi (Nama & Email)*.
  - Aksi 1-klik: Setujui (Approve), Tangguhkan (Pending), Tandai Spam, Hapus Permanen.
  - **Balas Komentar Resmi sebagai Admin**: Admin dapat langsung membalas komentar pengunjung melalui modal respon cepat.
- **Profil & Pengaturan Website (`admin/profile.php`)**:
  - Update identitas sekolah (Nama, Slogan, Kepala Sekolah, Sambutan, Visi, Misi, Alamat, No Telepon, WhatsApp, Email).
  - **Konfigurasi Kebijakan Komentar**:
    - Toggle *Izinkan Komentar Anonim* (Ya / Tidak).
    - Toggle *Wajibkan Moderasi Komentar* sebelum tayang ke publik (Ya / Tidak).
  - Pengaturan akun administrator dan ubah password login.

---

## 🛠️ Arsitektur & Teknologi

| Komponen | Teknologi |
| :--- | :--- |
| **Backend** | PHP Native 8.x (Clean PDO, Prepared Statements, Secure Session) |
| **Database** | MySQL / MariaDB (Database: `ajatsmk_db`) |
| **Frontend Framework** | Bootstrap 5.3 + Bootstrap Icons 1.11 |
| **Desain & Tipografi** | Google Fonts (*Outfit* & *Inter*), Custom CSS Modern & Responsif |
| **Keamanan** | PDO Prepared Statements (Anti-SQLi), `htmlspecialchars` (Anti-XSS), Token CSRF, Honeypot Anti-Spam Bot, File Upload MIME Validation |

---

## 🔑 Kredensial Login Administrator

- **URL Login Admin**: `http://localhost/ajatsmk/admin/login.php`
- **Username**: `admin`
- **Password**: `admin123`

---

## 💻 Cara Menjalankan di XAMPP

1. Pastikan folder proyek berada di direktori XAMPP:
   ```
   C:\xampp\htdocs\ajatsmk\
   ```
2. Buka **XAMPP Control Panel**, lalu aktifkan modul **Apache** dan **MySQL** (keduanya berstatus *Running*).
3. Database `ajatsmk_db` telah otomatis terpasang dengan data awal. (Jika ingin mengimpor manual, berkas `database.sql` tersedia di root folder).
4. Buka peramban (browser) dan akses:
   - **Portal Publik Website**: [http://localhost/ajatsmk/](http://localhost/ajatsmk/)
   - **Dashboard Admin**: [http://localhost/ajatsmk/admin/](http://localhost/ajatsmk/admin/)
