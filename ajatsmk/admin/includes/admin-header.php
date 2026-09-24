<?php
/**
 * Admin Header & Sidebar Template
 */
require_once __DIR__ . '/auth-check.php';

$user = current_user();
$school_name = get_setting('school_name', 'SMK Bangun Nusa Bangsa');
$current_admin_page = basename($_SERVER['PHP_SELF'], '.php');

// Hitung komentar pending untuk badge notifikasi
$db = get_db();
try {
    $pendingCommentsCount = (int)$db->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn();
} catch (Exception $e) {
    $pendingCommentsCount = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($admin_title ?? 'Dashboard') ?> | Admin SMK Bangun Nusa Bangsa</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body class="admin-body">

    <!-- ADMIN SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-brand">
            <img src="<?= base_url('assets/images/logo.jpg') ?>" alt="Logo" class="admin-sidebar-logo" onerror="this.src='<?= base_url('assets/images/logo.svg') ?>'">
            <div>
                <div class="admin-sidebar-title">Admin Panel</div>
                <div class="admin-sidebar-subtitle">SMK Nusa Bangsa</div>
            </div>
        </div>

        <div class="admin-sidebar-menu">
            <div class="admin-menu-header">Menu Utama</div>
            <a href="<?= base_url('admin/index.php') ?>" class="admin-nav-item <?= ($current_admin_page == 'index') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            <div class="admin-menu-header">Manajemen Konten</div>
            <a href="<?= base_url('admin/articles.php') ?>" class="admin-nav-item <?= in_array($current_admin_page, ['articles', 'article-create', 'article-edit']) ? 'active' : '' ?>">
                <i class="bi bi-newspaper"></i>
                <span>Kelola Artikel</span>
            </a>
            <a href="<?= base_url('admin/categories.php') ?>" class="admin-nav-item <?= ($current_admin_page == 'categories') ? 'active' : '' ?>">
                <i class="bi bi-tags"></i>
                <span>Kategori Artikel</span>
            </a>
            <a href="<?= base_url('admin/comments.php') ?>" class="admin-nav-item <?= ($current_admin_page == 'comments') ? 'active' : '' ?> d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-chat-left-text"></i>
                    <span>Moderasi Komentar</span>
                </div>
                <?php if ($pendingCommentsCount > 0): ?>
                    <span class="badge bg-warning text-dark rounded-pill small"><?= $pendingCommentsCount ?></span>
                <?php endif; ?>
            </a>

            <div class="admin-menu-header">Konfigurasi</div>
            <a href="<?= base_url('admin/profile.php') ?>" class="admin-nav-item <?= ($current_admin_page == 'profile') ? 'active' : '' ?>">
                <i class="bi bi-gear"></i>
                <span>Profil & Pengaturan</span>
            </a>
            <a href="<?= base_url('index.php') ?>" target="_blank" class="admin-nav-item text-info">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>Lihat Website</span>
            </a>
        </div>

        <div class="admin-sidebar-footer">
            <a href="<?= base_url('admin/logout.php') ?>" class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2" onclick="return confirm('Apakah Anda yakin ingin keluar dari sesi admin?');">
                <i class="bi bi-box-arrow-left"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="admin-main">
        <!-- TOPBAR -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" id="sidebarToggleBtn" class="btn btn-light d-lg-none border-0 shadow-none">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h5 class="mb-0 fw-bold text-dark d-none d-sm-block">
                    <?= e($admin_title ?? 'Dashboard') ?>
                </h5>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="<?= base_url('admin/article-create.php') ?>" class="btn btn-primary btn-sm rounded-pill d-none d-sm-inline-flex align-items-center gap-1 px-3">
                    <i class="bi bi-plus-lg"></i> Tulis Artikel Baru
                </a>

                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2 border rounded-pill py-1 px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= base_url(e($user['avatar'] ?? 'assets/images/default-avatar.svg')) ?>" alt="Avatar" class="admin-user-avatar" onerror="this.src='<?= base_url('assets/images/default-avatar.svg') ?>'">
                        <span class="small fw-semibold text-dark d-none d-md-inline"><?= e($user['fullname']) ?></span>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2">
                        <li><h6 class="dropdown-header"><?= e($user['email']) ?></h6></li>
                        <li><a class="dropdown-item" href="<?= base_url('admin/profile.php') ?>"><i class="bi bi-person me-2"></i> Edit Akun</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= base_url('admin/logout.php') ?>"><i class="bi bi-box-arrow-left me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- CONTENT BODY -->
        <main class="admin-content">
            <!-- FLASH MESSAGES -->
            <?php require_once __DIR__ . '/../../includes/alerts.php'; ?>
