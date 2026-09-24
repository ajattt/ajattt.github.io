<?php
/**
 * Pengaturan Profil Sekolah & Akun Admin
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/includes/auth-check.php';

$user = current_user();
$db = get_db();

// 1. Tangani Update Pengaturan Sekolah & Website
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Validasi sesi keamanan gagal.');
    } else {
        $keys = [
            'school_name', 'school_slogan', 'principal_name', 'principal_welcome',
            'school_vision', 'school_mission', 'school_address', 'school_phone',
            'school_email', 'school_whatsapp', 'facebook_url', 'instagram_url',
            'youtube_url', 'require_comment_moderation', 'allow_anonymous_comments'
        ];

        try {
            $updateStmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            foreach ($keys as $k) {
                $val = trim($_POST[$k] ?? '');
                // Handle switch checkboxes
                if ($k === 'require_comment_moderation' || $k === 'allow_anonymous_comments') {
                    $val = isset($_POST[$k]) ? '1' : '0';
                }
                $updateStmt->execute([$k, $val, $val]);
            }
            set_flash('success', '<strong>Berhasil!</strong> Pengaturan website dan profil sekolah telah diperbarui.');
            header('Location: ' . base_url('admin/profile.php'));
            exit;
        } catch (Exception $e) {
            set_flash('danger', 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }
}

// 2. Tangani Update Akun & Password Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_account') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Validasi sesi keamanan gagal.');
    } else {
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($fullname) || empty($email)) {
            set_flash('danger', 'Nama lengkap dan email tidak boleh kosong.');
        } else {
            // Ambil data admin saat ini
            $stmtUser = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmtUser->execute([$user['id']]);
            $userData = $stmtUser->fetch();

            if (!empty($newPassword)) {
                // Ingin ganti password
                if (empty($oldPassword) || !password_verify($oldPassword, $userData['password'])) {
                    set_flash('danger', 'Password lama yang Anda masukkan salah.');
                } elseif (strlen($newPassword) < 6) {
                    set_flash('danger', 'Password baru minimal harus 6 karakter.');
                } elseif ($newPassword !== $confirmPassword) {
                    set_flash('danger', 'Konfirmasi password baru tidak cocok.');
                } else {
                    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                    $upd = $db->prepare("UPDATE users SET fullname = ?, email = ?, password = ? WHERE id = ?");
                    $upd->execute([$fullname, $email, $newHash, $user['id']]);
                    $_SESSION['fullname'] = $fullname;
                    $_SESSION['email'] = $email;
                    set_flash('success', 'Akun dan password admin berhasil diperbarui.');
                    header('Location: ' . base_url('admin/profile.php'));
                    exit;
                }
            } else {
                // Hanya update profil (tanpa ganti password)
                $upd = $db->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
                $upd->execute([$fullname, $email, $user['id']]);
                $_SESSION['fullname'] = $fullname;
                $_SESSION['email'] = $email;
                set_flash('success', 'Data profil admin berhasil diperbarui.');
                header('Location: ' . base_url('admin/profile.php'));
                exit;
            }
        }
    }
}

// Ambil Semua Nilai Settings
$settings = [];
$stQuery = $db->query("SELECT setting_key, setting_value FROM settings");
while ($r = $stQuery->fetch()) {
    $settings[$r['setting_key']] = $r['setting_value'];
}

$admin_title = 'Pengaturan Profil & Website';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="row g-4">
    <!-- PENGATURAN IDENTITAS SEKOLAH & FITUR -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="admin-card-title d-flex align-items-center gap-2">
                    <i class="bi bi-building-gear text-primary"></i> Identitas & Konfigurasi Sekolah
                </h6>
            </div>
            <div class="admin-card-body p-4">
                <form action="<?= base_url('admin/profile.php') ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="update_settings">

                    <!-- Profil Utama -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Nama Sekolah</label>
                            <input type="text" class="form-control" name="school_name" value="<?= e($settings['school_name'] ?? 'SMK Bangun Nusa Bangsa') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Slogan / Tagline</label>
                            <input type="text" class="form-control" name="school_slogan" value="<?= e($settings['school_slogan'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Nama Kepala Sekolah</label>
                            <input type="text" class="form-control" name="principal_name" value="<?= e($settings['principal_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Alamat Email Resmi</label>
                            <input type="email" class="form-control" name="school_email" value="<?= e($settings['school_email'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Sambutan Kepala Sekolah</label>
                        <textarea class="form-control" name="principal_welcome" rows="3"><?= e($settings['principal_welcome'] ?? '') ?></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Visi Sekolah</label>
                            <textarea class="form-control" name="school_vision" rows="3"><?= e($settings['school_vision'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Misi Sekolah</label>
                            <textarea class="form-control" name="school_mission" rows="3"><?= e($settings['school_mission'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Alamat Lengkap Sekolah</label>
                        <input type="text" class="form-control" name="school_address" value="<?= e($settings['school_address'] ?? '') ?>">
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">No. Telepon Sekolah</label>
                            <input type="text" class="form-control" name="school_phone" value="<?= e($settings['school_phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">No. WhatsApp Resmi</label>
                            <input type="text" class="form-control" name="school_whatsapp" value="<?= e($settings['school_whatsapp'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- KEBIJAKAN SISTEM KOMENTAR (USER REQUIREMENT) -->
                    <div class="card bg-light border-0 p-3 mb-4 rounded-3">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-chat-left-dots text-primary"></i> Kebijakan Komentar Artikel
                        </h6>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="allow_anonymous_comments" name="allow_anonymous_comments" value="1" <?= (($settings['allow_anonymous_comments'] ?? '1') === '1') ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-semibold text-dark" for="allow_anonymous_comments">
                                Izinkan Pengunjung Berkomentar Secara <strong>Anonim</strong> (Tanpa mencantumkan identitas asli)
                            </label>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="require_comment_moderation" name="require_comment_moderation" value="1" <?= (($settings['require_comment_moderation'] ?? '0') === '1') ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-semibold text-dark" for="require_comment_moderation">
                                Wajibkan Moderasi Admin Sebelum Komentar Ditampilkan ke Publik
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                        <i class="bi bi-save me-1"></i> Simpan Pengaturan Website
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- PENGATURAN AKUN ADMIN -->
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="admin-card-title d-flex align-items-center gap-2">
                    <i class="bi bi-person-gear text-primary"></i> Akun Administrator
                </h6>
            </div>
            <div class="admin-card-body p-4">
                <form action="<?= base_url('admin/profile.php') ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="update_account">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Username</label>
                        <input type="text" class="form-control" value="<?= e($user['username']) ?>" disabled readonly>
                        <div class="form-text small text-muted">Username tidak dapat diubah.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Nama Lengkap</label>
                        <input type="text" class="form-control" name="fullname" value="<?= e($user['fullname']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Alamat Email</label>
                        <input type="email" class="form-control" name="email" value="<?= e($user['email']) ?>" required>
                    </div>

                    <hr class="my-3">
                    <h6 class="fw-bold small text-dark mb-2">Ganti Password (Opsional)</h6>

                    <div class="mb-2">
                        <label class="form-label small text-muted">Password Saat Ini</label>
                        <input type="password" class="form-control form-control-sm" name="old_password" placeholder="Masukkan password lama">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small text-muted">Password Baru</label>
                        <input type="password" class="form-control form-control-sm" name="new_password" placeholder="Minimal 6 karakter">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Ulangi Password Baru</label>
                        <input type="password" class="form-control form-control-sm" name="confirm_password" placeholder="Konfirmasi password baru">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <i class="bi bi-shield-lock me-1"></i> Perbarui Profil Admin
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
