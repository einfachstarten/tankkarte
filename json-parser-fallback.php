<?php
// Professional JSON Parser Fallback
class JSONParserFallback {
    public static function decode($json, $assoc = false) {
        if (function_exists('json_decode')) {
            return json_decode($json, $assoc);
        }
        return self::parseJSON($json, $assoc);
    }

    public static function encode($value, $options = 0) {
        if (function_exists('json_encode')) {
            return json_encode($value, $options);
        }
        return self::arrayToPHPJSON($value);
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

    private static function arrayToPHPJSON($value) {
        if (is_array($value)) {
            if (self::isAssociativeArray($value)) {
                $items = [];
                foreach ($value as $key => $val) {
                    $items[] = '"' . addslashes($key) . '":' . self::arrayToPHPJSON($val);
                }
                return '{' . implode(',', $items) . '}';
            } else {
                $items = [];
                foreach ($value as $val) {
                    $items[] = self::arrayToPHPJSON($val);
                }
                return '[' . implode(',', $items) . ']';
            }
        } elseif (is_string($value)) {
            return '"' . addslashes($value) . '"';
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_null($value)) {
            return 'null';
        } elseif (is_numeric($value)) {
            return $value;
        }
        return 'null';
    }

    private static function isAssociativeArray($array) {
        if (!is_array($array)) return false;
        return array_keys($array) !== range(0, count($array) - 1);
    }
}

// Test the fallback
if (!function_exists('json_decode')) {
    function json_decode($json, $assoc = false) {
        return JSONParserFallback::decode($json, $assoc);
    }
}

if (!function_exists('json_encode')) {
    function json_encode($value, $options = 0) {
        return JSONParserFallback::encode($value, $options);
    }
}
?>
