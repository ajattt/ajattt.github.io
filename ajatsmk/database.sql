-- Database Schema & Initial Data for SMK Bangun Nusa Bangsa
-- Database: ajatsmk_db

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `ajatsmk_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ajatsmk_db`;

-- 1. Table Users
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `role` ENUM('admin', 'editor') DEFAULT 'admin',
  `avatar` VARCHAR(255) DEFAULT 'assets/images/default-avatar.svg',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `role`, `avatar`) VALUES
(1, 'admin', '$2y$10$7Z9MTyj7vsm/UO5viKkz6O.Tmj3xQr2hijkddIFBbabYxhd8WtdI6', 'Administrator Utama', 'admin@smk-bangunnusabangsa.sch.id', 'admin', 'assets/images/default-avatar.svg');

-- 2. Table Categories
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `icon` VARCHAR(50) DEFAULT 'bi-bookmark-check',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`) VALUES
(1, 'Berita & Kegiatan', 'berita-kegiatan', 'Seputar kegiatan dan kabar terbaru di SMK Bangun Nusa Bangsa', 'bi-newspaper'),
(2, 'Prestasi Siswa', 'prestasi-siswa', 'Deretan capaian dan kejuaraan siswa tingkat nasional dan internasional', 'bi-trophy'),
(3, 'Info PPDB & Akademik', 'info-ppdb-akademik', 'Informasi pendaftaran peserta didik baru dan kalender pendidikan', 'bi-mortarboard'),
(4, 'Kerjasama Industri', 'kerjasama-industri', 'Program link and match, magang, dan penyerapan kerja lulusan', 'bi-briefcase'),
(5, 'Artikel & Edukasi', 'artikel-edukasi', 'Tulisan edukatif, tips teknologi, dan karya inovasi guru serta siswa', 'bi-lightbulb');

-- 3. Table Articles
DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `author_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `thumbnail` VARCHAR(255) DEFAULT 'assets/images/default-article.svg',
  `excerpt` TEXT NOT NULL,
  `content` LONGTEXT NOT NULL,
  `views` INT DEFAULT 0,
  `status` ENUM('published', 'draft') DEFAULT 'published',
  `allow_comments` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_articles_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_articles_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `articles` (`id`, `category_id`, `author_id`, `title`, `slug`, `thumbnail`, `excerpt`, `content`, `views`, `status`, `allow_comments`, `created_at`) VALUES
