<?php
/**
 * Hapus Artikel
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/includes/auth-check.php';

$id = (int)($_GET['id'] ?? 0);
$token = $_GET['csrf'] ?? '';

if ($id <= 0 || !verify_csrf($token)) {
    set_flash('danger', 'Permintaan penghapusan tidak valid atau sesi telah berakhir.');
    header('Location: ' . base_url('admin/articles.php'));
    exit;
}

$db = get_db();

try {
    // Ambil data thumbnail untuk dihapus fisiknya
    $stmt = $db->prepare("SELECT thumbnail FROM articles WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $article = $stmt->fetch();

    if ($article) {
        // Hapus berkas fisik thumbnail jika bukan default
        delete_file($article['thumbnail']);

        // Hapus dari database (komentar terkait akan terhapus cascading)
        $delStmt = $db->prepare("DELETE FROM articles WHERE id = ?");
        $delStmt->execute([$id]);

        set_flash('success', 'Artikel dan seluruh komentar terkait berhasil dihapus permanen.');
    } else {
        set_flash('warning', 'Artikel tidak ditemukan.');
    }
} catch (Exception $e) {
    set_flash('danger', 'Gagal menghapus artikel: ' . htmlspecialchars($e->getMessage()));
}

header('Location: ' . base_url('admin/articles.php'));
exit;
