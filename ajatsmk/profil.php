<?php
/**
 * Profil Sekolah
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Profil Sekolah - Visi, Misi & Sejarah';
$page_description = 'Mengenal lebih dekat SMK Bangun Nusa Bangsa, sejarah berdirinya, visi misi kejuruan berstandar industri, serta fasilitas modern.';

$school_name = get_setting('school_name', 'SMK Bangun Nusa Bangsa');
$school_slogan = get_setting('school_slogan', 'Mencetak Generasi Unggul, Berkarakter, & Berdaya Saing Global');
$school_vision = get_setting('school_vision');
$school_mission = get_setting('school_mission');
$school_history = get_setting('school_history');
$principal_name = get_setting('principal_name', 'Drs. H. Mulyadi, M.Kom.');
$principal_photo = get_setting('principal_photo', 'assets/images/kepsek.jpg');

require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNER HEADER -->
<div class="py-5 text-white" style="background: linear-gradient(135deg, #0b1329 0%, #1e3a8a 100%);">
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-white-50 text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Profil Sekolah</li>
            </ol>
        </nav>
        <h1 class="fw-bold mb-2">Profil SMK Bangun Nusa Bangsa</h1>
        <p class="text-white-50 lead mb-0">Dedikasi kami dalam menghadirkan pendidikan vokasi unggul dan relevan dengan industri.</p>
    </div>
</div>

<!-- TENTANG KAMI & SEJARAH -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="section-tag"><i class="bi bi-clock-history me-1"></i> Sejarah Singkat</span>
                <h2 class="section-title">Pondasi Kuat Mencetak Generasi Emas</h2>
                <div class="text-muted" style="line-height: 1.8;">
                    <p>
                        <?= nl2br(e($school_history)) ?>
                    </p>
                    <p>
                        Dengan sarana dan prasarana yang terus diperbarui mengikuti perkembangan era otomasi industri, kecerdasan buatan (AI), dan digital finance, kami memastikan para peserta didik memiliki bekal kompetensi nyata yang diakui secara nasional maupun internasional melalui Badan Nasional Sertifikasi Profesi (BNSP).
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?= base_url('assets/images/kampus-bnb.jpg') ?>" alt="Kampus Sekolah" class="img-fluid rounded-4 shadow-lg w-100" onerror="this.src='<?= base_url('assets/images/article-1.jpg') ?>'">
            </div>
        </div>
    </div>
</section>

<!-- VISI & MISI -->
<section class="py-5" style="background-color: #f8fafc;">
    <div class="container py-4">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="custom-card h-100 p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-primary text-white rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <i class="bi bi-compass fs-2"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0 text-dark">Visi Sekolah</h3>
                            <small class="text-primary fw-semibold">Arah & Cita-Cita Mulia</small>
                        </div>
                    </div>
                    <blockquote class="blockquote fs-5 text-muted fst-italic border-start border-primary border-4 ps-3 py-2">
                        "<?= e($school_vision) ?>"
                    </blockquote>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="custom-card h-100 p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-warning text-dark rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <i class="bi bi-check2-all fs-2"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0 text-dark">Misi Sekolah</h3>
                            <small class="text-warning fw-bold">Langkah Strategis Nyata</small>
                        </div>
                    </div>
                    <div class="text-muted" style="line-height: 1.8;">
                        <?= nl2br(e($school_mission)) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FASILITAS UNGGULAN -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="section-tag"><i class="bi bi-buildings me-1"></i> Sarana & Prasarana</span>
            <h2 class="section-title">Fasilitas Berstandar Industri</h2>
            <p class="section-subtitle">Ruang pembelajaran dan praktikum modern dirancang khusus mensimulasikan lingkungan kerja profesional.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-box h-100">
                    <i class="bi bi-cpu fs-1 text-primary mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark">Lab Komputer & Cloud 4.0</h5>
                    <p class="text-muted small mb-0">Dilengkapi workstation berspesifikasi tinggi, perangkat jaringan Cisco/MikroTik enterprise, serta koneksi fiber optik kecepatan tinggi.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box h-100">
                    <i class="bi bi-wrench-adjustable fs-1 text-warning mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark">Bengkel Otomotif EFI & EV</h5>
                    <p class="text-muted small mb-0">Peralatan scanner diagnostik computerized, sarana lift hidrolik, simulator sistem rem ABS, dan modul pembelajaran kendaraan listrik.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box h-100">
                    <i class="bi bi-calculator fs-1 text-success mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark">Lab Bank Mini & Fintech</h5>
                    <p class="text-muted small mb-0">Simulasi perbankan riil dengan software akuntansi profesional Accurate, MYOB, dan sistem administrasi perpajakan digital.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
