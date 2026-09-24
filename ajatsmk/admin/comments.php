<?php
/**
 * Moderasi & Manajemen Komentar (Fitur Utama)
 * SMK Bangun Nusa Bangsa
 */
require_once __DIR__ . '/includes/auth-check.php';
$user = current_user();
$db = get_db();

// 1. Tangani Aksi Status Komentar (Approve, Pending, Spam, Delete)
if (isset($_GET['action']) && isset($_GET['id']) && verify_csrf($_GET['csrf'] ?? '')) {
    $commentId = (int)$_GET['id'];
    $action = $_GET['action'];

    try {
        if ($action === 'approve') {
            $stmt = $db->prepare("UPDATE comments SET status = 'approved' WHERE id = ?");
            $stmt->execute([$commentId]);
            set_flash('success', 'Komentar berhasil <strong>disetujui (Approved)</strong> dan kini tampil di publik.');
        } elseif ($action === 'pending') {
            $stmt = $db->prepare("UPDATE comments SET status = 'pending' WHERE id = ?");
            $stmt->execute([$commentId]);
            set_flash('info', 'Status komentar diubah menjadi <strong>Pending</strong>.');
        } elseif ($action === 'spam') {
            $stmt = $db->prepare("UPDATE comments SET status = 'spam' WHERE id = ?");
            $stmt->execute([$commentId]);
            set_flash('warning', 'Komentar ditandai sebagai <strong>Spam</strong>.');
        } elseif ($action === 'delete') {
            $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
            $stmt->execute([$commentId]);
            set_flash('success', 'Komentar telah dihapus permanen.');
        }
    } catch (Exception $e) {
        set_flash('danger', 'Gagal memproses aksi komentar: ' . $e->getMessage());
    }

    $redirectUrl = base_url('admin/comments.php');
    if (!empty($_GET['status_filter'])) $redirectUrl .= '?status=' . urlencode($_GET['status_filter']);
    header('Location: ' . $redirectUrl);
    exit;
}

// 2. Tangani Balasan Admin (Reply as Admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Validasi sesi keamanan gagal.');
    } else {
        $parentCommentId = (int)($_POST['parent_id'] ?? 0);
        $articleId = (int)($_POST['article_id'] ?? 0);
        $replyText = trim($_POST['reply_content'] ?? '');

        if ($parentCommentId > 0 && $articleId > 0 && !empty($replyText)) {
            try {
                $insReply = $db->prepare("
                    INSERT INTO comments (article_id, parent_id, name, email, is_anonymous, comment, status, ip_address, created_at)
                    VALUES (?, ?, ?, ?, 0, ?, 'approved', '127.0.0.1', NOW())
                ");
                $adminName = $user['fullname'] . ' (Admin)';
                $adminEmail = $user['email'];
                $insReply->execute([$articleId, $parentCommentId, $adminName, $adminEmail, $replyText]);

                set_flash('success', 'Balasan resmi Admin berhasil dipublikasikan!');
                header('Location: ' . base_url('admin/comments.php'));
                exit;
            } catch (Exception $e) {
                set_flash('danger', 'Gagal membalas komentar: ' . $e->getMessage());
            }
        } else {
            set_flash('danger', 'Harap isi teks balasan komentar.');
        }
    }
}

