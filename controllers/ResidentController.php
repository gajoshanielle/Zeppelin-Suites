<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Middleware.php';
require_once __DIR__ . '/../models/Resident.php';
require_once __DIR__ . '/../models/Analytics.php';
require_once __DIR__ . '/../models/User.php';

/**
 * Zeppelin Suites - Resident Controller
 * Handles listing, live AJAX filtering, creation, updating, and status toggles for residents.
 * Strictly NO SQL in controller.
 */
class ResidentController extends Controller {
    private Resident $residentModel;

    public function __construct() {
        $this->residentModel = new Resident();
    }

    /**
     * Display residents list or return HTML table rows for live search AJAX.
     */
    public function index(): void {
        $userSession = Middleware::requireRole(['admin']);

        $search = trim((string)$this->getQuery('search', ''));
        $role = trim((string)$this->getQuery('role', ''));
        $status = trim((string)$this->getQuery('status', ''));
        $isAjax = $this->getQuery('ajax') === '1';

        $residents = $this->residentModel->getAll($search, $role, $status);
        $baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');
        $isSuperAdmin = Middleware::isSuperAdmin();

        // Live AJAX search requests return only rendered table rows
        if ($isAjax) {
            header('Content-Type: text/html; charset=UTF-8');
            if (empty($residents)) {
                $colspan = $isSuperAdmin ? 7 : 6;
                echo '<tr><td colspan="' . $colspan . '" class="px-4 py-10 text-center text-slate-500 text-sm">No residents found.</td></tr>';
            } else {
                foreach ($residents as $resident) {
                    $this->render('admin/residents_rows', [
                        'resident'     => $resident,
                        'baseUrl'      => $baseUrl,
                        'isSuperAdmin' => $isSuperAdmin,
                    ]);
                }
            }
            exit;
        }

        $stats = $this->residentModel->getStats();
        $successMessage = $this->getFlash('success_message');
        $errorMessage = $this->getFlash('error_message');

        $this->render('admin/residents', [
            'pageTitle'      => 'Zeppelin Suites Admin - Residents',
            'activeTab'      => 'residents',
            'adminName'      => $userSession['full_name'],
            'adminInitial'   => $userSession['initial'],
            'baseUrl'        => $baseUrl,
            'stats'          => $stats,
            'residents'      => $residents,
            'search'         => $search,
            'roleFilter'     => $role,
            'statusFilter'   => $status,
            'successMessage' => $successMessage,
            'errorMessage'   => $errorMessage,
            'isSuperAdmin'   => $isSuperAdmin,
        ]);
    }

    /**
     * Handle resident registration.
     */
    public function store(): void {
        Middleware::requireRole(['admin']);
        $baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');

        if (!$this->isPost()) {
            $this->redirect("{$baseUrl}/admin/residents");
            return;
        }

        try {
            $this->residentModel->create($_POST);
            $this->setFlash('success_message', 'Resident account added successfully.');
        } catch (Throwable $e) {
            $this->setFlash('error_message', $e->getMessage());
        }

        $this->redirect("{$baseUrl}/admin/residents");
    }

    /**
     * Handle resident profile update.
     */
    public function update(): void {
        Middleware::requireRole(['admin']);
        $baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');

        if (!$this->isPost()) {
            $this->redirect("{$baseUrl}/admin/residents");
            return;
        }

        $userId = (int)$this->getPost('user_id', 0);

        try {
            $this->residentModel->update($userId, $_POST);
            $this->setFlash('success_message', 'Resident account updated successfully.');
        } catch (Throwable $e) {
            $this->setFlash('error_message', $e->getMessage());
        }

        $this->redirect("{$baseUrl}/admin/residents");
    }

    /**
     * Handle resident status toggle ('Active' | 'Inactive').
     */
    public function toggleStatus(): void {
        Middleware::requireRole(['admin']);
        $baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');

        if (!$this->isPost()) {
            $this->redirect("{$baseUrl}/admin/residents");
            return;
        }

        $userId = (int)$this->getPost('user_id', 0);
        $status = trim((string)$this->getPost('resident_status', 'Inactive'));

        try {
            $this->residentModel->toggleStatus($userId, $status);
            $this->setFlash('success_message', 'Resident status updated successfully.');
        } catch (Throwable $e) {
            $this->setFlash('error_message', $e->getMessage());
        }

        $this->redirect("{$baseUrl}/admin/residents");
    }