(1, 2, 1, 'Siswa SMK Bangun Nusa Bangsa Raih Juara 1 Nasional Teknologi & Robotika Cerdas 2026', 'siswa-smk-bangun-nusa-bangsa-raih-juara-1-nasional-teknologi-robotika-2026', 'assets/images/article-1.jpg', 'Prestasi membanggakan kembali ditorehkan oleh kontingen robotika SMK Bangun Nusa Bangsa di ajang Olimpiade Vokasi Indonesia.', '<p>Prestasi membanggakan kembali ditorehkan oleh kontingen robotika <strong>SMK Bangun Nusa Bangsa</strong>. Dalam ajang bergengsi <em>Olimpiade Vokasi Indonesia & Artificial Intelligence Expo 2026</em> yang diselenggarakan di Jakarta Convention Center, tim robotika berhasil menyabet <strong>Juara 1 Tingkat Nasional</strong> pada kategori Autonomous Logistics Robot.</p><p>Robot cerdas karya siswa ini mampu membaca rute pergudangan secara mandiri, menghindari rintangan dinamis, dan melakukan sortir barang secara presisi menggunakan sensor LiDAR dan machine learning on-the-edge. Keberhasilan ini merupakan buah dari pembinaan intensif di Teaching Factory program keahlian Teknik Komputer & Jaringan bekerjasama dengan mitra industri manufaktur otomasi.</p><p>Kepala SMK Bangun Nusa Bangsa, <strong>Drs. H. Mulyadi, M.Kom</strong>, menyampaikan apresiasi setinggi-tingginya kepada para siswa dan guru pembimbing. \"Kemenangan ini membuktikan bahwa siswa vokasi kami tidak hanya menguasai teori, namun memiliki kemampuan problem-solving berstandar internasional yang siap menjawab kebutuhan industri modern,\" tutur beliau.</p>', 148, 'published', 1, '2026-09-18 10:15:00'),
(2, 4, 1, 'Transformasi Kurikulum AI & Cloud Computing untuk Mempersiapkan Lulusan Siap Kerja Global', 'transformasi-kurikulum-ai-cloud-computing-mempersiapkan-lulusan-global', 'assets/images/article-2.jpg', 'Menjawab pesatnya era industri digital 4.0 dan Society 5.0, SMK Bangun Nusa Bangsa meresmikan kurikulum terintegrasi Cloud & Kecerdasan Buatan.', '<p>Era disrupsi teknologi menuntut dunia pendidikan vokasi bergerak cepat dan adaptif. <strong>SMK Bangun Nusa Bangsa</strong> resmi meluncurkan integrasi kurikulum <em>Cloud Computing, Artificial Intelligence, dan Cyber Security</em> yang diselaraskan langsung dengan standar sertifikasi internasional.</p><p>Melalui kemitraan strategis dengan penyedia platform teknologi global, para siswa kini memiliki akses ke laboratorium cloud virtual, modul pembelajaran machine learning, serta simulasi arsitektur jaringan skala enterprise. Program ini memberikan kesempatan bagi siswa untuk meraih sertifikasi profesional resmi sebelum menuntaskan masa studi di jenjang SMK.</p><p>Wakil Kepala Sekolah Bidang Kurikulum menjelaskan bahwa kurikulum ini menggunakan pendekatan Project-Based Learning (PBL). Setiap siswa ditantang membangun solusi nyata untuk kebutuhan bisnis lokal, seperti sistem pencatatan keuangan digital untuk UMKM hingga sistem monitoring IoT untuk perbengkelan modern.</p>', 95, 'published', 1, '2026-09-19 14:30:00'),
(3, 1, 1, 'Peresmian Studio Animasi & Laboratorium Jaringan Berstandar Industri di SMK Bangun Nusa Bangsa', 'peresmian-studio-animasi-laboratorium-jaringan-standar-industri', 'assets/images/kampus-bnb.jpg', 'Fasilitas baru laboratorium berstandar 4.0 resmi beroperasi untuk mendukung pembelajaran praktek siswa kejuruan.', '<p>Guna memberikan pengalaman belajar yang merefleksikan kondisi kerja sebenarnya, <strong>SMK Bangun Nusa Bangsa</strong> meresmikan fasilitas baru berupa <em>Studio Animasi 3D & Laboratorium Arsitektur Jaringan Komputer</em>.</p><p>Fasilitas ini dilengkapi dengan workstation komputasi grafis berperforma tinggi, perangkat switching enterprise grade, simulator Electronic Fuel Injection (EFI), serta jaringan fiber optic internal kampus berkecepatan 10 Gbps. Laboratorium ini tidak hanya digunakan untuk praktikum harian, tetapi juga difungsikan sebagai pusat sertifikasi kompetensi (LSP-P1) resmi.</p><p>Dengan hadirnya fasilitas modern ini, diharapkan lulusan SMK Bangun Nusa Bangsa memiliki daya saing tinggi saat langsung memasuki dunia kerja maupun berwirausaha mandiri di sektor kreatif dan teknologi.</p>', 76, 'published', 1, '2026-09-20 09:00:00'),
(4, 3, 1, 'Penerimaan Peserta Didik Baru (PPDB) 2026/2027 Gelombang 1 Resmi Dibuka', 'penerimaan-peserta-didik-baru-ppdb-2026-2027-gelombang-1-resmi-dibuka', 'assets/images/mpls-bnb.jpg', 'SMK Bangun Nusa Bangsa membuka kesempatan pendaftaran siswa baru untuk 3 kompetensi keahlian unggulan dengan beasiswa prestasi.', '<p>Kabar gembira bagi para lulusan SMP/MTs sederajat! <strong>SMK Bangun Nusa Bangsa</strong> kini resmi membuka pendaftaran <em>Penerimaan Peserta Didik Baru (PPDB) Tahun Ajaran 2026/2027 Gelombang 1</em>.</p><p>Kami membuka pendaftaran untuk 3 program keahlian unggulan:<br>1. <strong>Akuntansi & Keuangan Lembaga (AKL)</strong> - Fokus pada Fintech & Digital Accounting.<br>2. <strong>Teknik Komputer & Jaringan (TKJ)</strong> - Fokus pada Cloud, Cyber Security, & Networking.<br>3. <strong>Teknik Kendaraan Ringan (TKR)</strong> - Fokus pada Otomotif Modern & Electric Vehicle (EV).</p><p>Tersedia beasiswa bebas biaya pendidikan bagi siswa berprestasi di bidang akademik, olahraga, maupun sains teknologi. Pendaftaran dapat dilakukan secara langsung di kampus kami atau melalui portal online resmi.</p>', 210, 'published', 1, '2026-09-21 08:00:00');

