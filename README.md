# 🌐 Panduan Portofolio & Profil Resmi - Drajad Wicaksono (Ajat)

Website portofolio interaktif dan profil resmi **Drajad Wicaksono** (Siswa Kelas XII Jurusan Teknik Komputer dan Jaringan - SMK Bangun Nusa Bangsa). Dibuat dengan desain modern berestetika tinggi (WebGL Lightning, Particle Text, Interactive Spiderman Spotlight, Floating Navigation Dock, Real-time Comment System, & Spotify Daily Rotation).

---

## 📂 Struktur File Proyek

```
📁 ajatprofil/
├── 📄 index.html          # Struktur utama website (HTML5 + Tailwind CSS)
├── 🎨 style.css           # Kustomisasi CSS, animasi partikel, navbar, & responsive
├── ⚡ script.js           # Logika interaktif, WebGL shader, scrollspy, modal, & komentar
├── 🖼️ ajatnormal.jpeg     # Foto profil utama (Normal)
├── 🖼️ ajatspiderman.jpeg  # Foto profil efek spotlight (Spider-Man mode)
└── 📖 README.md           # Petunjuk dan dokumentasi cara mengupdate data
```

---

## 🚀 Cara Menjalankan Website

### 1. Menggunakan XAMPP (Localhost)
1. Pastikan folder `ajatprofil` berada di dalam folder `C:/xampp/htdocs/ajatprofil`.
2. Buka aplikasi **XAMPP Control Panel**, lalu klik **Start** pada modul **Apache**.
3. Buka browser (Chrome / Edge / Firefox) dan ketik URL:
   ```
   http://localhost/ajatprofil
   ```

### 2. Tanpa XAMPP (Langsung Buka File / VS Code Live Server)
- Klik 2x pada file `index.html` untuk langsung membukanya di browser.
- Atau jika menggunakan VS Code, pasang ekstensi **Live Server**, klik kanan pada `index.html` dan pilih **Open with Live Server**.

---

## 📝 Panduan Lengkap Cara Mengubah Data