    /**
     * Display detailed resident view with owned and rented units.
     */
    public function show(): void {
        $userSession = Middleware::requireRole(['admin']);
        $baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');

        $userId = (int)($this->getQuery('id', 0) ?: $this->getQuery('user_id', 0));
        if ($userId <= 0) {
            $this->redirect("{$baseUrl}/admin/residents");
            return;
        }

        $resident = $this->residentModel->getResidentDetails($userId);
        if (!$resident) {
            $this->setFlash('error_message', 'Resident not found.');
            $this->redirect("{$baseUrl}/admin/residents");
            return;
        }

        $analyticsModel = new Analytics();
        $pendingCounts = $analyticsModel->getPendingCounts();

        $successMessage = $this->getFlash('success_message') ?? ($_SESSION['success_message'] ?? null);
        $errorMessage = $this->getFlash('error_message') ?? ($_SESSION['error_message'] ?? null);
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $this->render('admin/view_resident', [
            'pageTitle'           => 'Zeppelin Suites - View Resident #' . $userId,
            'activeTab'           => 'residents',
            'adminName'           => $userSession['full_name'],
            'adminInitial'        => $userSession['initial'],
            'baseUrl'             => $baseUrl,
            'resident'            => $resident,
            'user_id'             => $userId,
            'pendingInquiries'    => $pendingCounts['pending_inquiries'],
            'pendingReservations' => $pendingCounts['pending_reservations'],
            'isSuperAdmin'        => Middleware::isSuperAdmin(),
            'successMessage'      => $successMessage,
            'errorMessage'        => $errorMessage,
        ]);
    }

    /**
     * Handle actions on resident detail page (toggle status or update profile).
     */
    public function profileAction(): void {
        Middleware::requireRole(['admin']);
        $baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');

        if ($this->isPost()) {
            $this->validateCsrf();
        }

        $userId = (int)($this->getPost('user_id', 0) ?: $this->getQuery('id', 0));
        if ($userId <= 0) {
            $this->redirect("{$baseUrl}/admin/residents");
            return;
        }

        $action = trim((string)$this->getPost('action', ''));

        if ($action === 'toggle_status') {
            $newStatus = trim((string)$this->getPost('resident_status', 'Active')) === 'Active' ? 'Active' : 'Inactive';
            try {
                $this->residentModel->toggleStatus($userId, $newStatus);
                $this->setFlash('success_message', "Resident status updated to {$newStatus}.");
            } catch (Throwable $e) {
                $this->setFlash('error_message', $e->getMessage());
            }
        } elseif ($action === 'update_profile') {
            $ok = $this->residentModel->updateResidentProfile($userId, $_POST);
            if ($ok) {
                $this->setFlash('success_message', 'Resident profile updated successfully.');
            } else {
                $this->setFlash('error_message', 'Failed to update profile. Please verify all fields.');
            }
        }

        $this->redirect("{$baseUrl}/admin/residents/view?id={$userId}");
    }

    /**
     * Secret Admin Impersonation ("Visit Account").
     * Allows visiting the resident's portal directly with seamless return capability.
     */
    public function impersonate(): void {
        Middleware::requireRole(['admin', 'superadmin']);
        $baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');

        if (!Middleware::isSuperAdmin()) {
            $this->setFlash('error_message', 'Access denied. Only the secret administrator can impersonate resident accounts.');
            $this->redirect("{$baseUrl}/admin/residents");
            return;
        }

        if ($this->isPost()) {
            $this->validateCsrf();
        }

        $userId = (int)($this->getPost('user_id', 0) ?: $this->getQuery('id', 0));
        if ($userId <= 0) {
            $this->redirect("{$baseUrl}/admin/residents");
            return;
        }

        $resident = $this->residentModel->findById($userId);
        if (!$resident) {
            $this->setFlash('error_message', 'Resident account not found.');
            $this->redirect("{$baseUrl}/admin/residents");
            return;
        }

        $targetRole = strtolower(trim((string)$resident['user_role']));
        if (!in_array($targetRole, ['unit owner', 'tenant'], true)) {
            $this->setFlash('error_message', 'Impersonation is only permitted for Unit Owners and Tenants.');
            $this->redirect("{$baseUrl}/admin/residents/view?id={$userId}");
            return;
        }

        // Preserve original admin session data
        if (empty($_SESSION['impersonator_admin_id'])) {
            $_SESSION['impersonator_admin_id'] = (int)$_SESSION['user_id'];
            $_SESSION['impersonator_role'] = (string)($_SESSION['role'] ?? 'superadmin');
            $_SESSION['impersonator_name'] = (string)($_SESSION['full_name'] ?? 'Secret Admin');
            $_SESSION['is_impersonating'] = true;
        }

        // Switch active user session to target resident
        $_SESSION['user_id'] = (int)$resident['user_id'];
        $_SESSION['email'] = (string)$resident['email'];
        $_SESSION['role'] = $targetRole;
        $_SESSION['full_name'] = (string)$resident['full_name'];
        $_SESSION['initial'] = strtoupper(substr(trim((string)$resident['full_name'] ?: 'R'), 0, 1));

        Logger::info("Secret Admin #{$_SESSION['impersonator_admin_id']} started impersonating resident #{$userId} ({$resident['full_name']} - {$targetRole})");

        if ($targetRole === 'unit owner') {
            $this->redirect("{$baseUrl}/owner/overview");
        } else {
            $this->redirect("{$baseUrl}/tenant/home");
        }
    }

