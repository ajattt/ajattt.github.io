<?php
/**
 * Database Connection & Auto-Setup
 * SMK Bangun Nusa Bangsa
 */

if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'ajatsmk_db');
if (!defined('DB_PORT')) define('DB_PORT', 3306);

function get_db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Jika database belum ada, coba buat otomatis
            if ($e->getCode() == 1049) {
                try {
                    $rootPdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4", DB_USER, DB_PASS, $options);
                    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    
                    // Hubungkan ke database yang baru dibuat
                    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                    
                    // Jalankan import dari database.sql jika file ada
                    $sqlFile = __DIR__ . '/../database.sql';
                    if (file_exists($sqlFile)) {
                        $sqlContent = file_get_contents($sqlFile);
                        $pdo->exec($sqlContent);
                    }
                } catch (PDOException $initEx) {
                    die("Gagal inisialisasi basis data: " . htmlspecialchars($initEx->getMessage()));
                }
            } else {
                die("Koneksi ke basis data MySQL gagal: " . htmlspecialchars($e->getMessage()));
            }
        }
    }

    return $pdo;
}
