<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/notifikasi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    setFlashMessage('error', 'Anda harus login terlebih dahulu');
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Proses mark as read
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notification_id = $_GET['mark_read'];
    if (markNotificationAsRead($conn, $notification_id)) {
        setFlashMessage('success', 'Notifikasi telah ditandai sebagai dibaca');
    }
    header('Location: notifikasi.php');
    exit;
}

// Proses mark all as read
if (isset($_GET['mark_all_read'])) {
    if (markAllNotificationsAsRead($conn, $user_id)) {
        setFlashMessage('success', 'Semua notifikasi telah ditandai sebagai dibaca');
    }
    header('Location: notifikasi.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get notifications
$notifications = getAllNotifications($conn, $user_id, $limit, $offset);

// Count total notifications for pagination
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM notifikasi WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_notifications = $result->fetch_assoc()['total'];
$total_pages = ceil($total_notifications / $limit);

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Notifikasi</h2>
                <?php if ($notifications->num_rows > 0): ?>
                <a href="notifikasi.php?mark_all_read=1" class="btn btn-outline-primary">
                    <i class="fas fa-check-double me-2"></i>Tandai Semua Dibaca
                </a>
                <?php endif; ?>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <?php if ($notifications->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($notification = $notifications->fetch_assoc()): ?>
                                <div class="list-group-item list-group-item-action py-3 <?= $notification['is_read'] ? '' : 'bg-light' ?>">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <?php if ($notification['type'] == 'overdue'): ?>
                                                <div class="badge bg-danger mb-2">Terlambat</div>
                                            <?php elseif ($notification['type'] == 'reminder'): ?>
                                                <div class="badge bg-warning text-dark mb-2">Pengingat</div>
                                            <?php else: ?>
                                                <div class="badge bg-info mb-2">Info</div>
                                            <?php endif; ?>
                                            
                                            <h6 class="mb-1">
                                                <?php if (!empty($notification['judul_buku'])): ?>
                                                    <a href="detail_buku.php?id=<?= $notification['buku_id'] ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($notification['judul_buku']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    Notifikasi Sistem
                                                <?php endif; ?>
                                            </h6>
                                            <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                                            <small class="text-muted">
                                                <?= date('d M Y H:i', strtotime($notification['created_at'])) ?>
                                            </small>
                                        </div>
                                        
                                        <div class="d-flex">
                                            <?php if (!$notification['is_read']): ?>
                                                <a href="notifikasi.php?mark_read=<?= $notification['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($notification['peminjaman_id'])): ?>
                                                <a href="peminjaman_saya.php" class="btn btn-sm btn-primary ms-2">
                                                    <i class="fas fa-eye me-1"></i>Lihat Peminjaman
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="d-flex justify-content-center mt-4">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="notifikasi.php?page=<?= $page - 1 ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link" href="notifikasi.php?page=<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="notifikasi.php?page=<?= $page + 1 ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="text-center py-5">
                            <img src="assets/img/empty-state.svg" alt="No notifications" class="img-fluid mb-3" style="max-width: 200px;">
                            <h5>Tidak Ada Notifikasi</h5>
                            <p class="text-muted">Anda tidak memiliki notifikasi saat ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>