-- 4. Table Comments
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `article_id` INT NOT NULL,
  `parent_id` INT NULL DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NULL DEFAULT NULL,
  `is_anonymous` TINYINT(1) DEFAULT 0,
  `comment` TEXT NOT NULL,
  `status` ENUM('approved', 'pending', 'spam') DEFAULT 'approved',
  `ip_address` VARCHAR(45) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_comments_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `comments` (`id`, `article_id`, `parent_id`, `name`, `email`, `is_anonymous`, `comment`, `status`, `created_at`) VALUES
(1, 1, NULL, 'Budi Santoso', 'budi.santoso@gmail.com', 0, 'Selamat dan sukses untuk adik-adik SMK Bangun Nusa Bangsa! Prestasi yang sangat membanggakan di bidang robotika nasional.', 'approved', '2026-09-18 11:20:00'),
(2, 1, NULL, 'Anonim', NULL, 1, 'Keren banget prestasinya! Fasilitas lab di SMK BNB memang mantap dan gurunya sangat suportif.', 'approved', '2026-09-18 12:45:00'),
(3, 1, 1, 'Admin SMK BNB', 'admin@smk-bangunnusabangsa.sch.id', 0, 'Terima kasih atas doa dan dukungannya Bapak Budi! Kami terus berkomitmen mencetak generasi unggul.', 'approved', '2026-09-18 13:00:00'),
(4, 4, NULL, 'Rina Wardhani', 'rina.wardhani@yahoo.com', 0, 'Untuk persyaratan beasiswa gelombang 1 apa saja ya persyaratannya? Terima kasih infonya.', 'approved', '2026-09-21 09:15:00'),
(5, 4, NULL, 'Anonim', NULL, 1, 'Bisa datang langsung ke kampus untuk lihat fasilitas lab dan tanya-tanya jurusan gak ya?', 'approved', '2026-09-21 10:30:00');

