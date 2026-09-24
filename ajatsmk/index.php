<?php
/**
 * Beranda / Home Page
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Beranda - Sekolah Menengah Kejuruan Pusat Keunggulan';
$page_description = 'Portal Resmi SMK Bangun Nusa Bangsa. Membina generasi terampil, berkarakter, dan berdaya saing global di bidang Akuntansi, Jaringan Komputer, dan Otomotif Modern.';

// Ambil data sekolah dari settings
$school_name = get_setting('school_name', 'SMK Bangun Nusa Bangsa');
$school_slogan = get_setting('school_slogan', 'Mencetak Generasi Unggul, Berkarakter, & Berdaya Saing Global');
$principal_name = get_setting('principal_name', 'Drs. H. Mulyadi, M.Kom.');
$principal_welcome = get_setting('principal_welcome', 'Selamat datang di portal resmi SMK Bangun Nusa Bangsa. Kami berkomitmen memberikan kurikulum terbaik berstandar industri 4.0.');
$principal_photo = get_setting('principal_photo', 'assets/images/kepsek.jpg');

$db = get_db();

// Ambil 3 artikel terbaru
try {
    $stmtArticles = $db->query("
        SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.fullname AS author_name 
        FROM articles a
        JOIN categories c ON a.category_id = c.id
        JOIN users u ON a.author_id = u.id
        WHERE a.status = 'published'
        ORDER BY a.created_at DESC
        LIMIT 3
    ");
    $latestArticles = $stmtArticles->fetchAll();
} catch (Exception $e) {
    $latestArticles = [];
}

// Ambil program keahlian / jurusan
try {
    $majors = $db->query("SELECT * FROM majors ORDER BY id ASC")->fetchAll();
} catch (Exception $e) {
    $majors = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="hero-badge mb-3">
                    <i class="bi bi-stars text-warning"></i>
                    <span>SMK Pusat Keunggulan Berstandar Industri 4.0</span>
                </div>
                <h1 class="hero-title mb-4">
                    Mencetak Generasi <span class="text-warning">Unggul, Terampil</span> & Siap Kerja Global
                </h1>
                <p class="hero-lead mb-4">
                    Selamat datang di <strong><?= e($school_name) ?></strong>. Kami memadukan pendidikan vokasi modern, kemitraan strategis industri dunia usaha, serta penguatan karakter untuk masa depan cemerlang.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= base_url('jurusan.php') ?>" class="btn btn-warning btn-lg fw-bold px-4 py-3 shadow-sm rounded-3">
                        <i class="bi bi-mortarboard-fill me-2"></i> Pilihan Jurusan
                    </a>
                    <a href="<?= base_url('artikel.php') ?>" class="btn btn-outline-light btn-lg px-4 py-3 rounded-3">
                        <i class="bi bi-newspaper me-2"></i> Artikel & Berita
                    </a>
                    <a href="<?= base_url('kontak.php') ?>" class="btn btn-primary-custom btn-lg px-4 py-3 rounded-3">
                        <i class="bi bi-telephone-fill me-2"></i> Hubungi Kami
                    </a>
                </div>

                <div class="row mt-5 pt-3 g-4 border-top border-white-50">
                    <div class="col-4">
                        <div class="fs-2 fw-bolder text-white">98%</div>
                        <div class="text-white-50 small">Serapan Kerja & Wirausaha</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-2 fw-bolder text-warning">35+</div>
                        <div class="text-white-50 small">Mitra Industri Nasional</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-2 fw-bolder text-info">A</div>
                        <div class="text-white-50 small">Akreditasi Unggul</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="hero-image-card">
                    <img src="<?= base_url('assets/images/kampus-bnb.jpg') ?>" alt="Kampus SMK Bangun Nusa Bangsa" onerror="this.src='<?= base_url('assets/images/article-1.jpg') ?>'">
                    <div class="hero-floating-stat">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="bi bi-shield-check fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-white">LSP-P1 BNSP Terlisensi</h6>
                                <small class="text-white-50">Sertifikasi Kompetensi Resmi</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SAMBUTAN KEPALA SEKOLAH -->
<section class="py-5 bg-white border-bottom">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-4 text-center">
                <div class="position-relative d-inline-block">
                    <img src="<?= base_url(e($principal_photo)) ?>" alt="Kepala Sekolah" class="img-fluid rounded-4 shadow-lg" style="max-height: 380px; object-fit: cover;" onerror="this.src='<?= base_url('assets/images/kepsek.png') ?>'">
                    <div class="position-absolute bottom-0 start-50 translate-middle-x bg-dark text-white px-4 py-2 rounded-pill shadow small fw-semibold text-nowrap mb-n2">
                        <i class="bi bi-patch-check-fill text-warning me-1"></i> Kepala Sekolah
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <span class="section-tag"><i class="bi bi-megaphone me-1"></i> Sambutan Pimpinan</span>
                <h2 class="section-title"><?= e($principal_name) ?></h2>
                <div class="lead text-primary fw-medium mb-3">
                    "Membangun Masa Depan Gemilang Melalui Kompetensi & Karakter Luhur"
                </div>
                <div class="text-muted fs-6 mb-4" style="line-height: 1.8;">
                    <?= nl2br(e($principal_welcome)) ?>
                </div>
                <div class="d-flex flex-wrap gap-4 pt-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle text-success fs-4"></i>
                        <span class="fw-semibold">Kurikulum Terintegrasi DUDI</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle text-success fs-4"></i>
                        <span class="fw-semibold">Laboratorium 4.0</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle text-success fs-4"></i>
                        <span class="fw-semibold">Pembinaan Karakter Disiplin</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- JURUSAN / PROGRAM KEAHLIAN -->
<section class="py-5" style="background-color: #f8fafc;">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="section-tag"><i class="bi bi-mortarboard me-1"></i> Program Keahlian</span>
            <h2 class="section-title">Jurusan Unggulan Masa Depan</h2>
            <p class="section-subtitle">
                Setiap kompetensi keahlian dirancang selaras dengan tren industri modern dan kebutuhan tenaga kerja profesional bersertifikat.
            </p>
        </div>

        <div class="row g-4">
            <?php foreach ($majors as $m): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="custom-card h-100 d-flex flex-column">
                        <div class="position-relative overflow-hidden">
                            <img src="<?= base_url(e($m['image'])) ?>" alt="<?= e($m['name']) ?>" class="major-card-img" onerror="this.src='<?= base_url('assets/images/default-article.svg') ?>'">
                            <span class="badge bg-<?= e($m['badge_color'] ?? 'primary') ?> position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill fs-7 shadow-sm">
                                <?= e($m['code']) ?>
                            </span>
                        </div>
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <h4 class="fw-bold mb-2 text-dark"><?= e($m['name']) ?></h4>
                            <p class="text-primary small fw-semibold mb-3"><?= e($m['tagline']) ?></p>
                            <p class="text-muted small mb-4 flex-grow-1">
                                <?= truncate_text($m['description'], 140) ?>
                            </p>
                            <div class="border-top pt-3 mt-auto">
                                <a href="<?= base_url('jurusan.php#' . e($m['slug'])) ?>" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold w-100">
                                    Detail Kompetensi & Karir <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FITUR UTAMA: ARTIKEL & BERITA TERKINI -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5">
            <div>
                <span class="section-tag"><i class="bi bi-newspaper me-1"></i> Warta & Artikel</span>
                <h2 class="section-title mb-1">Kabar & Artikel Terkini</h2>
                <p class="text-muted mb-0">Informasi prestasi, kegiatan sekolah, dan wawasan edukatif dari civitas akademika.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="<?= base_url('artikel.php') ?>" class="btn btn-primary-custom d-inline-flex align-items-center gap-2">
                    <span>Lihat Semua Artikel</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($latestArticles)): ?>
                <?php foreach ($latestArticles as $art): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="custom-card h-100 d-flex flex-column">
                            <div class="article-thumbnail-wrapper">
                                <img src="<?= base_url(e($art['thumbnail'])) ?>" alt="<?= e($art['title']) ?>" class="article-thumbnail" onerror="this.src='<?= base_url('assets/images/default-article.svg') ?>'">
                                <span class="article-category-badge">
                                    <?= e($art['category_name']) ?>
                                </span>
                            </div>
                            <div class="p-4 d-flex flex-column flex-grow-1">
                                <div class="article-meta mb-2">
                                    <span><i class="bi bi-calendar3 me-1"></i> <?= format_date_id($art['created_at'], false) ?></span>
                                    <span><i class="bi bi-eye me-1"></i> <?= (int)$art['views'] ?> views</span>
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
                                        Baca Lengkap <i class="bi bi-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Belum ada artikel yang dipublikasikan saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CALL TO ACTION PPDB & KONSULTASI -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);">
    <div class="container py-4 text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3">
                    <i class="bi bi-bell-fill me-1"></i> PPDB TAHUN AJARAN 2026/2027
                </span>
                <h2 class="display-6 fw-bold mb-3">Siap Menjadi Bagian dari Generasi Unggul?</h2>
                <p class="lead text-white-50 mb-4">
                    Daftarkan putra-putri Anda di SMK Bangun Nusa Bangsa. Dapatkan pembinaan kompetensi industri terbaik dan kesempatan beasiswa prestasi.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', get_setting('school_whatsapp', '081234567890')) ?>?text=Halo%20Admin%20SMK%20Bangun%20Nusa%20Bangsa,%20saya%20ingin%20tanya%20informasi%20PPDB" target="_blank" class="btn btn-success btn-lg px-4 py-3 fw-bold rounded-3">
                        <i class="bi bi-whatsapp me-2"></i> Konsultasi via WhatsApp
                    </a>
                    <a href="<?= base_url('kontak.php') ?>" class="btn btn-outline-light btn-lg px-4 py-3 rounded-3">
                        <i class="bi bi-geo-alt me-2"></i> Kunjungi Kampus Kami
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
