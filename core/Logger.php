<?php
declare(strict_types=1);

/**
 * Zeppelin Suites - PSR-3 Inspired Application Logger & Error Auditor
 * 
 * Provides structured logging with daily file rotation, level filtering,
 * exception stack tracing, and administrative log retrieval.
 * No external dependencies required.
 */
class Logger {
    public const EMERGENCY = 'EMERGENCY';
    public const ALERT     = 'ALERT';
    public const CRITICAL  = 'CRITICAL';
    public const ERROR     = 'ERROR';
    public const WARNING   = 'WARNING';
    public const NOTICE    = 'NOTICE';
    public const INFO      = 'INFO';
    public const DEBUG     = 'DEBUG';

    private static ?string $logDirectory = null;

    /**
     * Get or initialize the log directory path with security protection.
     */
    public static function getLogDir(): string {
        if (self::$logDirectory === null) {
            self::$logDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        }

        if (!is_dir(self::$logDirectory)) {
            mkdir(self::$logDirectory, 0755, true);
        }

        // Ensure security protection so log files cannot be downloaded over HTTP
        $htaccessPath = self::$logDirectory . DIRECTORY_SEPARATOR . '.htaccess';
        if (!file_exists($htaccessPath)) {
            @file_put_contents($htaccessPath, "Deny from all\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n");
        }

        $webConfigPath = self::$logDirectory . DIRECTORY_SEPARATOR . 'web.config';
        if (!file_exists($webConfigPath)) {
            @file_put_contents($webConfigPath, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration>\n<system.webServer>\n<authorization>\n<deny users=\"*\" />\n</authorization>\n</system.webServer>\n</configuration>");
        }

        return self::$logDirectory;
    }

