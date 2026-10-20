<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Middleware.php';
require_once __DIR__ . '/../models/User.php';

/**
 * Zeppelin Suites - Authentication Controller
 * Handles login presentations, credential verification, session lifecycle, and role dispatching.
 * Zero direct SQL or presentation markup permitted.
 */
class AuthController extends Controller {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Display the login interface.
     */
    public function showLogin(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // If user is already authenticated, redirect to their role-specific dashboard
        if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
            $dashboardUrl = Middleware::getDashboardUrlForRole((string)$_SESSION['role']);
            $this->redirect($dashboardUrl);
            return;
        }

        // Check for error flash message or error GET parameter
        $errorMessage = $this->getFlash('error_message');
        if (!$errorMessage && isset($_GET['error'])) {
            if ($_GET['error'] === 'no_user') {
                $errorMessage = 'No account found with this email.';
            } elseif ($_GET['error'] === 'wrong_password') {
                $errorMessage = 'Incorrect password. Try again.';
            }
        }

        $baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');

        $this->render('auth/login', [
            'pageTitle'    => 'Zeppelin Suites - Login',
            'errorMessage' => $errorMessage,
            'actionUrl'    => "{$baseUrl}/login",
            'baseUrl'      => $baseUrl,
        ], 'auth');
    }

    /**
     * Handle login form submission.
     */
    public function login(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');
        $loginUrl = "{$baseUrl}/login";

        if (!$this->isPost()) {
            $this->setFlash('error_message', 'Invalid request method.');
            $this->redirect($loginUrl);
            return;
        }

        $email = filter_var(trim((string)$this->getPost('email', '')), FILTER_SANITIZE_EMAIL);
        $password = (string)$this->getPost('password', '');

        if (empty($email) || empty($password)) {
            $errorMsg = 'Please enter both email and password.';
            if ($this->isAjax()) {
                $this->json(['status' => 'error', 'message' => $errorMsg], 400);
            }
            $this->setFlash('error_message', $errorMsg);
            $this->redirect($loginUrl);
            return;
        }

        // Delegate credential verification to the User Model
        $result = $this->userModel->authenticate($email, $password);

        if (!$result['success']) {
            if ($this->isAjax()) {
                $this->json(['status' => 'error', 'message' => $result['error']], 401);
            }
            $this->setFlash('error_message', $result['error']);
            $this->redirect($loginUrl);
            return;
        }

        $user = $result['user'];

        // Automatically activate account on first login if resident status is Inactive
        if (isset($user['resident_status']) && strtolower((string)$user['resident_status']) === 'inactive') {
            $this->userModel->activateResidentStatus((int)$user['user_id']);
            $user['resident_status'] = 'Active';
        }

        // Securely initialize session
        session_regenerate_id(true);

        $role = Middleware::normalizeRole((string)($user['user_role'] ?? ''));
        $displayInfo = $this->userModel->getUserDisplayInfo((int)$user['user_id'], $role);

        $_SESSION['user_id']   = (int)$user['user_id'];
        $_SESSION['email']     = (string)($user['email'] ?? '');
        $_SESSION['role']      = $role;
        $_SESSION['full_name'] = $displayInfo['full_name'];
        $_SESSION['initial']   = $displayInfo['initial'];

        // Determine destination dashboard
        $destination = Middleware::getDashboardUrlForRole($role);

        if ($this->isAjax()) {
            $this->json([
                'status'   => 'success',
                'redirect' => $destination,
                'user'     => [
                    'id'        => $_SESSION['user_id'],
                    'role'      => $_SESSION['role'],
                    'full_name' => $_SESSION['full_name'],
                ],
            ]);
        }

        $this->redirect($destination);
    }

    /**
     * Handle user logout and session destruction.
     */
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear all session variables
        $_SESSION = [];

        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy the session storage
        session_destroy();

        $baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');
        $this->redirect("{$baseUrl}/login");
    }

    /**
     * Send password reset verification code via AJAX POST.
     * Always returns generic success response to prevent user enumeration.
     */
    public function sendPasswordResetCode(): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method.'], 405);
            return;
        }

        $email = filter_var(trim((string)$this->getPost('email', '')), FILTER_SANITIZE_EMAIL);
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Please enter a valid email address.'], 400);
            return;
        }

        $email = strtolower($email);
        $user = $this->userModel->findByEmail($email);

        // Generic response message to prevent email enumeration
        $genericSuccessMsg = 'If your email is registered in our system, you will receive a 6-digit verification code shortly.';

        if (!$user) {
            // Sleep briefly to mitigate timing attacks, then return generic message
            usleep(250000);
            $this->json(['success' => true, 'message' => $genericSuccessMsg]);
            return;
        }

        $resetResult = $this->userModel->createPasswordReset($email);
        if (!$resetResult['success']) {
            if (!empty($resetResult['rate_limited'])) {
                $this->json(['success' => false, 'message' => $resetResult['error']], 429);
                return;
            }
            $this->json(['success' => false, 'message' => $resetResult['error'] ?? 'Unable to generate verification code.'], 500);
            return;
        }

        $code = (string)$resetResult['code'];
        $userName = (string)($user['full_name'] ?? 'User');

        // Dispatch email using project's existing email configuration & PHPMailer
        $this->dispatchPasswordResetEmail($email, $userName, $code);

        $this->json([
            'success' => true,
            'message' => $genericSuccessMsg,
        ]);
    }

    /**
     * Verify submitted 6-digit reset code via AJAX POST.
     */
    public function verifyPasswordResetCode(): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method.'], 405);
            return;
        }

        $email = filter_var(trim((string)$this->getPost('email', '')), FILTER_SANITIZE_EMAIL);
        $code = trim((string)$this->getPost('code', ''));

        if (empty($email) || empty($code)) {
            $this->json(['success' => false, 'message' => 'Email and verification code are required.'], 400);
            return;
        }

        $result = $this->userModel->verifyPasswordResetCode($email, $code);
        if (!$result['success']) {
            $this->json(['success' => false, 'message' => $result['error']], 400);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Verification code confirmed. You may now enter your new password.',
        ]);
    }

    /**
     * Set new password via AJAX POST.
     */
    public function resetPassword(): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method.'], 405);
            return;
        }

        $email = filter_var(trim((string)$this->getPost('email', '')), FILTER_SANITIZE_EMAIL);
        $code = trim((string)$this->getPost('code', ''));
        $newPassword = (string)$this->getPost('new_password', '');
        $confirmPassword = (string)$this->getPost('confirm_password', '');

        if (empty($email) || empty($code) || empty($newPassword) || empty($confirmPassword)) {
            $this->json(['success' => false, 'message' => 'All fields are required.'], 400);
            return;
        }

        $result = $this->userModel->resetPasswordWithCode($email, $code, $newPassword, $confirmPassword);
        if (!$result['success']) {
            $this->json(['success' => false, 'message' => $result['error']], 400);
            return;
        }

        $this->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }

    /**
     * Dispatch password reset email using existing PHPMailer & config setup.
     */
    private function dispatchPasswordResetEmail(string $email, string $name, string $code): bool {
        $emailConfigFile = dirname(__DIR__) . '/config/email_config.php';
        if (file_exists($emailConfigFile)) {
            require_once $emailConfigFile;
        }

        $mailerDir = dirname(__DIR__) . '/phpmailer/src';
        if (file_exists($mailerDir . '/PHPMailer.php')) {
            require_once $mailerDir . '/Exception.php';
            require_once $mailerDir . '/PHPMailer.php';
            require_once $mailerDir . '/SMTP.php';
        }

        $subject = 'Zeppelin Suites - Password Reset Verification Code';
        $body = "Dear {$name},\n\n"
            . "We received a request to reset your password for your Zeppelin Suites account.\n\n"
            . "Your 6-digit verification code is:\n\n"
            . "        {$code}\n\n"
            . "This code is valid for 10 minutes. For security reasons, do not share this code with anyone.\n\n"
            . "If you did not request a password reset, you can safely ignore this email. Your password will remain unchanged.\n\n"
            . "Thank you,\n"
            . "Zeppelin Suites Management Team";

        $username = defined('SMTP_USERNAME') ? SMTP_USERNAME : (string)env('SMTP_USERNAME', '');
        $password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : (string)env('SMTP_PASSWORD', '');

        // If credentials are blank, simulate delivery in developer log
        if (empty($username) || empty($password)) {
            error_log("[Zeppelin Suites Dev Mail] Simulating password reset email to {$email}. Code: {$code}");
            return true;
        }

        if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            error_log("PHPMailer library unavailable for password reset email to {$email}");
            return false;
        }

        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : (string)env('SMTP_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = $username;
            $mail->Password   = str_replace(' ', '', (string)$password);
            $mail->SMTPSecure = (defined('SMTP_PORT') && SMTP_PORT === 465)
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];

            $mail->setFrom(
                defined('MAIL_FROM_EMAIL') ? MAIL_FROM_EMAIL : (string)env('MAIL_FROM_EMAIL', 'noreply@zeppelinsuites.com'),
                defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : (string)env('MAIL_FROM_NAME', 'Zeppelin Suites')
            );
            $mail->addAddress($email, $name);
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            error_log("Failed to send password reset email to {$email}: " . $e->getMessage());
            return false;
        }
    }
}
