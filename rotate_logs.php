<?php
// Run this monthly via cron: 0 0 1 * * php /path/to/rotate_logs.php

function rotateLogs() {
    $logDir = __DIR__ . '/logs/';
    $logFiles = ['contact_submissions.log', 'contact_errors.log', 'contact_spam.log'];
    
    foreach ($logFiles as $logFile) {
        $filePath = $logDir . $logFile;
        
        if (file_exists($filePath) && filesize($filePath) > 10 * 1024 * 1024) { // 10MB
            $archiveName = $logDir . date('Y-m') . '_' . $logFile;
            rename($filePath, $archiveName);
            touch($filePath); // Create new empty log file
            echo "Rotated: " . $logFile . " to " . $archiveName . "\n";
        }
    }
}

rotateLogs();
?>
