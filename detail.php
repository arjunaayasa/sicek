<?php
/**
 * Phishing Detector - Detail Page
 */

// Include required files
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Initialize database
initializeDatabase();

// Get database connection
$conn = getDbConnection();

// Get scan detail
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$scan = null;
$error = null;

if ($id > 0) {
    $sql = "SELECT * FROM scan_logs WHERE id = $id LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $scan = $result->fetch_assoc();
    } else {
        $error = 'Data pemindaian tidak ditemukan';
    }
} else {
    $error = 'ID pemindaian tidak valid';
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
    <title>Detail Pemindaian - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container py-5">
        <header class="text-center mb-4">
            <h1 class="display-4"><?php echo APP_NAME; ?></h1>
            <p class="lead">Detail Pemindaian</p>
        </header>
        
        <div class="row justify-content-center mb-4">
            <div class="col-md-10">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="history.php" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Riwayat
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-house me-2"></i>Beranda
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-md-10">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php elseif ($scan): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-<?php echo getStatusClass($scan['status']); ?> text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">
                                    <i class="bi bi-<?php echo getStatusIcon($scan['status']); ?> me-2"></i>
                                    Status: <?php echo ucfirst(htmlspecialchars($scan['status'])); ?>
                                </h3>
                                <span class="badge bg-white text-<?php echo getStatusClass($scan['status']); ?> fs-5">
                                    Skor: <?php echo $scan['score']; ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h4>Informasi Website</h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="30%">URL</th>
                                            <td>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <a href="<?php echo htmlspecialchars($scan['url']); ?>" target="_blank">
                                                        <?php echo htmlspecialchars($scan['url']); ?>
                                                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-secondary btn-copy" data-copy-text="<?php echo htmlspecialchars($scan['url']); ?>">
                                                        <i class="bi bi-clipboard me-1"></i>Salin
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Domain</th>
                                            <td><?php echo htmlspecialchars($scan['domain']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Pemindaian</th>
                                            <td><?php echo date('d F Y H:i:s', strtotime($scan['created_at'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Umur Domain</th>
                                            <td>
                                                <?php echo $scan['domain_age_years'] > 0 ? htmlspecialchars($scan['domain_age_years']) . ' tahun' : 'Tidak diketahui'; ?>
                                                <?php if ($scan['domain_age_years'] < DOMAIN_AGE_THRESHOLD): ?>
                                                    <span class="badge bg-warning ms-2">Domain baru</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h4>Hasil Deteksi</h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="30%">SSL Valid</th>
                                            <td>
                                                <?php if ($scan['ssl_valid']): ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-success me-2"><i class="bi bi-check-lg"></i></span>
                                                        <span>Website menggunakan sertifikat SSL yang valid</span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-danger me-2"><i class="bi bi-x-lg"></i></span>
                                                        <span>Website tidak menggunakan sertifikat SSL yang valid</span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Form Login</th>
                                            <td>
                                                <?php if ($scan['has_login_form']): ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-warning me-2"><i class="bi bi-check-lg"></i></span>
                                                        <span>Ditemukan form login pada website</span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-secondary me-2"><i class="bi bi-x-lg"></i></span>
                                                        <span>Tidak ditemukan form login pada website</span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Redirect Mencurigakan</th>
                                            <td>
                                                <?php if ($scan['has_redirect']): ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-warning me-2"><i class="bi bi-check-lg"></i></span>
                                                        <span>Ditemukan redirect mencurigakan pada website</span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-secondary me-2"><i class="bi bi-x-lg"></i></span>
                                                        <span>Tidak ditemukan redirect mencurigakan pada website</span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Kemiripan Domain</th>
                                            <td>
                                                <?php if ($scan['similarity_score'] > SIMILARITY_THRESHOLD): ?>
                                                    <div class="alert alert-warning mb-0 py-2">
                                                        Domain ini mirip dengan <strong><?php echo htmlspecialchars($scan['similarity_domain']); ?></strong> 
                                                        (<?php echo round($scan['similarity_score'], 1); ?>%)
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-secondary me-2"><i class="bi bi-x-lg"></i></span>
                                                        <span>Tidak ada kemiripan signifikan dengan domain populer</span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h4>Analisis AI</h4>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <?php if (strpos($scan['ai_analysis'], 'PHISHING: ya') !== false): ?>
                                                <span class="badge bg-danger mb-2"><i class="bi bi-robot me-1"></i>Terdeteksi sebagai Phishing</span>
                                            <?php else: ?>
                                                <span class="badge bg-success mb-2"><i class="bi bi-robot me-1"></i>Terdeteksi sebagai Aman</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ai-explanation">
                                            <?php echo nl2br(htmlspecialchars($scan['ai_analysis'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <div class="score-display mb-3">
                                    <span class="score-value bg-<?php echo getStatusClass($scan['status']); ?>">
                                        <?php echo $scan['score']; ?>
                                    </span>
                                    <span class="score-label">Skor Phishing</span>
                                </div>
                                <p class="mb-0">
                                    <?php if ($scan['status'] === 'aman'): ?>
                                        <i class="bi bi-shield-check text-success me-2"></i>Website ini terlihat aman untuk digunakan.
                                    <?php elseif ($scan['status'] === 'curiga'): ?>
                                        <i class="bi bi-shield-exclamation text-warning me-2"></i>Website ini mencurigakan. Berhati-hatilah saat memberikan informasi pribadi.
                                    <?php else: ?>
                                        <i class="bi bi-shield-x text-danger me-2"></i>Website ini kemungkinan besar adalah phishing. Jangan berikan informasi pribadi apapun!
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        <a href="index.php" class="btn btn-primary me-2">
                            <i class="bi bi-search me-2"></i>Periksa URL Lain
                        </a>
                        <a href="history.php" class="btn btn-outline-secondary">
                            <i class="bi bi-clock-history me-2"></i>Lihat Riwayat
                        </a>
                    </div>
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