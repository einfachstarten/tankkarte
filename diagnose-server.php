<?php
echo "=== PHP SERVER DIAGNOSIS ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Working Directory: " . getcwd() . "\n\n";

echo "=== JSON EXTENSION STATUS ===\n";
echo "JSON Extension Loaded: " . (extension_loaded('json') ? 'YES' : 'NO') . "\n";
echo "json_decode exists: " . (function_exists('json_decode') ? 'YES' : 'NO') . "\n";
echo "json_encode exists: " . (function_exists('json_encode') ? 'YES' : 'NO') . "\n";

if (function_exists('json_decode')) {
    echo "JSON Test: ";
    $test = json_decode('{"test":"value"}', true);
    echo ($test && $test['test'] === 'value' ? 'WORKING' : 'BROKEN') . "\n";
} else {
    echo "JSON Test: CANNOT TEST - FUNCTION MISSING\n";
}

echo "\n=== LOADED EXTENSIONS ===\n";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    echo "- $ext\n";
}

echo "\n=== FILE SYSTEM ACCESS ===\n";
echo "Current Dir: " . getcwd() . "\n";
echo "translations.json exists: " . (file_exists('data/translations.json') ? 'YES' : 'NO') . "\n";

if (file_exists('data/translations.json')) {
    $size = filesize('data/translations.json');
    echo "translations.json size: $size bytes\n";
    
    $content = file_get_contents('data/translations.json');
    if ($content !== false) {
        echo "File read: SUCCESS\n";
        echo "Content preview: " . substr($content, 0, 100) . "...\n";
        
        if (function_exists('json_decode')) {
            $decoded = json_decode($content, true);
            echo "JSON decode: " . ($decoded ? 'SUCCESS' : 'FAILED') . "\n";
            if (!$decoded) {
                echo "JSON Error: " . json_last_error_msg() . "\n";
            }
        }
    } else {
        echo "File read: FAILED\n";
    }
}

echo "\n=== INCLUDE PATH ===\n";
echo get_include_path() . "\n";

echo "\n=== MEMORY & LIMITS ===\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n";
echo "Error Reporting: " . ini_get('error_reporting') . "\n";

echo "\n=== DIAGNOSIS COMPLETE ===\n";
?>
