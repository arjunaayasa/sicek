<?php
/**
 * Phishing Detector - Main Page
 */

// Include required files
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Initialize database
initializeDatabase();

// Process form submission
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = trim($_POST['url']);
    
    if (empty($url)) {
        $error = 'URL tidak boleh kosong';
    } else {
        // Get domain from URL
        $domain = getDomainFromUrl($url);
        
        if (empty($domain)) {
            $error = 'URL tidak valid';
        } else {
            // Fetch HTML content
            $html = fetchHtmlContent($url);
            
            if (empty($html)) {
                $error = 'Tidak dapat mengakses URL';
            } else {
                // Perform checks
                $domainAge = getDomainAge($domain);
                $sslValid = checkSSLValidity($domain);
                $hasLoginForm = hasLoginForm($html);
                $hasRedirect = hasSuspiciousRedirect($html);
                $similarityResult = checkDomainSimilarity($domain);
                $aiResult = analyzeWithAI($url, $html);
                
                // Calculate score
                $checks = [
                    'domain_age' => $domainAge,
                    'ssl_valid' => $sslValid,
                    'has_login_form' => $hasLoginForm,
                    'has_redirect' => $hasRedirect,
                    'similarity_score' => $similarityResult['similarity_score'],
                    'ai_is_phishing' => $aiResult['is_phishing']
                ];
                
                $scoreResult = calculatePhishingScore($checks);
                
                // Prepare result
                $result = [
                    'url' => $url,
                    'domain' => $domain,
                    'domain_age' => $domainAge,
                    'ssl_valid' => $sslValid,
                    'has_login_form' => $hasLoginForm,
                    'has_redirect' => $hasRedirect,
                    'similarity_domain' => $similarityResult['similar_domain'],
                    'similarity_score' => $similarityResult['similarity_score'],
                    'ai_is_phishing' => $aiResult['is_phishing'],
                    'ai_explanation' => $aiResult['explanation'],
                    'score' => $scoreResult['score'],
                    'status' => $scoreResult['status']
                ];
                
                // Save result to database
                $conn = getDbConnection();
                saveScanResult($result, $conn);
            }
        }
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
    <title><?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">


</head>
<body>
    <div class="container py-5">
        <header class="text-center mb-5">
            <h1 class="display-4" style="font-family: 'Poppins', sans-serif; font-weight: 700;">
    <?php echo APP_NAME; ?>
</h1>
            <p class="lead">Deteksi website phishing dengan teknologi AI</p>
        </header>
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="post" action="" class="mb-0">
                            <div class="input-group">
                                <input type="text" name="url" class="form-control form-control-lg" placeholder="Masukkan URL website" value="<?php echo isset($_POST['url']) ? htmlspecialchars($_POST['url']) : ''; ?>" required>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-search me-2"></i>Periksa
                                </button>
                            </div>
                            <?php if ($error): ?>
                                <div class="alert alert-danger mt-3 mb-0">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <?php if ($result): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-<?php echo getStatusClass($result['status']); ?> text-white">
                            <h3 class="card-title mb-0">
                                <i class="bi bi-<?php echo getStatusIcon($result['status']); ?> me-2"></i>
                                Status: <?php echo ucfirst(htmlspecialchars($result['status'])); ?>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h4>Informasi Website</h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="30%">URL</th>
                                            <td><?php echo htmlspecialchars($result['url']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Domain</th>
                                            <td><?php echo htmlspecialchars($result['domain']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Umur Domain</th>
                                            <td>
                                                <?php echo $result['domain_age'] > 0 ? htmlspecialchars($result['domain_age']) . ' tahun' : 'Tidak diketahui'; ?>
                                                <?php if ($result['domain_age'] < DOMAIN_AGE_THRESHOLD): ?>
                                                    <span class="badge bg-warning ms-2">Domain baru</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>SSL Valid</th>
                                            <td>
                                                <?php if ($result['ssl_valid']): ?>
                                                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Ya</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"><i class="bi bi-x-lg me-1"></i>Tidak</span>
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
                                            <th width="30%">Form Login</th>
                                            <td>
                                                <?php if ($result['has_login_form']): ?>
                                                    <span class="badge bg-warning"><i class="bi bi-check-lg me-1"></i>Ditemukan</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i class="bi bi-x-lg me-1"></i>Tidak ditemukan</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Redirect Mencurigakan</th>
                                            <td>
                                                <?php if ($result['has_redirect']): ?>
                                                    <span class="badge bg-warning"><i class="bi bi-check-lg me-1"></i>Ditemukan</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i class="bi bi-x-lg me-1"></i>Tidak ditemukan</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Kemiripan Domain</th>
                                            <td>
                                                <?php if ($result['similarity_score'] > SIMILARITY_THRESHOLD): ?>
                                                    <div class="alert alert-warning mb-0 py-2">
                                                        Mirip dengan <strong><?php echo htmlspecialchars($result['similarity_domain']); ?></strong> 
                                                        (<?php echo round($result['similarity_score'], 1); ?>%)
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Tidak ada kemiripan signifikan</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Analisis AI</th>
                                            <td>
                                                <?php if ($result['ai_is_phishing']): ?>
                                                    <span class="badge bg-danger mb-2"><i class="bi bi-robot me-1"></i>Terdeteksi sebagai Phishing</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success mb-2"><i class="bi bi-robot me-1"></i>Terdeteksi sebagai Aman</span>
                                                <?php endif; ?>
                                                <div class="mt-2">
                                                    <?php echo nl2br(htmlspecialchars($result['ai_explanation'])); ?>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <div class="score-display mb-3">
                                    <span class="score-value bg-<?php echo getStatusClass($result['status']); ?>">
                                        <?php echo $result['score']; ?>
                                    </span>
                                    <span class="score-label">Skor Phishing</span>
                                </div>
                                <p class="mb-0">
                                    <?php if ($result['status'] === 'aman'): ?>
                                        <i class="bi bi-shield-check text-success me-2"></i>Website ini terlihat aman untuk digunakan.
                                    <?php elseif ($result['status'] === 'curiga'): ?>
                                        <i class="bi bi-shield-exclamation text-warning me-2"></i>Website ini mencurigakan. Berhati-hatilah saat memberikan informasi pribadi.
                                    <?php else: ?>
                                        <i class="bi bi-shield-x text-danger me-2"></i>Website ini kemungkinan besar adalah phishing. Jangan berikan informasi pribadi apapun!
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
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