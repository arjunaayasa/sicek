<?php
// Gemini API Configuration
define('GEMINI_API_KEY', 'AIzaSyD2HOB0uF8CFOY8o71l8ikmQHWIYlROezw'); // Replace with your actual API key
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent');

// Application Configuration
define('APP_NAME', 'SiCek!');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/phisingdetector');

// Phishing Detection Configuration
define('DOMAIN_AGE_THRESHOLD', 3); // Domain age threshold in years
define('SIMILARITY_THRESHOLD', 80); // Domain similarity threshold (0-100)
define('MAX_HTML_SIZE', 3000); // Maximum HTML size to send to AI

// Tranco List Configuration
define('TRANCO_LIST_URL', 'https://tranco-list.eu/top-1m.csv');
define('TRANCO_LIST_PATH', __DIR__ . '/../data/tranco_list.json');
define('TRANCO_LIST_COUNT', 1000); // Number of top domains to use

// Scoring Configuration
define('SCORE_THRESHOLD_SAFE', 1); // Maximum score for safe status
define('SCORE_THRESHOLD_SUSPICIOUS', 2); // Maximum score for suspicious status
// Anything above SCORE_THRESHOLD_SUSPICIOUS is considered dangerous