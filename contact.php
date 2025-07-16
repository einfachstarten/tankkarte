<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

// Check for spam patterns
$spam_words = ['viagra', 'casino', 'lottery', 'winner', 'click here'];
$content_check = strtolower($name . ' ' . $company . ' ' . $message);
foreach ($spam_words as $spam_word) {
    if (strpos($content_check, $spam_word) !== false) {
        $errors[] = 'Spam erkannt';
        break;
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Prepare email
$to = 'marcusbraun@outlook.com';
$subject = 'Neue Anfrage von filo.cards - ' . $company;

$email_body = "Neue Kontaktanfrage von der filo.cards Website\n\n";
$email_body .= "Name: " . $name . "\n";
$email_body .= "Firma: " . $company . "\n";
$email_body .= "E-Mail: " . $email . "\n";
$email_body .= "Telefon: " . $phone . "\n";
$email_body .= "Sprache: " . $language . "\n\n";
$email_body .= "Nachricht:\n" . $message . "\n\n";
$email_body .= "---\n";
$email_body .= "Gesendet am: " . date('d.m.Y H:i:s') . "\n";
$email_body .= "IP-Adresse: " . $_SERVER['REMOTE_ADDR'] . "\n";

$headers = "From: noreply@filo.cards\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Send email
if (mail($to, $subject, $email_body, $headers)) {
    echo json_encode([
        'success' => true,
        'message' => 'Vielen Dank f\xc3\xbcr Ihre Anfrage! Wir melden uns schnellstm\xc3\xb6glich bei Ihnen.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Senden der E-Mail. Bitte versuchen Sie es sp\xc3\xa4ter erneut.'
    ]);
}
?>
