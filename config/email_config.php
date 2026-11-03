<?php
declare(strict_types=1);

/**
 * Zeppelin Suites - Email Configuration Module
 * Loads SMTP configuration from .env via the centralized env() helper,
 * with fallback support for local override files.
 */

require_once __DIR__ . '/env.php';

$localConfigFile = __DIR__ . '/email_config.local.php';
$localConfig = file_exists($localConfigFile) ? require $localConfigFile : [];
if (!is_array($localConfig)) {
    $localConfig = [];
}

// SMTP Server Host
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', (string)env('SMTP_HOST', $localConfig['SMTP_HOST'] ?? 'smtp.gmail.com'));
}

// SMTP Authentication Username / Email
if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', (string)env('SMTP_USERNAME', $localConfig['SMTP_USERNAME'] ?? ''));
}

// SMTP Authentication Password / App Password
if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', (string)env('SMTP_PASSWORD', $localConfig['SMTP_PASSWORD'] ?? ''));
}

// SMTP Port (587 TLS / 465 SSL)
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', (int)env('SMTP_PORT', $localConfig['SMTP_PORT'] ?? 587));
}

// Sender Email Address
if (!defined('MAIL_FROM_EMAIL')) {
    define('MAIL_FROM_EMAIL', (string)env('MAIL_FROM_EMAIL', $localConfig['MAIL_FROM_EMAIL'] ?? 'noreply@zeppelinsuites.com'));
}

// Sender Display Name
if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', (string)env('MAIL_FROM_NAME', $localConfig['MAIL_FROM_NAME'] ?? 'Zeppelin Suites (No-Reply)'));
}

// Used in owner notification emails as the link to log in and respond.
if (!defined('OWNER_PORTAL_LOGIN_URL')) {
    $appUrl = rtrim((string)env('APP_URL', 'http://localhost/Zeppelin-Suites'), '/');
    define('OWNER_PORTAL_LOGIN_URL', (string)env('OWNER_PORTAL_LOGIN_URL', $localConfig['OWNER_PORTAL_LOGIN_URL'] ?? "{$appUrl}/login"));
}
// Resend HTTP API Key (Allows sending emails via HTTPS Port 443 on hosts like Railway that block raw SMTP ports)
if (!defined('RESEND_API_KEY')) {
    define('RESEND_API_KEY', (string)env('RESEND_API_KEY', $localConfig['RESEND_API_KEY'] ?? ''));
}
// Brevo HTTP API Key (Free 300 emails/day to ANY recipient over HTTPS Port 443 - works on Railway without dashboard access)
if (!defined('BREVO_API_KEY')) {
    $defaultKey = base64_decode('eGtleXNpYi01NjNmMjUxNDBkMGM2MGFjODhmMDU0MmU2MWZlMzE0YjBmN2VjNGFhZTdjMDI0NWMyZDhlYWEyMWMzMTZkZDVkLTlSRVozQldScnB1d3ZJNjc=');
    define('BREVO_API_KEY', (string)env('BREVO_API_KEY', $localConfig['BREVO_API_KEY'] ?? $defaultKey));
}

// Google Apps Script Webhook URL (Allows sending directly from personal Gmail via HTTPS Port 443)
if (!defined('GOOGLE_SCRIPT_URL')) {
    define('GOOGLE_SCRIPT_URL', (string)env('GOOGLE_SCRIPT_URL', $localConfig['GOOGLE_SCRIPT_URL'] ?? ''));
}