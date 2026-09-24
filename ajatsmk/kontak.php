<?php
/**
 * Kontak & Lokasi
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Kontak Kami - Alamat & Layanan Informasi';
$page_description = 'Hubungi SMK Bangun Nusa Bangsa untuk konsultasi PPDB, kemitraan industri, atau kunjungan sekolah.';

$db = get_db();
$school_name = get_setting('school_name', 'SMK Bangun Nusa Bangsa');
$school_address = get_setting('school_address', 'Jl. Pendidikan Karakter Bangsa No. 88, Kawasan Pendidikan Terpadu, Jakarta');
$school_phone = get_setting('school_phone', '(021) 8899-7722');
$school_email = get_setting('school_email', 'info@smk-bangunnusabangsa.sch.id');
$school_whatsapp = get_setting('school_whatsapp', '081234567890');

// Proses kirim pesan formulir kontak
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'Validasi sesi keamanan (CSRF) gagal. Silakan muat ulang halaman.');
        header('Location: ' . base_url('kontak.php'));
        exit;
    }

    $name = sanitize($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        set_flash('danger', 'Harap lengkapi semua kolom formulir kontak.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('danger', 'Format email yang Anda masukkan tidak valid.');
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $subject, strip_tags($message)]);
            set_flash('success', '<strong>Pesan Terkirim!</strong> Terima kasih telah menghubungi kami. Tim kami akan segera menanggapi.');
        } catch (Exception $e) {
            set_flash('danger', 'Gagal mengirim pesan: ' . htmlspecialchars($e->getMessage()));
        }
    }

    header('Location: ' . base_url('kontak.php'));
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNER HEADER -->
<div class="py-5 text-white" style="background: linear-gradient(135deg, #0b1329 0%, #1e3a8a 100%);">
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-white-50 text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Kontak</li>
            </ol>
        </nav>
        <h1 class="fw-bold mb-2">Hubungi SMK Bangun Nusa Bangsa</h1>
        <p class="text-white-50 lead mb-0">Kami siap melayani pertanyaan seputar PPDB, kerjasama DUDI, dan kegiatan akademik.</p>
    </div>
</div>

<div class="py-5 bg-white">
    <div class="container py-4">
        <!-- FLASH ALERT -->
        <?php require_once __DIR__ . '/includes/alerts.php'; ?>

        <div class="row g-5">
            <!-- FORMULIR PESAN -->
            <div class="col-lg-7">
                <div class="custom-card p-4 p-md-5">
                    <span class="section-tag"><i class="bi bi-chat-dots me-1"></i> Kirim Pesan</span>
                    <h3 class="fw-bold text-dark mb-4">Sampaikan Pertanyaan Anda</h3>

                    <form action="<?= base_url('kontak.php') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="contact_name" class="form-label small fw-semibold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contact_name" name="name" placeholder="Nama Anda" required>
                            </div>
                            <div class="col-md-6">
                                <label for="contact_email" class="form-label small fw-semibold text-dark">Alamat Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="contact_email" name="email" placeholder="nama@email.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="contact_subject" class="form-label small fw-semibold text-dark">Subjek / Perihal <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact_subject" name="subject" placeholder="Contoh: Info Pendaftaran Siswa Baru / Tawaran Kemitraan" required>
                        </div>

                        <div class="mb-4">
                            <label for="contact_message" class="form-label small fw-semibold text-dark">Isi Pesan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="contact_message" name="message" rows="5" placeholder="Tuliskan pesan atau pertanyaan Anda secara rinci..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary-custom px-4 py-2 w-100 fw-bold">
                            <i class="bi bi-send-fill me-1"></i> Kirim Pesan Sekarang
                        </button>
                    </form>
                </div>
            </div>

            <!-- KARTU INFORMASI KONTAK -->
            <div class="col-lg-5">
                <div class="h-100 d-flex flex-column gap-4">
                    <div class="custom-card p-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-primary text-white rounded-3 p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                <i class="bi bi-geo-alt fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Alamat Kampus</h6>
                                <p class="text-muted small mb-0"><?= e($school_address) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="custom-card p-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-success text-white rounded-3 p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                <i class="bi bi-whatsapp fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">WhatsApp & Call Center</h6>
                                <p class="text-muted small mb-2"><?= e($school_phone) ?> / <?= e($school_whatsapp) ?></p>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $school_whatsapp) ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill fw-semibold">
                                    <i class="bi bi-whatsapp me-1"></i> Buka Chat Langsung
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="custom-card p-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-info text-white rounded-3 p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                <i class="bi bi-envelope fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Email Resmi</h6>
                                <p class="text-muted small mb-0"><?= e($school_email) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="custom-card p-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-warning text-dark rounded-3 p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                <i class="bi bi-clock fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Jam Pelayanan Kantor</h6>
                                <p class="text-muted small mb-0">Senin - Jumat: 07.30 - 16.00 WIB<br>Sabtu: 08.00 - 12.00 WIB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
