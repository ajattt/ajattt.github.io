<?php
/**
 * Header Template - Public Website
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/functions.php';

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$school_name = get_setting('school_name', 'SMK Bangun Nusa Bangsa');
$school_slogan = get_setting('school_slogan', 'Mencetak Generasi Unggul & Berkarakter');
$school_phone = get_setting('school_phone', '(021) 8899-7722');
$school_email = get_setting('school_email', 'info@smk-bangunnusabangsa.sch.id');
$school_whatsapp = get_setting('school_whatsapp', '081234567890');
$school_address = get_setting('school_address', 'Jl. Pendidikan Karakter Bangsa No. 88, Jakarta');

// Default page title
if (!isset($page_title)) {
    $page_title = $school_name . ' - ' . $school_slogan;
} else {
    $page_title = $page_title . ' | ' . $school_name;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?></title>
    <meta name="description" content="<?= e($page_description ?? 'Website Resmi SMK Bangun Nusa Bangsa - Pusat Keunggulan Pendidikan Vokasi Berstandar Industri') ?>">
    <meta name="author" content="SMK Bangun Nusa Bangsa">

    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

    <!-- TOPBAR -->
    <div class="topbar d-none d-lg-block">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-4">
                        <span><i class="bi bi-geo-alt-fill text-warning me-1"></i> <?= e($school_address) ?></span>
                        <span><i class="bi bi-telephone-fill text-warning me-1"></i> <?= e($school_phone) ?></span>
                        <span><i class="bi bi-envelope-fill text-warning me-1"></i> <?= e($school_email) ?></span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-inline-flex align-items-center gap-3">
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $school_whatsapp) ?>" target="_blank" class="text-success fw-semibold"><i class="bi bi-whatsapp"></i> Chat WhatsApp</a>
                        <span class="text-white-50">|</span>
                        <?php if (is_admin()): ?>
                            <a href="<?= base_url('admin/index.php') ?>" class="text-info fw-semibold"><i class="bi bi-speedometer2"></i> Dashboard Admin</a>
                        <?php else: ?>
                            <a href="<?= base_url('admin/login.php') ?>" class="text-white-50"><i class="bi bi-lock-fill"></i> Login Admin</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand navbar-brand-wrapper" href="<?= base_url('index.php') ?>">
                <img src="<?= base_url('assets/images/logo.jpg') ?>" alt="Logo SMK" class="navbar-brand-logo" onerror="this.src='<?= base_url('assets/images/logo.svg') ?>'">
                <div>
                    <div class="brand-title"><?= e($school_name) ?></div>
                    <div class="brand-subtitle">School of Excellence & Technology</div>
                </div>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-1 mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'index' || $current_page == '') ? 'active' : '' ?>" href="<?= base_url('index.php') ?>">
                            <i class="bi bi-house-door me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'profil') ? 'active' : '' ?>" href="<?= base_url('profil.php') ?>">
                            <i class="bi bi-building me-1"></i> Profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'jurusan') ? 'active' : '' ?>" href="<?= base_url('jurusan.php') ?>">
                            <i class="bi bi-mortarboard me-1"></i> Jurusan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= in_array($current_page, ['artikel', 'artikel-detail']) ? 'active' : '' ?>" href="<?= base_url('artikel.php') ?>">
                            <i class="bi bi-newspaper me-1"></i> Artikel & Berita
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'kontak') ? 'active' : '' ?>" href="<?= base_url('kontak.php') ?>">
                            <i class="bi bi-envelope me-1"></i> Kontak
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a href="<?= base_url('artikel.php') ?>" class="btn btn-primary-custom d-flex align-items-center gap-2">
                            <i class="bi bi-search"></i> Baca Artikel
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
