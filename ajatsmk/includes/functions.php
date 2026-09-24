<?php
/**
 * Helper Functions & Utilities
 * SMK Bangun Nusa Bangsa
 */

if (!ob_get_level()) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Base URL helper
 */
function base_url(string $path = ''): string {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = dirname($scriptName);
    $dir = preg_replace('#/(admin|includes)$#', '', $dir);
    $dir = rtrim($dir, '/\\');
    
    if (empty($dir) || $dir === '.' || $dir === '/') {
        $dir = '/ajatsmk';
    }
    
    $cleanPath = ltrim($path, '/');
    return $cleanPath ? $dir . '/' . $cleanPath : $dir;
}

/**
 * Escape string for HTML output (XSS protection)
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize basic input string
 */
function sanitize(?string $data): string {
    if ($data === null) return '';
    return trim(strip_tags($data));
}

/**
 * Generate slug from text
 */
function slugify(string $text): string {
    // Replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // Remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    // Trim
    $text = trim($text, '-');
    // Remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    // Lowercase
    $text = strtolower($text);

    return empty($text) ? 'item-' . time() : $text;
}

/**
 * Indonesian Date Formatter
 */
function format_date_id(?string $datetime, bool $with_time = false): string {
    if (empty($datetime)) return '-';
    
    $timestamp = strtotime($datetime);
    if (!$timestamp) return '-';

    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    $days = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];

    $dayName = $days[date('l', $timestamp)] ?? '';
    $day = date('d', $timestamp);
    $month = $months[(int)date('m', $timestamp)] ?? '';
    $year = date('Y', $timestamp);

    $formatted = "$dayName, $day $month $year";
    if ($with_time) {
        $formatted .= ' pukul ' . date('H:i', $timestamp) . ' WIB';
    }

    return $formatted;
}

/**
 * Human readable time difference (Time Ago in Indonesian)
 */
function time_ago(?string $datetime): string {
    if (empty($datetime)) return '-';
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) {
        return 'Baru saja';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' menit yang lalu';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' jam yang lalu';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' hari yang lalu';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' minggu yang lalu';
    } else {
        return format_date_id($datetime, false);
    }
}

/**
 * Truncate text cleanly with ellipsis
 */
function truncate_text(string $text, int $limit = 120): string {
    $clean = strip_tags($text);
    if (mb_strlen($clean) <= $limit) {
        return $clean;
    }
    return mb_substr($clean, 0, $limit) . '...';
}

/**
 * Get setting value from database
 */
function get_setting(string $key, string $default = ''): string {
    static $settingsCache = null;

    if ($settingsCache === null) {
        $db = get_db();
        try {
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            $settingsCache = [];
            while ($row = $stmt->fetch()) {
                $settingsCache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            return $default;
        }
    }

    return $settingsCache[$key] ?? $default;
}

/**
 * Flash Message Handling
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type'    => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * CSRF Protection
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Authentication Helpers
 */
function is_admin(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_admin(string $redirectPath = 'admin/login.php'): void {
    if (!is_admin()) {
        set_flash('warning', 'Silakan login terlebih dahulu untuk mengakses dashboard.');
        header('Location: ' . base_url($redirectPath));
        exit;
    }
}

function current_user(): ?array {
    if (!is_admin()) return null;
    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'fullname' => $_SESSION['fullname'] ?? '',
        'email'    => $_SESSION['email'] ?? '',
        'role'     => $_SESSION['role'] ?? 'admin',
        'avatar'   => $_SESSION['avatar'] ?? 'assets/images/default-avatar.svg',
    ];
}

/**
 * Upload Image Helper
 */
function upload_image(array $file, string $targetFolder = 'assets/uploads/articles/'): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Terjadi kesalahan saat mengunggah berkas. Kode: ' . $file['error']];
    }

    $maxSize = 3 * 1024 * 1024; // 3MB
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Ukuran berkas melebihi batas maksimal 3MB.'];
    }

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMimes)) {
        return ['success' => false, 'message' => 'Format berkas tidak didukung. Harap unggah gambar format JPG, PNG, atau WEBP.'];
    }

    $extension = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => 'jpg'
    };

    $targetDirAbsolute = __DIR__ . '/../' . ltrim($targetFolder, '/');
    if (!is_dir($targetDirAbsolute)) {
        mkdir($targetDirAbsolute, 0755, true);
    }

    $uniqueName = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $targetDirAbsolute . '/' . $uniqueName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $relativePath = rtrim($targetFolder, '/') . '/' . $uniqueName;
        return ['success' => true, 'path' => $relativePath, 'filename' => $uniqueName];
    }

    return ['success' => false, 'message' => 'Gagal memindahkan berkas yang diunggah ke server.'];
}

/**
 * Safely delete file
 */
function delete_file(?string $relativePath): void {
    if (empty($relativePath)) return;
    // Hindari menghapus default images
    if (str_contains($relativePath, 'default-')) return;
    
    $fullPath = __DIR__ . '/../' . ltrim($relativePath, '/');
    if (file_exists($fullPath) && is_file($fullPath)) {
        @unlink($fullPath);
    }
}