// 3. Filter Komentar
$statusFilter = trim($_GET['status'] ?? 'all');
$typeFilter = trim($_GET['type'] ?? 'all'); // 'anonymous', 'verified'
$articleIdFilter = (int)($_GET['article_id'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$where = ["1=1"];
$params = [];

if ($statusFilter !== 'all' && in_array($statusFilter, ['approved', 'pending', 'spam'])) {
    $where[] = "c.status = ?";
    $params[] = $statusFilter;
}

if ($typeFilter === 'anonymous') {
    $where[] = "c.is_anonymous = 1";
} elseif ($typeFilter === 'verified') {
    $where[] = "c.is_anonymous = 0";
}

if ($articleIdFilter > 0) {
    $where[] = "c.article_id = ?";
    $params[] = $articleIdFilter;
}

$whereClause = implode(' AND ', $where);

// Hitung total untuk pagination
$stmtCount = $db->prepare("SELECT COUNT(*) FROM comments c WHERE $whereClause");
$stmtCount->execute($params);
$totalComments = (int)$stmtCount->fetchColumn();
$totalPages = ceil($totalComments / $limit);

// Ambil data komentar
$sql = "
    SELECT c.*, a.title AS article_title, a.slug AS article_slug
    FROM comments c
    JOIN articles a ON c.article_id = a.id
    WHERE $whereClause
    ORDER BY c.created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmtData = $db->prepare($sql);
$stmtData->execute($params);
$comments = $stmtData->fetchAll();

// Metrik ringkasan status komentar
$countApproved = (int)$db->query("SELECT COUNT(*) FROM comments WHERE status = 'approved'")->fetchColumn();
$countPending  = (int)$db->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn();
$countSpam     = (int)$db->query("SELECT COUNT(*) FROM comments WHERE status = 'spam'")->fetchColumn();
$countAnon     = (int)$db->query("SELECT COUNT(*) FROM comments WHERE is_anonymous = 1")->fetchColumn();

$admin_title = 'Moderasi Komentar Artikel';
require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- FILTER TAB STATUS -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Moderasi & Manajemen Komentar</h4>
        <p class="text-muted small mb-0">Kelola komentar pembaca (Komentar Anonim maupun dengan Identitas Nama & Email).</p>
    </div>

    <!-- TABS STATUS -->
    <div class="btn-group shadow-sm">
        <a href="<?= base_url('admin/comments.php?status=all') ?>" class="btn btn-sm <?= ($statusFilter === 'all') ? 'btn-primary' : 'btn-light border' ?>">
            Semua (<?= $countApproved + $countPending + $countSpam ?>)
        </a>
        <a href="<?= base_url('admin/comments.php?status=pending') ?>" class="btn btn-sm <?= ($statusFilter === 'pending') ? 'btn-warning text-dark fw-bold' : 'btn-light border' ?>">
            Pending (<?= $countPending ?>)
        </a>
        <a href="<?= base_url('admin/comments.php?status=approved') ?>" class="btn btn-sm <?= ($statusFilter === 'approved') ? 'btn-success' : 'btn-light border' ?>">
            Approved (<?= $countApproved ?>)
        </a>
        <a href="<?= base_url('admin/comments.php?status=spam') ?>" class="btn btn-sm <?= ($statusFilter === 'spam') ? 'btn-danger' : 'btn-light border' ?>">
            Spam (<?= $countSpam ?>)
        </a>
    </div>
</div>

<!-- FILTER TIPE IDENTITAS (ANONIM VS VERIFIED) -->
<div class="admin-card mb-4">
    <div class="admin-card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="small text-muted fw-semibold">Filter Identitas:</span>
            <a href="<?= base_url('admin/comments.php?status=' . $statusFilter . '&type=all') ?>" class="btn btn-sm <?= ($typeFilter === 'all') ? 'btn-dark' : 'btn-outline-secondary' ?> rounded-pill">
                Semua Tipe
            </a>
            <a href="<?= base_url('admin/comments.php?status=' . $statusFilter . '&type=anonymous') ?>" class="btn btn-sm <?= ($typeFilter === 'anonymous') ? 'btn-dark' : 'btn-outline-secondary' ?> rounded-pill d-inline-flex align-items-center gap-1">
                <i class="bi bi-incognito"></i> Anonim (<?= $countAnon ?>)
            </a>
            <a href="<?= base_url('admin/comments.php?status=' . $statusFilter . '&type=verified') ?>" class="btn btn-sm <?= ($typeFilter === 'verified') ? 'btn-dark' : 'btn-outline-secondary' ?> rounded-pill d-inline-flex align-items-center gap-1">
                <i class="bi bi-check-circle-fill text-success"></i> Nama & Email (<?= ($countApproved + $countPending + $countSpam) - $countAnon ?>)
            </a>
        </div>

        <?php if ($articleIdFilter > 0): ?>
            <div>
                <span class="badge bg-info text-dark">Filter per artikel aktif</span>
                <a href="<?= base_url('admin/comments.php') ?>" class="btn btn-sm btn-link text-danger p-0 ms-2">Hapus Filter</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- DAFTAR KOMENTAR -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Pengirim (Identitas)</th>
                    <th>Artikel Terkait</th>
                    <th>Isi Komentar</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th class="text-end" style="width: 170px;">Aksi Moderasi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $index => $c): ?>
                        <tr>
                            <td><?= $offset + $index + 1 ?></td>
                            <td>
                                <?php if ($c['is_anonymous']): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                            <i class="bi bi-incognito"></i>
                                        </div>
                                        <div>
                                            <strong class="text-dark d-block">Anonim</strong>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small" style="font-size: 0.7rem;">Mode Anonim</span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px;">
                                            <?= strtoupper(substr($c['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong class="text-dark d-block"><?= e($c['name']) ?></strong>
                                            <small class="text-muted d-block"><?= e($c['email'] ?? '-') ?></small>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle small" style="font-size: 0.7rem;">Identitas Terverifikasi</span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= base_url('artikel-detail.php?slug=' . e($c['article_slug'])) ?>" target="_blank" class="text-dark text-decoration-none fw-semibold small d-block text-truncate" style="max-width: 200px;">
                                    <?= e($c['article_title']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="text-muted small" style="max-width: 320px; line-height: 1.4;">
                                    <?= nl2br(e($c['comment'])) ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($c['status'] === 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php elseif ($c['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Spam</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted d-block"><?= time_ago($c['created_at']) ?></small>
                                <small class="text-muted" style="font-size: 0.72rem;"><?= format_date_id($c['created_at'], true) ?></small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm mb-1">
                                    <?php if ($c['status'] !== 'approved'): ?>
                                        <a href="<?= base_url('admin/comments.php?action=approve&id=' . $c['id'] . '&csrf=' . csrf_token() . '&status_filter=' . $statusFilter) ?>" class="btn btn-outline-success" title="Setujui (Tampilkan)">
                                            <i class="bi bi-check-lg"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($c['status'] !== 'pending'): ?>
                                        <a href="<?= base_url('admin/comments.php?action=pending&id=' . $c['id'] . '&csrf=' . csrf_token() . '&status_filter=' . $statusFilter) ?>" class="btn btn-outline-warning text-dark" title="Tangguhkan (Pending)">
                                            <i class="bi bi-pause"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($c['status'] !== 'spam'): ?>
                                        <a href="<?= base_url('admin/comments.php?action=spam&id=' . $c['id'] . '&csrf=' . csrf_token() . '&status_filter=' . $statusFilter) ?>" class="btn btn-outline-secondary" title="Tandai Spam">
                                            <i class="bi bi-shield-x"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Tombol Balas Komentar -->
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#replyModal<?= $c['id'] ?>" title="Balas sebagai Admin">
                                        <i class="bi bi-reply"></i>
                                    </button>

                                    <!-- Tombol Hapus -->
                                    <a href="<?= base_url('admin/comments.php?action=delete&id=' . $c['id'] . '&csrf=' . csrf_token() . '&status_filter=' . $statusFilter) ?>" class="btn btn-outline-danger" title="Hapus Permanen" onclick="return confirm('Hapus komentar ini secara permanen?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>

                                <!-- MODAL BALAS KOMENTAR -->
                                <div class="modal fade text-start" id="replyModal<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <form action="<?= base_url('admin/comments.php') ?>" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                <input type="hidden" name="action" value="reply">
                                                <input type="hidden" name="parent_id" value="<?= $c['id'] ?>">
                                                <input type="hidden" name="article_id" value="<?= $c['article_id'] ?>">

                                                <div class="modal-header border-bottom">
                                                    <h6 class="modal-title fw-bold">Balas Komentar: <?= $c['is_anonymous'] ? 'Anonim' : e($c['name']) ?></h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="p-3 bg-light rounded-3 mb-3 border small">
                                                        <strong>Komentar Pengguna:</strong><br>
                                                        "<?= e($c['comment']) ?>"
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-dark">Tulis Tanggapan Resmi Admin:</label>
                                                        <textarea class="form-control" name="reply_content" rows="4" placeholder="Ketik jawaban atau tanggapan resmi dari pihak sekolah di sini..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                                                        <i class="bi bi-send-fill me-1"></i> Kirim Balasan Admin
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-chat-square-dots fs-1 d-block mb-2 text-secondary"></i>
                            Tidak ada komentar yang sesuai dengan filter.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
            <small class="text-muted">Halaman <?= $page ?> dari <?= $totalPages ?></small>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php
                    $qp = $_GET;
                    $qp['page'] = $p;
                    ?>
                    <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= base_url('admin/comments.php?' . http_build_query($qp)) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
