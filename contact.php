<?php
// Ensure UTF-8 encoding
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Logging function
function logSubmission($type, $data, $message = '') {
    $logDir = __DIR__ . '/logs/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $referer = $_SERVER['HTTP_REFERER'] ?? 'direct';
    
    $logData = [
        'timestamp' => $timestamp,
        'ip' => $ip,
        'user_agent' => substr($userAgent, 0, 200), // Limit length
        'referer' => $referer,
        'type' => $type,
        'message' => $message,
        'data' => $data
    ];
    
    $logLine = json_encode($logData) . "\n";
    
    switch($type) {
        case 'success':
            file_put_contents($logDir . 'contact_submissions.log', $logLine, FILE_APPEND | LOCK_EX);
            break;
        case 'error':
        case 'validation_error':
            file_put_contents($logDir . 'contact_errors.log', $logLine, FILE_APPEND | LOCK_EX);
            break;
        case 'spam':
            file_put_contents($logDir . 'contact_spam.log', $logLine, FILE_APPEND | LOCK_EX);
            break;
        case 'request':
            // We could store requests separately if needed; for now append to submissions
            file_put_contents($logDir . 'contact_submissions.log', $logLine, FILE_APPEND | LOCK_EX);
            break;
    }
}

// Start logging request
$startTime = microtime(true);
logSubmission('request', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'post_data_size' => strlen(json_encode($_POST)),
    'has_files' => !empty($_FILES)
], 'Form request received');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logSubmission('error', ['method' => $_SERVER['REQUEST_METHOD']], 'Invalid request method');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$name = trim($_POST['name'] ?? '');
$company = trim($_POST['company'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');
$language = trim($_POST['language'] ?? 'de');

// Log submission data (sanitized)
$submissionData = [
    'name_length' => strlen($name),
    'company_length' => strlen($company),
    'email_domain' => substr(strrchr($email, '@'), 1),
    'phone_length' => strlen($phone),
    'message_length' => strlen($message),
    'language' => $language,
    'form_completion_time' => round(microtime(true) - $startTime, 3)
];

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Name ist erforderlich';
}

if (empty($company)) {
    $errors[] = 'Firma ist erforderlich';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Gültige E-Mail-Adresse ist erforderlich';
}

if (empty($phone)) {
    $errors[] = 'Telefonnummer ist erforderlich';
}

// Basic spam protection
if (strlen($message) > 2000) {
    $errors[] = 'Nachricht zu lang';
}

// Enhanced spam detection
$spam_words = ['viagra', 'casino', 'lottery', 'winner', 'click here', 'make money', 'free money'];
$content_check = strtolower($name . ' ' . $company . ' ' . $message);
$spam_detected = false;

foreach ($spam_words as $spam_word) {
    if (strpos($content_check, $spam_word) !== false) {
        $spam_detected = true;
        logSubmission('spam', array_merge($submissionData, [
            'spam_word' => $spam_word,
            'content_snippet' => substr($content_check, 0, 100)
        ]), 'Spam word detected: ' . $spam_word);
        break;
    }
}

// Additional spam checks
if (preg_match('/[а-я]/u', $content_check)) { // Cyrillic characters
    $spam_detected = true;
    logSubmission('spam', $submissionData, 'Cyrillic characters detected');
}

if (substr_count($content_check, 'http') > 2) { // Too many links
    $spam_detected = true;
    logSubmission('spam', $submissionData, 'Too many URLs detected');
}

if ($spam_detected) {
    $errors[] = 'Spam erkannt';
}

if (!empty($errors)) {
    logSubmission('validation_error', array_merge($submissionData, [
        'errors' => $errors
    ]), 'Validation failed');
    
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Prepare email
$to = 'o.gokceviran@rmc-service.com';
$subject = '[Filo Cards] Neue Anfrage von ' . $company . ' (' . $name . ')';

$email_body = "NEUE KONTAKTANFRAGE VON FILO.CARDS\n";
$email_body .= "==========================================\n\n";
$email_body .= "KUNDENDATEN:\n";
$email_body .= "Name: " . $name . "\n";
$email_body .= "Firma: " . $company . "\n";
$email_body .= "E-Mail: " . $email . "\n";
$email_body .= "Telefon: " . $phone . "\n";
$email_body .= "Sprache: " . strtoupper($language) . "\n\n";

if (!empty($message)) {
    $email_body .= "NACHRICHT:\n";
    $email_body .= "----------\n";
    $email_body .= $message . "\n\n";
}

$email_body .= "SYSTEM-INFO:\n";
$email_body .= "Gesendet am: " . date('d.m.Y H:i:s') . "\n";
$email_body .= "IP-Adresse: " . $_SERVER['REMOTE_ADDR'] . "\n";
$email_body .= "User-Agent: " . substr($_SERVER['HTTP_USER_AGENT'], 0, 100) . "\n";
$email_body .= "Website: https://www.filo.cards\n";

$headers = "From: Filo.Cards Kontaktformular <ftp7951508@www80.world4you.com>\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Return-Path: ftp7951508@www80.world4you.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: Filo Cards Website\r\n";

// Send email
if (mail($to, $subject, $email_body, $headers)) {
    logSubmission('success', array_merge($submissionData, [
        'email_sent' => true,
        'processing_time' => round(microtime(true) - $startTime, 3)
    ]), 'Email sent successfully');
    
    echo json_encode([
        'success' => true, 
        'message' => 'Vielen Dank f\u00fcr Ihre Anfrage! Wir melden uns schnellstm\u00f6glich bei Ihnen.'
    ]);
} else {
    logSubmission('error', array_merge($submissionData, [
        'email_sent' => false,
        'processing_time' => round(microtime(true) - $startTime, 3)
    ]), 'Failed to send email');
    
    echo json_encode([
        'success' => false, 
        'message' => 'Fehler beim Senden der E-Mail. Bitte versuchen Sie es sp\u00e4ter erneut.'
    ]);
}
?>
