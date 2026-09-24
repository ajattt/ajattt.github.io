<?php
/**
 * Manajemen Kategori Artikel
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/includes/auth-check.php';
$user = current_user();
$db = get_db();

// 1. Tambah Kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Validasi sesi keamanan gagal.');
    } else {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-bookmark-check');

        if (empty($name)) {
            set_flash('danger', 'Nama kategori wajib diisi.');
        } else {
            if (empty($slug)) $slug = slugify($name);
            else $slug = slugify($slug);

            // Cek keunikan slug
            $stmtC = $db->prepare("SELECT id FROM categories WHERE slug = ? LIMIT 1");
            $stmtC->execute([$slug]);
            if ($stmtC->fetch()) {
                $slug .= '-' . time();
            }

            try {
                $ins = $db->prepare("INSERT INTO categories (name, slug, description, icon, created_at) VALUES (?, ?, ?, ?, NOW())");
                $ins->execute([$name, $slug, $description, $icon]);
                set_flash('success', 'Kategori baru berhasil ditambahkan.');
                header('Location: ' . base_url('admin/categories.php'));
                exit;
            } catch (Exception $e) {
                set_flash('danger', 'Gagal menambahkan kategori: ' . $e->getMessage());
            }
        }
    }
}

// 2. Edit Kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Validasi sesi keamanan gagal.');
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-bookmark-check');

        if ($id <= 0 || empty($name)) {
            set_flash('danger', 'Data kategori tidak lengkap.');
        } else {
            if (empty($slug)) $slug = slugify($name);
            else $slug = slugify($slug);

            $stmtC = $db->prepare("SELECT id FROM categories WHERE slug = ? AND id != ? LIMIT 1");
            $stmtC->execute([$slug, $id]);
            if ($stmtC->fetch()) {
                $slug .= '-' . time();
            }

            try {
                $upd = $db->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, icon = ? WHERE id = ?");
                $upd->execute([$name, $slug, $description, $icon, $id]);
                set_flash('success', 'Perubahan kategori berhasil disimpan.');
                header('Location: ' . base_url('admin/categories.php'));
                exit;
            } catch (Exception $e) {
                set_flash('danger', 'Gagal memperbarui kategori: ' . $e->getMessage());
            }
        }
    }
}

// 3. Hapus Kategori
if (isset($_GET['delete_id']) && verify_csrf($_GET['csrf'] ?? '')) {
    $delId = (int)$_GET['delete_id'];
    
    // Cek apakah ada artikel di kategori ini
    $checkArticles = $db->prepare("SELECT COUNT(*) FROM articles WHERE category_id = ?");
    $checkArticles->execute([$delId]);
    $hasArticles = (int)$checkArticles->fetchColumn();

    if ($hasArticles > 0) {
        set_flash('warning', "Kategori tidak dapat dihapus karena masih digunakan oleh $hasArticles artikel.");
    } else {
        $del = $db->prepare("DELETE FROM categories WHERE id = ?");
        $del->execute([$delId]);
        set_flash('success', 'Kategori berhasil dihapus.');
    }
    header('Location: ' . base_url('admin/categories.php'));
    exit;
}

// Ambil Semua Kategori dengan Total Artikel
$categories = $db->query("
    SELECT c.*, COUNT(a.id) AS total_articles
    FROM categories c
    LEFT JOIN articles a ON c.id = a.category_id
    GROUP BY c.id
    ORDER BY c.name ASC
")->fetchAll();

$admin_title = 'Kategori Artikel';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="row g-4">
    <!-- FORM TAMBAH KATEGORI -->
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="admin-card-title d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle text-primary"></i> Tambah Kategori
                </h6>
            </div>
            <div class="admin-card-body p-4">
                <form action="<?= base_url('admin/categories.php') ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="create">

                    <div class="mb-3">
                        <label for="name" class="form-label small fw-semibold text-dark">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Misal: Prestasi Siswa" required>
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label small fw-semibold text-muted">Slug URL (Opsional)</label>
                        <input type="text" class="form-control form-control-sm" id="slug" name="slug" placeholder="prestasi-siswa">
                    </div>

                    <div class="mb-3">
                        <label for="icon" class="form-label small fw-semibold text-dark">Ikon Bootstrap</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-star"></i></span>
                            <input type="text" class="form-control" id="icon" name="icon" value="bi-bookmark-check" placeholder="bi-trophy, bi-newspaper">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label small fw-semibold text-dark">Deskripsi Singkat</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Penjelasan isi kategori..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i> Simpan Kategori
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- TABEL DAFTAR KATEGORI -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="admin-card-title d-flex align-items-center gap-2">
                    <i class="bi bi-tags text-primary"></i> Daftar Kategori (<?= count($categories) ?>)
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Nama Kategori</th>
                            <th>Slug</th>
                            <th>Total Artikel</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $idx => $cat): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi <?= e($cat['icon'] ?? 'bi-bookmark') ?> text-primary fs-5"></i>
                                            <div>
                                                <strong class="text-dark d-block"><?= e($cat['name']) ?></strong>
                                                <small class="text-muted"><?= e($cat['description']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><code><?= e($cat['slug']) ?></code></td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= (int)$cat['total_articles'] ?> Artikel</span>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $cat['id'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ((int)$cat['total_articles'] == 0): ?>
                                            <a href="<?= base_url('admin/categories.php?delete_id=' . $cat['id'] . '&csrf=' . csrf_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kategori ini?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Tidak dapat dihapus karena masih ada artikel">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- MODAL EDIT -->
                                <div class="modal fade" id="editModal<?= $cat['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <form action="<?= base_url('admin/categories.php') ?>" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">

                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title fw-bold">Edit Kategori</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Nama Kategori</label>
                                                        <input type="text" class="form-control" name="name" value="<?= e($cat['name']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Slug URL</label>
                                                        <input type="text" class="form-control" name="slug" value="<?= e($cat['slug']) ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Ikon Bootstrap</label>
                                                        <input type="text" class="form-control" name="icon" value="<?= e($cat['icon']) ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Deskripsi</label>
                                                        <textarea class="form-control" name="description" rows="3"><?= e($cat['description']) ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary fw-semibold">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada kategori.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
