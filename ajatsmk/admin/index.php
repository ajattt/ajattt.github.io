<?php
/**
 * Dashboard Overview
 * SMK Bangun Nusa Bangsa
 */
$admin_title = 'Dashboard Overview';
require_once __DIR__ . '/includes/admin-header.php';

$db = get_db();

// 1. Ambil Metrik Statistik
try {
    $totalArticles = (int)$db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
    $publishedArticles = (int)$db->query("SELECT COUNT(*) FROM articles WHERE status = 'published'")->fetchColumn();
    $draftArticles = (int)$db->query("SELECT COUNT(*) FROM articles WHERE status = 'draft'")->fetchColumn();
    $totalViews = (int)$db->query("SELECT COALESCE(SUM(views), 0) FROM articles")->fetchColumn();
    
    $totalComments = (int)$db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
    $approvedComments = (int)$db->query("SELECT COUNT(*) FROM comments WHERE status = 'approved'")->fetchColumn();
    $pendingComments = (int)$db->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn();
    $anonComments = (int)$db->query("SELECT COUNT(*) FROM comments WHERE is_anonymous = 1")->fetchColumn();
    
    $totalCategories = (int)$db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
} catch (Exception $e) {
    $totalArticles = $publishedArticles = $draftArticles = $totalViews = $totalComments = $approvedComments = $pendingComments = $anonComments = $totalCategories = 0;
}

