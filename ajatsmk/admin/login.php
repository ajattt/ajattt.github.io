<?php
/**
 * Login Admin Dashboard
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/../includes/functions.php';

// Jika sudah login, redirect langsung ke dashboard
if (is_admin()) {
    header('Location: ' . base_url('admin/index.php'));
    exit;
}

$school_name = get_setting('school_name', 'SMK Bangun Nusa Bangsa');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        $error = 'Validasi token keamanan gagal. Silakan coba kembali.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = 'Harap masukkan username dan password.';
        } else {
            $db = get_db();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login Berhasil
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['avatar'] = $user['avatar'];

                set_flash('success', 'Selamat datang kembali, <strong>' . e($user['fullname']) . '</strong>!');
                header('Location: ' . base_url('admin/index.php'));
                exit;
            } else {
                $error = 'Username atau password yang Anda masukkan tidak sesuai.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Dashboard Admin | <?= e($school_name) ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0b1329 0%, #1e3a8a 60%, #1e40af 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .login-card {
            background: #ffffff;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="p-4 p-md-5">
            <div class="text-center mb-4">
                <img src="<?= base_url('assets/images/logo.jpg') ?>" alt="Logo" class="rounded-3 mb-3 shadow" style="width: 64px; height: 64px; object-fit: cover;" onerror="this.src='<?= base_url('assets/images/logo.svg') ?>'">
                <h4 class="fw-bold text-dark mb-1">Portal Administrator</h4>
                <p class="text-muted small mb-0"><?= e($school_name) ?></p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 px-3 small rounded-3 mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e($error) ?>
                </div>
            <?php endif; ?>

            <?php require_once __DIR__ . '/../includes/alerts.php'; ?>

            <form action="<?= base_url('admin/login.php') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="mb-3">
                    <label for="username" class="form-label small fw-semibold text-dark">Username atau Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-person text-muted"></i></span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label small fw-semibold text-dark">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100 py-2 fw-bold">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Dashboard
                </button>
            </form>

            <div class="mt-4 pt-3 border-top text-center">
                <div class="alert alert-light border small text-muted mb-3 py-2 px-3">
                    <i class="bi bi-info-circle me-1 text-primary"></i> Akun Default: <strong>admin</strong> | Password: <strong>admin123</strong>
                </div>
                <a href="<?= base_url('index.php') ?>" class="text-decoration-none small text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Website Utama
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
