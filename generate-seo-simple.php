<?php
// Minimal SEO Page Generator - No Dependencies
@error_reporting(0);
@ini_set('display_errors', 0);

$outputDir = 'seo/';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$pages = [
    'tankkarte-europa-de.html' => [
        'title' => 'Tankkarte Europa - Günstiger Diesel für LKW Flotten',
        'description' => 'Europäische Tankkarte für Unternehmen. Günstiger Diesel, 19 Länder, keine Gebühren.',
        'content' => '<h1>Tankkarte Europa</h1><p>Günstig tanken in 19 europäischen Ländern. Keine Gebühren, attraktive Preise für Unternehmen.</p>'
    ],
    'maut-europa-lkw-de.html' => [
        'title' => 'LKW Maut Europa - EETS Mautbox für alle Länder',
        'description' => 'LKW Maut Europa aus einer Hand. EETS-kompatibel für 17 Länder.',
        'content' => '<h1>LKW Maut Europa</h1><p>Ein Anbieter für 17 Länder. EETS-System für einfache Mautabrechnung.</p>'
    ]
];

foreach ($pages as $filename => $data) {
    $html = <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>{$data['title']}</title>
    <meta name="description" content="{$data['description']}">
    <meta http-equiv="refresh" content="3;url=https://www.filo.cards/">
    <style>body{font-family:Arial,sans-serif;max-width:800px;margin:0 auto;padding:20px}</style>
</head>
<body>
    <div style="background:#1B5E20;color:white;padding:15px;border-radius:8px;margin-bottom:30px">
        ⏱️ Sie werden automatisch zur Hauptseite weitergeleitet.
        <a href="https://www.filo.cards/" style="color:#FFD600">Direkt zur Seite</a>
    </div>
    {$data['content']}
    <script>window.location.href='https://www.filo.cards/';</script>
</body>
</html>
HTML;

    file_put_contents($outputDir . $filename, $html);
    echo "Generated: $filename\n";
}

echo "Simple SEO pages generated successfully!\n";
?>
