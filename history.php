<?php
/**
 * Phishing Detector - History Page
 */

// Include required files
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Initialize database
initializeDatabase();

// Get database connection
$conn = getDbConnection();

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get total records
$totalQuery = "SELECT COUNT(*) as total FROM scan_logs";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $perPage);

// Get scan history
$sql = "SELECT * FROM scan_logs ORDER BY created_at DESC LIMIT $offset, $perPage";
$result = $conn->query($sql);
$history = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
}

// Get status class
function getStatusClass($status) {
    switch ($status) {
        case 'aman':
            return 'success';
        case 'curiga':
            return 'warning';
        case 'bahaya':
            return 'danger';
        default:
            return 'secondary';
    }
}

// Get status icon
function getStatusIcon($status) {
    switch ($status) {
        case 'aman':
            return 'check-circle';
        case 'curiga':
            return 'exclamation-triangle';
        case 'bahaya':
            return 'exclamation-circle';
        default:
            return 'question-circle';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemindaian - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container py-5">
        <header class="text-center mb-4">
            <h1 class="display-4"><?php echo APP_NAME; ?></h1>
            <p class="lead">Riwayat Pemindaian</p>
        </header>
        
        <div class="row justify-content-center mb-4">
            <div class="col-md-10">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="index.php" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
                    </a>
                    <div>
                        <span class="badge bg-secondary">Total: <?php echo $totalRecords; ?> pemindaian</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-md-10">
                <?php if (empty($history)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>Belum ada riwayat pemindaian.
                    </div>
                <?php else: ?>
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Domain</th>
                                            <th>Umur Domain</th>
                                            <th>SSL</th>
                                            <th>Login Form</th>
                                            <th>Redirect</th>
                                            <th>Skor</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($history as $item): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?php echo htmlspecialchars($item['url']); ?>" target="_blank" class="text-truncate d-inline-block" style="max-width: 150px;" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($item['url']); ?>">
                                                        <?php echo htmlspecialchars($item['domain']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php echo $item['domain_age_years'] > 0 ? htmlspecialchars($item['domain_age_years']) . ' tahun' : 'N/A'; ?>
                                                </td>
                                                <td>
                                                    <?php if ($item['ssl_valid']): ?>
                                                        <span class="badge bg-success"><i class="bi bi-check-lg"></i></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger"><i class="bi bi-x-lg"></i></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($item['has_login_form']): ?>
                                                        <span class="badge bg-warning"><i class="bi bi-check-lg"></i></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><i class="bi bi-x-lg"></i></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($item['has_redirect']): ?>
                                                        <span class="badge bg-warning"><i class="bi bi-check-lg"></i></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><i class="bi bi-x-lg"></i></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo getStatusClass($item['status']); ?>">
                                                        <?php echo $item['score']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo getStatusClass($item['status']); ?>">
                                                        <i class="bi bi-<?php echo getStatusIcon($item['status']); ?> me-1"></i>
                                                        <?php echo ucfirst(htmlspecialchars($item['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <a href="detail.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0"><?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?> &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>