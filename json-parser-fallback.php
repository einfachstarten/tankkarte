<?php
// Ultra-minimal JSON fallback for webhook.php only
// Only handles specific case: json_encode(['files' => array_of_strings])

if (!function_exists('json_encode')) {
    function json_encode($value, $options = 0) {
        // Handle only the specific Cloudflare cache purge case
        if (is_array($value) && isset($value['files']) && is_array($value['files'])) {
            $urls = [];
            foreach ($value['files'] as $url) {
                $urls[] = '"' . addslashes($url) . '"';
            }
            return '{"files":[' . implode(',', $urls) . ']}';
        }
        return 'null';
    }
}

if (!function_exists('json_decode')) {
    function json_decode($json, $assoc = false) {
        // Ultra-basic decode for simple cases only
        if (trim($json) === 'null') return null;
        if (trim($json) === 'true') return true;
        if (trim($json) === 'false') return false;
        if (is_numeric(trim($json))) return (float)trim($json);
        
        // For Cloudflare response: just check if it contains "success":true
        if (strpos($json, '"success":true') !== false) {
            return $assoc ? ['success' => true] : (object)['success' => true];
        }
        
        return null;
    }
}
?>
