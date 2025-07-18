<?php
function loadTranslations($file = 'data/translations.json') {
    if (!file_exists($file)) {
        throw new Exception("Translations file not found: {$file}");
    }
    $json = file_get_contents($file);
    return json_decode($json, true);
}
?>
