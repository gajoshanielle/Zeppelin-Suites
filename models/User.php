<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Model.php';

/**
 * Zeppelin Suites - User Model
 * Handles all database operations for users_table using PDO prepared statements.
 * Strictly NO HTML output or presentation logic.
 */
class User extends Model {

    /**
     * Find a user record by email address.
     */
    public function findByEmail(string $email): ?array {
        $sql = "SELECT * FROM users_table WHERE email = ? LIMIT 1";
        return $this->fetchOne($sql, [$email]);
    }

    /**
     * Find a user record by user ID.
     */
    public function findById(int $id): ?array {
        $sql = "SELECT * FROM users_table WHERE user_id = ? LIMIT 1";
        return $this->fetchOne($sql, [$id]);
    }

    /**
     * Authenticate user credentials with support for BCrypt and legacy plaintext password auto-upgrade.
     *
     * @param string $email User email
     * @param string $password User entered plaintext password
     * @return array Result array ['success' => bool, 'user' => ?array, 'error' => ?string]
     */
    public function authenticate(string $email, string $password): array {
        $user = $this->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'user'    => null,
                'error'   => 'No account found with this email.',
            ];
        }

        $storedPassword = (string)($user['password'] ?? '');
        $isPasswordValid = false;

        // 1. Verify standard BCrypt hash
        if ($storedPassword !== '' && password_verify($password, $storedPassword)) {
            $isPasswordValid = true;

            // Auto-rehash if password cost/algorithm settings changed
            if (password_needs_rehash($storedPassword, PASSWORD_BCRYPT)) {
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                $this->rehashPassword((int)$user['user_id'], $newHash);
            }
        }
        // 2. Backward compatibility fallback: check for legacy plaintext password and seamlessly upgrade to BCrypt
        elseif ($storedPassword !== '' && hash_equals($storedPassword, $password)) {
            $isPasswordValid = true;
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $this->rehashPassword((int)$user['user_id'], $newHash);
        }

        if (!$isPasswordValid) {
            return [
                'success' => false,
                'user'    => null,
                'error'   => 'Incorrect password. Try again.',
            ];
        }

        // Prevent deactivated resident accounts from authenticating
        if (isset($user['resident_status']) && strtolower((string)$user['resident_status']) === 'inactive') {
            return [
                'success' => false,
                'user'    => null,
                'error'   => 'Your account is currently inactive. Please contact the Zeppelin Suites administration for assistance.',
            ];
        }

        return [
            'success' => true,
            'user'    => $user,
            'error'   => null,
        ];
    }

    /**
     * Update user password hash in the database.
     */
    public function rehashPassword(int $userId, string $newHash): bool {
        $sql = "UPDATE users_table SET password = ? WHERE user_id = ?";
        return $this->execute($sql, [$newHash, $userId]);
    }

    /**
     * Verify the current password and securely replace it for an authenticated user.
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword, string $confirmPassword): array {
        if ($userId <= 0 || $currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            return ['success' => false, 'message' => 'Complete all password fields.'];
        }

        if (!hash_equals($newPassword, $confirmPassword)) {
            return ['success' => false, 'message' => 'The new password and confirmation do not match.'];
        }

        $passwordLength = function_exists('mb_strlen') ? mb_strlen($newPassword, 'UTF-8') : strlen($newPassword);
        if ($passwordLength < 8) {
            return ['success' => false, 'message' => 'The new password must be at least 8 characters long.'];
        }

        $user = $this->fetchOne("SELECT password FROM users_table WHERE user_id = ? LIMIT 1", [$userId]);
        if (!$user || !password_verify($currentPassword, (string)($user['password'] ?? ''))) {
            return ['success' => false, 'message' => 'The current password is incorrect.'];
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        if ($newHash === false || !$this->execute("UPDATE users_table SET password = ? WHERE user_id = ?", [$newHash, $userId])) {
            return ['success' => false, 'message' => 'Unable to change the password. Please try again.'];
        }

        return ['success' => true, 'message' => 'Your password has been changed successfully.'];
    }

    /**
     * Update user profile data.
     */
    public function updateProfile(int $userId, array $data): bool {
        $allowedFields = ['full_name', 'contact', 'additional_email'];
        $setClauses = [];
        $params = [];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $setClauses[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($setClauses)) {
            return false;
        }

        $params[] = $userId;
        $sql = "UPDATE users_table SET " . implode(', ', $setClauses) . " WHERE user_id = ?";
        return $this->execute($sql, $params);
    }

    /**
     * Get user display name and initials.
     */
    public function getUserDisplayInfo(int $userId, string $role): array {
        $user = $this->findById($userId);

        $defaultNames = [
            'superadmin' => 'Secret Admin',
            'admin'      => 'Admin User',
            'tenant'     => 'Tenant',
            'unit owner' => 'Unit Owner',
        ];

        $fullName = $user['full_name'] ?? ($defaultNames[strtolower($role)] ?? 'User');
        $initial = strtoupper(substr($fullName, 0, 1));

        return [
            'full_name' => $fullName,
            'initial'   => $initial,
        ];
    }

    /**
     * Generate a cryptographically secure random temporary password.
     * Example: Zep-k9M2xP7a
     */
    public static function generateTemporaryPassword(): string {
        $chars = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $random = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < 8; $i++) {
            $random .= $chars[random_int(0, $max)];
        }
        return 'Zep-' . $random;
    }

    /**
     * Superadmin direct password reset for a target user.
     */
    public function adminResetPassword(int $userId, string $newPlainPassword): bool {
        $hash = password_hash($newPlainPassword, PASSWORD_BCRYPT);
        $sql = "
            UPDATE users_table 
            SET password = ?,
                reset_code_hash = NULL,
                reset_expires_at = NULL,
                reset_attempts = 0,
                reset_requested_at = NULL
            WHERE user_id = ?
        ";
        return $this->execute($sql, [$hash, $userId]);
    }

    /**
     * Activate resident status upon first login.
     */
    public function activateResidentStatus(int $userId): bool {
        $sql = "UPDATE users_table SET resident_status = 'Active' WHERE user_id = ?";
        return $this->execute($sql, [$userId]);
    }

    /**
     * Create a password reset verification code entry directly on users_table.
     * Enforces rate limiting: Max 1 code request per 60 seconds per email.
     */
    public function createPasswordReset(string $email): array {
        $email = strtolower(trim($email));

        $user = $this->fetchOne(
            "SELECT user_id, reset_requested_at FROM users_table WHERE email = ? LIMIT 1",
            [$email]
        );
        if (!$user) {
            return ['success' => false, 'error' => 'No account found with this email.'];
        }

        // Rate limiting: 60-second cooldown
        if (!empty($user['reset_requested_at'])) {
            $lastRequested = strtotime((string)$user['reset_requested_at']);
            if ((time() - $lastRequested) < 60) {
                return [
                    'success' => false,
                    'rate_limited' => true,
                    'error' => 'A verification code was requested recently. Please wait 60 seconds before requesting another code.'
                ];
            }
        }

        // Generate 6-digit numeric verification code
        $code = (string)random_int(100000, 999999);
        $codeHash = password_hash($code, PASSWORD_BCRYPT);

        $updated = $this->execute(
            "UPDATE users_table 
             SET reset_code_hash = ?, 
                 reset_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE), 
                 reset_attempts = 0, 
                 reset_requested_at = NOW() 
             WHERE user_id = ?",
            [$codeHash, (int)$user['user_id']]
        );

        if (!$updated) {
            return ['success' => false, 'error' => 'Failed to initialize password reset request.'];
        }

        return [
            'success' => true,
            'code' => $code
        ];
    }

    /**
     * Verify an entered 6-digit code for an email directly from users_table.
     */
    public function verifyPasswordResetCode(string $email, string $code): array {
        $email = strtolower(trim($email));
        $code = trim($code);

        $user = $this->fetchOne(
            "SELECT user_id, reset_code_hash, reset_expires_at, reset_attempts 
             FROM users_table 
             WHERE email = ? AND reset_code_hash IS NOT NULL AND reset_expires_at > NOW() 
             LIMIT 1",
            [$email]
        );

        if (!$user) {
            return ['success' => false, 'error' => 'Verification code has expired or was not requested. Please request a new one.'];
        }

        $userId = (int)$user['user_id'];
        $attempts = (int)$user['reset_attempts'];

        if ($attempts >= 5) {
            $this->execute(
                "UPDATE users_table SET reset_code_hash = NULL, reset_expires_at = NULL, reset_attempts = 0 WHERE user_id = ?",
                [$userId]
            );
            return ['success' => false, 'error' => 'Too many failed verification attempts. Please request a new code.'];
        }

        if (!password_verify($code, (string)$user['reset_code_hash'])) {
            $this->execute(
                "UPDATE users_table SET reset_attempts = reset_attempts + 1 WHERE user_id = ?",
                [$userId]
            );
            $remaining = 4 - $attempts;
            $msg = $remaining > 0 
                ? "Invalid verification code. {$remaining} attempt(s) remaining."
                : "Invalid verification code. Maximum attempts reached. Please request a new code.";
            return ['success' => false, 'error' => $msg];
        }

        return ['success' => true, 'user_id' => $userId];
    }

    /**
     * Reset password for an email using a verified code directly on users_table.
     */
    public function resetPasswordWithCode(string $email, string $code, string $newPassword, string $confirmPassword): array {
        $email = strtolower(trim($email));

        if ($newPassword === '' || $confirmPassword === '') {
            return ['success' => false, 'error' => 'Please enter and confirm your new password.'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'error' => 'New password and confirmation do not match.'];
        }

        if (strlen($newPassword) < 6) {
            return ['success' => false, 'error' => 'Password must be at least 6 characters long.'];
        }

        // Re-verify code and expiration
        $verification = $this->verifyPasswordResetCode($email, $code);
        if (!$verification['success']) {
            return $verification;
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $updated = $this->execute(
            "UPDATE users_table 
             SET password = ?, 
                 reset_code_hash = NULL, 
                 reset_expires_at = NULL, 
                 reset_attempts = 0, 
                 reset_requested_at = NULL 
             WHERE email = ?",
            [$newHash, $email]
        );

        if (!$updated) {
            return ['success' => false, 'error' => 'Database error while updating password.'];
        }

        return ['success' => true, 'message' => 'Password reset successfully. You can now sign in with your new password.'];
    }
}
