<?php
// Memory-optimized JSON Parser Fallback - only used when native JSON unavailable

// Early exit if native JSON functions exist
if (function_exists('json_decode') && function_exists('json_encode')) {
    return; // Don't load fallback at all
}

class JSONParserFallback {
    public static function decode($json, $assoc = false) {
        // Use native if available (double-check)
        if (function_exists('json_decode')) {
            return json_decode($json, $assoc);
        }
        
        // Memory-efficient simple fallback for basic cases only
        $json = trim($json);
        if (empty($json)) {
            return null;
        }
        
        // Only handle simple objects/arrays to avoid memory issues
        if (strlen($json) > 1000000) { // 1MB limit
            trigger_error('JSON too large for fallback parser', E_USER_WARNING);
            return null;
        }
        
        return self::simpleJSONDecode($json, $assoc);
    }
    
    public static function encode($value, $options = 0) {
        // Use native if available
        if (function_exists('json_encode')) {
            return json_encode($value, $options);
        }
        
        return self::simpleJSONEncode($value);
    }
    
    private static function simpleJSONDecode($json, $assoc = false) {
        // Basic eval-based approach for emergency cases only
        $json = trim($json);
        if (substr($json, 0, 1) === '{' && substr($json, -1) === '}') {
            // Simple object conversion
            $php = str_replace(['{', '}', ':', 'true', 'false', 'null'], 
                              ['array(', ')', '=>', 'true', 'false', 'null'], $json);
            $php = preg_replace('/"([^"\\]+)"\s*=>/', '"$1" =>', $php);
            
            $result = null;
            @eval('$result = ' . $php . ';');
            return $result;
        }
        return null;
    }
    
    private static function simpleJSONEncode($value) {
        if (is_array($value) && count($value) < 100) { // Limit array size
            $isAssoc = (array_keys($value) !== range(0, count($value) - 1));
            
            if ($isAssoc) {
                $items = [];
                foreach ($value as $key => $val) {
                    $items[] = '"' . addslashes($key) . '":' . self::simpleJSONEncode($val);
                }
                return '{' . implode(',', $items) . '}';
            } else {
                $items = [];
                foreach ($value as $val) {
                    $items[] = self::simpleJSONEncode($val);
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
}

// Only register fallback functions if native ones don't exist
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
