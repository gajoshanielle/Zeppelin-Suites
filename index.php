<?php
declare(strict_types=1);

/**
 * Zeppelin Suites - MVC Front Controller & Single Web Entrypoint
 */

// 1. Initialize environment configuration & logging
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/core/Logger.php';

// 2. Configure error reporting and register global error & exception listeners
if (env('APP_DEBUG', false)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Global Exception Handler: Log all uncaught exceptions with full traces
set_exception_handler(function (Throwable $e): void {
    Logger::error("Uncaught " . get_class($e) . ": " . $e->getMessage(), [
        'exception' => $e
    ]);

    http_response_code(500);

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'status'  => 'error',
            'message' => env('APP_DEBUG', false) ? $e->getMessage() : 'An unexpected internal error occurred. Please try again later.'
        ]);
        exit;
    }

    if (env('APP_DEBUG', false)) {
        echo "<div style='font-family:sans-serif;padding:24px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;margin:20px;border-radius:12px;'>";
        echo "<h2 style='margin-top:0;'>Fatal Error / Uncaught Exception</h2>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Location:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
        echo "<pre style='background:#fff;padding:12px;border-radius:6px;overflow:auto;font-size:12px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    } else {
        echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Internal Server Error</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-slate-50 flex items-center justify-center min-h-screen p-4 font-sans text-center'><div class='max-w-md w-full bg-white p-8 rounded-2xl shadow-sm border border-slate-200'><h1 class='text-2xl font-bold text-slate-800 mb-2'>500 - Server Error</h1><p class='text-sm text-slate-500 mb-6'>We ran into an unexpected error. Our system administrators have been notified.</p><a href='/Zeppelin-Suites/' class='inline-block px-5 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-medium hover:bg-slate-800 transition-colors'>Return to Homepage</a></div></body></html>";
    }
    exit;
});

// Global Error Handler: Log PHP warnings and notices
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    // Only capture errors matching current error_reporting level
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $level = match ($errno) {
        E_USER_ERROR, E_RECOVERABLE_ERROR => Logger::ERROR,
        E_WARNING, E_USER_WARNING         => Logger::WARNING,
        E_NOTICE, E_USER_NOTICE           => Logger::NOTICE,
        default                           => Logger::INFO,
    };

    Logger::log($level, "PHP Error ({$errno}): {$errstr}", [
        'file' => $errfile,
        'line' => $errline
    ]);

    return false; // Allow standard PHP handling to proceed if needed
});

// Fatal Error Handler: Detect fatal script terminations
register_shutdown_function(function (): void {
    $lastError = error_get_last();
    if ($lastError && in_array($lastError['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        Logger::critical("Fatal PHP Script Termination: {$lastError['message']}", [
            'file' => $lastError['file'],
            'line' => $lastError['line'],
            'type' => $lastError['type'],
        ]);
    }
});

// 3. Initialize secure session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