    /**
     * Exit impersonation mode and safely restore the original Secret Admin session.
     */
    public function exitImpersonation(): void {
        $baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $impersonatorId = (int)($_SESSION['impersonator_admin_id'] ?? 0);
        if ($impersonatorId <= 0) {
            $this->redirect("{$baseUrl}/admin/residents");
            return;
        }

        $userModel = new User();
        $adminUser = $userModel->findById($impersonatorId);

        // Clear impersonation state
        unset($_SESSION['impersonator_admin_id'], $_SESSION['impersonator_role'], $_SESSION['impersonator_name'], $_SESSION['is_impersonating']);

        if ($adminUser) {
            $_SESSION['user_id'] = (int)$adminUser['user_id'];
            $_SESSION['email'] = (string)$adminUser['email'];
            $_SESSION['role'] = (string)$adminUser['user_role'];
            $displayInfo = $userModel->getUserDisplayInfo($impersonatorId, (string)$adminUser['user_role']);
            $_SESSION['full_name'] = $displayInfo['full_name'];
            $_SESSION['initial'] = $displayInfo['initial'];
        }

        Logger::info("Secret Admin #{$impersonatorId} exited impersonation and returned to Admin Residents.");

        $this->setFlash('success_message', 'Exited impersonation mode. You are now back in the Admin Portal.');
        $this->redirect("{$baseUrl}/admin/residents");
    }

    /**
     * Secret Admin random temporary password reset with email notification.
     */
    public function resetPassword(): void {
        Middleware::requireRole(['admin', 'superadmin']);
        $baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');

        if (!Middleware::isSuperAdmin()) {
            $this->setFlash('error_message', 'Access denied. Only the secret administrator can reset resident passwords.');
            $this->redirect("{$baseUrl}/admin/residents");
            return;
        }

        $this->validateCsrf();

        $userId = (int)($this->getPost('user_id', 0) ?: $this->getQuery('id', 0));
        if ($userId <= 0) {
            $this->redirect("{$baseUrl}/admin/residents");
            return;
        }

        $resident = $this->residentModel->findById($userId);
        if (!$resident) {
            $this->setFlash('error_message', 'Resident account not found.');
            $this->redirect("{$baseUrl}/admin/residents");
            return;
        }

        // Generate cryptographically secure temporary password
        $tempPassword = User::generateTemporaryPassword();
        $userModel = new User();
        $success = $userModel->adminResetPassword($userId, $tempPassword);

        if (!$success) {
            $this->setFlash('error_message', 'Failed to reset password in the database. Please try again.');
            $this->redirect("{$baseUrl}/admin/residents/view?id={$userId}");
            return;
        }

        // Dispatch automated notification email
        require_once __DIR__ . '/../config/owner_notifications.php';
        $email = (string)($resident['email'] ?? '');
        $fullName = (string)($resident['full_name'] ?? 'Resident');

        $mailSent = notifyResidentOfPasswordReset($email, $fullName, $tempPassword);

        Logger::info("Secret Admin #{$_SESSION['user_id']} reset password for resident #{$userId} ({$email}). Mail delivered: " . ($mailSent ? 'YES' : 'NO'));

        $safeFullName = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');
        $msg = "Password for {$safeFullName} was successfully reset. Temporary password: <span class='font-mono font-bold select-all bg-emerald-100 text-emerald-900 px-2 py-0.5 rounded'>{$tempPassword}</span>";
        if ($mailSent) {
            $msg .= " — A notification email with these credentials was sent to {$email}.";
        } else {
            $msg .= " — (Note: SMTP credentials not active; please share the temporary password with the resident manually.)";
        }

        $this->setFlash('success_message', $msg);
        $this->redirect("{$baseUrl}/admin/residents/view?id={$userId}");
    }
}
