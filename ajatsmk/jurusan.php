<?php
/**
 * Jurusan / Program Keahlian
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Program Keahlian - Jurusan Unggulan Vokasi';
$page_description = 'Pilihan kompetensi keahlian di SMK Bangun Nusa Bangsa: Akuntansi & Keuangan Lembaga, Teknik Komputer & Jaringan, dan Teknik Kendaraan Ringan.';

$db = get_db();
try {
    $majors = $db->query("SELECT * FROM majors ORDER BY id ASC")->fetchAll();
} catch (Exception $e) {
    $majors = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNER HEADER -->
<div class="py-5 text-white" style="background: linear-gradient(135deg, #0b1329 0%, #1e3a8a 100%);">
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-white-50 text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Program Keahlian</li>
            </ol>
        </nav>
        <h1 class="fw-bold mb-2">Program Keahlian & Jurusan</h1>
        <p class="text-white-50 lead mb-0">Kurikulum vokasi terkini yang diselaraskan langsung dengan standar industri kerja.</p>
    </div>
</div>

<!-- DAFTAR JURUSAN DETAIL -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <?php foreach ($majors as $index => $m): ?>
            <div id="<?= e($m['slug']) ?>" class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden <?= ($index % 2 == 1) ? 'bg-light' : 'bg-white' ?>" style="border: 1px solid #e2e8f0 !important;">
                <div class="row g-0 align-items-center">
                    <div class="col-lg-5 <?= ($index % 2 == 1) ? 'order-lg-2' : '' ?>">
                        <img src="<?= base_url(e($m['image'])) ?>" alt="<?= e($m['name']) ?>" class="w-100 h-100 object-fit-cover" style="min-height: 380px; max-height: 460px;" onerror="this.src='<?= base_url('assets/images/default-article.svg') ?>'">
                    </div>
                    <div class="col-lg-7 p-4 p-md-5 <?= ($index % 2 == 1) ? 'order-lg-1' : '' ?>">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-<?= e($m['badge_color'] ?? 'primary') ?> px-3 py-2 rounded-pill fs-6">
                                Kode: <?= e($m['code']) ?>
                            </span>
                            <span class="text-muted small fw-semibold">Pusat Keunggulan</span>
                        </div>
                        <h2 class="fw-bold text-dark mb-2"><?= e($m['name']) ?></h2>
                        <p class="text-primary fw-semibold lead mb-4"><?= e($m['tagline']) ?></p>
                        
                        <p class="text-muted mb-4" style="line-height: 1.8;">
                            <?= nl2br(e($m['description'])) ?>
                        </p>

                        <div class="row g-4 mb-4">
                            <!-- Kompetensi -->
                            <div class="col-md-6">
                                <h6 class="fw-bold text-dark d-flex align-items-center gap-2 mb-3">
                                    <i class="bi bi-award text-success fs-5"></i> Kompetensi Unggulan:
                                </h6>
                                <ul class="list-unstyled mb-0 small text-muted">
                                    <?php 
                                    $kompetensiList = explode(',', $m['competencies'] ?? '');
                                    foreach ($kompetensiList as $item):
                                        if (trim($item) === '') continue;
                                    ?>
                                        <li class="mb-2 d-flex align-items-start gap-2">
                                            <i class="bi bi-check-circle-fill text-success flex-shrink-0 mt-1"></i>
                                            <span><?= e(trim($item)) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <!-- Prospek Karir -->
                            <div class="col-md-6">
                                <h6 class="fw-bold text-dark d-flex align-items-center gap-2 mb-3">
                                    <i class="bi bi-briefcase text-primary fs-5"></i> Peluang & Karir Kerja:
                                </h6>
                                <ul class="list-unstyled mb-0 small text-muted">
                                    <?php 
                                    $karirList = explode(',', $m['careers'] ?? '');
                                    foreach ($karirList as $item):
                                        if (trim($item) === '') continue;
                                    ?>
                                        <li class="mb-2 d-flex align-items-start gap-2">
                                            <i class="bi bi-arrow-right-circle-fill text-primary flex-shrink-0 mt-1"></i>
                                            <span><?= e(trim($item)) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-3 pt-2">
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', get_setting('school_whatsapp', '081234567890')) ?>?text=Halo%20Admin,%20saya%20tertarik%20dengan%20jurusan%20<?= urlencode($m['name']) ?>" target="_blank" class="btn btn-primary-custom d-inline-flex align-items-center gap-2">
                                <i class="bi bi-whatsapp"></i> Tanya Jurusan Ini
                            </a>
                            <a href="<?= base_url('kontak.php') ?>" class="btn btn-outline-secondary">
                                Pendaftaran Siswa Baru
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
