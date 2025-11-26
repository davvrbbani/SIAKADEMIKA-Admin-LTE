<?php
// Pastikan file config.php di-load
// Path '../' mengasumsikan file ini ada di dalam folder 'Admin'
require_once '../config.php'; 
require_login(); // Memastikan user sudah login

// (SANGAT DISARANKAN) 
// Sebaiknya hanya admin yang bisa melihat semua log.
// Jika kamu belum punya fungsi require_admin(), tambahkan ini di config.php:
// function require_admin() {
//     if (!is_admin()) {
//         echo "Akses ditolak."; // atau redirect
//         exit;
//     }
// }
// require_admin(); // Lalu panggil di sini

// --- LOGIKA PAGINASI (DAFTAR HALAMAN) ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 25; // Tampilkan 25 log per halaman
$offset = ($page - 1) * $limit;

try {
    // 1. Ambil total log untuk paginasi
    $total_stmt = $pdo->query("SELECT COUNT(*) FROM activity_logs");
    $total_logs = $total_stmt->fetchColumn();
    $total_pages = ceil($total_logs / $limit);

    // 2. Ambil data log untuk halaman ini (JOIN dengan tabel users)
    $log_query = "SELECT al.action_message, al.created_at, u.username 
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  ORDER BY al.created_at DESC
                  LIMIT :limit OFFSET :offset";
    
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $log_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $log_stmt->execute();
    
    $activity_logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error mengambil data log: " . $e->getMessage());
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0"><i class="bi bi-clock-history me-2">Semua Aktivitas Log</i></h3>
            </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="?p=dashboard">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Activity Log</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Menampilkan <?php echo count($activity_logs); ?> dari <?php echo $total_logs; ?> total log</h3>
                        </div>
                        
                        <div class="card-body p-0">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 20%;">User (Admin)</th>
                                        <th style="width: 50%;">Aksi yang Dilakukan</th>
                                        <th style="width: 30%;">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($activity_logs)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Belum ada aktivitas.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($activity_logs as $log): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($log['username'] ?: 'System'); ?></strong>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($log['action_message']); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars(time_elapsed_string($log['created_at'])); ?>
                                                    <small class="d-block text-muted"><?php echo htmlspecialchars($log['created_at']); ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_pages > 1): ?>
                        <div class="card-footer">
                            <nav>
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?p=activity&page=<?php echo $page - 1; ?>">Previous</a>
                                    </li>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?p=activity&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?p=activity&page=<?php echo $page + 1; ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>