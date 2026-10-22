<?php
declare(strict_types=1);

/**
 * Zeppelin Suites - Middleware & Authentication Guards
 */
class Middleware {

    /**
     * Normalize role string (lowercase, trimmed).
     */
    public static function normalizeRole(string $role): string {
        return strtolower(trim($role));
    }

    /**
     * Start session safely if not already active and headers not sent.
     */
    private static function startSessionIfNeeded(): void {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
    }

    /**
     * Guard that ensures the user is logged in and possesses one of the allowed roles.
     *
     * @param array $allowedRoles Array of allowed role names (e.g., ['admin'], ['unit owner'], ['tenant'])
     * @return array User identity data from session
     */
    public static function requireRole(array $allowedRoles): array {
        self::startSessionIfNeeded();

        $userId = $_SESSION['user_id'] ?? null;
        $role = self::normalizeRole((string)($_SESSION['role'] ?? ''));

        $normalizedAllowed = array_map([self::class, 'normalizeRole'], $allowedRoles);

        // Superadmin automatically inherits all 'admin' role permissions
        $hasAccess = in_array($role, $normalizedAllowed, true)
            || ($role === 'superadmin' && in_array('admin', $normalizedAllowed, true));

        if (!$userId || (!empty($normalizedAllowed) && !$hasAccess)) {
            // If AJAX request, return 401 Unauthorized JSON
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized or session expired.']);
                exit;
            }

            // Clear invalid session
            session_unset();
            session_destroy();

            // Redirect to login page
            $loginUrl = self::getLoginUrl();
            header("Location: {$loginUrl}");
            exit;
        }

        return [
            'user_id'   => (int)$userId,
            'email'     => (string)($_SESSION['email'] ?? ''),
            'role'      => $role,
            'full_name' => (string)($_SESSION['full_name'] ?? 'User'),
            'initial'   => (string)($_SESSION['initial'] ?? 'U'),
        ];
    }

    /**
     * Determine if current active user or background impersonator is a superadmin.
     */
    public static function isSuperAdmin(): bool {
        self::startSessionIfNeeded();
        $role = self::normalizeRole((string)($_SESSION['role'] ?? ''));
        if (!empty($_SESSION['is_impersonating']) && !empty($_SESSION['impersonator_role'])) {
            return self::normalizeRole((string)$_SESSION['impersonator_role']) === 'superadmin';
        }
        return $role === 'superadmin';
    }

    /**
     * Determine if currently actively impersonating another user.
     */
    public static function isImpersonating(): bool {
        self::startSessionIfNeeded();
        return !empty($_SESSION['is_impersonating']) && !empty($_SESSION['impersonator_admin_id']);
    }

    /**
     * Guard that ensures only guests (unauthenticated visitors) can access the route (e.g. login page).
     */
    public static function requireGuest(): void {
        self::startSessionIfNeeded();

        if (!empty($_SESSION['user_id'])) {
            $role = self::normalizeRole((string)($_SESSION['role'] ?? ''));
            $destination = self::getDashboardUrlForRole($role);
            header("Location: {$destination}");
            exit;
        }
    }

    /**
     * Determine dashboard redirect destination by role.
     */
    public static function getDashboardUrlForRole(string $role): string {
        $baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');
        
        switch (self::normalizeRole($role)) {
            case 'superadmin':
            case 'admin':
                return "{$baseUrl}/admin/home";
            case 'unit owner':
                return "{$baseUrl}/owner/overview";
            case 'tenant':
                return "{$baseUrl}/tenant/home";
            default:
                return "{$baseUrl}/login";
        }
    }

    /**
     * Determine login URL based on environment.
     */
    public static function getLoginUrl(): string {
        $baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');
        return "{$baseUrl}/login";
    }
}
