<?php
/**
 * Handler Pemrosesan Komentar Artikel
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('artikel.php'));
    exit;
}

// 1. Verifikasi Honeypot Anti-Spam
if (!empty($_POST['honeypot_username'])) {
    // Spam bot terdeteksi karena mengisi field tersembunyi
    header('Location: ' . base_url('artikel.php'));
    exit;
}

// 2. Verifikasi CSRF Token
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) {
    set_flash('danger', 'Validasi sesi keamanan (CSRF) gagal. Silakan coba kembali.');
    $slug = sanitize($_POST['article_slug'] ?? '');
    header('Location: ' . base_url('artikel-detail.php?slug=' . urlencode($slug) . '#komentar'));
    exit;
}

$db = get_db();
$articleId = (int)($_POST['article_id'] ?? 0);
$articleSlug = sanitize($_POST['article_slug'] ?? '');
$parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

// 3. Pastikan Artikel Ada dan Mengizinkan Komentar
$stmtArticle = $db->prepare("SELECT id, slug, allow_comments FROM articles WHERE id = ? AND status = 'published' LIMIT 1");
$stmtArticle->execute([$articleId]);
$article = $stmtArticle->fetch();

if (!$article || !$article['allow_comments']) {
    set_flash('danger', 'Artikel tidak ditemukan atau kolom komentar sedang dinonaktifkan.');
    header('Location: ' . base_url('artikel.php'));
    exit;
}

// 4. Validasi Parent Comment (Jika Membalas Komentar Lain)
if ($parentId !== null) {
    $stmtParent = $db->prepare("SELECT id FROM comments WHERE id = ? AND article_id = ? LIMIT 1");
    $stmtParent->execute([$parentId, $articleId]);
    if (!$stmtParent->fetch()) {
        $parentId = null; // Reset jika tidak valid
    }
}

// 5. Evaluasi Mode Anonim vs Wajib Nama & Email (USER REQUIREMENT)
$isAnonymous = isset($_POST['is_anonymous']) && $_POST['is_anonymous'] == '1';
$rawComment = trim($_POST['comment'] ?? '');

$name = '';
$email = null;

if ($isAnonymous) {
    // Mode Anonim: Nama otomatis Anonim, email diset NULL
    $name = 'Anonim';
    $email = null;
    $isAnonVal = 1;
} else {
    // Mode Identitas: Wajib menyertakan Nama & Email valid
    $name = sanitize($_POST['name'] ?? '');
    $emailInput = trim($_POST['email'] ?? '');

    if (empty($name) || mb_strlen($name) < 3) {
        set_flash('danger', 'Gagal mengirim komentar: Nama lengkap wajib diisi minimal 3 karakter bila tidak memilih mode Anonim.');
        header('Location: ' . base_url('artikel-detail.php?slug=' . urlencode($article['slug']) . '#komentar'));
        exit;
    }

    if (empty($emailInput) || !filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
        set_flash('danger', 'Gagal mengirim komentar: Format alamat email tidak valid.');
        header('Location: ' . base_url('artikel-detail.php?slug=' . urlencode($article['slug']) . '#komentar'));
        exit;
    }

    $email = $emailInput;
    $isAnonVal = 0;
}

// 6. Validasi Isi Komentar
if (empty($rawComment) || mb_strlen($rawComment) < 5) {
    set_flash('danger', 'Gagal mengirim komentar: Isi komentar terlalu pendek (minimal 5 karakter).');
    header('Location: ' . base_url('artikel-detail.php?slug=' . urlencode($article['slug']) . '#komentar'));
    exit;
}

if (mb_strlen($rawComment) > 2000) {
    set_flash('danger', 'Gagal mengirim komentar: Isi komentar melebihi batas maksimal 2000 karakter.');
    header('Location: ' . base_url('artikel-detail.php?slug=' . urlencode($article['slug']) . '#komentar'));
    exit;
}

// Sanitasi isi komentar
$cleanComment = strip_tags($rawComment);

// 7. Tentukan Status Komentar (Langsung tampil atau butuh moderasi)
$requireModeration = get_setting('require_comment_moderation', '0') === '1';
$status = $requireModeration ? 'pending' : 'approved';
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

try {
    $insertStmt = $db->prepare("
        INSERT INTO comments (article_id, parent_id, name, email, is_anonymous, comment, status, ip_address, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $insertStmt->execute([
        $articleId,
        $parentId,
        $name,
        $email,
        $isAnonVal,
        $cleanComment,
        $status,
        $ipAddress
    ]);

    if ($status === 'approved') {
        set_flash('success', '<strong>Komentar Berhasil Terkirim!</strong> Tanggapan Anda telah dipublikasikan.');
    } else {
        set_flash('info', '<strong>Komentar Terkirim!</strong> Tanggapan Anda akan ditampilkan setelah diverifikasi oleh tim redaksi.');
    }
} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem saat menyimpan komentar: ' . htmlspecialchars($e->getMessage()));
}

header('Location: ' . base_url('artikel-detail.php?slug=' . urlencode($article['slug']) . '#komentar'));
exit;