    /**
     * Primary logging method.
     * Formats log line with timestamp, severity level, message, context, and request metadata.
     */
    public static function log(string $level, string $message, array $context = []): void {
        try {
            $dir = self::getLogDir();
            $date = date('Y-m-d');
            $logFile = $dir . DIRECTORY_SEPARATOR . "app-{$date}.log";

            $timestamp = date('Y-m-d H:i:s');
            $upperLevel = strtoupper($level);

            // Extract exception details if present in context
            $trace = null;
            if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
                $e = $context['exception'];
                $context['exception_message'] = $e->getMessage();
                $context['file'] = $e->getFile() . ':' . $e->getLine();
                $trace = $e->getTraceAsString();
                unset($context['exception']);
            } elseif (isset($context['trace']) && is_string($context['trace'])) {
                $trace = $context['trace'];
                unset($context['trace']);
            }

            // Capture HTTP request metadata if available
            $uri = $_SERVER['REQUEST_URI'] ?? 'CLI';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $method = $_SERVER['REQUEST_METHOD'] ?? 'CMD';

            $meta = [
                'timestamp' => $timestamp,
                'level'     => $upperLevel,
                'message'   => $message,
                'method'    => $method,
                'uri'       => $uri,
                'ip'        => $ip,
                'context'   => $context,
                'trace'     => $trace,
            ];

            // Store single-line JSON formatted record for high-precision parsing
            $logLine = '[JSON]' . json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;

            file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
        } catch (Throwable) {
            // Fail silent to never crash application during logging failure
            error_log("[Logger Failure] {$message}");
        }
    }

    // PSR-3 standard static convenience methods
    public static function emergency(string $message, array $context = []): void {
        self::log(self::EMERGENCY, $message, $context);
    }

    public static function alert(string $message, array $context = []): void {
        self::log(self::ALERT, $message, $context);
    }

    public static function critical(string $message, array $context = []): void {
        self::log(self::CRITICAL, $message, $context);
    }

    public static function error(string $message, array $context = []): void {
        self::log(self::ERROR, $message, $context);
    }

    public static function warning(string $message, array $context = []): void {
        self::log(self::WARNING, $message, $context);
    }

    public static function notice(string $message, array $context = []): void {
        self::log(self::NOTICE, $message, $context);
    }

    public static function info(string $message, array $context = []): void {
        self::log(self::INFO, $message, $context);
    }

    public static function debug(string $message, array $context = []): void {
        self::log(self::DEBUG, $message, $context);
    }

    /**
     * Retrieve all available log dates based on existing log files.
     * @return string[] Array of dates (YYYY-MM-DD), ordered newest first.
     */
    public static function getAvailableLogDates(): array {
        $dir = self::getLogDir();
        $files = glob($dir . DIRECTORY_SEPARATOR . 'app-*.log');
        if (!$files) {
            return [date('Y-m-d')];
        }

        $dates = [];
        foreach ($files as $file) {
            if (preg_match('/app-(\d{4}-\d{2}-\d{2})\.log$/', basename($file), $matches)) {
                $dates[] = $matches[1];
            }
        }

        rsort($dates);
        if (empty($dates)) {
            $dates[] = date('Y-m-d');
        }

        return array_values(array_unique($dates));
    }

    /**
     * Parse and retrieve logs for administrative review.
     *
     * @param string|null $date Specific date to inspect (defaults to latest)
     * @param string|null $levelFilter Filter by severity level (ERROR, WARNING, INFO, etc.)
     * @param string|null $searchQuery Text search query
     * @param int $limit Maximum records to return
     * @return array Array of parsed log entries
     */
    public static function getLogs(?string $date = null, ?string $levelFilter = null, ?string $searchQuery = null, int $limit = 250): array {
        $dir = self::getLogDir();
        $date = $date ?: date('Y-m-d');
        $logFile = $dir . DIRECTORY_SEPARATOR . "app-{$date}.log";

        if (!file_exists($logFile)) {
            return [];
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        // Process newest lines first
        $lines = array_reverse($lines);
        $entries = [];
        $counter = 1;

        foreach ($lines as $line) {
            if (count($entries) >= $limit) {
                break;
            }

            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $entry = null;

            if (str_starts_with($line, '[JSON]')) {
                $jsonStr = substr($line, 6);
                $decoded = json_decode($jsonStr, true);
                if (is_array($decoded)) {
                    $entry = [
                        'id'        => $counter++,
                        'timestamp' => $decoded['timestamp'] ?? '',
                        'level'     => $decoded['level'] ?? 'INFO',
                        'message'   => $decoded['message'] ?? '',
                        'method'    => $decoded['method'] ?? 'GET',
                        'uri'       => $decoded['uri'] ?? '',
                        'ip'        => $decoded['ip'] ?? '',
                        'context'   => $decoded['context'] ?? [],
                        'trace'     => $decoded['trace'] ?? null,
                    ];
                }
            } else {
                // Fallback parser for standard formatted lines: [2026-10-19 06:00:00] [LEVEL] Message
                if (preg_match('/^\[(.*?)\]\s+\[(.*?)\]\s+(.*)$/', $line, $matches)) {
                    $entry = [
                        'id'        => $counter++,
                        'timestamp' => $matches[1],
                        'level'     => strtoupper($matches[2]),
                        'message'   => $matches[3],
                        'method'    => 'APP',
                        'uri'       => '',
                        'ip'        => '',
                        'context'   => [],
                        'trace'     => null,
                    ];
                }
            }

            if (!$entry) {
                continue;
            }

            // Apply level filter
            if ($levelFilter && strtoupper($levelFilter) !== 'ALL') {
                if (strtoupper($entry['level']) !== strtoupper($levelFilter)) {
                    continue;
                }
            }

            // Apply search query filter
            if ($searchQuery && trim($searchQuery) !== '') {
                $q = strtolower(trim($searchQuery));
                $haystack = strtolower(
                    $entry['message'] . ' ' .
                    $entry['uri'] . ' ' .
                    $entry['ip'] . ' ' .
                    json_encode($entry['context']) . ' ' .
                    ($entry['trace'] ?? '')
                );
                if (!str_contains($haystack, $q)) {
                    continue;
                }
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    /**
     * Clear / truncate a specific log file.
     */
    public static function clearLog(?string $date = null): bool {
        $dir = self::getLogDir();
        $date = $date ?: date('Y-m-d');
        $logFile = $dir . DIRECTORY_SEPARATOR . "app-{$date}.log";

        if (file_exists($logFile)) {
            return @unlink($logFile);
        }

        return true;
    }
}
