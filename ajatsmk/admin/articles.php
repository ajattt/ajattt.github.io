<?php
/**
 * Manajemen Artikel (List & Filter)
 * SMK Bangun Nusa Bangsa
 */
$admin_title = 'Kelola Artikel & Berita';
require_once __DIR__ . '/includes/admin-header.php';

$db = get_db();

// 1. Parameter Filter
$search = trim($_GET['q'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// 2. Query Builder
$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(a.title LIKE ? OR a.excerpt LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
}

if (!empty($categoryFilter)) {
    $where[] = "a.category_id = ?";
    $params[] = (int)$categoryFilter;
}

if (!empty($statusFilter)) {
    $where[] = "a.status = ?";
    $params[] = $statusFilter;
}

$whereClause = implode(' AND ', $where);

// Hitung Total Data
$countStmt = $db->prepare("SELECT COUNT(*) FROM articles a WHERE $whereClause");
$countStmt->execute($params);
$totalArticles = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalArticles / $limit);

// Ambil Data Artikel
$sql = "
    SELECT a.*, c.name AS category_name, u.fullname AS author_name,
           (SELECT COUNT(*) FROM comments WHERE article_id = a.id) AS total_comments,
           (SELECT COUNT(*) FROM comments WHERE article_id = a.id AND status = 'pending') AS pending_comments
    FROM articles a
    JOIN categories c ON a.category_id = c.id
    JOIN users u ON a.author_id = u.id
    WHERE $whereClause
    ORDER BY a.created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Ambil Kategori untuk dropdown filter
$categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Daftar Artikel & Berita</h4>
        <p class="text-muted small mb-0">Total <?= $totalArticles ?> artikel tersimpan di database.</p>
    </div>
    <div>
        <a href="<?= base_url('admin/article-create.php') ?>" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold">
            <i class="bi bi-plus-circle"></i> Tambah Artikel Baru
        </a>
    </div>
</div>

<!-- FILTER CARD -->
<div class="admin-card mb-4">
    <div class="admin-card-body p-3">
        <form action="<?= base_url('admin/articles.php') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Cari judul artikel..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($categoryFilter == $cat['id']) ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="published" <?= ($statusFilter == 'published') ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= ($statusFilter == 'draft') ? 'selected' : '' ?>>Draft</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">Filter</button>
                <?php if (!empty($search) || !empty($categoryFilter) || !empty($statusFilter)): ?>
                    <a href="<?= base_url('admin/articles.php') ?>" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- TABEL ARTIKEL -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 80px;">Sampul</th>
                    <th>Judul & Ringkasan</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Komentar</th>
                    <th>Statistik</th>
                    <th>Tanggal</th>
                    <th class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($articles)): ?>
                    <?php foreach ($articles as $index => $art): ?>
                        <tr>
                            <td><?= $offset + $index + 1 ?></td>
                            <td>
                                <img src="<?= base_url(e($art['thumbnail'])) ?>" alt="Sampul" class="rounded border shadow-sm" style="width: 60px; height: 46px; object-fit: cover;" onerror="this.src='<?= base_url('assets/images/default-article.svg') ?>'">
                            </td>
                            <td>
                                <a href="<?= base_url('admin/article-edit.php?id=' . $art['id']) ?>" class="fw-bold text-dark text-decoration-none d-block mb-1">
                                    <?= e($art['title']) ?>
                                </a>
                                <small class="text-muted d-block text-truncate" style="max-width: 320px;">
                                    <?= e($art['excerpt']) ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= e($art['category_name']) ?></span>
                            </td>
                            <td>
                                <?php if ($art['status'] === 'published'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Published</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= base_url('admin/comments.php?article_id=' . $art['id']) ?>" class="text-decoration-none d-inline-flex align-items-center gap-1">
                                    <span class="badge bg-primary rounded-pill"><?= $art['total_comments'] ?></span>
                                    <?php if ($art['pending_comments'] > 0): ?>
                                        <span class="badge bg-warning text-dark rounded-pill" title="Pending moderasi">+<?= $art['pending_comments'] ?></span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td>
                                <small class="text-muted"><i class="bi bi-eye me-1"></i><?= (int)$art['views'] ?></small>
                            </td>
                            <td>
                                <small class="text-muted"><?= format_date_id($art['created_at'], false) ?></small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('artikel-detail.php?slug=' . e($art['slug'])) ?>" target="_blank" class="btn btn-outline-info" title="Lihat di Portal Web">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <a href="<?= base_url('admin/article-edit.php?id=' . $art['id']) ?>" class="btn btn-outline-primary" title="Edit Artikel">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= base_url('admin/article-delete.php?id=' . $art['id'] . '&csrf=' . csrf_token()) ?>" class="btn btn-outline-danger" title="Hapus Artikel" onclick="return confirm('Apakah Anda yakin ingin menghapus artikel ini? Tindakan ini tidak dapat dibatalkan.');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                            Tidak ada artikel yang sesuai dengan filter atau kata kunci.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
            <small class="text-muted">Halaman <?= $page ?> dari <?= $totalPages ?></small>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php
                    $qp = $_GET;
                    $qp['page'] = $p;
                    ?>
                    <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= base_url('admin/articles.php?' . http_build_query($qp)) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
