<?php
/**
 * Tambah Artikel Baru
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/includes/auth-check.php';

$user = current_user();
$db = get_db();
$categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

$errors = [];
$title = '';
$slug = '';
$categoryId = '';
$excerpt = '';
$content = '';
$status = 'published';
$allowComments = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        $errors[] = 'Validasi sesi keamanan (CSRF) gagal. Silakan coba kembali.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
        $allowComments = isset($_POST['allow_comments']) ? 1 : 0;

        if (empty($title)) {
            $errors[] = 'Judul artikel wajib diisi.';
        }

        if (empty($slug)) {
            $slug = slugify($title);
        } else {
            $slug = slugify($slug);
        }

        // Cek keunikan slug
        $stmtCheck = $db->prepare("SELECT id FROM articles WHERE slug = ? LIMIT 1");
        $stmtCheck->execute([$slug]);
        if ($stmtCheck->fetch()) {
            $slug .= '-' . time();
        }

        if ($categoryId <= 0) {
            $errors[] = 'Silakan pilih kategori artikel.';
        }

        if (empty($excerpt)) {
            // Auto excerpt dari konten
            $excerpt = truncate_text($content, 160);
        }

        if (empty($content)) {
            $errors[] = 'Isi konten artikel tidak boleh kosong.';
        }

        // Upload Thumbnail
        $thumbnailPath = 'assets/images/default-article.svg';
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $uploadRes = upload_image($_FILES['thumbnail'], 'assets/uploads/articles/');
            if ($uploadRes['success']) {
                $thumbnailPath = $uploadRes['path'];
            } else {
                $errors[] = $uploadRes['message'];
            }
        }

        // Jika tidak ada error, simpan ke database
        if (empty($errors)) {
            try {
                $insertStmt = $db->prepare("
                    INSERT INTO articles (category_id, author_id, title, slug, thumbnail, excerpt, content, status, allow_comments, views, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())
                ");
                $insertStmt->execute([
                    $categoryId,
                    $user['id'],
                    $title,
                    $slug,
                    $thumbnailPath,
                    $excerpt,
                    $content,
                    $status,
                    $allowComments
                ]);

                set_flash('success', '<strong>Berhasil!</strong> Artikel baru telah berhasil disimpan.');
                header('Location: ' . base_url('admin/articles.php'));
                exit;
            } catch (Exception $e) {
                $errors[] = 'Gagal menyimpan ke basis data: ' . $e->getMessage();
            }
        }
    }
}

$admin_title = 'Tambah Artikel Baru';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Tulis Artikel Baru</h4>
        <p class="text-muted small mb-0">Publikasikan warta, pengumuman, atau artikel edukatif.</p>
    </div>
    <a href="<?= base_url('admin/articles.php') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger py-3 px-4 rounded-3 mb-4">
        <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terjadi Kesalahan:</h6>
        <ul class="mb-0 small ps-3">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= base_url('admin/article-create.php') ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <div class="row g-4">
        <!-- FORM UTAMA -->
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="admin-card-body p-4">
                    <!-- Judul Artikel -->
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold text-dark">Judul Artikel <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg fs-5" id="title" name="title" placeholder="Masukkan judul artikel yang menarik..." value="<?= e($title) ?>" required>
                    </div>

                    <!-- Slug URL -->
                    <div class="mb-3">
                        <label for="slug" class="form-label small fw-semibold text-muted">URL Slug (Otomatis dibuat dari judul)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">/artikel/</span>
                            <input type="text" class="form-control" id="slug" name="slug" placeholder="url-slug-artikel" value="<?= e($slug) ?>">
                        </div>
                    </div>

                    <!-- Ringkasan Excerpt -->
                    <div class="mb-3">
                        <label for="excerpt" class="form-label fw-semibold text-dark">Ringkasan / Excerpt</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="2" placeholder="Ringkasan singkat artikel yang akan tampil pada kartu preview..."><?= e($excerpt) ?></textarea>
                        <div class="form-text small text-muted">Jika dikosongkan, ringkasan akan dipotong secara otomatis dari konten.</div>
                    </div>

                    <!-- Isi Konten Artikel -->
                    <div class="mb-3">
                        <label for="content" class="form-label fw-bold text-dark">Isi Konten Artikel <span class="text-danger">*</span></label>
                        <div class="mb-2 btn-toolbar gap-1" role="toolbar">
                            <button type="button" class="btn btn-sm btn-light border" onclick="wrapText('content', '<strong>', '</strong>')" title="Tebal"><i class="bi bi-type-bold"></i></button>
                            <button type="button" class="btn btn-sm btn-light border" onclick="wrapText('content', '<em>', '</em>')" title="Miring"><i class="bi bi-type-italic"></i></button>
                            <button type="button" class="btn btn-sm btn-light border" onclick="wrapText('content', '<h3>', '</h3>')" title="Heading"><i class="bi bi-type-h3"></i></button>
                            <button type="button" class="btn btn-sm btn-light border" onclick="wrapText('content', '<blockquote>', '</blockquote>')" title="Kutipan"><i class="bi bi-quote"></i></button>
                            <button type="button" class="btn btn-sm btn-light border" onclick="wrapText('content', '<p>', '</p>')" title="Paragraf"><i class="bi bi-paragraph"></i></button>
                            <button type="button" class="btn btn-sm btn-light border" onclick="wrapText('content', '<ul>\n  <li>', '</li>\n</ul>')" title="List"><i class="bi bi-list-ul"></i></button>
                        </div>
                        <textarea class="form-control" id="content" name="content" rows="14" placeholder="Tuliskan konten artikel lengkap Anda di sini... Mendukung tag HTML standar seperti <p>, <strong>, <h3>, <ul>, <li>, dll." required><?= e($content) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- SIDEBAR PENGATURAN ARTIKEL -->
        <div class="col-lg-4">
            <!-- PUBLIKASI CARD -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h6 class="admin-card-title mb-0">Publikasi</h6>
                </div>
                <div class="admin-card-body p-3">
                    <div class="mb-3">
                        <label for="status" class="form-label small fw-semibold text-dark">Status Artikel</label>
                        <select class="form-select" id="status" name="status">
                            <option value="published" <?= ($status == 'published') ? 'selected' : '' ?>>Published (Terbit)</option>
                            <option value="draft" <?= ($status == 'draft') ? 'selected' : '' ?>>Draft (Draf)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label small fw-semibold text-dark">Kategori Artikel <span class="text-danger">*</span></label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($categoryId == $cat['id']) ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="allow_comments" name="allow_comments" value="1" <?= $allowComments ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-semibold text-dark" for="allow_comments">
                            Izinkan Komentar Pembaca
                        </label>
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-save me-1"></i> Simpan Artikel
                    </button>
                </div>
            </div>

            <!-- THUMBNAIL CARD -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h6 class="admin-card-title mb-0">Gambar Sampul (Thumbnail)</h6>
                </div>
                <div class="admin-card-body p-3">
                    <label for="thumbnail_input" class="form-label small text-muted">Unggah gambar format JPG, PNG, atau WEBP (Maksimal 3MB):</label>
                    <input class="form-control form-control-sm mb-3" type="file" id="thumbnail_input" name="thumbnail" accept="image/jpeg,image/png,image/webp">

                    <div id="thumbnail_preview_container" class="img-preview-box">
                        <img id="thumbnail_preview_img" src="<?= base_url('assets/images/default-article.svg') ?>" alt="Preview">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function wrapText(elementId, openTag, closeTag) {
    const textarea = document.getElementById(elementId);
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const selectedText = text.substring(start, end);
    const replacement = openTag + selectedText + closeTag;
    textarea.value = text.substring(0, start) + replacement + text.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + openTag.length, end + openTag.length);
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
