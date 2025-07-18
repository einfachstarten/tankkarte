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
logMessage('Generating SEO landing pages...');
if (file_exists("$repoDir/generate-seo-pages.php")) {
    exec("cd $repoDir && php -d display_errors=0 generate-seo-pages.php 2>&1", $seoOutput, $seoReturn);
    if ($seoReturn === 0) {
        logMessage('SEO pages generated successfully: ' . implode(' | ', $seoOutput));
    } else {
        logMessage('Primary SEO generator failed, trying simple fallback...');
        if (file_exists("$repoDir/generate-seo-simple.php")) {
            exec("cd $repoDir && php generate-seo-simple.php 2>&1", $fallbackOutput, $fallbackReturn);
            if ($fallbackReturn === 0) {
                logMessage('Fallback SEO pages generated: ' . implode(' | ', $fallbackOutput));
            } else {
                logMessage('Both SEO generators failed: ' . implode(' | ', $fallbackOutput));
            }
        }
        logMessage('Deployment continues despite SEO generation issues');
    }
} else {
    logMessage('generate-seo-pages.php not found, skipping SEO generation');
}
?>
