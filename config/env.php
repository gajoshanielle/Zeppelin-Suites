<?php
declare(strict_types=1);

/**
 * Zeppelin Suites - Lightweight .env Environment Loader
 */

if (!function_exists('resolveDynamicAppUrl')) {
    /**
     * Dynamically resolve APP_URL based on active HTTP host.
     * Ensures seamless behavior across local XAMPP and live deployments (Railway/Cloudflare)
     * even if environment variables are mismatched.
     */
    function resolveDynamicAppUrl(string $url): string {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (empty($host)) {
            return $url;
        }

        $cleanHost = strtolower(explode(':', $host)[0]);
        $isLocal = in_array($cleanHost, ['localhost', '127.0.0.1', '::1'], true);

        // Deployed live environment (e.g. *.beulahlabs.com, Railway, etc.)
        if (!$isLocal) {
            $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
                ? $_SERVER['HTTP_X_FORWARDED_PROTO']
                : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');

            return "{$proto}://{$host}";
        }

        // Local environment (XAMPP localhost)
        if ($isLocal && !str_contains($url, 'localhost') && !str_contains($url, '127.0.0.1')) {
            $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            return "{$proto}://{$host}/Zeppelin-Suites";
        }

        return $url;
    }
}

if (!function_exists('loadEnv')) {
    function loadEnv(string $filePath): void {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Split on the first '=' character
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key   = trim($parts[0]);
            $value = trim($parts[1]);

            // Handle quoted strings
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            // Convert boolean and null strings
            $lowerVal = strtolower($value);
            if ($lowerVal === 'true') {
                $parsedVal = true;
            } elseif ($lowerVal === 'false') {
                $parsedVal = false;
            } elseif ($lowerVal === 'null') {
                $parsedVal = null;
            } else {
                $parsedVal = $value;
            }

            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $parsedVal;
            }
            if (!array_key_exists($key, $_SERVER)) {
                $_SERVER[$key] = $parsedVal;
            }
            putenv("{$key}={$value}");
        }
    }
}

if (!function_exists('env')) {
    /**
     * Get an environment variable with optional fallback.
     */
    function env(string $key, mixed $default = null): mixed {
        if (array_key_exists($key, $_ENV)) {
            $val = $_ENV[$key];
        } elseif (array_key_exists($key, $_SERVER)) {
            $val = $_SERVER[$key];
        } else {
            $envVal = getenv($key);
            $val = ($envVal !== false) ? $envVal : $default;
        }

        // Auto-resolve APP_URL and OWNER_PORTAL_LOGIN_URL to prevent localhost redirection in live deployments
        if ($key === 'APP_URL' && is_string($val)) {
            return resolveDynamicAppUrl($val);
        }

        if ($key === 'OWNER_PORTAL_LOGIN_URL' && is_string($val)) {
            $baseUrl = resolveDynamicAppUrl((string)env('APP_URL', 'http://localhost/Zeppelin-Suites'));
            return rtrim($baseUrl, '/') . '/login';
        }

        return $val;
    }
}

// Automatically load .env from the project root if it exists
$rootDir = dirname(__DIR__);
loadEnv($rootDir . DIRECTORY_SEPARATOR . '.env');