// 4. Class autoloader for core, controllers, models, and config
spl_autoload_register(function (string $className): void {
    $root = __DIR__;
    $directories = [
        $root . '/core/',
        $root . '/config/',
        $root . '/controllers/',
        $root . '/models/',
    ];

    foreach ($directories as $dir) {
        $file = $dir . str_replace('\\', '/', $className) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// 5. Initialize the Central MVC Router
$appUrl = (string) env('APP_URL', '/Zeppelin-Suites');
$basePath = parse_url($appUrl, PHP_URL_PATH) ?? '';
$router = new Router($basePath);

// 6. Define Application Routes
// Public & General Marketing Routes
$router->get('/', [GeneralController::class, 'index']);
$router->get('/home', [GeneralController::class, 'index']);
$router->get('/about', [GeneralController::class, 'about']);
$router->get('/about-us', [GeneralController::class, 'about']);
$router->get('/faq', [GeneralController::class, 'faq']);
$router->get('/amenities', [GeneralController::class, 'amenities']);
$router->get('/tour', [GeneralController::class, 'tour']);
$router->get('/virtual-tour', [GeneralController::class, 'tour']);

// Unit Showcase Routes
$router->get('/units/studio-type-a', [GeneralController::class, 'studioTypeA']);
$router->get('/units/studio-type-b', [GeneralController::class, 'studioTypeB']);
$router->get('/units/one-bedroom', [GeneralController::class, 'oneBedroom']);
$router->get('/units/two-bedroom', [GeneralController::class, 'twoBedroom']);

// Policy & Legal Routes
$router->get('/privacy-policy', [GeneralController::class, 'privacyPolicy']);
$router->get('/terms-of-service', [GeneralController::class, 'termsOfService']);

// Contact & Inquiry Submission Routes
$router->get('/contact', [GeneralController::class, 'contact']);
$router->post('/contact', [GeneralController::class, 'submitInquiry']);
$router->post('/inquiry/submit', [GeneralController::class, 'submitInquiry']);
$router->get('/inquiry-confirmation', [GeneralController::class, 'inquiryConfirmation']);

// Public Reservation Routes
$router->get('/reservation', [GeneralController::class, 'reservationForm']);
$router->post('/reservation', [GeneralController::class, 'submitReservation']);
$router->post('/reservation/submit', [GeneralController::class, 'submitReservation']);
$router->get('/reservation-confirmation', [GeneralController::class, 'reservationConfirmation']);

// Client Cancellation Request Routes
$router->get('/cancel-reservation', [GeneralController::class, 'cancelReservation']);
$router->post('/cancel-reservation', [GeneralController::class, 'submitCancellation']);
$router->post('/cancel-reservation/submit', [GeneralController::class, 'submitCancellation']);
$router->get('/cancellation-confirmation', [GeneralController::class, 'cancellationConfirmation']);

// Public API Utility Routes
$router->get('/api/check-email-domain', [GeneralController::class, 'checkEmailDomain']);

// Authentication Routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->post('/api/auth/forgot-password/send-code', [AuthController::class, 'sendPasswordResetCode']);
$router->post('/api/auth/forgot-password/verify-code', [AuthController::class, 'verifyPasswordResetCode']);
$router->post('/api/auth/forgot-password/reset', [AuthController::class, 'resetPassword']);

// Admin Portal Routes (Guarded by 'admin' role middleware)
$router->get('/admin', [AdminController::class, 'dashboard'], ['admin']);
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'], ['admin']);
$router->get('/admin/home', [AdminController::class, 'home'], ['admin']);
$router->get('/admin/pending-actions', [AdminController::class, 'pendingActions'], ['admin']);
$router->get('/admin/account', [AdminController::class, 'account'], ['admin']);
$router->post('/admin/account', [AdminController::class, 'account'], ['admin']);
$router->get('/admin/analytics', [AdminController::class, 'analytics'], ['admin']);
$router->get('/admin/backup/download', [AdminController::class, 'backupDownload'], ['admin']);
$router->post('/admin/backup/restore', [AdminController::class, 'restoreDatabase'], ['admin']);
$router->get('/admin/logs', [AdminController::class, 'logs'], ['admin']);
$router->post('/admin/logs/clear', [AdminController::class, 'clearLogs'], ['admin']);

$router->get('/admin/inquiries', [InquiryController::class, 'index'], ['admin']);
$router->post('/admin/inquiries/status', [InquiryController::class, 'updateStatus'], ['admin']);
$router->get('/admin/inquiries/reply', [InquiryController::class, 'replyForm'], ['admin']);
$router->post('/admin/inquiries/reply', [InquiryController::class, 'sendReply'], ['admin']);
$router->get('/admin/inquiries/check-units', [InquiryController::class, 'checkUnits'], ['admin']);
$router->post('/admin/inquiries/send-approval', [InquiryController::class, 'sendApproval'], ['admin']);
$router->post('/admin/inquiries/cancel-approval', [InquiryController::class, 'cancelApproval'], ['admin']);
$router->post('/admin/inquiries/assign-unit', [InquiryController::class, 'assignUnit'], ['admin']);

$router->get('/admin/reservations', [ReservationController::class, 'index'], ['admin']);
$router->get('/admin/reservations/view', [ReservationController::class, 'show'], ['admin']);
$router->get('/admin/reservations/documents', [ReservationController::class, 'getDocuments'], ['admin']);
$router->post('/admin/reservations/documents', [ReservationController::class, 'saveDocuments'], ['admin']);
$router->post('/admin/reservations/upload-document', [ReservationController::class, 'uploadDocument'], ['admin']);
$router->post('/admin/reservations/delete-document', [ReservationController::class, 'deleteDocument'], ['admin']);
$router->post('/admin/reservations/officially-book', [ReservationController::class, 'markOfficiallyBooked'], ['admin']);
$router->post('/admin/reservations/officially-booked', [ReservationController::class, 'markOfficiallyBooked'], ['admin']);
$router->post('/admin/reservations/cancel', [ReservationController::class, 'cancelReservation'], ['admin']);
$router->post('/admin/reservations/reject-cancellation', [ReservationController::class, 'rejectCancellation'], ['admin']);
$router->post('/admin/reservations/handover', [ReservationController::class, 'handover'], ['admin']);
$router->post('/admin/reservations/confirm-signing-date', [ReservationController::class, 'confirmSigningDate'], ['admin']);
$router->post('/admin/reservations/lease-signing', [ReservationController::class, 'updateLeaseSigning'], ['admin']);
$router->post('/admin/reservations/payment', [ReservationController::class, 'updatePaymentStatus'], ['admin']);
$router->post('/admin/reservations/update-payment', [ReservationController::class, 'updatePaymentStatus'], ['admin']);

$router->get('/admin/residents', [ResidentController::class, 'index'], ['admin']);
$router->get('/admin/residents/view', [ResidentController::class, 'show'], ['admin']);
$router->post('/admin/residents', [ResidentController::class, 'store'], ['admin']);
$router->post('/admin/residents/update', [ResidentController::class, 'update'], ['admin']);
$router->post('/admin/residents/toggle-status', [ResidentController::class, 'toggleStatus'], ['admin']);
$router->post('/admin/residents/profile-action', [ResidentController::class, 'profileAction'], ['admin']);
$router->post('/admin/residents/impersonate', [ResidentController::class, 'impersonate'], ['admin', 'superadmin']);
$router->get('/admin/residents/impersonate', [ResidentController::class, 'impersonate'], ['admin', 'superadmin']);
$router->get('/admin/residents/exit-impersonation', [ResidentController::class, 'exitImpersonation']);
$router->post('/admin/residents/exit-impersonation', [ResidentController::class, 'exitImpersonation']);
$router->post('/admin/residents/reset-password', [ResidentController::class, 'resetPassword'], ['admin', 'superadmin']);

$router->get('/admin/units', [UnitController::class, 'index'], ['admin']);
$router->get('/admin/units/view', [UnitController::class, 'show'], ['admin']);
$router->get('/admin/units/next-number', [UnitController::class, 'getNextNumber'], ['admin']);
$router->post('/admin/units', [UnitController::class, 'store'], ['admin']);
$router->post('/admin/units/create', [UnitController::class, 'store'], ['admin']);
$router->post('/admin/units/update', [UnitController::class, 'update'], ['admin']);

$router->get('/admin/maintenance', [MaintenanceController::class, 'index'], ['admin']);
$router->post('/admin/maintenance', [MaintenanceController::class, 'store'], ['admin']);
$router->post('/admin/maintenance/create', [MaintenanceController::class, 'store'], ['admin']);
$router->post('/admin/maintenance/update', [MaintenanceController::class, 'update'], ['admin']);

$router->get('/admin/booking-calendar', [BookingCalendarController::class, 'index'], ['admin']);
$router->get('/admin/booking-calendar/data', [BookingCalendarController::class, 'getData'], ['admin']);
$router->post('/admin/booking-calendar/block', [BookingCalendarController::class, 'saveBlockedDate'], ['admin']);
$router->post('/admin/booking-calendar/unblock', [BookingCalendarController::class, 'deleteBlockedDate'], ['admin']);

// Tenant Portal Routes (Guarded by 'tenant' role middleware)
$router->get('/tenant', [TenantController::class, 'home'], ['tenant']);
$router->get('/tenant/home', [TenantController::class, 'home'], ['tenant']);
$router->get('/tenant/dashboard', [TenantController::class, 'home'], ['tenant']);
$router->get('/tenant/account', [TenantController::class, 'account'], ['tenant']);
$router->post('/tenant/account', [TenantController::class, 'account'], ['tenant']);
$router->get('/tenant/maintenance', [TenantController::class, 'maintenance'], ['tenant']);
$router->post('/tenant/maintenance', [TenantController::class, 'storeMaintenance'], ['tenant']);

// Unit Owner Portal Routes (Guarded by 'unit owner' role middleware)
$router->get('/owner', [UnitOwnerController::class, 'overview'], ['unit owner']);
$router->get('/owner/overview', [UnitOwnerController::class, 'overview'], ['unit owner']);
$router->get('/owner/units', [UnitOwnerController::class, 'units'], ['unit owner']);
$router->get('/owner/units/view', [UnitOwnerController::class, 'showUnit'], ['unit owner']);
$router->post('/owner/units/update', [UnitOwnerController::class, 'updateUnit'], ['unit owner']);
$router->get('/owner/inquiries', [UnitOwnerController::class, 'inquiries'], ['unit owner']);
$router->post('/owner/inquiries/respond', [UnitOwnerController::class, 'respondApproval'], ['unit owner']);
$router->get('/owner/reservations', [UnitOwnerController::class, 'reservations'], ['unit owner']);
$router->get('/owner/reservations/view', [UnitOwnerController::class, 'showReservation'], ['unit owner']);
$router->get('/owner/reservations/documents', [UnitOwnerController::class, 'getDocuments'], ['unit owner']);
$router->post('/owner/reservations/documents', [UnitOwnerController::class, 'saveDocuments'], ['unit owner']);
$router->post('/owner/reservations/upload-document', [UnitOwnerController::class, 'uploadDocument'], ['unit owner']);
$router->post('/owner/reservations/delete-document', [UnitOwnerController::class, 'deleteDocument'], ['unit owner']);
$router->post('/owner/reservations/request-cancellation', [UnitOwnerController::class, 'requestCancellation'], ['unit owner']);
$router->post('/owner/reservations/withdraw-cancellation', [UnitOwnerController::class, 'withdrawCancellation'], ['unit owner']);
$router->post('/owner/reservations/confirm-signing-date', [UnitOwnerController::class, 'confirmSigningDate'], ['unit owner']);
$router->post('/owner/reservations/lease-signing', [UnitOwnerController::class, 'updateLeaseSigning'], ['unit owner']);
$router->post('/owner/reservations/verify-payment', [UnitOwnerController::class, 'updatePaymentStatus'], ['unit owner']);

$router->get('/owner/booking-calendar', [UnitOwnerController::class, 'calendar'], ['unit owner']);
$router->get('/owner/booking-calendar/data', [UnitOwnerController::class, 'calendarData'], ['unit owner']);
$router->post('/owner/booking-calendar/block', [UnitOwnerController::class, 'saveBlockedDate'], ['unit owner']);
$router->post('/owner/booking-calendar/unblock', [UnitOwnerController::class, 'deleteBlockedDate'], ['unit owner']);
$router->get('/owner/tenants', [UnitOwnerController::class, 'tenants'], ['unit owner']);
$router->get('/owner/maintenance', [UnitOwnerController::class, 'maintenance'], ['unit owner']);
$router->post('/owner/maintenance', [UnitOwnerController::class, 'storeMaintenance'], ['unit owner']);
$router->get('/owner/account', [UnitOwnerController::class, 'account'], ['unit owner']);
$router->post('/owner/account', [UnitOwnerController::class, 'account'], ['unit owner']);

// 7. Legacy Infrastructure 301 Fallback Redirections (O(1) lookup in Router)
$legacyRouteMap = [
    // Admin legacy paths
    '/adminPages/analytics.php' => '/admin/analytics',
    '/admin/analytics.php' => '/admin/analytics',
    '/adminPages/homeAdmin.php' => '/admin/home',
    '/adminPages/account.php' => '/admin/account',
    '/adminPages/inquiry.php' => '/admin/inquiries',
    '/adminPages/replyform.php' => '/admin/inquiries/reply',
    '/adminPages/reservation.php' => '/admin/reservations',
    '/adminPages/viewReservation.php' => '/admin/reservations/view',
    '/adminPages/residents.php' => '/admin/residents',
    '/adminPages/viewResident.php' => '/admin/residents/view',
    '/adminPages/units.php' => '/admin/units',
    '/adminPages/unitDetails.php' => '/admin/units/view',
    '/adminPages/maintenance.php' => '/admin/maintenance',
    '/adminPages/bookingcalendar.php' => '/admin/booking-calendar',

    // Tenant legacy paths
    '/tenantPages/homeTenant.php' => '/tenant/home',
    '/tenantPages/account.php' => '/tenant/account',
    '/tenantPages/maintenanceTenant.php' => '/tenant/maintenance',

    // Unit Owner legacy paths
    '/unitOwnerPages/overview.php' => '/owner/overview',
    '/unitOwnerPages/ownersUnit.php' => '/owner/units',
    '/unitOwnerPages/unitDetails.php' => '/owner/units/view',
    '/unitOwnerPages/ownersInquiries.php' => '/owner/inquiries',
    '/unitOwnerPages/ownersReservations.php' => '/owner/reservations',
    '/unitOwnerPages/ownersUnitReservations.php' => '/owner/reservations',
    '/unitOwnerPages/ownersViewReservation.php' => '/owner/reservations/view',
    '/unitOwnerPages/ownersBookingCalendar.php' => '/owner/booking-calendar',
    '/unitOwnerPages/tenants.php' => '/owner/tenants',
    '/unitOwnerPages/ownersMaintenance.php' => '/owner/maintenance',
    '/unitOwnerPages/ActionsUOP/submitMaintenanceRequest.php' => '/owner/maintenance',
    '/unitOwnerPages/account.php' => '/owner/account',

    // General / Public legacy paths
    '/generalViewPages/index.html' => '/',
    '/generalViewPages/aboutUs.html' => '/about',
    '/generalViewPages/faq.html' => '/faq',
    '/generalViewPages/amenities.php' => '/amenities',
    '/generalViewPages/amenities.html' => '/amenities',
    '/generalViewPages/tour.html' => '/tour',
    '/generalViewPages/studioTypeA.html' => '/units/studio-type-a',
    '/generalViewPages/studioTypeB.html' => '/units/studio-type-b',
    '/generalViewPages/oneBedroom.html' => '/units/one-bedroom',
    '/generalViewPages/twoBedroom.html' => '/units/two-bedroom',
    '/generalViewPages/privacy-policy.html' => '/privacy-policy',
    '/generalViewPages/terms-of-service.htm' => '/terms-of-service',
    '/generalViewPages/terms-of-service.html' => '/terms-of-service',
    '/generalViewPages/contact.php' => '/contact',
    '/generalViewPages/inquiryConfirmation.html' => '/inquiry-confirmation',
    '/generalViewPages/reservationform.php' => '/reservation',
    '/generalViewPages/reservationConfirmation.html' => '/reservation-confirmation',
    '/generalViewPages/cancelReservation.php' => '/cancel-reservation',
    '/generalViewPages/cancellationConfirmation.html' => '/cancellation-confirmation',
    '/generalViewPages/login.php' => '/login',

    // /public/* legacy prefixed paths
    '/public' => '/',
    '/public/' => '/',
    '/public/login' => '/login',
    '/public/about' => '/about',
    '/public/faq' => '/faq',
    '/public/amenities' => '/amenities',
    '/public/tour' => '/tour',
    '/public/contact' => '/contact',
    '/public/reservation' => '/reservation',
    '/public/cancel-reservation' => '/cancel-reservation',
    '/public/generalViewPages/index.html' => '/',
    '/public/generalViewPages/aboutUs.html' => '/about',
    '/public/generalViewPages/faq.html' => '/faq',
    '/public/generalViewPages/amenities.php' => '/amenities',
    '/public/generalViewPages/tour.html' => '/tour',
    '/public/generalViewPages/login.php' => '/login',
    '/public/generalViewPages/contact.php' => '/contact',
    '/public/generalViewPages/reservationform.php' => '/reservation',
    '/public/generalViewPages/cancelReservation.php' => '/cancel-reservation',
];

$router->registerRedirects($legacyRouteMap);

// 8. Dispatch incoming HTTP request
$router->dispatch();
