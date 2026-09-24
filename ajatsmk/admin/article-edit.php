<?php
/**
 * Edit Artikel
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/includes/auth-check.php';
$user = current_user();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . base_url('admin/articles.php'));
    exit;
}

$db = get_db();

// Ambil artikel saat ini
$stmt = $db->prepare("SELECT * FROM articles WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    set_flash('danger', 'Artikel tidak ditemukan.');
    header('Location: ' . base_url('admin/articles.php'));
    exit;
}

$categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

$errors = [];
$title = $article['title'];
$slug = $article['slug'];
$categoryId = $article['category_id'];
$excerpt = $article['excerpt'];
$content = $article['content'];
$status = $article['status'];
$allowComments = $article['allow_comments'];
$thumbnailPath = $article['thumbnail'];

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

        // Cek keunikan slug (selain id artikel ini)
        $stmtCheck = $db->prepare("SELECT id FROM articles WHERE slug = ? AND id != ? LIMIT 1");
        $stmtCheck->execute([$slug, $id]);
        if ($stmtCheck->fetch()) {
            $slug .= '-' . time();
        }

        if ($categoryId <= 0) {
            $errors[] = 'Silakan pilih kategori artikel.';
        }

        if (empty($excerpt)) {
            $excerpt = truncate_text($content, 160);
        }

        if (empty($content)) {
            $errors[] = 'Isi konten artikel tidak boleh kosong.';
        }

        // Cek apakah ada upload thumbnail baru
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $uploadRes = upload_image($_FILES['thumbnail'], 'assets/uploads/articles/');
            if ($uploadRes['success']) {
                // Hapus thumbnail lama jika bukan default
                delete_file($article['thumbnail']);
                $thumbnailPath = $uploadRes['path'];
            } else {
                $errors[] = $uploadRes['message'];
            }
        }

        if (empty($errors)) {
            try {
                $updateStmt = $db->prepare("
                    UPDATE articles 
                    SET category_id = ?, title = ?, slug = ?, thumbnail = ?, excerpt = ?, content = ?, status = ?, allow_comments = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $updateStmt->execute([
                    $categoryId,
                    $title,
                    $slug,
                    $thumbnailPath,
                    $excerpt,
                    $content,
                    $status,
                    $allowComments,
                    $id
                ]);

                set_flash('success', '<strong>Berhasil!</strong> Perubahan artikel berhasil disimpan.');
                header('Location: ' . base_url('admin/articles.php'));
                exit;
            } catch (Exception $e) {
                $errors[] = 'Gagal memperbarui artikel: ' . $e->getMessage();
            }
        }
    }
}

$admin_title = 'Edit Artikel';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Edit Artikel</h4>
        <p class="text-muted small mb-0">Perbarui konten, status publikasi, atau gambar sampul.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('artikel-detail.php?slug=' . e($article['slug'])) ?>" target="_blank" class="btn btn-outline-info btn-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i> Lihat di Web
        </a>
        <a href="<?= base_url('admin/articles.php') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>
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

<form action="<?= base_url('admin/article-edit.php?id=' . $id) ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <div class="row g-4">
        <!-- FORM UTAMA -->
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="admin-card-body p-4">
                    <!-- Judul Artikel -->
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold text-dark">Judul Artikel <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg fs-5" id="title" name="title" value="<?= e($title) ?>" required>
                    </div>

                    <!-- Slug URL -->
                    <div class="mb-3">
                        <label for="slug" class="form-label small fw-semibold text-muted">URL Slug</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">/artikel/</span>
                            <input type="text" class="form-control" id="slug" name="slug" value="<?= e($slug) ?>">
                        </div>
                    </div>

                    <!-- Ringkasan Excerpt -->
                    <div class="mb-3">
                        <label for="excerpt" class="form-label fw-semibold text-dark">Ringkasan / Excerpt</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="2"><?= e($excerpt) ?></textarea>
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
                        <textarea class="form-control" id="content" name="content" rows="14" required><?= e($content) ?></textarea>
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

                    <div class="mb-3 text-muted small border-top pt-2">
                        <div><i class="bi bi-eye me-1"></i> Dibaca: <?= (int)$article['views'] ?> kali</div>
                        <div><i class="bi bi-clock me-1"></i> Dibuat: <?= format_date_id($article['created_at'], true) ?></div>
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Perbarui Artikel
                    </button>
                </div>
            </div>

            <!-- THUMBNAIL CARD -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h6 class="admin-card-title mb-0">Gambar Sampul (Thumbnail)</h6>
                </div>
                <div class="admin-card-body p-3">
                    <label for="thumbnail_input" class="form-label small text-muted">Ganti gambar sampul (biarkan kosong jika tidak diganti):</label>
                    <input class="form-control form-control-sm mb-3" type="file" id="thumbnail_input" name="thumbnail" accept="image/jpeg,image/png,image/webp">

                    <div class="small fw-semibold text-dark mb-1">Pratinjau Saat Ini:</div>
                    <div id="thumbnail_preview_container" class="img-preview-box">
                        <img id="thumbnail_preview_img" src="<?= base_url(e($thumbnailPath)) ?>" alt="Preview" onerror="this.src='<?= base_url('assets/images/default-article.svg') ?>'">
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
