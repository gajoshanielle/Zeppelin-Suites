<?php
declare(strict_types=1);

/**
 * Zeppelin Suites - Cross-Site Request Forgery (CSRF) Protection
 * Provides token generation, verification, and automated client injection.
 */
class Csrf {
    private const SESSION_KEY = 'csrf_token';

    /**
     * Retrieve or generate the active CSRF token for the current session.
     */
    public static function token(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return (string)$_SESSION[self::SESSION_KEY];
    }

    /**
     * Alias for token() to support generate() syntax.
     */
    public static function generate(): string {
        return self::token();
    }

    /**
     * Validate an incoming CSRF token against the active session token.
     */
    public static function validate(?string $submittedToken = null): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionToken = (string)($_SESSION[self::SESSION_KEY] ?? '');
        if ($sessionToken === '') {
            return false;
        }

        if ($submittedToken === null || $submittedToken === '') {
            $submittedToken = (string)($_POST['csrf_token'] ?? '');
        }

        if ($submittedToken === '') {
            $submittedToken = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        }

        if ($submittedToken === '') {
            $submittedToken = (string)($_SERVER['HTTP_X_XSRF_TOKEN'] ?? '');
        }

        if ($submittedToken === '' && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $submittedToken = (string)($headers['X-CSRF-TOKEN'] ?? ($headers['X-Csrf-Token'] ?? ''));
        }

        if ($submittedToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $submittedToken);
    }

    /**
     * Assert that the current request has a valid CSRF token.
     * Terminates with 403 Forbidden on failure.
     */
    public static function check(): void {
        if (!self::validate()) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
                || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);

            if ($isAjax) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'Invalid or expired security token. Please refresh the page and try again.'
                ]);
                exit;
            }

            http_response_code(403);
            die('<!DOCTYPE html><html><head><title>403 Forbidden</title><style>body{font-family:sans-serif;padding:3rem;text-align:center;color:#1e293b}h1{color:#e11d48}</style></head><body><h1>403 Forbidden: Security Token Mismatch</h1><p>Your session security token could not be verified. Please go back, refresh the page, and try again.</p></body></html>');
        }
    }

    /**
     * Render a hidden HTML input field with the CSRF token.
     */
    public static function field(): string {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Render an HTML meta tag containing the CSRF token.
     */
    public static function meta(): string {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<meta name="csrf-token" content="' . $token . '">';
    }

    /**
     * Render an automated client-side JavaScript snippet to inject CSRF tokens into
     * all standard HTML POST forms, fetch() requests, and XMLHttpRequest / jQuery calls.
     */
    public static function script(): string {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return <<<HTML
<script>
(function() {
    const csrfToken = "{$token}";

    // 1. Ensure meta tag exists
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = csrfToken;
        document.head.appendChild(meta);
    }

    function ensureCsrf(form) {
        if (!form || form.tagName !== 'FORM') return;
        const method = (form.method || 'GET').toUpperCase();
        if (method === 'POST') {
            let input = form.querySelector('input[name="csrf_token"]');
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'csrf_token';
                input.value = csrfToken;
                form.appendChild(input);
            } else if (!input.value) {
                input.value = csrfToken;
            }
        }
    }

    // 2. Intercept and auto-populate all POST forms
    document.addEventListener('submit', function(e) {
        ensureCsrf(e.target);
    }, true);

    // Also wrap HTMLFormElement.prototype.submit for programmatic form.submit() calls
    const origFormSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function() {
        ensureCsrf(this);
        return origFormSubmit.apply(this, arguments);
    };

    // 3. Patch window.fetch to automatically include X-CSRF-TOKEN on state-changing calls
    const originalFetch = window.fetch;
    window.fetch = function(resource, init) {
        init = init || {};
        const method = (init.method || 'GET').toUpperCase();
        if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
            init.headers = init.headers || {};
            if (init.headers instanceof Headers) {
                if (!init.headers.has('X-CSRF-TOKEN')) {
                    init.headers.set('X-CSRF-TOKEN', csrfToken);
                }
            } else if (Array.isArray(init.headers)) {
                const hasHeader = init.headers.some(h => h[0].toLowerCase() === 'x-csrf-token');
                if (!hasHeader) {
                    init.headers.push(['X-CSRF-TOKEN', csrfToken]);
                }
            } else {
                if (!init.headers['X-CSRF-TOKEN'] && !init.headers['x-csrf-token']) {
                    init.headers['X-CSRF-TOKEN'] = csrfToken;
                }
            }
        }
        return originalFetch.call(this, resource, init);
    };

    // 4. Patch XMLHttpRequest to automatically include X-CSRF-TOKEN
    const originalOpen = XMLHttpRequest.prototype.open;
    const originalSend = XMLHttpRequest.prototype.send;

    XMLHttpRequest.prototype.open = function(method, url) {
        this._csrfMethod = (method || 'GET').toUpperCase();
        return originalOpen.apply(this, arguments);
    };

    XMLHttpRequest.prototype.send = function() {
        if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(this._csrfMethod)) {
            try {
                this.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            } catch (err) {}
        }
        return originalSend.apply(this, arguments);
    };

    // 5. jQuery AJAX setup if jQuery is present
    if (typeof window.jQuery !== 'undefined') {
        window.jQuery.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                }
            }
        });
    }
})();
</script>
HTML;
    }
}
