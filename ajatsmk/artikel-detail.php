<?php
/**
 * Halaman Detail Artikel & Sistem Komentar Fleksibel
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: ' . base_url('artikel.php'));
    exit;
}

$db = get_db();

// 1. Ambil Data Artikel
$stmt = $db->prepare("
    SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.fullname AS author_name, u.avatar AS author_avatar
    FROM articles a
    JOIN categories c ON a.category_id = c.id
    JOIN users u ON a.author_id = u.id
    WHERE a.slug = ? AND a.status = 'published'
    LIMIT 1
");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: ' . base_url('artikel.php'));
    exit;
}

// 2. Increment Views Counter
$stmtView = $db->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
$stmtView->execute([$article['id']]);
$article['views'] = (int)$article['views'] + 1;

// 3. Ambil Komentar yang Disetujui (Approved)
$stmtComments = $db->prepare("
    SELECT * FROM comments 
    WHERE article_id = ? AND status = 'approved'
    ORDER BY created_at ASC
");
$stmtComments->execute([$article['id']]);
$allComments = $stmtComments->fetchAll();

// Pisahkan komentar utama dan balasan (replies)
$rootComments = [];
$replies = [];
foreach ($allComments as $c) {
    if (empty($c['parent_id'])) {
        $rootComments[] = $c;
    } else {
        $replies[$c['parent_id']][] = $c;
    }
}

// 4. Ambil Artikel Terkait (Related Articles)
$stmtRelated = $db->prepare("
    SELECT id, title, slug, thumbnail, created_at, views 
    FROM articles 
    WHERE category_id = ? AND id != ? AND status = 'published'
    ORDER BY created_at DESC 
    LIMIT 3
");
$stmtRelated->execute([$article['category_id'], $article['id']]);
$relatedArticles = $stmtRelated->fetchAll();

// Setting komentar
$allowCommentsOnArticle = (bool)$article['allow_comments'];
$allowAnonymousSetting = (get_setting('allow_anonymous_comments', '1') === '1');

$page_title = $article['title'];
$page_description = $article['excerpt'];

$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

require_once __DIR__ . '/includes/header.php';
?>

<!-- BREADCRUMB -->
<div class="bg-light py-3 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('artikel.php') ?>" class="text-decoration-none">Artikel</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('artikel.php?category=' . e($article['category_slug'])) ?>" class="text-decoration-none"><?= e($article['category_name']) ?></a></li>
                <li class="breadcrumb-item active text-truncate" style="max-width: 320px;" aria-current="page"><?= e($article['title']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- MAIN ARTICLE AREA -->
            <div class="col-lg-8">
                <!-- FLASH MESSAGE -->
                <?php require_once __DIR__ . '/includes/alerts.php'; ?>

                <article class="bg-white p-4 p-md-5 rounded-4 shadow-sm border mb-5">
                    <!-- KATEGORI & METADATA -->
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <a href="<?= base_url('artikel.php?category=' . e($article['category_slug'])) ?>" class="badge bg-primary text-decoration-none px-3 py-2 rounded-pill">
                            <?= e($article['category_name']) ?>
                        </a>
                        <span class="text-muted small">&bull;</span>
                        <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i> <?= format_date_id($article['created_at'], true) ?></span>
                        <span class="text-muted small">&bull;</span>
                        <span class="text-muted small"><i class="bi bi-eye me-1"></i> <?= (int)$article['views'] ?> dilihat</span>
                    </div>

                    <!-- JUDUL -->
                    <h1 class="fw-bold text-dark mb-4" style="line-height: 1.3; font-size: 2.15rem;">
                        <?= e($article['title']) ?>
                    </h1>

                    <!-- INFO PENULIS -->
                    <div class="d-flex align-items-center gap-3 pb-4 mb-4 border-bottom">
                        <img src="<?= base_url(e($article['author_avatar'] ?? 'assets/images/default-avatar.svg')) ?>" alt="Avatar" class="rounded-circle" style="width: 44px; height: 44px; object-fit: cover;" onerror="this.src='<?= base_url('assets/images/default-avatar.svg') ?>'">
                        <div>
                            <div class="fw-bold text-dark"><?= e($article['author_name']) ?></div>
                            <small class="text-muted">Tim Humas & Redaksi SMK BNB</small>
                        </div>
                    </div>

                    <!-- GAMBAR UTAMA -->
                    <?php if (!empty($article['thumbnail'])): ?>
                        <div class="text-center mb-4">
                            <img src="<?= base_url(e($article['thumbnail'])) ?>" alt="<?= e($article['title']) ?>" class="article-main-image img-fluid" onerror="this.src='<?= base_url('assets/images/default-article.svg') ?>'">
                        </div>
                    <?php endif; ?>

                    <!-- RINGKASAN INTRO -->
                    <div class="lead text-dark fw-medium mb-4 p-3 bg-light rounded-3 border-start border-primary border-4">
                        <?= e($article['excerpt']) ?>
                    </div>

                    <!-- KONTEN LENGKAP -->
                    <div class="article-content mb-5">
                        <?= $article['content'] ?>
                    </div>

                    <!-- BAGIKAN ARTIKEL -->
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 pt-4 border-top">
                        <span class="fw-bold text-muted small"><i class="bi bi-share me-1"></i> Bagikan Artikel Ini:</span>
                        <div class="d-flex gap-2">
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['title'] . ' ' . $currentUrl) ?>" target="_blank" class="btn btn-success btn-sm rounded-pill px-3">
                                <i class="bi bi-whatsapp me-1"></i> WhatsApp
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-facebook me-1"></i> Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?text=<?= urlencode($article['title']) ?>&url=<?= urlencode($currentUrl) ?>" target="_blank" class="btn btn-dark btn-sm rounded-pill px-3">
                                <i class="bi bi-twitter-x me-1"></i> X
                            </a>
                        </div>
                    </div>
                </article>

                <!-- SECTION KOMENTAR -->
                <div id="komentar" class="bg-white p-4 p-md-5 rounded-4 shadow-sm border mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-chat-left-text text-primary"></i> 
                            Komentar & Diskusi 
                            <span class="badge bg-primary rounded-pill fs-6"><?= count($allComments) ?></span>
                        </h4>
                        <?php if ($allowCommentsOnArticle): ?>
                            <a href="#comment-form-section" class="btn btn-outline-primary btn-sm rounded-pill">
                                <i class="bi bi-pencil-square me-1"></i> Tulis Komentar
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- DAFTAR KOMENTAR -->
                    <?php if (!empty($rootComments)): ?>
                        <div class="comment-list mb-5">
                            <?php foreach ($rootComments as $comment): ?>
                                <div class="comment-box">
                                    <div class="d-flex gap-3">
                                        <!-- Avatar -->
                                        <?php if ($comment['is_anonymous']): ?>
                                            <div class="comment-avatar anonymous flex-shrink-0" title="Komentar Anonim">
                                                <i class="bi bi-incognito"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="comment-avatar flex-shrink-0" title="<?= e($comment['name']) ?>">
                                                <?= strtoupper(substr($comment['name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="flex-grow-1">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-bold text-dark">
                                                        <?= $comment['is_anonymous'] ? 'Anonim' : e($comment['name']) ?>
                                                    </span>
                                                    <?php if ($comment['is_anonymous']): ?>
                                                        <span class="comment-badge-anonymous">
                                                            <i class="bi bi-shield-slash me-1"></i> Anonim
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="comment-badge-verified">
                                                            <i class="bi bi-check-circle-fill me-1"></i> Terverifikasi
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted"><i class="bi bi-clock me-1"></i> <?= time_ago($comment['created_at']) ?></small>
                                            </div>

                                            <div class="text-muted mb-3" style="line-height: 1.6;">
                                                <?= nl2br(e($comment['comment'])) ?>
                                            </div>

                                            <?php if ($allowCommentsOnArticle): ?>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none btn-reply-comment" 
                                                            data-comment-id="<?= $comment['id'] ?>" 
                                                            data-author-name="<?= $comment['is_anonymous'] ? 'Anonim' : e($comment['name']) ?>">
                                                        <i class="bi bi-reply-fill"></i> Balas
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- BALASAN KOMENTAR (NESTED REPLIES) -->
                                    <?php if (!empty($replies[$comment['id']])): ?>
                                        <div class="comment-reply-wrapper mt-4 pt-3">
                                            <?php foreach ($replies[$comment['id']] as $reply): ?>
                                                <div class="d-flex gap-3 mb-3">
                                                    <?php if ($reply['is_anonymous']): ?>
                                                        <div class="comment-avatar anonymous flex-shrink-0" style="width: 38px; height: 38px; font-size: 0.95rem;">
                                                            <i class="bi bi-incognito"></i>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="comment-avatar flex-shrink-0" style="width: 38px; height: 38px; font-size: 0.95rem;">
                                                            <?= strtoupper(substr($reply['name'], 0, 1)) ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="flex-grow-1 bg-light p-3 rounded-3 border">
                                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="fw-bold small text-dark">
                                                                    <?= $reply['is_anonymous'] ? 'Anonim' : e($reply['name']) ?>
                                                                </span>
                                                                <?php if ($reply['is_anonymous']): ?>
                                                                    <span class="comment-badge-anonymous" style="font-size: 0.68rem;">Anonim</span>
                                                                <?php else: ?>
                                                                    <span class="comment-badge-verified" style="font-size: 0.68rem;">Terverifikasi</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <small class="text-muted" style="font-size: 0.72rem;"><?= time_ago($reply['created_at']) ?></small>
                                                        </div>
                                                        <div class="text-muted small">
                                                            <?= nl2br(e($reply['comment'])) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 mb-4 bg-light rounded-3">
                            <i class="bi bi-chat-square-dots fs-2 text-muted mb-2 d-block"></i>
                            <p class="text-muted mb-0">Belum ada komentar pada artikel ini. Jadilah yang pertama berkomentar!</p>
                        </div>
                    <?php endif; ?>

                    <!-- FORMULIR KOMENTAR -->
                    <?php if ($allowCommentsOnArticle): ?>
                        <div id="comment-form-section" class="pt-4 border-top">
                            <h5 class="fw-bold text-dark mb-2">Tinggalkan Komentar Anda</h5>
                            <p class="text-muted small mb-3">
                                Anda dapat memilih berkomentar secara <strong>Anonim</strong> atau menyertakan <strong>Nama & Email</strong> Anda secara lengkap.
                            </p>

                            <!-- Indikator Sedang Membalas Komentar Tertentu -->
                            <div id="replying-to-indicator" class="alert alert-info py-2 px-3 d-flex align-items-center justify-content-between d-none mb-3">
                                <span class="small">
                                    <i class="bi bi-reply-fill me-1"></i> Membalas komentar dari: <strong id="replying-to-name"></strong>
                                </span>
                                <button type="button" id="btn-cancel-reply" class="btn btn-sm btn-link text-danger p-0 text-decoration-none small">
                                    <i class="bi bi-x-circle"></i> Batal Balas
                                </button>
                            </div>

                            <form action="<?= base_url('proses-komentar.php') ?>" method="POST" id="commentForm">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                <input type="hidden" name="article_slug" value="<?= e($article['slug']) ?>">
                                <input type="hidden" name="parent_id" id="parent_comment_id" value="">
                                
                                <!-- Honeypot anti-spam (harus tetap kosong) -->
                                <div style="display:none !important;" aria-hidden="true">
                                    <input type="text" name="honeypot_username" tabindex="-1" autocomplete="off">
                                </div>

                                <!-- OPSI ANONYMOUS CHECKBOX (FITUR UTAMA USER REQUEST) -->
                                <?php if ($allowAnonymousSetting): ?>
                                    <div class="card bg-light border-0 p-3 mb-3 rounded-3">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="is_anonymous" name="is_anonymous" value="1">
                                            <label class="form-check-label fw-bold text-dark" for="is_anonymous">
                                                <i class="bi bi-incognito text-primary me-1"></i> Kirim Komentar sebagai Anonim
                                            </label>
                                        </div>
                                        <div id="anon-info-alert" class="small text-muted mt-2 d-none">
                                            <i class="bi bi-info-circle text-info me-1"></i> <em>Mode Anonim aktif: Nama Anda akan ditampilkan sebagai 'Anonim' dan email Anda tidak wajib diisi.</em>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- IDENTITAS PENGIRIM (Wajib Nama & Email jika bukan Anonim) -->
                                <div class="row g-3 mb-3" id="identity-fields">
                                    <div class="col-md-6">
                                        <label for="comment_name" class="form-label small fw-semibold text-dark">
                                            Nama Lengkap <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-person text-muted"></i></span>
                                            <input type="text" class="form-control" id="comment_name" name="name" placeholder="Nama Anda" required maxlength="100">
                                        </div>
                                        <div class="form-text small text-muted">Wajib jika tidak menggunakan mode Anonim.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="comment_email" class="form-label small fw-semibold text-dark">
                                            Alamat Email <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-envelope text-muted"></i></span>
                                            <input type="email" class="form-control" id="comment_email" name="email" placeholder="nama@email.com" required maxlength="100">
                                        </div>
                                        <div class="form-text small text-muted">Email tidak akan ditampilkan ke publik.</div>
                                    </div>
                                </div>

                                <!-- ISI KOMENTAR -->
                                <div class="mb-3">
                                    <label for="comment_content" class="form-label small fw-semibold text-dark">
                                        Isi Tanggapan / Komentar <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="comment_content" name="comment" rows="4" placeholder="Tuliskan pendapat, apresiasi, atau pertanyaan Anda secara santun..." required minlength="5"></textarea>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-shield-check text-success me-1"></i> Komentar santun & edukatif sangat kami hargai.
                                    </small>
                                    <button type="submit" class="btn btn-primary-custom px-4 py-2">
                                        <i class="bi bi-send-fill me-1"></i> Kirim Komentar
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary py-3 text-center mb-0 rounded-3">
                            <i class="bi bi-lock-fill me-1"></i> Kolom komentar untuk artikel ini dinonaktifkan oleh redaksi.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SIDEBAR ARTIKEL TERKAIT -->
            <div class="col-lg-4">
                <!-- ARTIKEL TERKAIT -->
                <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-collection text-primary"></i> Artikel Terkait
                        </h5>
                        <?php if (!empty($relatedArticles)): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($relatedArticles as $rel): ?>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= base_url(e($rel['thumbnail'])) ?>" alt="<?= e($rel['title']) ?>" class="rounded-3 flex-shrink-0" style="width: 72px; height: 60px; object-fit: cover;" onerror="this.src='<?= base_url('assets/images/default-article.svg') ?>'">
                                        <div>
                                            <h6 class="mb-1" style="font-size: 0.88rem; line-height: 1.35;">
                                                <a href="<?= base_url('artikel-detail.php?slug=' . e($rel['slug'])) ?>" class="text-dark text-decoration-none">
                                                    <?= truncate_text($rel['title'], 60) ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                <i class="bi bi-calendar3 me-1"></i> <?= format_date_id($rel['created_at'], false) ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">Belum ada artikel terkait lainnya dalam kategori ini.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- INFO PPDB BANNER -->
                <div class="card border-0 shadow-sm rounded-4 text-white p-4" style="background: linear-gradient(135deg, #1e40af 0%, #0f172a 100%);">
                    <span class="badge bg-warning text-dark align-self-start px-3 py-1 rounded-pill fw-bold mb-2">Pusat Keunggulan</span>
                    <h5 class="fw-bold mb-2">Ingin Bergabung dengan SMK Bangun Nusa Bangsa?</h5>
                    <p class="small text-white-50 mb-3">Dapatkan bimbingan vokasi terbaik dan jaminan penyaluran kerja berstandar internasional.</p>
                    <a href="<?= base_url('kontak.php') ?>" class="btn btn-warning btn-sm fw-bold w-100">
                        <i class="bi bi-info-circle me-1"></i> Info Pendaftaran Siswa
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
