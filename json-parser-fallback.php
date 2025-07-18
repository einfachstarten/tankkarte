<?php
// Professional JSON Parser Fallback
class JSONParserFallback {
    public static function decode($json, $assoc = false) {
        if (function_exists('json_decode')) {
            return json_decode($json, $assoc);
        }
        return self::parseJSON($json, $assoc);
    }

    private static function parseJSON($json, $assoc = false) {
        $json = trim($json);
        if (empty($json)) {
            return null;
        }
        if (self::isValidJSONStructure($json)) {
            $phpCode = self::jsonToPHPArray($json);
            $result = null;
            eval('$result = ' . $phpCode . ';');
            return $assoc ? $result : (object)$result;
        }
        return null;
    }

    private static function isValidJSONStructure($json) {
        $json = trim($json);
        return (
            (substr($json, 0, 1) === '{' && substr($json, -1) === '}') ||
            (substr($json, 0, 1) === '[' && substr($json, -1) === ']')
        );
    }

    private static function jsonToPHPArray($json) {
        $php = $json;
        $php = preg_replace('/:\s*true\b/', ': true', $php);
        $php = preg_replace('/:\s*false\b/', ': false', $php);
        $php = preg_replace('/:\s*null\b/', ': null', $php);
        $php = str_replace('{', 'array(', $php);
        $php = str_replace('}', ')', $php);
        $php = str_replace('[', 'array(', $php);
        $php = str_replace(']', ')', $php);
        $php = preg_replace('/"([^"]+)"\s*:/', '"$1" =>', $php);
        return $php;
    }
}

// Test the fallback
if (!function_exists('json_decode')) {
    function json_decode($json, $assoc = false) {
        return JSONParserFallback::decode($json, $assoc);
    }
}
?>
