<?php
// Alternative: Token im User-Agent oder Header
$secret = 'IchHebAbNichtsHaeltMichAmBoden';

// Check multiple sources
$token = $_GET['token'] ?? $_POST['token'] ?? $_SERVER['HTTP_X_TOKEN'] ?? null;

echo "DEBUG INFO:\n";
echo "GET token: " . ($_GET['token'] ?? 'NULL') . "\n";
echo "POST token: " . ($_POST['token'] ?? 'NULL') . "\n";
echo "Header token: " . ($_SERVER['HTTP_X_TOKEN'] ?? 'NULL') . "\n";
echo "Query string: " . ($_SERVER['QUERY_STRING'] ?? 'NULL') . "\n";

if ($token !== $secret) {
    http_response_code(403);
    exit('Forbidden');
}
echo "Token validation works!";
?>