-- 5. Table Majors
DROP TABLE IF EXISTS `majors`;
CREATE TABLE `majors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(160) NOT NULL UNIQUE,
  `tagline` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `competencies` TEXT NULL,
  `careers` TEXT NULL,
  `image` VARCHAR(255) DEFAULT 'assets/images/default-article.svg',
  `badge_color` VARCHAR(30) DEFAULT 'primary',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `majors` (`id`, `code`, `name`, `slug`, `tagline`, `description`, `competencies`, `careers`, `image`, `badge_color`) VALUES
(1, 'AKL', 'Akuntansi & Keuangan Lembaga', 'akuntansi-dan-keuangan-lembaga', 'Menguasai Akuntansi Digital, Sistem Keuangan Modern, Perpajakan & Fintech 4.0', 'Program Keahlian Akuntansi dan Keuangan Lembaga membekali siswa dengan penguasaan pembukuan keuangan modern berbasis software (Accurate, MYOB, Spreadsheet), administrasi perpajakan digital (e-Tax), transaksi perbankan, serta analisis data keuangan perusahaan.', 'Komputer Akuntansi (Accurate & MYOB), Administrasi Perpajakan Digital & e-Faktur, Pengelolaan Kas & Rekonsiliasi Bank, Spreadsheet Finansial Lanjut, Akuntansi Syariah & Fintech.', 'Digital Accountant, Staf Keuangan / Finance, Petugas Pajak Perusahaan, Customer Service & Teller Bank, Analis Anggaran Junior, Konsultan Keuangan Mandiri.', 'assets/images/jurusan-akuntansi.jpg', 'success'),
(2, 'TKJ', 'Teknik Komputer & Jaringan', 'teknik-komputer-dan-jaringan', 'Menguasai Arsitektur Jaringan, Infrastruktur Cloud, & Keamanan Siber', 'Jurusan TKJ berfokus pada perancangan infrastruktur jaringan skala enterprise, routing switching MikroTik/Cisco bersertifikasi internasional, implementasi Server Linux/Windows, instalasi Fiber Optic, serta pertahanan Cyber Security.', 'MikroTik Certified Network Associate (MTCNA), Cisco CCNA Routing & Switching, Cloud Server Architecture (AWS/GCP), Ethical Hacking & Cyber Defense, Fiber Optic Splicing.', 'Network Administrator, Cyber Security Analyst, Cloud Engineer, System Administrator, IT Support & Infrastructure Specialist.', 'assets/images/jurusan-tkj.jpg', 'primary'),
(3, 'TKR', 'Teknik Kendaraan Ringan', 'teknik-kendaraan-ringan', 'Inovasi Otomotif Modern, Engine Management System & Kendaraan Listrik (EV)', 'Jurusan TKR membekali siswa dengan keahlian pemeliharaan dan perbaikan mesin otomotif modern, Electronic Fuel Injection (EFI), scanner diagnostik komputer, sistem transmisi otomatis, kelistrikan body, chassis, serta teknologi kendaraan listrik (Electric Vehicle).', 'Engine Management System & Scanner Diagnostik, Electronic Fuel Injection (EFI), Transmisi Otomatis & CVT, Sistem Rem ABS & Airbag, Teknologi Baterai & Perawatan Kendaraan Listrik (EV).', 'Automotive Service Technician, Teknisi Spesialis Kendaraan Listrik (EV), Service Advisor Diler Resmi, Quality Control Otomotif, Wirausaha Bengkel Modern.', 'assets/images/jurusan-tkr.jpg', 'warning');

-- 6. Table Settings
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('school_name', 'SMK Bangun Nusa Bangsa'),
('school_slogan', 'Mencetak Generasi Unggul, Berkarakter, & Berdaya Saing Global'),
('school_vision', 'Menjadi Sekolah Menengah Kejuruan Pusat Keunggulan (Center of Excellence) yang menghasilkan lulusan berakhlak mulia, kompeten berstandar internasional, serta siap berwirausaha di era revolusi industri 4.0 dan Society 5.0.'),
('school_mission', '1. Menyelenggarakan pendidikan kejuruan berbasis kompetensi industri global.\n2. Mengembangkan karakter disiplin, religius, inovatif, dan berjiwa wirausaha.\n3. Memperluas jejaring kemitraan strategis dengan DUDI (Dunia Usaha & Dunia Industri) skala nasional & internasional.\n4. Menerapkan teknologi digital termutakhir dalam seluruh proses pembelajaran dan tata kelola sekolah.'),
('school_history', 'SMK Bangun Nusa Bangsa didirikan dengan tekad kuat untuk menjembatani kesenjangan antara dunia pendidikan dengan kebutuhan industri nyata. Berawal dari komitmen para pendidik dan praktisi industri, kini SMK Bangun Nusa Bangsa telah berkembang menjadi salah satu SMK Pusat Keunggulan favorit dengan ribuan alumni yang sukses berkarir di perusahaan nasional, multinasional, maupun wirausahawan mandiri.'),
('school_address', 'Jl. Pendidikan Karakter Bangsa No. 88, Kawasan Pendidikan Terpadu, Jakarta'),
('school_phone', '(021) 8899-7722'),
('school_email', 'info@smk-bangunnusabangsa.sch.id'),
('school_whatsapp', '081234567890'),
('principal_name', 'Drs. H. Mulyadi, M.Kom.'),
('principal_welcome', 'Selamat datang di portal resmi SMK Bangun Nusa Bangsa. Kami berkomitmen memberikan kurikulum terbaik yang terintegrasi langsung dengan kebutuhan dunia industri, didukung fasilitas berstandar 4.0 dan tenaga pengajar profesional bersertifikat. Mari bersama-sama wujudkan masa depan gemilang bersama SMK Bangun Nusa Bangsa!'),
('principal_photo', 'assets/images/kepsek.jpg'),
('facebook_url', 'https://facebook.com'),
('instagram_url', 'https://instagram.com'),
('youtube_url', 'https://youtube.com'),
('linkedin_url', 'https://linkedin.com'),
('require_comment_moderation', '0'),
('allow_anonymous_comments', '1');

-- 7. Table Messages (Contact Us Form)
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `messages` (`name`, `email`, `subject`, `message`, `is_read`) VALUES
('Ahmad Fauzi', 'ahmad.fauzi@gmail.com', 'Tanya Kerjasama Magang Siswa', 'Selamat pagi pihak SMK Bangun Nusa Bangsa, kami dari PT Solusi Teknologi ingin menawarkan peluang magang industri untuk siswa jurusan TKJ. Mohon informasi kontak pihak Humas / Hubin.', 0);

SET FOREIGN_KEY_CHECKS = 1;