// 2. Artikel Terbaru (5 data)
try {
    $recentArticles = $db->query("
        SELECT a.*, c.name AS category_name,
               (SELECT COUNT(*) FROM comments WHERE article_id = a.id) AS total_comments
        FROM articles a
        JOIN categories c ON a.category_id = c.id
        ORDER BY a.created_at DESC
        LIMIT 5
    ")->fetchAll();
} catch (Exception $e) {
    $recentArticles = [];
}

// 3. Komentar Terkini (5 data)
try {
    $recentComments = $db->query("
        SELECT c.*, a.title AS article_title, a.slug AS article_slug
        FROM comments c
        JOIN articles a ON c.article_id = a.id
        ORDER BY c.created_at DESC
        LIMIT 5
    ")->fetchAll();
} catch (Exception $e) {
    $recentComments = [];
}
?>

<!-- KARTU STATISTIK UTAMA -->
<div class="row g-4 mb-4">
    <!-- Total Artikel -->
    <div class="col-xl-3 col-sm-6">
        <div class="admin-stat-card">
            <div>
                <div class="admin-stat-label">Total Artikel</div>
                <div class="admin-stat-number"><?= $totalArticles ?></div>
                <small class="text-muted">
                    <span class="text-success fw-bold"><?= $publishedArticles ?></span> Published &bull; <span class="text-secondary"><?= $draftArticles ?></span> Draft
                </small>
            </div>
            <div class="admin-stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-newspaper"></i>
            </div>
        </div>
    </div>

    <!-- Total Views / Pembaca -->
    <div class="col-xl-3 col-sm-6">
        <div class="admin-stat-card">
            <div>
                <div class="admin-stat-label">Total Pembaca (Views)</div>
                <div class="admin-stat-number"><?= number_format($totalViews, 0, ',', '.') ?></div>
                <small class="text-muted">Akumulasi seluruh artikel</small>
            </div>
            <div class="admin-stat-icon bg-info-subtle text-info">
                <i class="bi bi-eye"></i>
            </div>
        </div>
    </div>

    <!-- Total Komentar Masuk -->
    <div class="col-xl-3 col-sm-6">
        <div class="admin-stat-card">
            <div>
                <div class="admin-stat-label">Total Komentar</div>
                <div class="admin-stat-number"><?= $totalComments ?></div>
                <small class="text-muted">
                    <span class="text-primary fw-semibold"><?= $anonComments ?></span> Anonim &bull; <span class="text-success fw-semibold"><?= $totalComments - $anonComments ?></span> Terverifikasi
                </small>
            </div>
            <div class="admin-stat-icon bg-success-subtle text-success">
                <i class="bi bi-chat-dots"></i>
            </div>
        </div>
    </div>

    <!-- Komentar Pending Moderasi -->
    <div class="col-xl-3 col-sm-6">
        <div class="admin-stat-card">
            <div>
                <div class="admin-stat-label">Komentar Pending</div>
                <div class="admin-stat-number <?= ($pendingComments > 0) ? 'text-warning' : 'text-muted' ?>">
                    <?= $pendingComments ?>
                </div>
                <small class="text-muted">
                    <?= ($pendingComments > 0) ? 'Perlu tindakan moderasi' : 'Semua telah diverifikasi' ?>
                </small>
            </div>
            <div class="admin-stat-icon bg-warning-subtle text-warning">
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- TABEL ARTIKEL TERBARU -->
    <div class="col-lg-7">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h6 class="admin-card-title d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-text text-primary"></i> Artikel Terbaru
                </h6>
                <a href="<?= base_url('admin/articles.php') ?>" class="btn btn-sm btn-link text-decoration-none">
                    Lihat Semua <i class="bi bi-chevron-right"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>Artikel</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Statistik</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentArticles)): ?>
                            <?php foreach ($recentArticles as $art): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2" style="max-width: 240px;">
                                            <img src="<?= base_url(e($art['thumbnail'])) ?>" alt="Thumb" class="rounded flex-shrink-0" style="width: 44px; height: 36px; object-fit: cover;" onerror="this.src='<?= base_url('assets/images/default-article.svg') ?>'">
                                            <div class="text-truncate">
                                                <a href="<?= base_url('admin/article-edit.php?id=' . $art['id']) ?>" class="fw-bold text-dark text-decoration-none small text-truncate d-block">
                                                    <?= e($art['title']) ?>
                                                </a>
                                                <small class="text-muted" style="font-size: 0.75rem;"><?= format_date_id($art['created_at'], false) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border small"><?= e($art['category_name']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($art['status'] == 'published'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Published</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small text-muted">
                                            <i class="bi bi-eye me-1"></i><?= $art['views'] ?> &bull; 
                                            <i class="bi bi-chat-dots me-1"></i><?= $art['total_comments'] ?>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('admin/article-edit.php?id=' . $art['id']) ?>" class="btn btn-outline-secondary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= base_url('artikel-detail.php?slug=' . e($art['slug'])) ?>" target="_blank" class="btn btn-outline-info" title="Lihat di Web">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada artikel.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TABEL KOMENTAR TERBARU -->
    <div class="col-lg-5">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h6 class="admin-card-title d-flex align-items-center gap-2">
                    <i class="bi bi-chat-left-quote text-success"></i> Komentar Terkini
                </h6>
                <a href="<?= base_url('admin/comments.php') ?>" class="btn btn-sm btn-link text-decoration-none">
                    Kelola Semua <i class="bi bi-chevron-right"></i>
                </a>
            </div>
            <div class="admin-card-body p-0">
                <?php if (!empty($recentComments)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentComments as $com): ?>
                            <div class="list-group-item p-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold small text-dark">
                                            <?= $com['is_anonymous'] ? 'Anonim' : e($com['name']) ?>
                                        </span>
                                        <?php if ($com['is_anonymous']): ?>
                                            <span class="badge bg-secondary-subtle text-secondary small py-0 px-2" style="font-size: 0.7rem;">Anonim</span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success small py-0 px-2" style="font-size: 0.7rem;">Verified</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.72rem;"><?= time_ago($com['created_at']) ?></small>
                                </div>
                                <p class="text-muted small mb-2" style="line-height: 1.4;">
                                    "<?= truncate_text($com['comment'], 90) ?>"
                                </p>
                                <div class="d-flex align-items-center justify-content-between">
                                    <small class="text-truncate text-muted" style="max-width: 200px;">
                                        Pada: <a href="<?= base_url('artikel-detail.php?slug=' . e($com['article_slug'])) ?>" target="_blank" class="text-decoration-none"><?= e($com['article_title']) ?></a>
                                    </small>
                                    <div>
                                        <?php if ($com['status'] == 'approved'): ?>
                                            <span class="badge bg-success small">Approved</span>
                                        <?php elseif ($com['status'] == 'pending'): ?>
                                            <span class="badge bg-warning text-dark small">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger small">Spam</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-chat-dots fs-3 mb-2 d-block"></i>
                        Belum ada komentar masuk.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
