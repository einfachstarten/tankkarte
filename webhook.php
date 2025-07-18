<?php
// Simple deployment webhook with SEO page generation fallback
$repoDir = __DIR__;
$logFile = __DIR__ . '/webhook.log';

function logMessage($message) {
    global $logFile;
    $line = date('Y-m-d H:i:s') . ' - ' . $message . "\n";
    file_put_contents($logFile, $line, FILE_APPEND);
}

logMessage('Webhook triggered');

// Pull latest changes
exec("git -C $repoDir pull 2>&1", $gitOutput, $gitReturn);
logMessage('Git pull exit ' . $gitReturn . ': ' . implode(' | ', $gitOutput));

$deploymentTime = time();
$gitHash = substr(exec("git -C $repoDir rev-parse HEAD"), 0, 8);
$version = $deploymentTime . '-' . $gitHash;

$cssTime = filemtime("$repoDir/css/styles.css");
$jsTransTime = filemtime("$repoDir/js/translations.js");
$jsMainTime = filemtime("$repoDir/js/main.js");
$featureFlagsTime = filemtime("$repoDir/js/feature-flags.js");
$chatIntegrationTime = filemtime("$repoDir/js/chat-integration.js");

$template = file_get_contents("$repoDir/index.template.html");
$html = str_replace(
    [
        'href="css/styles.css"',
        'src="js/translations.js"',
        'src="js/main.js"',
        'src="js/feature-flags.js"',
        'src="js/chat-integration.js"',
        'data-version=""'
    ],
    [
        'href="css/styles.css?v=' . $cssTime . '"',
        'src="js/translations.js?v=' . $jsTransTime . '"',
        'src="js/main.js?v=' . $jsMainTime . '"',
        'src="js/feature-flags.js?v=' . $featureFlagsTime . '"',
        'src="js/chat-integration.js?v=' . $chatIntegrationTime . '"',
        'data-version="' . $version . '"'
    ],
    $template
);
file_put_contents("$repoDir/index.html", $html);
logMessage('index.html regenerated');

// SEO Landing Pages Generation with Fallback
// SEO Landing Pages Generation - Robust Approach
logMessage('Generating SEO landing pages...');

// Try diagnosis first if debug mode
if (file_exists("$repoDir/diagnose-server.php") && isset($_GET['debug'])) {
    exec("cd $repoDir && php diagnose-server.php 2>&1", $diagOutput, $diagReturn);
    logMessage('Server diagnosis: ' . implode(' | ', $diagOutput));
}

// Try robust generator first
if (file_exists("$repoDir/generate-seo-pages-robust.php")) {
    exec("cd $repoDir && php generate-seo-pages-robust.php 2>&1", $robustOutput, $robustReturn);

    if ($robustReturn === 0) {
        logMessage('Robust SEO pages generated: ' . implode(' | ', $robustOutput));
    } else {
        logMessage('Robust SEO generator failed: ' . implode(' | ', $robustOutput));

        // Fallback to simple generator
        if (file_exists("$repoDir/generate-seo-simple.php")) {
            exec("cd $repoDir && php generate-seo-simple.php 2>&1", $fallbackOutput, $fallbackReturn);
            if ($fallbackReturn === 0) {
                logMessage('Fallback SEO pages generated: ' . implode(' | ', $fallbackOutput));
            }
        }
    }
} else {
    logMessage('Robust SEO generator not found, skipping SEO generation');
}
?>
