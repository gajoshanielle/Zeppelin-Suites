<?php
require_once __DIR__ . '/email_config.php';
require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Send a plain-text notification email to a unit owner.
 *
 * @return bool true if the email was sent, false otherwise (check error_log for details)
 */
function sendOwnerNotificationEmail(string $ownerEmail, string $ownerName, string $subject, string $body): bool
{
    if (trim($ownerEmail) === '' || !filter_var($ownerEmail, FILTER_VALIDATE_EMAIL)) {
        error_log("sendOwnerNotificationEmail: skipped, invalid/missing owner email ('{$ownerEmail}')");
        return false;
    }

    $username = defined('SMTP_USERNAME') ? SMTP_USERNAME : (string)env('SMTP_USERNAME', '');
    $password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : (string)env('SMTP_PASSWORD', '');

    // If SMTP credentials are not configured in local environment, log simulation and succeed
    if (empty($username) || empty($password)) {
        error_log("[Zeppelin Suites Dev Mail] Simulating owner notification to {$ownerEmail} ({$ownerName}) with subject '{$subject}'. SMTP credentials not set in .env.");
        return true;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = str_replace(' ', '', (string)$password);
        $mail->SMTPSecure = (SMTP_PORT === 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($ownerEmail, $ownerName);

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;

    } catch (PHPMailerException $e) {
        error_log("sendOwnerNotificationEmail failed for {$ownerEmail}: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Notify a unit owner that a new reservation request (from an admin
 * approving/forwarding an inquiry) is waiting on them.
 */
function notifyOwnerOfApprovalRequest(string $ownerEmail, string $ownerName, string $unitNumber): bool
{
    $subject = "Zeppelin Suites - New Reservation Request for Unit {$unitNumber}";

    $body = "Hi {$ownerName},\n\n"
        . "A prospective tenant has inquired about your unit ({$unitNumber}), and our admin team would like your approval to proceed with a reservation.\n\n"
        . "Please log in to your unit owner portal to review and respond (approve or decline) at your earliest convenience:\n"
        . OWNER_PORTAL_LOGIN_URL . "\n\n"
        . "If another owner's unit is approved first, this request will automatically close.\n\n"
        . "Thank you,\nZeppelin Suites";

    return sendOwnerNotificationEmail($ownerEmail, $ownerName, $subject, $body);
}

/**
 * Notify a unit owner that their unit has just been reserved by a tenant/buyer.
 */
function notifyOwnerOfNewReservation(string $ownerEmail, string $ownerName, string $unitNumber, string $clientName, ?string $moveInDate = null): bool
{
    $subject = "Zeppelin Suites - Unit {$unitNumber} Has Been Reserved";

    $dateLine = !empty($moveInDate) && $moveInDate !== 'N/A'
        ? "Requested move-in / appointment date: {$moveInDate}\n\n"
        : "";

    $body = "Hi {$ownerName},\n\n"
        . "Your unit ({$unitNumber}) has just been reserved by {$clientName}.\n"
        . ($dateLine !== '' ? $dateLine : "\n")
        . "The payment proof has been submitted and is pending review by our admin team. "
        . "You can check the reservation details anytime in your unit owner portal:\n"
        . OWNER_PORTAL_LOGIN_URL . "\n\n"
        . "Thank you,\nZeppelin Suites";

    return sendOwnerNotificationEmail($ownerEmail, $ownerName, $subject, $body);
}

/**
 * Notify a unit owner that the admin left feedback/an update on a
 * maintenance request they submitted.
 */
function notifyOwnerOfMaintenanceFeedback(string $ownerEmail, string $ownerName, string $subjectLine, string $status, string $adminRemarks): bool
{
    $subject = "Zeppelin Suites - Update on Your Maintenance Request";

    $body = "Hi {$ownerName},\n\n"
        . "There's an update on your maintenance request \"{$subjectLine}\".\n\n"
        . "Status: " . ucwords($status) . "\n"
        . ($adminRemarks !== '' ? "Admin remarks:\n{$adminRemarks}\n\n" : "\n")
        . "You can view the full details in your unit owner portal:\n"
        . OWNER_PORTAL_LOGIN_URL . "\n\n"
        . "Thank you,\nZeppelin Suites";

    return sendOwnerNotificationEmail($ownerEmail, $ownerName, $subject, $body);
}

/**
 * Notify a newly moved-in tenant of unit handover completion with their login credentials.
 */
function notifyTenantOfHandover(string $tenantEmail, string $tenantName, string $unitDisplay, string $initialPassword): bool
{
    $subject = "Zeppelin Suites - Unit Handover Completed & Tenant Account Credentials";
    $appUrl = rtrim((string)env('APP_URL', 'http://localhost/Zeppelin-Suites'), '/');
    $loginUrl = $appUrl . '/login';

    $body = "Dear {$tenantName},\n\n"
        . "Congratulations! The unit handover for {$unitDisplay} at Zeppelin Suites has been formally completed.\n\n"
        . "Your Tenant Portal account has been created. Once you log in for the first time, your account will be officially activated.\n\n"
        . "Login Credentials:\n"
        . "--------------------------------------------------------\n"
        . "Tenant Portal URL: {$loginUrl}\n"
        . "Registered Email:  {$tenantEmail}\n"
        . "Default Password:  {$initialPassword}\n"
        . "--------------------------------------------------------\n\n"
        . "You can now log in to manage your tenant account, submit service & maintenance requests, and view announcements.\n\n"
        . "For security purposes, please change your password after logging in for the first time.\n\n"
        . "Welcome to Zeppelin Suites!\n\n"
        . "Best regards,\n"
        . "Zeppelin Suites Management Team";

    return sendOwnerNotificationEmail($tenantEmail, $tenantName, $subject, $body);
}

/**
 * Notify the client that their reservation has been cancelled.
 */
function notifyClientOfCancellationApproval(
    string $clientEmail,
    string $clientName,
    int $reservationId,
    string $unitDisplay,
    ?string $cancellationReason = null,
    ?string $adminRemarks = null,
    ?string $requestedByRole = null,
    ?string $requestedByName = null
): bool {
    $subject = "Zeppelin Suites - Reservation #{$reservationId} Cancelled";

    $roleLower = strtolower(trim((string)$requestedByRole));
    if ($roleLower === 'unit owner') {
        $ownerLabel = !empty($requestedByName) ? "the unit owner ({$requestedByName})" : "the unit owner";
        $intro = "Please be advised that your reservation (#{$reservationId}) for {$unitDisplay} at Zeppelin Suites has been cancelled following a cancellation request submitted by {$ownerLabel} and approved by management.\n\n"
            . "The unit hold has been officially released.\n\n";
    } elseif ($roleLower === 'client') {
        $intro = "This is an official confirmation that the cancellation request submitted by you for your reservation (#{$reservationId}) for {$unitDisplay} at Zeppelin Suites has been reviewed and approved by management.\n\n"
            . "The reservation has been cancelled, and the unit hold has been officially released.\n\n";
    } else {
        $intro = "Please be advised that your reservation (#{$reservationId}) for {$unitDisplay} at Zeppelin Suites has been cancelled by administration.\n\n"
            . "The reservation has been cancelled, and the unit hold has been officially released.\n\n";
    }

    $reasonText = (!empty($cancellationReason) && $cancellationReason !== 'No reason provided.' && $cancellationReason !== 'n/a')
        ? "Cancellation Reason:\n{$cancellationReason}\n\n"
        : "";
    $remarksText = (!empty($adminRemarks) && $adminRemarks !== $cancellationReason && $adminRemarks !== 'Approved cancellation request from unit owner.' && $adminRemarks !== 'Approved cancellation request.')
        ? "Admin Remarks:\n{$adminRemarks}\n\n"
        : "";

    $body = "Dear {$clientName},\n\n"
        . $intro
        . "--------------------------------------------------------\n"
        . "Reservation ID: #{$reservationId}\n"
        . "Unit:           {$unitDisplay}\n"
        . ($roleLower === 'unit owner' ? "Cancelled By:   Unit Owner Request\n" : "")
        . "Status:         Cancelled\n"
        . "--------------------------------------------------------\n\n"
        . ($reasonText !== '' ? $reasonText : '')
        . ($remarksText !== '' ? $remarksText : '')
        . "If you have questions regarding any payments, refunds, or require further assistance, please contact our administrative office.\n\n"
        . "Thank you,\n"
        . "Zeppelin Suites Management Team";

    return sendOwnerNotificationEmail($clientEmail, $clientName, $subject, $body);
}

/**
 * Notify the unit owner that a reservation cancellation on their unit was approved by admin.
 */
function notifyOwnerOfCancellation(
    string $ownerEmail,
    string $ownerName,
    int $reservationId,
    string $unitDisplay,
    string $clientName,
    ?string $cancellationReason = null,
    ?string $adminRemarks = null,
    ?string $requestedByRole = null
): bool {
    $subject = "Zeppelin Suites - Reservation #{$reservationId} Cancelled for {$unitDisplay}";

    $roleLower = strtolower(trim((string)$requestedByRole));
    if ($roleLower === 'unit owner') {
        $intro = "This is an official confirmation that your cancellation request for reservation (#{$reservationId}) by {$clientName} for your unit ({$unitDisplay}) has been approved by management.\n\n"
            . "The unit has been released back to active availability.\n\n";
    } else {
        $intro = "Please be informed that the reservation (#{$reservationId}) by {$clientName} for your unit ({$unitDisplay}) has been officially cancelled following an approved cancellation request.\n\n"
            . "The unit has been released back to active availability.\n\n";
    }

    $reasonText = (!empty($cancellationReason) && $cancellationReason !== 'No reason provided.' && $cancellationReason !== 'n/a')
        ? "Cancellation Reason:\n{$cancellationReason}\n\n"
        : "";
    $remarksText = (!empty($adminRemarks) && $adminRemarks !== $cancellationReason && $adminRemarks !== 'Approved cancellation request from unit owner.' && $adminRemarks !== 'Approved cancellation request.')
        ? "Admin Remarks:\n{$adminRemarks}\n\n"
        : "";

    $body = "Hi {$ownerName},\n\n"
        . $intro
        . "--------------------------------------------------------\n"
        . "Reservation ID: #{$reservationId}\n"
        . "Unit:           {$unitDisplay}\n"
        . "Client Name:    {$clientName}\n"
        . "Status:         Cancelled\n"
        . "--------------------------------------------------------\n\n"
        . ($reasonText !== '' ? $reasonText : '')
        . ($remarksText !== '' ? $remarksText : '')
        . "You can check your updated property calendar and availability anytime in your portal:\n"
        . OWNER_PORTAL_LOGIN_URL . "\n\n"
        . "Thank you,\n"
        . "Zeppelin Suites Management Team";

    return sendOwnerNotificationEmail($ownerEmail, $ownerName, $subject, $body);
}

/**
 * Notify recipient that a reservation cancellation request was declined by admin.
 */
function notifyCancellationDeclined(
    string $recipientEmail,
    string $recipientName,
    int $reservationId,
    string $unitDisplay,
    string $adminRemarks = '',
    bool $isRequester = true
): bool {
    $subject = "Zeppelin Suites - Reservation #{$reservationId} Cancellation Request Declined";

    $remarksText = !empty($adminRemarks) ? "Admin Remarks:\n{$adminRemarks}\n\n" : "";

    if ($isRequester) {
        $intro = "Please be advised that your cancellation request for reservation #{$reservationId} ({$unitDisplay}) at Zeppelin Suites has been reviewed and declined by administration.\n\n"
            . "Your reservation remains active in our system.\n\n";
    } else {
        $intro = "Please be advised that the cancellation request submitted for reservation #{$reservationId} ({$unitDisplay}) has been reviewed and declined by administration.\n\n"
            . "The reservation remains active in our system.\n\n";
    }

    $body = "Dear {$recipientName},\n\n"
        . $intro
        . "--------------------------------------------------------\n"
        . "Reservation ID: #{$reservationId}\n"
        . "Unit:           {$unitDisplay}\n"
        . "Status:         Active (Cancellation Request Declined)\n"
        . "--------------------------------------------------------\n\n"
        . $remarksText
        . "If you have any questions or require further clarification, please contact our leasing and customer support office.\n\n"
        . "Thank you,\n"
        . "Zeppelin Suites Management Team";

    return sendOwnerNotificationEmail($recipientEmail, $recipientName, $subject, $body);
}
