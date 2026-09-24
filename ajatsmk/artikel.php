<?php
/**
 * Katalog Artikel & Berita (Fitur Utama)
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Artikel, Berita & Pengumuman Sekolah';
$page_description = 'Kumpulan berita kegiatan, prestasi siswa, tips edukasi vokasi, dan informasi resmi SMK Bangun Nusa Bangsa.';

$db = get_db();

// 1. Ambil Parameter Filter
$search = trim($_GET['q'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$sort = trim($_GET['sort'] ?? 'terbaru');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 6;
$offset = ($page - 1) * $limit;

// 2. Siapkan Query Filter
$where = ["a.status = 'published'"];
$params = [];

if (!empty($search)) {
    $where[] = "(a.title LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($categorySlug)) {
    $where[] = "c.slug = ?";
    $params[] = $categorySlug;
}

$whereClause = implode(' AND ', $where);

// Sorting
$orderBy = match ($sort) {
    'populer' => 'a.views DESC, a.created_at DESC',
    'terlama' => 'a.created_at ASC',
    default   => 'a.created_at DESC'
};

// 3. Hitung Total Data untuk Pagination
$countSql = "SELECT COUNT(*) FROM articles a JOIN categories c ON a.category_id = c.id WHERE $whereClause";
$stmtCount = $db->prepare($countSql);
$stmtCount->execute($params);
$totalArticles = (int)$stmtCount->fetchColumn();
$totalPages = ceil($totalArticles / $limit);

// 4. Ambil Data Artikel
$dataSql = "
    SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.fullname AS author_name,
           (SELECT COUNT(*) FROM comments WHERE article_id = a.id AND status = 'approved') AS comment_count
    FROM articles a
    JOIN categories c ON a.category_id = c.id
    JOIN users u ON a.author_id = u.id
    WHERE $whereClause
    ORDER BY $orderBy
    LIMIT $limit OFFSET $offset
";
$stmtData = $db->prepare($dataSql);
$stmtData->execute($params);
$articles = $stmtData->fetchAll();

// 5. Data Sidebar: Kategori & Artikel Populer
try {
    $categoriesStmt = $db->query("
        SELECT c.*, COUNT(a.id) AS total_articles
        FROM categories c
        LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published'
        GROUP BY c.id
        ORDER BY c.name ASC
    ");
    $categories = $categoriesStmt->fetchAll();

    $popularStmt = $db->query("
        SELECT title, slug, thumbnail, views, created_at
        FROM articles
        WHERE status = 'published'
        ORDER BY views DESC
        LIMIT 5
    ");
    $popularArticles = $popularStmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
    $popularArticles = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNER HEADER -->
<div class="py-5 text-white" style="background: linear-gradient(135deg, #0b1329 0%, #1e3a8a 100%);">
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-white-50 text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Artikel & Berita</li>
            </ol>
        </nav>
        <h1 class="fw-bold mb-2">Pusat Informasi & Artikel</h1>
        <p class="text-white-50 lead mb-0">Wawasan edukasi, liputan prestasi, dan kabar terkini SMK Bangun Nusa Bangsa.</p>
    </div>
</div>

<div class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- MAIN CONTENT: ARTIKEL GRID -->
            <div class="col-lg-8">
                <!-- FILTER BAR & PENCARIAN AKTIF INFO -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center bg-white p-3 rounded-4 shadow-sm border mb-4 gap-3">
                    <div>
                        <span class="text-muted small">Menampilkan: </span>
                        <strong><?= $totalArticles ?> Artikel</strong>
                        <?php if (!empty($search)): ?>
                            <span class="badge bg-primary ms-2">Pencarian: "<?= e($search) ?>"</span>
                        <?php endif; ?>
                        <?php if (!empty($categorySlug)): ?>
                            <span class="badge bg-info text-dark ms-2">Kategori: <?= e($categorySlug) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($search) || !empty($categorySlug)): ?>
                            <a href="<?= base_url('artikel.php') ?>" class="btn btn-sm btn-link text-danger p-0 ms-2 text-decoration-none">Reset Filter</a>
                        <?php endif; ?>
                    </div>

                    <!-- SORT DROPDOWN -->
                    <div class="d-flex align-items-center gap-2">
                        <label for="sortSelect" class="small text-muted text-nowrap">Urutkan:</label>
                        <select id="sortSelect" class="form-select form-select-sm" onchange="location = this.value;" style="min-width: 140px;">
                            <?php
                            $queryParams = $_GET;
                            $buildUrl = function($newSort) use ($queryParams) {
                                $queryParams['sort'] = $newSort;
                                $queryParams['page'] = 1;
                                return base_url('artikel.php?' . http_build_query($queryParams));
                            };
                            ?>
                            <option value="<?= $buildUrl('terbaru') ?>" <?= ($sort == 'terbaru') ? 'selected' : '' ?>>Terbaru</option>
                            <option value="<?= $buildUrl('populer') ?>" <?= ($sort == 'populer') ? 'selected' : '' ?>>Terpopuler</option>
                            <option value="<?= $buildUrl('terlama') ?>" <?= ($sort == 'terlama') ? 'selected' : '' ?>>Terlama</option>
                        </select>
                    </div>
                </div>

                <!-- ARTIKEL GRID -->
                <?php if (!empty($articles)): ?>
                    <div class="row g-4">
                        <?php foreach ($articles as $art): ?>
                            <div class="col-md-6">
                                <div class="custom-card h-100 d-flex flex-column">
                                    <div class="article-thumbnail-wrapper">
                                        <img src="<?= base_url(e($art['thumbnail'])) ?>" alt="<?= e($art['title']) ?>" class="article-thumbnail" onerror="this.src='<?= base_url('assets/images/default-article.svg') ?>'">
                                        <a href="<?= base_url('artikel.php?category=' . e($art['category_slug'])) ?>" class="article-category-badge text-decoration-none">
                                            <?= e($art['category_name']) ?>
                                        </a>
                                    </div>
                                    <div class="p-4 d-flex flex-column flex-grow-1">
                                        <div class="article-meta mb-2">
                                            <span><i class="bi bi-calendar3 me-1"></i> <?= format_date_id($art['created_at'], false) ?></span>
                                            <span><i class="bi bi-chat-dots me-1"></i> <?= (int)$art['comment_count'] ?></span>
                                            <span><i class="bi bi-eye me-1"></i> <?= (int)$art['views'] ?></span>
                                        </div>
                                        <h5 class="fw-bold mb-3">
                                            <a href="<?= base_url('artikel-detail.php?slug=' . e($art['slug'])) ?>" class="text-dark text-decoration-none hover-primary">
                                                <?= e($art['title']) ?>
                                            </a>
                                        </h5>
                                        <p class="text-muted small mb-4 flex-grow-1">
                                            <?= e($art['excerpt']) ?>
                                        </p>
                                        <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-auto">
                                            <span class="small text-muted"><i class="bi bi-person me-1"></i> <?= e($art['author_name']) ?></span>
                                            <a href="<?= base_url('artikel-detail.php?slug=' . e($art['slug'])) ?>" class="btn btn-sm btn-link text-primary fw-semibold p-0 text-decoration-none">
                                                Baca Lengkap <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- PAGINATION -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-5">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <?php
                                    $prevParams = $_GET;
                                    $prevParams['page'] = $page - 1;
                                    ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= base_url('artikel.php?' . http_build_query($prevParams)) ?>" aria-label="Previous">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                    <?php
                                    $pageParams = $_GET;
                                    $pageParams['page'] = $p;
                                    ?>
                                    <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= base_url('artikel.php?' . http_build_query($pageParams)) ?>">
                                            <?= $p ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <?php
                                    $nextParams = $_GET;
                                    $nextParams['page'] = $page + 1;
                                    ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= base_url('artikel.php?' . http_build_query($nextParams)) ?>" aria-label="Next">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                        <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
                        <h4 class="fw-bold text-dark">Tidak Ada Artikel Ditemukan</h4>
                        <p class="text-muted">Kata kunci atau filter yang Anda cari belum memiliki artikel terkait.</p>
                        <div>
                            <a href="<?= base_url('artikel.php') ?>" class="btn btn-outline-primary rounded-pill">
                                Lihat Semua Artikel
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-4">
                <!-- PENCARIAN -->
                <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-search text-primary"></i> Cari Artikel
                        </h5>
                        <form action="<?= base_url('artikel.php') ?>" method="GET">
                            <?php if (!empty($categorySlug)): ?>
                                <input type="hidden" name="category" value="<?= e($categorySlug) ?>">
                            <?php endif; ?>
                            <div class="input-group">
                                <input type="text" name="q" class="form-control" placeholder="Kata kunci artikel..." value="<?= e($search) ?>" required>
                                <button type="submit" class="btn btn-primary-custom px-3">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- KATEGORI ARTIKEL -->
                <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-tags text-primary"></i> Kategori
                        </h5>
                        <div class="list-group list-group-flush">
                            <a href="<?= base_url('artikel.php') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0 <?= empty($categorySlug) ? 'fw-bold text-primary' : 'text-muted' ?>">
                                <span><i class="bi bi-grid-fill me-2"></i> Semua Kategori</span>
                                <span class="badge bg-light text-dark rounded-pill"><?= $totalArticles ?></span>
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="<?= base_url('artikel.php?category=' . e($cat['slug'])) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0 <?= ($categorySlug == $cat['slug']) ? 'fw-bold text-primary' : 'text-muted' ?>">
                                    <span><i class="bi <?= e($cat['icon'] ?? 'bi-bookmark') ?> me-2"></i> <?= e($cat['name']) ?></span>
                                    <span class="badge bg-light text-dark rounded-pill"><?= (int)$cat['total_articles'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- ARTIKEL TERPOPULER -->
                <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-fire text-danger"></i> Artikel Populer
                        </h5>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($popularArticles as $pop): ?>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= base_url(e($pop['thumbnail'])) ?>" alt="<?= e($pop['title']) ?>" class="rounded-3 flex-shrink-0" style="width: 70px; height: 60px; object-fit: cover;" onerror="this.src='<?= base_url('assets/images/default-article.svg') ?>'">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1" style="font-size: 0.88rem; line-height: 1.35;">
                                            <a href="<?= base_url('artikel-detail.php?slug=' . e($pop['slug'])) ?>" class="text-dark text-decoration-none">
                                                <?= truncate_text($pop['title'], 60) ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <i class="bi bi-eye me-1"></i> <?= (int)$pop['views'] ?> views
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- PPDB WIDGET BANNER -->
                <div class="card border-0 shadow-sm rounded-4 text-white p-4" style="background: linear-gradient(135deg, #1e40af 0%, #0f172a 100%);">
                    <span class="badge bg-warning text-dark align-self-start px-3 py-1 rounded-pill fw-bold mb-2">Info PPDB</span>
                    <h5 class="fw-bold mb-2">Pendaftaran Siswa Baru 2026/2027</h5>
                    <p class="small text-white-50 mb-3">Telah dibuka pendaftaran gelombang pertama dengan kuota terbatas dan beasiswa prestasi.</p>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', get_setting('school_whatsapp', '081234567890')) ?>" target="_blank" class="btn btn-warning btn-sm fw-bold w-100">
                        <i class="bi bi-whatsapp me-1"></i> Hubungi Panitia PPDB
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
