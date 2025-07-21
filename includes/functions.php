<?php
/**
 * Functions for Phishing Detection
 */

/**
 * Check domain age using WHOIS
 * 
 * @param string $domain Domain to check
 * @return float Domain age in years or 0 if not found
 */
function getDomainAge($domain) {
    try {
        $whoisData = shell_exec("whois {$domain}");
        
        // Extract creation date from WHOIS data
        $pattern = '/Creation Date:\s*(\d{4}-\d{2}-\d{2})/i';
        if (preg_match($pattern, $whoisData, $matches)) {
            $creationDate = strtotime($matches[1]);
            $currentDate = time();
            $ageInSeconds = $currentDate - $creationDate;
            $ageInYears = $ageInSeconds / (60 * 60 * 24 * 365.25);
            return round($ageInYears, 1);
        }
        
        // Try alternative pattern
        $pattern = '/created.*?:\s*(\d{4}-\d{2}-\d{2})/i';
        if (preg_match($pattern, $whoisData, $matches)) {
            $creationDate = strtotime($matches[1]);
            $currentDate = time();
            $ageInSeconds = $currentDate - $creationDate;
            $ageInYears = $ageInSeconds / (60 * 60 * 24 * 365.25);
            return round($ageInYears, 1);
        }
        
        return 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Check SSL certificate validity
 * 
 * @param string $domain Domain to check
 * @return bool True if SSL is valid, false otherwise
 */
function checkSSLValidity($domain) {
    try {
        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        $socket = @stream_socket_client("ssl://{$domain}:443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        
        if (!$socket) {
            return false;
        }
        
        $params = stream_context_get_params($socket);
        $cert = $params['options']['ssl']['peer_certificate'];
        
        if (!$cert) {
            return false;
        }
        
        $certInfo = openssl_x509_parse($cert);
        $validTo = $certInfo['validTo_time_t'];
        
        // Check if certificate is still valid
        return time() < $validTo;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Check if HTML contains login form
 * 
 * @param string $html HTML content
 * @return bool True if login form is found, false otherwise
 */
function hasLoginForm($html) {
    // Check for password input field
    $hasPasswordField = preg_match('/<input[^>]*type=["\']password["\'][^>]*>/i', $html);
    
    // Check for form tag
    $hasFormTag = preg_match('/<form[^>]*>/i', $html);
    
    return $hasPasswordField && $hasFormTag;
}

/**
 * Check if HTML contains suspicious redirects
 * 
 * @param string $html HTML content
 * @return bool True if suspicious redirect is found, false otherwise
 */
function hasSuspiciousRedirect($html) {
    // Check for meta refresh tag
    $hasMetaRefresh = preg_match('/<meta[^>]*http-equiv=["\']refresh["\'][^>]*>/i', $html);
    
    // Check for JavaScript redirects
    $hasJsRedirect = preg_match('/window\.location|location\.href|location\.replace/i', $html);
    
    return $hasMetaRefresh || $hasJsRedirect;
}

/**
 * Check domain similarity with popular domains
 * 
 * @param string $domain Domain to check
 * @return array Array with most similar domain and similarity score
 */
function checkDomainSimilarity($domain) {
    $result = [
        'similar_domain' => '',
        'similarity_score' => 0
    ];
    
    // Get Tranco list
    $trancoDomains = getTrancoDomains();
    
    if (empty($trancoDomains)) {
        return $result;
    }
    
    $highestSimilarity = 0;
    $mostSimilarDomain = '';
    
    foreach ($trancoDomains as $popularDomain) {
        similar_text($domain, $popularDomain, $percent);
        
        if ($percent > $highestSimilarity && $percent > SIMILARITY_THRESHOLD) {
            $highestSimilarity = $percent;
            $mostSimilarDomain = $popularDomain;
        }
    }
    
    $result['similar_domain'] = $mostSimilarDomain;
    $result['similarity_score'] = $highestSimilarity;
    
    return $result;
}

/**
 * Get Tranco domains list
 * 
 * @return array Array of popular domains
 */
function getTrancoDomains() {
    // Check if we have a cached version
    if (file_exists(TRANCO_LIST_PATH)) {
        $data = json_decode(file_get_contents(TRANCO_LIST_PATH), true);
        if (!empty($data)) {
            return $data;
        }
    }
    
    // Create data directory if it doesn't exist
    $dataDir = dirname(TRANCO_LIST_PATH);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // Download and parse Tranco list
    try {
        $ch = curl_init(TRANCO_LIST_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $csv = curl_exec($ch);
        curl_close($ch);
        
        if (!$csv) {
            return [];
        }
        
        $lines = explode("\n", $csv);
        $domains = [];
        $count = 0;
        
        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            $parts = explode(",", $line);
            if (isset($parts[1])) {
                $domains[] = $parts[1];
                $count++;
                
                if ($count >= TRANCO_LIST_COUNT) {
                    break;
                }
            }
        }
        
        // Cache the domains
        file_put_contents(TRANCO_LIST_PATH, json_encode($domains));
        
        return $domains;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Analyze HTML content using Gemini AI
 * 
 * @param string $html HTML content
 * @return array Array with analysis result and explanation
 */
function analyzeWithAI($url, $html) {
    $result = [
        'is_phishing' => false,
        'explanation' => 'AI analysis not available'
    ];
    
    // Limit HTML size
    $html_subset = substr($html, 0, MAX_HTML_SIZE);

    // Prepare prompt for Gemini
    $prompt = "Tolong tinjau link berikut dan tentukan apakah itu phishing atau bukan, berdasarkan cuplikan HTML ini. Berikan analisis Anda dalam format 'PHISHING: [ya/tidak]' diikuti dengan penjelasan singkat.\n\nLink: {$url}\n\nHTML Snippet:\n{$html_subset}";
    
    // Prepare prompt for Gemini
    $prompt = "Tolong tinjau link berikut dan tentukan apakah itu phishing atau bukan. Berikan analisis Anda dalam format 'PHISHING: [ya/tidak]' diikuti dengan penjelasan singkat.\n\nLink: " . $url;

    
    // Prepare request data
    $data = [
        'contents' => [
            [
                'parts' => [
                    [
                        'text' => $prompt
                    ]
                ]
            ]
        ]
    ];
    
    // Make API request
    try {
        $ch = curl_init(GEMINI_API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . GEMINI_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("Gemini API Error: " . $error);
            return $result;
        }
        
        if ($httpCode !== 200) {
            error_log("Gemini API HTTP Error: " . $httpCode . " Response: " . $response);
            return $result;
        }
        
        $responseData = json_decode($response, true);
        if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            error_log("Gemini API Invalid Response Format: " . $response);
            return $result;
        }
        
        $aiResponse = $responseData['candidates'][0]['content']['parts'][0]['text'];
        
        // Parse AI response
        if (preg_match('/PHISHING:\s*(ya|tidak)/i', $aiResponse, $matches)) {
            $result['is_phishing'] = strtolower($matches[1]) === 'ya';
            $result['explanation'] = trim(str_replace($matches[0], '', $aiResponse));
        } else {
            $result['explanation'] = $aiResponse;
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("AI Analysis Error: " . $e->getMessage());
        return $result;
    }
}

/**
 * Calculate phishing score based on various checks
 * 
 * @param array $checks Array of check results
 * @return array Array with score and status
 */
function calculatePhishingScore($checks) {
    $score = 0;
    
    // Domain age check
    if ($checks['domain_age'] < DOMAIN_AGE_THRESHOLD) {
        $score++;
    }
    
    // SSL validity check
    if (!$checks['ssl_valid']) {
        $score++;
    }
    
    // Login form check
    if ($checks['has_login_form']) {
        $score++;
    }
    
    // Redirect check
    if ($checks['has_redirect']) {
        $score++;
    }
    
    // Domain similarity check
    if ($checks['similarity_score'] > SIMILARITY_THRESHOLD) {
        $score++;
    }
    
    // AI analysis check
    if ($checks['ai_is_phishing']) {
        $score++;
    }
    
    // Determine status
    $status = 'aman';
    if ($score > SCORE_THRESHOLD_SUSPICIOUS) {
        $status = 'bahaya';
    } else if ($score > SCORE_THRESHOLD_SAFE) {
        $status = 'curiga';
    }
    
    return [
        'score' => $score,
        'status' => $status
    ];
}

/**
 * Save scan result to database
 * 
 * @param array $data Scan data
 * @param mysqli $conn Database connection
 * @return bool True if saved successfully, false otherwise
 */
function saveScanResult($data, $conn) {
    $url = $conn->real_escape_string($data['url']);
    $domain = $conn->real_escape_string($data['domain']);
    $domainAge = floatval($data['domain_age']);
    $sslValid = $data['ssl_valid'] ? 1 : 0;
    $hasLoginForm = $data['has_login_form'] ? 1 : 0;
    $hasRedirect = $data['has_redirect'] ? 1 : 0;
    $similarityDomain = $conn->real_escape_string($data['similarity_domain']);
    $similarityScore = floatval($data['similarity_score']);
    $aiAnalysis = $conn->real_escape_string($data['ai_explanation']);
    $score = intval($data['score']);
    $status = $conn->real_escape_string($data['status']);
    
    $sql = "INSERT INTO scan_logs (url, domain, domain_age_years, ssl_valid, has_login_form, has_redirect, similarity_domain, similarity_score, ai_analysis, score, status) 
            VALUES ('$url', '$domain', $domainAge, $sslValid, $hasLoginForm, $hasRedirect, '$similarityDomain', $similarityScore, '$aiAnalysis', $score, '$status')";
    
    return $conn->query($sql);
}

/**
 * Get domain from URL
 * 
 * @param string $url URL to extract domain from
 * @return string Domain name
 */
function getDomainFromUrl($url) {
    // Add scheme if missing
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        $url = 'http://' . $url;
    }
    
    $parsedUrl = parse_url($url);
    $domain = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
    
    // Remove www. prefix if present
    if (substr($domain, 0, 4) === 'www.') {
        $domain = substr($domain, 4);
    }
    
    return $domain;
}

/**
 * Fetch HTML content from URL
 * 
 * @param string $url URL to fetch
 * @return string HTML content
 */
function fetchHtmlContent($url) {
    // Add scheme if missing
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        $url = 'http://' . $url;
    }
    
    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        
        $html = curl_exec($ch);
        curl_close($ch);
        
        return $html ?: '';
    } catch (Exception $e) {
        return '';
    }
}