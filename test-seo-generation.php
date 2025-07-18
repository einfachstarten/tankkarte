<?php
// Test SEO Generation directly
echo "Testing SEO Generation...\n";
echo "============================\n";

// Capture output from generator script executed in a subprocess
$output = shell_exec('php generate-seo-pages.php 2>&1');

echo "Generation Output:\n";
echo $output;
echo "\n============================\n";

// Check generated files
$seoDir = 'seo/';
if (is_dir($seoDir)) {
    $files = scandir($seoDir);
    $htmlFiles = array_filter($files, function($file) {
        return substr($file, -5) === '.html';
    });
    echo "Generated HTML files: " . count($htmlFiles) . "\n";
    foreach ($htmlFiles as $file) {
        echo "- $file\n";
    }
} else {
    echo "SEO directory not found!\n";
}
?>
