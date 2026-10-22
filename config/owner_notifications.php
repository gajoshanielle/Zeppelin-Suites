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
function sendOwnerNotificationEmail(string $ownerEmail, string $ownerName, string $subject, string $body, ?string $htmlBody = null): bool
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

        if ($htmlBody !== null && trim($htmlBody) !== '') {
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $body;
        } else {
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $body;
        }

        $mail->send();
        return true;

    } catch (PHPMailerException $e) {
        error_log("sendOwnerNotificationEmail failed for {$ownerEmail}: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Generate a beautifully styled, responsive HTML email body matching the Zeppelin Suites design system.
 */
function buildZeppelinEmailHtml(array $options): string
{
    $badgeText = $options['badgeText'] ?? '';
    $badgeType = $options['badgeType'] ?? 'blue';
    $recipientName = htmlspecialchars($options['recipientName'] ?? 'Valued Resident', ENT_QUOTES, 'UTF-8');
    $greeting = $options['greeting'] ?? "Hi {$recipientName},";
    $messageHtml = $options['messageHtml'] ?? '';
    $highlightBox = $options['highlightBox'] ?? null;
    $details = $options['details'] ?? [];
    $reminders = $options['reminders'] ?? null;
    $actionButton = $options['actionButton'] ?? null;
    $calloutBox = $options['calloutBox'] ?? null;
    $reassuranceText = $options['reassuranceText'] ?? 'If you have any questions or require assistance, please contact our leasing administration office.';
    $footerSubtext = $options['footerSubtext'] ?? 'This is an automated notification regarding your condominium unit reservation.';
    $year = date('Y');

    // Badge styling
    $badgeStyles = [
        'blue'    => 'background: #eff6ff; color: #1d4ed8;',
        'emerald' => 'background: #ecfdf5; color: #047857;',
        'amber'   => 'background: #fffbeb; color: #b45309;',
        'rose'    => 'background: #fff1f2; color: #be123c;',
        'slate'   => 'background: #f1f5f9; color: #334155;',
    ];
    $badgeStyle = $badgeStyles[$badgeType] ?? $badgeStyles['blue'];

    $badgeHtml = '';
    if (!empty($badgeText)) {
        $escapedBadge = htmlspecialchars($badgeText, ENT_QUOTES, 'UTF-8');
        $badgeHtml = "<div class=\"badge\" style=\"{$badgeStyle}\">{$escapedBadge}</div>";
    }

    // Highlight Box
    $highlightHtml = '';
    if (!empty($highlightBox) && !empty($highlightBox['value'])) {
        $hlType = $highlightBox['type'] ?? 'emerald';
        $hlStyles = [
            'emerald' => ['bg' => '#f0fdf4', 'border' => '#bbf7d0', 'sub' => '#15803d', 'main' => '#166534'],
            'blue'    => ['bg' => '#eff6ff', 'border' => '#bfdbfe', 'sub' => '#1d4ed8', 'main' => '#1e40af'],
            'amber'   => ['bg' => '#fffbeb', 'border' => '#fde68a', 'sub' => '#b45309', 'main' => '#92400e'],
            'rose'    => ['bg' => '#fff1f2', 'border' => '#fecdd3', 'sub' => '#be123c', 'main' => '#9f1239'],
            'slate'   => ['bg' => '#f8fafc', 'border' => '#cbd5e1', 'sub' => '#64748b', 'main' => '#0f172a'],
        ];
        $st = $hlStyles[$hlType] ?? $hlStyles['emerald'];
        $subText = !empty($highlightBox['label']) ? htmlspecialchars($highlightBox['label'], ENT_QUOTES, 'UTF-8') : '';
        $mainText = htmlspecialchars($highlightBox['value'], ENT_QUOTES, 'UTF-8');
        $isMonospace = !empty($highlightBox['monospace']) ? "font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; letter-spacing: 0.12em;" : "";

        $highlightHtml = <<<HTML
      <div class="date-highlight" style="background: {$st['bg']}; border: 1px solid {$st['border']};">
HTML;
        if ($subText !== '') {
            $highlightHtml .= "<div class=\"date-sub\" style=\"color: {$st['sub']};\">{$subText}</div>";
        }
        $highlightHtml .= "<div class=\"date-main\" style=\"color: {$st['main']}; {$isMonospace}\">{$mainText}</div></div>";
    }

    // Details Card
    $detailsHtml = '';
    if (!empty($details) && is_array($details)) {
        $detailsRows = '';
        foreach ($details as $row) {
            if (empty($row['label']) && empty($row['value'])) continue;
            $lbl = htmlspecialchars($row['label'] ?? '', ENT_QUOTES, 'UTF-8');
            $val = htmlspecialchars($row['value'] ?? '', ENT_QUOTES, 'UTF-8');
            $valStyle = !empty($row['color']) ? "style=\"color: " . htmlspecialchars($row['color'], ENT_QUOTES, 'UTF-8') . ";\"" : "";
            $detailsRows .= <<<ROW
        <div class="details-row">
          <span class="details-label">{$lbl}</span>
          <span class="details-val" {$valStyle}>{$val}</span>
        </div>
ROW;
        }
        if ($detailsRows !== '') {
            $detailsHtml = "<div class=\"details-card\">{$detailsRows}</div>";
        }
    }

    // Callout / Action / Cancel Box
    $calloutHtml = '';
    if (!empty($calloutBox)) {
        $cText = htmlspecialchars($calloutBox['text'] ?? '', ENT_QUOTES, 'UTF-8');
        $cBtnText = htmlspecialchars($calloutBox['btnText'] ?? '', ENT_QUOTES, 'UTF-8');
        $cBtnUrl = htmlspecialchars($calloutBox['btnUrl'] ?? '#', ENT_QUOTES, 'UTF-8');
        $cSub = htmlspecialchars($calloutBox['subtext'] ?? '', ENT_QUOTES, 'UTF-8');
        $cType = $calloutBox['type'] ?? 'rose';

        $cBg = $cType === 'rose' ? '#fff1f2' : '#f0f9ff';
        $cBorder = $cType === 'rose' ? '#ffe4e6' : '#e0f2fe';
        $cColor = $cType === 'rose' ? '#9f1239' : '#0369a1';
        $cBtnBg = $cType === 'rose' ? '#e11d48' : '#0284c7';

        $calloutHtml = <<<HTML
      <div class="cancel-box" style="background: {$cBg}; border: 1px solid {$cBorder};">
        <div class="cancel-text" style="color: {$cColor};">{$cText}</div>
HTML;
        if ($cBtnText !== '') {
            $calloutHtml .= "<a href=\"{$cBtnUrl}\" class=\"cancel-btn\" style=\"background: {$cBtnBg};\">{$cBtnText}</a>";
        }
        if ($cSub !== '') {
            $calloutHtml .= "<div class=\"link-subtext\">{$cSub}</div>";
        }
        $calloutHtml .= "</div>";
    }

    // Single Action Button
    $actionBtnHtml = '';
    if (!empty($actionButton) && !empty($actionButton['text']) && !empty($actionButton['url'])) {
        $abText = htmlspecialchars($actionButton['text'], ENT_QUOTES, 'UTF-8');
        $abUrl = htmlspecialchars($actionButton['url'], ENT_QUOTES, 'UTF-8');
        $actionBtnHtml = <<<HTML
      <div style="text-align: center; margin: 24px 0;">
        <a href="{$abUrl}" class="action-btn">{$abText}</a>
      </div>
HTML;
    }

    // Reminders Box
    $remindersHtml = '';
    if (!empty($reminders) && !empty($reminders['items']) && is_array($reminders['items'])) {
        $rTitle = htmlspecialchars($reminders['title'] ?? 'Important Reminders', ENT_QUOTES, 'UTF-8');
        $rItemsHtml = '';
        foreach ($reminders['items'] as $item) {
            $rItemsHtml .= "<li>" . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . "</li>";
        }
        $remindersHtml = <<<HTML
      <div class="reminders-box">
        <div class="reminders-title">{$rTitle}</div>
        <ul class="reminders-list">
          {$rItemsHtml}
        </ul>
      </div>
HTML;
    }

    $escapedReassurance = !empty($reassuranceText) ? "<p class=\"message\" style=\"margin-bottom:0;\">" . htmlspecialchars($reassuranceText, ENT_QUOTES, 'UTF-8') . "</p>" : "";
    $escapedFooterNote = htmlspecialchars($footerSubtext, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b; }
    .container { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    .header { background: #0f172a; padding: 28px 32px; text-align: center; color: #ffffff; }
    .brand { font-size: 20px; font-weight: 800; letter-spacing: 0.15em; margin: 0; }
    .subbrand { font-size: 10px; font-weight: 500; letter-spacing: 0.3em; color: #94a3b8; margin-top: 4px; }
    .content { padding: 32px; }
    .badge { display: inline-block; padding: 4px 12px; font-size: 12px; font-weight: 600; border-radius: 9999px; margin-bottom: 16px; }
    .greeting { font-size: 18px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 12px; }
    .message { font-size: 14px; line-height: 1.6; color: #475569; margin-bottom: 20px; }
    .date-highlight { border-radius: 14px; padding: 18px 20px; text-align: center; margin: 20px 0; }
    .date-sub { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px; }
    .date-main { font-size: 18px; font-weight: 800; }
    .details-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin: 20px 0; }
    .details-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px; }
    .details-row:last-child { margin-bottom: 0; }
    .details-label { color: #64748b; font-weight: 500; }
    .details-val { color: #0f172a; font-weight: 600; }
    .reminders-box { background: #f8fafc; border-left: 3px solid #0f172a; padding: 14px 16px; margin: 20px 0; border-radius: 0 10px 10px 0; }
    .reminders-title { font-size: 12px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
    .reminders-list { font-size: 13px; color: #475569; line-height: 1.6; margin: 0; padding-left: 18px; }
    .cancel-box { border-radius: 12px; padding: 20px; margin: 24px 0; text-align: center; }
    .cancel-text { font-size: 13px; margin-bottom: 14px; line-height: 1.5; }
    .cancel-btn { display: inline-block; color: #ffffff !important; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; }
    .action-btn { display: inline-block; background: #0f172a; color: #ffffff !important; text-decoration: none; padding: 11px 24px; border-radius: 10px; font-size: 13px; font-weight: 700; }
    .link-subtext { font-size: 11px; color: #94a3b8; word-break: break-all; margin-top: 12px; }
    .footer { padding: 20px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #94a3b8; line-height: 1.6; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="brand">ZEPPELIN SUITES</div>
      <div class="subbrand">RESIDENCE & LEASING</div>
    </div>
    <div class="content">
      {$badgeHtml}
      <h2 class="greeting">{$greeting}</h2>
      <div class="message">
        {$messageHtml}
      </div>

      {$highlightHtml}

      {$detailsHtml}

      {$calloutHtml}

      {$actionBtnHtml}

      {$remindersHtml}

      {$escapedReassurance}
    </div>
    <div class="footer">
      &copy; {$year} Zeppelin Suites. All rights reserved.<br>
      {$escapedFooterNote}
    </div>
  </div>
</body>
</html>
HTML;
}

/**
 * Notify a unit owner that a new reservation request is waiting on them.
 */
function notifyOwnerOfApprovalRequest(string $ownerEmail, string $ownerName, string $unitNumber): bool
{
    $subject = "Zeppelin Suites - New Reservation Request for Unit {$unitNumber}";

    $plainBody = "Hi {$ownerName},\n\n"
        . "A prospective tenant has inquired about your unit ({$unitNumber}), and our admin team would like your approval to proceed with a reservation.\n\n"
        . "Please log in to your unit owner portal to review and respond (approve or decline) at your earliest convenience:\n"
        . OWNER_PORTAL_LOGIN_URL . "\n\n"
        . "If another owner's unit is approved first, this request will automatically close.\n\n"
        . "Thank you,\nZeppelin Suites";

    $htmlBody = buildZeppelinEmailHtml([
        'badgeText'       => 'Approval Required',
        'badgeType'       => 'blue',
        'recipientName'   => $ownerName,
        'greeting'        => "Hi " . htmlspecialchars($ownerName, ENT_QUOTES, 'UTF-8') . ",",
        'messageHtml'     => "A prospective tenant has submitted a reservation inquiry for your unit (<strong>Unit " . htmlspecialchars($unitNumber, ENT_QUOTES, 'UTF-8') . "</strong>). Our administration team requests your review and approval to proceed.",
        'details'         => [
            ['label' => 'Unit Number:', 'value' => 'Unit ' . $unitNumber],
            ['label' => 'Status:', 'value' => 'Pending Owner Review', 'color' => '#1d4ed8'],
        ],
        'actionButton'    => [
            'text' => 'Review Request in Portal',
            'url'  => OWNER_PORTAL_LOGIN_URL,
        ],
        'reminders'       => [
            'title' => 'Important Notes',
            'items' => [
                'Please log in to review and respond (approve or decline) at your earliest convenience.',
                'If another unit is approved first for this applicant, this inquiry request will automatically close.',
            ],
        ],
        'reassuranceText' => 'If you have any questions, please contact the Zeppelin Suites administration office.',
        'footerSubtext'   => 'This is an automated notification for Zeppelin Suites unit owners.',
    ]);

    return sendOwnerNotificationEmail($ownerEmail, $ownerName, $subject, $plainBody, $htmlBody);
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

    $plainBody = "Hi {$ownerName},\n\n"
        . "Your unit ({$unitNumber}) has just been reserved by {$clientName}.\n"
        . ($dateLine !== '' ? $dateLine : "\n")
        . "The payment proof has been submitted and is pending review by our admin team. "
        . "You can check the reservation details anytime in your unit owner portal:\n"
        . OWNER_PORTAL_LOGIN_URL . "\n\n"
        . "Thank you,\nZeppelin Suites";

    $details = [
        ['label' => 'Assigned Unit:', 'value' => 'Unit ' . $unitNumber],
        ['label' => 'Applicant Name:', 'value' => $clientName],
    ];
    if (!empty($moveInDate) && $moveInDate !== 'N/A') {
        $details[] = ['label' => 'Requested Move-In:', 'value' => $moveInDate];
    }
    $details[] = ['label' => 'Status:', 'value' => 'Payment Submitted & Pending Review', 'color' => '#059669'];

    $htmlBody = buildZeppelinEmailHtml([
        'badgeText'       => 'New Reservation',
        'badgeType'       => 'emerald',
        'recipientName'   => $ownerName,
        'greeting'        => "Hi " . htmlspecialchars($ownerName, ENT_QUOTES, 'UTF-8') . ",",
        'messageHtml'     => "Great news! Your unit (<strong>Unit " . htmlspecialchars($unitNumber, ENT_QUOTES, 'UTF-8') . "</strong>) has been reserved by <strong>" . htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') . "</strong>.",
        'details'         => $details,
        'actionButton'    => [
            'text' => 'View in Owner Portal',
            'url'  => OWNER_PORTAL_LOGIN_URL,
        ],
        'reminders'       => [
            'title' => 'Reservation Information',
            'items' => [
                'Downpayment proof has been submitted and is being verified by our administration team.',
                'You can review applicant details and lease signing schedule anytime in your portal.',
            ],
        ],
        'reassuranceText' => 'Thank you for listing your property with Zeppelin Suites.',
        'footerSubtext'   => 'This is an automated reservation alert for unit owners.',
    ]);

    return sendOwnerNotificationEmail($ownerEmail, $ownerName, $subject, $plainBody, $htmlBody);
}

/**
 * Notify a unit owner that the admin left feedback/an update on a maintenance request.
 */
function notifyOwnerOfMaintenanceFeedback(string $ownerEmail, string $ownerName, string $subjectLine, string $status, string $adminRemarks): bool
{
    $subject = "Zeppelin Suites - Update on Your Maintenance Request";

    $plainBody = "Hi {$ownerName},\n\n"
        . "There's an update on your maintenance request \"{$subjectLine}\".\n\n"
        . "Status: " . ucwords($status) . "\n"
        . ($adminRemarks !== '' ? "Admin remarks:\n{$adminRemarks}\n\n" : "\n")
        . "You can view the full details in your unit owner portal:\n"
        . OWNER_PORTAL_LOGIN_URL . "\n\n"
        . "Thank you,\nZeppelin Suites";

    $details = [
        ['label' => 'Request Subject:', 'value' => $subjectLine],
        ['label' => 'Current Status:', 'value' => ucwords($status), 'color' => '#1d4ed8'],
    ];
    if (!empty($adminRemarks)) {
        $details[] = ['label' => 'Admin Remarks:', 'value' => $adminRemarks];
    }

    $htmlBody = buildZeppelinEmailHtml([
        'badgeText'       => 'Maintenance Update',
        'badgeType'       => 'blue',
        'recipientName'   => $ownerName,
        'greeting'        => "Hi " . htmlspecialchars($ownerName, ENT_QUOTES, 'UTF-8') . ",",
        'messageHtml'     => "There is an administrative update on your maintenance request for <strong>\"" . htmlspecialchars($subjectLine, ENT_QUOTES, 'UTF-8') . "\"</strong>.",
        'details'         => $details,
        'actionButton'    => [
            'text' => 'View Maintenance Details',
            'url'  => OWNER_PORTAL_LOGIN_URL,
        ],
        'reassuranceText' => 'If you have further questions regarding maintenance, please contact our facilities office.',
        'footerSubtext'   => 'This is an automated maintenance tracking notification.',
    ]);

    return sendOwnerNotificationEmail($ownerEmail, $ownerName, $subject, $plainBody, $htmlBody);
}

/**
 * Notify a client of unit handover completion with their login credentials (adapted for Tenant or Unit Owner).
 */
function notifyTenantOfHandover(string $tenantEmail, string $tenantName, string $unitDisplay, string $initialPassword, string $accountRole = 'tenant'): bool
{
    $isOwner = strtolower(trim($accountRole)) === 'unit owner';
    $roleName = $isOwner ? 'Unit Owner' : 'Tenant';
    $portalName = $isOwner ? 'Unit Owner Portal' : 'Tenant Portal';

    $subject = $isOwner
        ? "Zeppelin Suites - Unit Ownership Handover Completed & Owner Account Credentials"
        : "Zeppelin Suites - Unit Handover Completed & Tenant Account Credentials";

    $appUrl = rtrim((string)env('APP_URL', 'http://localhost/Zeppelin-Suites'), '/');
    $loginUrl = $appUrl . '/login';

    $portalDescription = $isOwner
        ? "You can now log in to manage your property, upload your personal GCash QR code, review reservation inquiries, and track building announcements."
        : "You can now log in to manage your tenant account, submit service & maintenance requests, and view announcements.";

    $plainBody = "Dear {$tenantName},\n\n"
        . "Congratulations! The unit handover for {$unitDisplay} at Zeppelin Suites has been formally completed.\n\n"
        . "Your {$portalName} account has been created. Once you log in for the first time, your account will be officially activated.\n\n"
        . "Login Credentials:\n"
        . "--------------------------------------------------------\n"
        . "{$portalName} URL: {$loginUrl}\n"
        . "Registered Email:  {$tenantEmail}\n"
        . "Default Password:  {$initialPassword}\n"
        . "--------------------------------------------------------\n\n"
        . "{$portalDescription}\n\n"
        . "For security purposes, please change your password after logging in for the first time.\n\n"
        . "Welcome to Zeppelin Suites!\n\n"
        . "Best regards,\n"
        . "Zeppelin Suites Management Team";

    $htmlBody = buildZeppelinEmailHtml([
        'badgeText'       => 'Handover Complete',
        'badgeType'       => 'emerald',
        'recipientName'   => $tenantName,
        'greeting'        => "Dear " . htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8') . ",",
        'messageHtml'     => "Congratulations! The unit handover for <strong>" . htmlspecialchars($unitDisplay, ENT_QUOTES, 'UTF-8') . "</strong> at Zeppelin Suites has been formally completed. Your official <strong>{$portalName}</strong> account is now ready.",
        'highlightBox'    => [
            'label'     => 'Temporary Password',
            'value'     => $initialPassword,
            'type'      => 'slate',
            'monospace' => true,
        ],
        'details'         => [
            ['label' => 'Account Role:', 'value' => $roleName],
            ['label' => 'Login Email:', 'value' => $tenantEmail],
            ['label' => 'Assigned Unit:', 'value' => $unitDisplay],
            ['label' => 'Account Status:', 'value' => 'Ready for First Login', 'color' => '#059669'],
        ],
        'actionButton'    => [
            'text' => 'Log In to ' . $portalName,
            'url'  => $loginUrl,
        ],
        'reminders'       => [
            'title' => 'Important Security Instructions',
            'items' => [
                'Log in using your registered email and the temporary password provided above.',
                'For your privacy and security, immediately update your password upon your first login.',
                $portalDescription,
            ],
        ],
        'reassuranceText' => 'Welcome to Zeppelin Suites! If you need any assistance, our management office is here to help.',
        'footerSubtext'   => 'This is an official account onboarding notification.',
    ]);

    return sendOwnerNotificationEmail($tenantEmail, $tenantName, $subject, $plainBody, $htmlBody);
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
        $introHtml = "Please be advised that your reservation (<strong>#{$reservationId}</strong>) for <strong>" . htmlspecialchars($unitDisplay, ENT_QUOTES, 'UTF-8') . "</strong> has been cancelled following a cancellation request submitted by {$ownerLabel} and approved by management.";
    } elseif ($roleLower === 'client') {
        $intro = "This is an official confirmation that the cancellation request submitted by you for your reservation (#{$reservationId}) for {$unitDisplay} at Zeppelin Suites has been reviewed and approved by management.\n\n"
            . "The reservation has been cancelled, and the unit hold has been officially released.\n\n";
        $introHtml = "This is an official confirmation that the cancellation request submitted by you for your reservation (<strong>#{$reservationId}</strong>) for <strong>" . htmlspecialchars($unitDisplay, ENT_QUOTES, 'UTF-8') . "</strong> has been reviewed and approved by management.";
    } else {
        $intro = "Please be advised that your reservation (#{$reservationId}) for {$unitDisplay} at Zeppelin Suites has been cancelled by administration.\n\n"
            . "The reservation has been cancelled, and the unit hold has been officially released.\n\n";
        $introHtml = "Please be advised that your reservation (<strong>#{$reservationId}</strong>) for <strong>" . htmlspecialchars($unitDisplay, ENT_QUOTES, 'UTF-8') . "</strong> has been cancelled by administration.";
    }

    $reasonText = (!empty($cancellationReason) && $cancellationReason !== 'No reason provided.' && $cancellationReason !== 'n/a')
        ? "Cancellation Reason:\n{$cancellationReason}\n\n"
        : "";
    $remarksText = (!empty($adminRemarks) && $adminRemarks !== $cancellationReason && $adminRemarks !== 'Approved cancellation request from unit owner.' && $adminRemarks !== 'Approved cancellation request.')
        ? "Admin Remarks:\n{$adminRemarks}\n\n"
        : "";

    $plainBody = "Dear {$clientName},\n\n"
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

    $details = [
        ['label' => 'Reference No:', 'value' => '#' . $reservationId],
        ['label' => 'Assigned Unit:', 'value' => $unitDisplay],
        ['label' => 'Status:', 'value' => 'Cancelled & Unit Released', 'color' => '#be123c'],
    ];
    if (!empty($cancellationReason) && $cancellationReason !== 'No reason provided.' && $cancellationReason !== 'n/a') {
        $details[] = ['label' => 'Cancellation Reason:', 'value' => $cancellationReason];
    }
    if (!empty($adminRemarks) && $adminRemarks !== $cancellationReason && $adminRemarks !== 'Approved cancellation request from unit owner.' && $adminRemarks !== 'Approved cancellation request.') {
        $details[] = ['label' => 'Admin Remarks:', 'value' => $adminRemarks];
    }

    $htmlBody = buildZeppelinEmailHtml([
        'badgeText'       => 'Reservation Cancelled',
        'badgeType'       => 'rose',
        'recipientName'   => $clientName,
        'greeting'        => "Dear " . htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') . ",",
        'messageHtml'     => $introHtml,
        'details'         => $details,
        'reminders'       => [
            'title' => 'Policy & Next Steps',
            'items' => [
                'The unit hold has been officially released back to active leasing availability.',
                'If you have questions regarding payment receipts or deposit policies, please contact our leasing office.',
            ],
        ],
        'reassuranceText' => 'If you require further assistance or wish to explore other units in the future, we remain at your service.',
        'footerSubtext'   => 'This is an official cancellation confirmation from Zeppelin Suites.',
    ]);

    return sendOwnerNotificationEmail($clientEmail, $clientName, $subject, $plainBody, $htmlBody);
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
        $introHtml = "This is an official confirmation that your cancellation request for reservation <strong>#{$reservationId}</strong> by {$clientName} for your unit (<strong>" . htmlspecialchars($unitDisplay, ENT_QUOTES, 'UTF-8') . "</strong>) has been approved by management.";
    } else {
        $intro = "Please be informed that the reservation (#{$reservationId}) by {$clientName} for your unit ({$unitDisplay}) has been officially cancelled following an approved cancellation request.\n\n"
            . "The unit has been released back to active availability.\n\n";
        $introHtml = "Please be informed that the reservation (<strong>#{$reservationId}</strong>) by {$clientName} for your unit (<strong>" . htmlspecialchars($unitDisplay, ENT_QUOTES, 'UTF-8') . "</strong>) has been officially cancelled following an approved cancellation request.";
    }

    $reasonText = (!empty($cancellationReason) && $cancellationReason !== 'No reason provided.' && $cancellationReason !== 'n/a')
        ? "Cancellation Reason:\n{$cancellationReason}\n\n"
        : "";
    $remarksText = (!empty($adminRemarks) && $adminRemarks !== $cancellationReason && $adminRemarks !== 'Approved cancellation request from unit owner.' && $adminRemarks !== 'Approved cancellation request.')
        ? "Admin Remarks:\n{$adminRemarks}\n\n"
        : "";

    $plainBody = "Hi {$ownerName},\n\n"
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

    $details = [
        ['label' => 'Reference No:', 'value' => '#' . $reservationId],
        ['label' => 'Assigned Unit:', 'value' => $unitDisplay],
        ['label' => 'Applicant Name:', 'value' => $clientName],
        ['label' => 'Unit Status:', 'value' => 'Released & Available', 'color' => '#059669'],
    ];
    if (!empty($cancellationReason) && $cancellationReason !== 'No reason provided.' && $cancellationReason !== 'n/a') {
        $details[] = ['label' => 'Cancellation Reason:', 'value' => $cancellationReason];
    }
    if (!empty($adminRemarks) && $adminRemarks !== $cancellationReason && $adminRemarks !== 'Approved cancellation request from unit owner.' && $adminRemarks !== 'Approved cancellation request.') {
        $details[] = ['label' => 'Admin Remarks:', 'value' => $adminRemarks];
    }

    $htmlBody = buildZeppelinEmailHtml([
        'badgeText'       => 'Reservation Cancelled',
        'badgeType'       => 'amber',
        'recipientName'   => $ownerName,
        'greeting'        => "Hi " . htmlspecialchars($ownerName, ENT_QUOTES, 'UTF-8') . ",",
        'messageHtml'     => $introHtml,
        'details'         => $details,
        'actionButton'    => [
            'text' => 'View Property Calendar',
            'url'  => OWNER_PORTAL_LOGIN_URL,
        ],
        'reminders'       => [
            'title' => 'Property Status',
            'items' => [
                'Your unit has been automatically restored to active status on our leasing availability list.',
                'You can review booking calendars and unit status anytime via the Unit Owner Portal.',
            ],
        ],
        'reassuranceText' => 'Thank you for your partnership with Zeppelin Suites.',
        'footerSubtext'   => 'This is an automated property management notice.',
    ]);

    return sendOwnerNotificationEmail($ownerEmail, $ownerName, $subject, $plainBody, $htmlBody);
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
        $introHtml = "Please be advised that your cancellation request for reservation <strong>#{$reservationId}</strong> (<strong>" . htmlspecialchars($unitDisplay, ENT_QUOTES, 'UTF-8') . "</strong>) has been reviewed and declined by administration.<br><br>Your reservation remains active in our system.";
    } else {
        $intro = "Please be advised that the cancellation request submitted for reservation #{$reservationId} ({$unitDisplay}) has been reviewed and declined by administration.\n\n"
            . "The reservation remains active in our system.\n\n";
        $introHtml = "Please be advised that the cancellation request submitted for reservation <strong>#{$reservationId}</strong> (<strong>" . htmlspecialchars($unitDisplay, ENT_QUOTES, 'UTF-8') . "</strong>) has been reviewed and declined by administration.<br><br>The reservation remains active in our system.";
    }

    $plainBody = "Dear {$recipientName},\n\n"
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

    $details = [
        ['label' => 'Reference No:', 'value' => '#' . $reservationId],
        ['label' => 'Assigned Unit:', 'value' => $unitDisplay],
        ['label' => 'Current Status:', 'value' => 'Active (Request Declined)', 'color' => '#059669'],
    ];
    if (!empty($adminRemarks)) {
        $details[] = ['label' => 'Admin Remarks:', 'value' => $adminRemarks];
    }

    $htmlBody = buildZeppelinEmailHtml([
        'badgeText'       => 'Cancellation Declined',
        'badgeType'       => 'amber',
        'recipientName'   => $recipientName,
        'greeting'        => "Dear " . htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8') . ",",
        'messageHtml'     => $introHtml,
        'details'         => $details,
        'reminders'       => [
            'title' => 'Important Information',
            'items' => [
                'The unit hold remains active and reserved under this booking.',
                'If you have questions or require further clarification, please contact our leasing support office.',
            ],
        ],
        'reassuranceText' => 'Zeppelin Suites administration is available to assist you with any questions.',
        'footerSubtext'   => 'This is an official reservation status notification.',
    ]);

    return sendOwnerNotificationEmail($recipientEmail, $recipientName, $subject, $plainBody, $htmlBody);
}

/**
 * Notify a resident (unit owner or tenant) that an administrator has reset their password.
 */
function notifyResidentOfPasswordReset(string $recipientEmail, string $recipientName, string $tempPassword): bool {
    $subject = "Zeppelin Suites - Temporary Password Reset Notification";

    $loginUrl = rtrim((string)env('APP_URL', 'http://localhost/Zeppelin-Suites'), '/') . '/login';

    $plainBody = "Dear {$recipientName},\n\n"
        . "Please be advised that your account password for Zeppelin Suites has been reset by the system administrator.\n\n"
        . "A new temporary password has been generated for your account:\n"
        . "--------------------------------------------------------\n"
        . "Temporary Password: {$tempPassword}\n"
        . "Login Portal:       {$loginUrl}\n"
        . "--------------------------------------------------------\n\n"
        . "Important Security Advice:\n"
        . "1. Log into your account using the temporary password above.\n"
        . "2. For your security, immediately navigate to your Account/Profile Settings and update your password.\n\n"
        . "If you did not request this assistance, please contact the Zeppelin Suites administration office immediately.\n\n"
        . "Thank you,\n"
        . "Zeppelin Suites Administration";

    $htmlBody = buildZeppelinEmailHtml([
        'badgeText'       => 'Security Alert',
        'badgeType'       => 'amber',
        'recipientName'   => $recipientName,
        'greeting'        => "Dear " . htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8') . ",",
        'messageHtml'     => "Please be advised that your account password for Zeppelin Suites has been reset by the system administrator. A new temporary password has been generated for your account:",
        'highlightBox'    => [
            'label'     => 'Temporary Password',
            'value'     => $tempPassword,
            'type'      => 'slate',
            'monospace' => true,
        ],
        'details'         => [
            ['label' => 'Registered Email:', 'value' => $recipientEmail],
            ['label' => 'Login Portal:', 'value' => $loginUrl],
        ],
        'actionButton'    => [
            'text' => 'Go to Login Portal',
            'url'  => $loginUrl,
        ],
        'reminders'       => [
            'title' => 'Security Instructions',
            'items' => [
                'Log into your account using the temporary password above.',
                'For your security, immediately navigate to your Account / Profile Settings and update your password.',
                'If you did not request this reset, please contact Zeppelin Suites administration immediately.',
            ],
        ],
        'reassuranceText' => 'Zeppelin Suites takes your account privacy and security seriously.',
        'footerSubtext'   => 'This is an automated security notification regarding your resident account.',
    ]);

    return sendOwnerNotificationEmail($recipientEmail, $recipientName, $subject, $plainBody, $htmlBody);
}

/**
 * Notify the client that their reservation form submission has been received.
 */
function notifyClientOfReservationSubmission(
    string $clientEmail,
    string $clientName,
    string $unitDisplay,
    string $cancellationLink,
    int $reservationId = 0
): bool {
    $subject = "Zeppelin Suites - Reservation Submission Received";

    $refIdText = $reservationId > 0 ? " (Reference #{$reservationId})" : "";

    $plainBody = "Hi {$clientName},\n\n"
        . "We have received your reservation submission form{$refIdText} for {$unitDisplay} at Zeppelin Suites.\n\n"
        . "Our team is currently reviewing your submission and verifying your payment details. Verification is typically completed within 1 business day.\n\n"
        . "If you wish to cancel or withdraw your reservation submission, you can access the cancellation form at the link below:\n"
        . "{$cancellationLink}\n\n"
        . "Please note: Submitting a cancellation request is subject to administrative review in accordance with our reservation policies.\n\n"
        . "If you have any questions or did not submit this request, please contact our leasing administration office immediately.\n\n"
        . "Thank you,\n"
        . "Zeppelin Suites Management Team";

    $details = [
        ['label' => 'Reference No:', 'value' => '#' . $reservationId],
        ['label' => 'Assigned Unit:', 'value' => $unitDisplay],
        ['label' => 'Status:', 'value' => 'Pending Admin Verification', 'color' => '#1d4ed8'],
    ];

    $htmlBody = buildZeppelinEmailHtml([
        'badgeText'       => 'Submission Received',
        'badgeType'       => 'blue',
        'recipientName'   => $clientName,
        'greeting'        => "Hi " . htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') . ",",
        'messageHtml'     => "We have received your reservation submission form for <strong>" . htmlspecialchars($unitDisplay, ENT_QUOTES, 'UTF-8') . "</strong> at Zeppelin Suites. Our administration team is currently verifying your application and payment details.",
        'details'         => $details,
        'calloutBox'      => [
            'text'    => 'Changed your mind or need to cancel this submission? You can submit a cancellation request through our online cancellation portal:',
            'btnText' => 'Request Cancellation',
            'btnUrl'  => $cancellationLink,
            'subtext' => 'Or copy this link into your browser: ' . $cancellationLink,
            'type'    => 'rose',
        ],
        'reminders'       => [
            'title' => 'Verification Timeline',
            'items' => [
                'Payment verification is typically completed within 1 business day.',
                'You will receive another email confirmation once your payment has been verified by administration.',
            ],
        ],
        'reassuranceText' => 'If you have questions or did not submit this reservation request, please contact our leasing office immediately.',
        'footerSubtext'   => 'This is an automated receipt for your reservation application.',
    ]);

    return sendOwnerNotificationEmail($clientEmail, $clientName, $subject, $plainBody, $htmlBody);
}

/**
 * Notify the client that the unit owner has set and confirmed the lease signing appointment date.
 */
function notifyClientOfLeaseSigningDate(
    string $clientEmail,
    string $clientName,
    int $reservationId,
    string $unitDisplay,
    string $signingDateFormatted,
    string $ownerName,
    string $ownerContact = '',
    string $moveInDateFormatted = ''
): bool {
    $subject = "Zeppelin Suites - Lease Signing Date Confirmed (#{$reservationId})";

    $moveInRowPlain = !empty($moveInDateFormatted) ? "Move-In Date:       {$moveInDateFormatted}\n" : "";
    $ownerContactPlain = !empty($ownerContact) ? " ({$ownerContact})" : "";

    $plainBody = "Hi {$clientName},\n\n"
        . "Good news! Your unit owner, {$ownerName}, has scheduled and confirmed the lease contract signing date for your reservation #{$reservationId} ({$unitDisplay}) at Zeppelin Suites.\n\n"
        . "--------------------------------------------------------\n"
        . "LEASE SIGNING DETAILS\n"
        . "--------------------------------------------------------\n"
        . "Signing Date:       {$signingDateFormatted}\n"
        . "Assigned Unit:      {$unitDisplay}\n"
        . $moveInRowPlain
        . "Unit Owner:         {$ownerName}{$ownerContactPlain}\n"
        . "Status:             Scheduled & Confirmed\n"
        . "--------------------------------------------------------\n\n"
        . "Important Reminders for Signing:\n"
        . "1. Please mark {$signingDateFormatted} on your calendar.\n"
        . "2. Please bring valid government-issued IDs for verification.\n"
        . "3. If you have any schedule conflicts or questions, please coordinate with your unit owner ({$ownerName}) or our leasing administration office.\n\n"
        . "Thank you,\n"
        . "Zeppelin Suites Management Team";

    $details = [
        ['label' => 'Reference No:', 'value' => '#' . $reservationId],
        ['label' => 'Assigned Unit:', 'value' => $unitDisplay],
        ['label' => 'Unit Owner:', 'value' => $ownerName],
    ];
    if (!empty($ownerContact)) {
        $details[] = ['label' => 'Owner Contact:', 'value' => $ownerContact];
    }
    if (!empty($moveInDateFormatted)) {
        $details[] = ['label' => 'Target Move-in:', 'value' => $moveInDateFormatted];
    }
    $details[] = ['label' => 'Status:', 'value' => 'Pending Contract Signing', 'color' => '#059669'];

    $htmlBody = buildZeppelinEmailHtml([
        'badgeText'       => 'Appointment Scheduled',
        'badgeType'       => 'blue',
        'recipientName'   => $clientName,
        'greeting'        => "Hi " . htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') . ",",
        'messageHtml'     => "Your unit owner, <strong>" . htmlspecialchars($ownerName, ENT_QUOTES, 'UTF-8') . "</strong>, has confirmed the lease contract signing date for reservation <strong>#{$reservationId}</strong>.",
        'highlightBox'    => [
            'label' => 'Scheduled Lease Signing Date',
            'value' => $signingDateFormatted,
            'type'  => 'emerald',
        ],
        'details'         => $details,
        'reminders'       => [
            'title' => 'Reminders for Signing',
            'items' => [
                'Please bring valid government-issued IDs for verification.',
                'Ensure all required lease documents are ready for final review.',
                'If you have any schedule conflicts, please coordinate with your unit owner as soon as possible.',
            ],
        ],
        'reassuranceText' => 'If you have any questions or require assistance, please contact the unit owner or Zeppelin Suites leasing administration.',
        'footerSubtext'   => 'This is an automated notification regarding your condominium unit reservation.',
    ]);

    return sendOwnerNotificationEmail($clientEmail, $clientName, $subject, $plainBody, $htmlBody);
}


