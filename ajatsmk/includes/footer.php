<?php
/**
 * Footer Template - Public Website
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/functions.php';

$school_name = get_setting('school_name', 'SMK Bangun Nusa Bangsa');
$school_slogan = get_setting('school_slogan', 'Mencetak Generasi Unggul, Berkarakter, & Berdaya Saing Global');
$school_phone = get_setting('school_phone', '(021) 8899-7722');
$school_email = get_setting('school_email', 'info@smk-bangunnusabangsa.sch.id');
$school_whatsapp = get_setting('school_whatsapp', '081234567890');
$school_address = get_setting('school_address', 'Jl. Pendidikan Karakter Bangsa No. 88, Kawasan Pendidikan Terpadu, Jakarta');
$fb = get_setting('facebook_url', '#');
$ig = get_setting('instagram_url', '#');
$yt = get_setting('youtube_url', '#');

// Ambil 3 artikel terbaru untuk footer
try {
    $db = get_db();
    $footerArticles = $db->query("SELECT title, slug, created_at FROM articles WHERE status = 'published' ORDER BY created_at DESC LIMIT 3")->fetchAll();
} catch (Exception $e) {
    $footerArticles = [];
}
?>

    <!-- SITE FOOTER -->
    <footer class="site-footer">
        <div class="container">
            <div class="row g-4">
                <!-- Kolom 1: Profil Singkat -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="<?= base_url('assets/images/logo.jpg') ?>" alt="Logo" class="rounded-3" style="width: 48px; height: 48px; object-fit: cover;" onerror="this.src='<?= base_url('assets/images/logo.svg') ?>'">
                        <div>
                            <h5 class="text-white fw-bold mb-0"><?= e($school_name) ?></h5>
                            <small class="text-info">Pusat Keunggulan Vokasi</small>
                        </div>
                    </div>
                    <p class="text-white-50 small mb-4">
                        <?= e($school_slogan) ?>. Berkomitmen mendidik generasi bangsa dengan kurikulum berstandar industri 4.0 dan Society 5.0.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="<?= e($fb) ?>" target="_blank" class="btn btn-outline-light btn-sm rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-facebook"></i></a>
                        <a href="<?= e($ig) ?>" target="_blank" class="btn btn-outline-light btn-sm rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-instagram"></i></a>
                        <a href="<?= e($yt) ?>" target="_blank" class="btn btn-outline-light btn-sm rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-youtube"></i></a>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $school_whatsapp) ?>" target="_blank" class="btn btn-outline-light btn-sm rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>

                <!-- Kolom 2: Tautan Cepat -->
                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title">Navigasi</h5>
                    <ul class="footer-links">
                        <li><a href="<?= base_url('index.php') ?>"><i class="bi bi-chevron-right me-1 small"></i> Beranda</a></li>
                        <li><a href="<?= base_url('profil.php') ?>"><i class="bi bi-chevron-right me-1 small"></i> Profil Sekolah</a></li>
                        <li><a href="<?= base_url('jurusan.php') ?>"><i class="bi bi-chevron-right me-1 small"></i> Program Keahlian</a></li>
                        <li><a href="<?= base_url('artikel.php') ?>"><i class="bi bi-chevron-right me-1 small"></i> Artikel & Berita</a></li>
                        <li><a href="<?= base_url('kontak.php') ?>"><i class="bi bi-chevron-right me-1 small"></i> Hubungi Kami</a></li>
                        <li><a href="<?= base_url('admin/login.php') ?>"><i class="bi bi-chevron-right me-1 small"></i> Portal Admin</a></li>
                    </ul>
                </div>

                <!-- Kolom 3: Artikel Terkini -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Artikel Terkini</h5>
                    <ul class="footer-links">
                        <?php if (!empty($footerArticles)): ?>
                            <?php foreach ($footerArticles as $fa): ?>
                                <li class="mb-3">
                                    <a href="<?= base_url('artikel-detail.php?slug=' . e($fa['slug'])) ?>" class="d-block text-truncate text-white-50">
                                        <?= e($fa['title']) ?>
                                    </a>
                                    <small class="text-white-50" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i> <?= time_ago($fa['created_at']) ?></small>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-white-50 small">Belum ada artikel dipublikasikan.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Kolom 4: Hubungi Kami -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Kontak Sekolah</h5>
                    <ul class="footer-links">
                        <li class="d-flex gap-2 text-white-50 small mb-2">
                            <i class="bi bi-geo-alt text-warning flex-shrink-0 mt-1"></i>
                            <span><?= e($school_address) ?></span>
                        </li>
                        <li class="d-flex gap-2 text-white-50 small mb-2">
                            <i class="bi bi-telephone text-warning flex-shrink-0"></i>
                            <span><?= e($school_phone) ?></span>
                        </li>
                        <li class="d-flex gap-2 text-white-50 small mb-2">
                            <i class="bi bi-envelope text-warning flex-shrink-0"></i>
                            <span><?= e($school_email) ?></span>
                        </li>
                        <li class="d-flex gap-2 text-white-50 small mb-2">
                            <i class="bi bi-whatsapp text-success flex-shrink-0"></i>
                            <span><?= e($school_whatsapp) ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="text-white-50">
                    &copy; <?= date('Y') ?> <strong><?= e($school_name) ?></strong>. Seluruh Hak Cipta Dilindungi.
                </div>
                <div class="text-white-50 small">
                    Ditenagai oleh PHP Native & Bootstrap 5
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to top button -->
    <button id="backToTopBtn" type="button" class="btn btn-primary-custom rounded-circle position-fixed bottom-0 end-0 m-4 shadow d-none" style="width: 48px; height: 48px; z-index: 1030;" title="Kembali ke atas">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Custom Main JS -->
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