### 1. Mengubah Data Pribadi & Identitas Diri
Buka file [`index.html`](file:///c:/xampp/htdocs/ajatprofil/index.html):
- **Nama & Gelar/Jurusan**: Cari kata kunci `Drajad Wicaksono` atau `TKJ XII` untuk mengubah nama, jurusan, dan asal sekolah.
- **Teks Typewriter yang Berjalan Otomatis**:
  Buka file [`script.js`](file:///c:/xampp/htdocs/ajatprofil/script.js) pada bagian `roles`:
  ```javascript
  const roles = [
    "Siswa Kelas XII TKJ",
    "Network Engineer Enthusiast",
    "Teknisi Jaringan & Komputer",
    "Pecinta Olahraga Badminton & Lari",
    "Junior Tech Explorer"
  ];
  ```
  Anda bisa menambah, mengurangi, atau mengganti kalimat di dalam tanda kutip tersebut.

---

### 2. Mengganti Foto Profil
Untuk mengganti foto profil Anda, cukup siapkan 2 foto dengan rasio vertikal (portrait / 4:5):
1. **Foto Normal**: Simpan foto Anda dengan nama `ajatnormal.jpeg` dan letakkan di folder utama menggantikan file lama.
2. **Foto Efek Spiderman / Kostum**: Simpan foto efek dengan nama `ajatspiderman.jpeg`.
> **Tips:** Pastikan ukuran file foto tidak terlalu besar (di bawah 1 MB) agar website memuat sangat cepat.

---

### 3. Mengubah Akun Media Sosial & Kontak
Saat ini link media sosial resmi yang terpasang adalah:
- **Instagram**: `https://www.instagram.com/true.ajat` (Username: `@true.ajat`)
- **GitHub**: `https://ajattt.github.io` (Domain: `ajattt.github.io`)
- **TikTok**: `https://www.tiktok.com/@true.ajatt` (Username: `@true.ajatt`)

Jika ingin mengubah URL atau username sosial media, buka [`index.html`](file:///c:/xampp/htdocs/ajatprofil/index.html) dan cari:
1. **Hero Section (Bagian Atas)**: Cari bagian `<!-- Social Links & Action Buttons -->` sekitar baris 230.
2. **Contact Section (Bagian Formulir)**: Cari bagian `<!-- Social Links Cards -->` sekitar baris 1090.
3. **Footer (Bagian Bawah)**: Cari bagian `<!-- Social Links in Footer -->` sekitar baris 1240.

Ganti tautan `href="..."` dan teks username dengan akun baru Anda.

---

### 4. Mengubah / Menambah Proyek Portofolio
Buka file [`index.html`](file:///c:/xampp/htdocs/ajatprofil/index.html) pada bagian `<!-- 3. PORTOFOLIO SECTION -->`:
- **Tab 1: Projects (Praktikum & Solusi Jaringan)**:
  Cari `<!-- TAB CONTENT 0: PROJECTS -->`.
  Setiap kartu proyek memiliki fungsi modal:
  ```html
  <button onclick="openProjectModal('Judul Proyek', 'Deskripsi lengkap proyek Anda.', ['Tag1', 'Tag2', 'Tag3'])">
    Lihat Detail
  </button>
  ```
- **Tab 2: Certificates (Sertifikasi)**:
  Cari `<!-- TAB CONTENT 1: CERTIFICATES -->`.
  Anda bisa mengklik kartu sertifikat untuk membuka detailnya melalui fungsi `openCertModal()`.
- **Tab 3: Awards & Hobi (Badminton & Lari)**:
  Cari `<!-- TAB CONTENT 2: AWARDS & HOBBIES -->`.
- **Tab 4: Tech Stack**:
  Cari `<!-- TAB CONTENT 3: TECH STACK -->` untuk menambah ikon teknologi seperti MikroTik, Cisco, Linux, HTML, dll.

---

### 5. Mengganti Playlist Musik Spotify
Di bagian Hero terdapat widget pemutar musik Spotify (Daily Rotation).
Untuk mengganti lagu atau artis:
1. Buka Spotify di web/aplikasi, klik **Share** pada lagu/album/artis, lalu pilih **Embed track/artist**.
2. Salin link embed-nya (misal `https://open.spotify.com/embed/track/...` atau `.../embed/artist/...`).
3. Buka [`index.html`](file:///c:/xampp/htdocs/ajatprofil/index.html), cari tag `<iframe>` Spotify sekitar baris 315 dan ganti atribut `src="..."` dengan link Spotify baru Anda.

---

### 6. Sistem Komentar (Live Comments)
- Komentar pengunjung disimpan secara otomatis di **LocalStorage** browser masing-masing.
- Ada komentar khusus yang tersemat (*Pinned Message*) dari Admin (Drajad Wicaksono).
- Jika Anda ingin mengubah pesan Pinned Admin, buka [`index.html`](file:///c:/xampp/htdocs/ajatprofil/index.html) dan [`script.js`](file:///c:/xampp/htdocs/ajatprofil/script.js) pada fungsi `renderComments()`.

---

## 📱 Fitur Responsif & Navigasi

Website ini sudah dioptimalkan 100% responsif untuk semua perangkat:
1. **Desktop & Laptop**:
   - Floating Navbar atas dengan indikator aktif & Maskot Anime Interaktif yang mengikuti posisi menu.
   - Efek kursor spotlight Spiderman reticle yang presisi.
2. **Smartphone & Tablet**:
   - **Floating Mobile Bottom Navigation Dock**: Menu navigasi yang mudah dijangkau dengan jempol (Home, About, Porto, Kontak, & tombol cepat CV).
   - **Floating Back to Top Button**: Tombol melayang di kanan bawah untuk kembali ke bagian atas dengan sekali sentuh saat halaman di-scroll.
   - Touch gesture spotlight pada foto profil.
   - Grid fleksibel pada tab portofolio dan formulir.

---

## 🌐 Cara Publish ke GitHub Pages (`ajattt.github.io`)

Agar website Anda bisa diakses oleh siapa saja di internet secara gratis:
1. Buat repository baru di GitHub dengan nama: `ajattt.github.io` (atau nama repository pilihan Anda).
2. Upload semua file dalam folder ini (`index.html`, `style.css`, `script.js`, `ajatnormal.jpeg`, `ajatspiderman.jpeg`, `README.md`) ke repository tersebut.
3. Buka **Settings** di repository GitHub Anda > Pilih menu **Pages** di sebelah kiri.
4. Di bagian **Branch**, pilih `main` atau `master` dan folder `/ (root)`, lalu klik **Save**.
5. Tunggu 1-2 menit, website Anda akan live dan bisa diakses di:
   ```
   https://ajattt.github.io
   ```

---

© 2026 **Drajad Wicaksono** • Siswa XII TKJ SMK Bangun Nusa Bangsa. All Rights Reserved.
