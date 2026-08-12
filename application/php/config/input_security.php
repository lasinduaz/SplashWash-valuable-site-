<?php
// /application/php/config/input_security.php

function detect_sql_injection(string $value): bool
{
    $normalized = strtolower($value);

    $patterns = [
        '/\bunion\b\s+\bselect\b/',
        '/\bor\b\s+1\s*=\s*1\b/',
        '/\bselect\b.+\bfrom\b/',
        '/\binsert\b.+\binto\b/',
        '/\bupdate\b.+\bset\b/',
        '/\bdelete\b\s+from\b/',
        '/--/',
        '/\/\*/',
        '/\*\//',
        "/['\"]\s*;\s*--/",
        "/['\"]\s*or\s*['\"]?1['\"]?\s*=\s*['\"]?1['\"]?/",
        "/['\"]\s*or\s*1\s*=\s*1/",
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $normalized)) {
            return true;
        }
    }

    return false;
}

function block_sql_injection(array $inputs, string $context): void
{
    foreach ($inputs as $value) {
        if (!is_string($value)) {
            continue;
        }

        if (detect_sql_injection($value)) {
            error_log('SQL injection attempt blocked in ' . $context . ': ' . $value);
            http_response_code(400);
            echo 'Suspicious input detected.';
            exit;
        }
    }
}
