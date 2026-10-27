<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Model.php';

/**
 * Zeppelin Suites - Inquiry Model
 * Handles all database operations for inquiry_table and associated owner approval requests.
 * Uses PDO prepared statements. Zero direct HTML output.
 */
class Inquiry extends Model {

    /**
     * Get snapshot stats for Inquiries.
     */
    public function getInquiryStats(): array {
        $newTodayRow = $this->fetchOne("SELECT COUNT(*) as count FROM inquiry_table WHERE DATE(timestamp) = CURDATE()");
        $pendingRow = $this->fetchOne("SELECT COUNT(*) as count FROM inquiry_table WHERE status = 'pending' OR status = '' OR status IS NULL");
        $respondedRow = $this->fetchOne("SELECT COUNT(*) as count FROM inquiry_table WHERE status = 'responded'");

        return [
            'newToday'  => (int)($newTodayRow['count'] ?? 0),
            'pending'   => (int)($pendingRow['count'] ?? 0),
            'responded' => (int)($respondedRow['count'] ?? 0),
        ];
    }

    /**
     * Fetch all inquiries with joined units and associated owner requests.
     */
    public function getAllInquiries(): array {
        $sql = "
            SELECT 
                i.inq_id,
                i.sender_name,
                i.sender_email,
                i.sender_contact,
                i.inquiry_type,
                i.Preferred_unit_id,
                i.preferred_move_in_time,
                i.message,
                DATE(i.timestamp) AS date_only,
                i.timestamp,
                i.status,
                i.approval_status,
                i.approved_unit_id,
                i.approval_approved_at,
                DATE_FORMAT(i.approval_approved_at, '%b %d, %Y %h:%i %p') AS approval_approved_at_display,
                i.lease_duration,
                u.unit_number AS approved_unit_number
            FROM inquiry_table i
            LEFT JOIN units_table u ON i.approved_unit_id = u.unit_id
            ORDER BY i.timestamp DESC, i.inq_id DESC
        ";

        $rows = $this->fetchAll($sql);

        // Batch load owner approval requests
        $reqSql = "
            SELECT
                r.request_id,
                r.inq_id,
                r.unit_id,
                r.request_status,
                r.owner_remarks,
                r.requested_at,
                r.responded_at,
                DATE_FORMAT(r.requested_at, '%b %d, %Y %h:%i %p') AS requested_at_display,
                DATE_FORMAT(r.responded_at, '%b %d, %Y %h:%i %p') AS responded_at_display,
                u.unit_number,
                u.unit_type,
                owner.full_name AS owner_name
            FROM owner_approval_table r
            LEFT JOIN units_table u ON r.unit_id = u.unit_id
            LEFT JOIN users_table owner ON r.unit_owner_id = owner.user_id
            WHERE NOT EXISTS (
                SELECT 1
                FROM owner_approval_table newer
                WHERE newer.inq_id = r.inq_id
                  AND newer.unit_id = r.unit_id
                  AND newer.request_id > r.request_id
            )
            ORDER BY r.requested_at ASC
        ";
        $allRequests = $this->fetchAll($reqSql);

        $requestsByInquiry = [];
        foreach ($allRequests as $req) {
            $inqId = (int)$req['inq_id'];
            if (!isset($requestsByInquiry[$inqId])) {
                $requestsByInquiry[$inqId] = [];
            }
            $requestsByInquiry[$inqId][] = [
                'request_id'           => (int)$req['request_id'],
                'unit_id'              => (int)$req['unit_id'],
                'unit_number'          => $req['unit_number'] ?? 'Unknown unit',
                'unit_type'            => $req['unit_type'] ?? '',
                'owner_name'           => $req['owner_name'] ?? 'Unknown owner',
                'request_status'       => (string)($req['request_status'] ?? ''),
                'owner_remarks'        => (string)($req['owner_remarks'] ?? ''),
                'requested_at'         => (string)($req['requested_at'] ?? ''),
                'responded_at'         => (string)($req['responded_at'] ?? ''),
                'requested_at_display' => (string)($req['requested_at_display'] ?? ''),
                'responded_at_display' => (string)($req['responded_at_display'] ?? ''),
            ];
        }

        foreach ($rows as &$row) {
            $inqId = (int)$row['inq_id'];
            $requests = $requestsByInquiry[$inqId] ?? [];
            $pendingCount = 0;
            foreach ($requests as $r) {
                if (strtolower($r['request_status']) === 'pending') {
                    $pendingCount++;
                }
            }
            $row['requests'] = $requests;
            $row['pending_request_count'] = $pendingCount;
        }
        unset($row);

        return $rows;
    }

    /**
     * Get single inquiry by ID with joined unit, approved owner, and approval request info.
     */
    public function getById(int $inqId): ?array {
        $sql = "
            SELECT 
                i.inq_id,
                i.sender_name,
                i.sender_email,
                i.sender_contact,
                i.inquiry_type,
                i.Preferred_unit_id,
                i.preferred_move_in_time,
                i.lease_duration,
                i.message,
                i.status,
                i.approval_status,
                i.approved_unit_id,
                i.approval_approved_at,
                i.reservation_token,
                i.timestamp,
                DATE_FORMAT(i.approval_approved_at, '%b %d, %Y %h:%i %p') AS approved_at_display,
                u.unit_number AS approved_unit_number,
                u.unit_type AS approved_unit_type,
                u.lease_rate AS approved_lease_rate,
                u.sqm AS approved_sqm,
                u.floor_number AS approved_floor_number,
                owner.full_name AS approved_owner_name
            FROM inquiry_table i
            LEFT JOIN units_table u ON i.approved_unit_id = u.unit_id
            LEFT JOIN owner_approval_table r
                ON r.inq_id = i.inq_id
                AND r.unit_id = i.approved_unit_id
                AND r.request_status = 'approved'
            LEFT JOIN users_table owner ON r.unit_owner_id = owner.user_id
            WHERE i.inq_id = ?
            LIMIT 1
        ";

        $row = $this->fetchOne($sql, [$inqId]);
        if (!$row) return null;

        // Fetch all approved units for this inquiry
        $approvedSql = "
            SELECT 
                r.request_id,
                r.unit_id,
                r.unit_owner_id,
                r.owner_remarks,
                DATE_FORMAT(r.responded_at, '%b %d, %Y %h:%i %p') AS responded_at_display,
                u.unit_number,
                u.unit_type,
                u.lease_rate,
                u.sqm,
                u.floor_number,
                owner.full_name AS owner_name,
                owner.email AS owner_email,
                owner.contact AS owner_contact
            FROM owner_approval_table r
            INNER JOIN units_table u ON r.unit_id = u.unit_id
            LEFT JOIN users_table owner ON r.unit_owner_id = owner.user_id
            WHERE r.inq_id = ? AND r.request_status = 'approved'
            ORDER BY r.responded_at ASC, u.unit_number ASC
        ";
        $approvedUnits = $this->fetchAll($approvedSql, [$inqId]);
        $row['approved_units'] = $approvedUnits;

        // Ensure reservation token exists if any owner has approved
        $hasApproved = count($approvedUnits) > 0 || strtolower((string)($row['approval_status'] ?? '')) === 'approved';
        if ($hasApproved && empty($row['reservation_token'])) {
            $newToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
            $this->execute(
                "UPDATE inquiry_table SET approval_status = 'approved', reservation_token = ?, reservation_token_expires_at = ? WHERE inq_id = ?",
                [$newToken, $expiresAt, $inqId]
            );
            $row['reservation_token'] = $newToken;
            $row['approval_status'] = 'approved';
        }

        // If approved_unit_number wasn't joined via approved_unit_id, populate from the first approved unit
        if (empty($row['approved_unit_number']) && count($approvedUnits) > 0) {
            $first = $approvedUnits[0];
            $row['approved_unit_number'] = $first['unit_number'];
            $row['approved_unit_type']   = $first['unit_type'];
            $row['approved_lease_rate']  = $first['lease_rate'];
            $row['approved_sqm']         = $first['sqm'];
            $row['approved_floor_number']= $first['floor_number'];
            $row['approved_owner_name']  = $first['owner_name'];
        }

        return $row;
    }

    public const ALLOWED_STATUSES = [
        'pending',
        'responded',
        'onhold',
        'declined',
        'reservation submitted',
        'officially booked',
    ];

    /**
     * Update an inquiry's status directly.
     */
    public function updateStatus(int $inqId, string $status): bool {
        $cleanStatus = strtolower(trim($status));
        if ($cleanStatus === 'on hold') {
            $cleanStatus = 'onhold';
        }

        if (!in_array($cleanStatus, self::ALLOWED_STATUSES, true)) {
            return false;
        }

        $sql = "UPDATE inquiry_table SET status = ? WHERE inq_id = ?";
        return $this->execute($sql, [$cleanStatus, $inqId]);
    }

    /**
     * Send email reply to an inquiry and record status update.
     */
    public function sendReply(int $inqId, string $replyTo, string $subject, string $emailBody): array {
        $inquiry = $this->getById($inqId);
        if (!$inquiry) {
            return ['success' => false, 'error' => 'Inquiry not found.'];
        }

        // Auto-append reservation token link if approved and not already included
        if (
            strtolower((string)($inquiry['approval_status'] ?? '')) === 'approved' &&
            !empty($inquiry['reservation_token'])
        ) {
            $token = (string)$inquiry['reservation_token'];
            $baseUrl = rtrim((string)env('APP_URL', 'http://localhost/Zeppelin-Suites'), '/');
            $reservationLink = "{$baseUrl}/reservation?token=" . urlencode($token);

            if (strpos($emailBody, $token) === false) {
                $emailBody .= "\n\nPlease proceed to finalize your reservation at the link below:\n" . $reservationLink;
            }
        }

        // Require email config and PHPMailer
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

        $username = defined('SMTP_USERNAME') ? SMTP_USERNAME : (string)env('SMTP_USERNAME', '');
        $password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : (string)env('SMTP_PASSWORD', '');

        // If credentials are blank (common in local XAMPP/development environments),
        // simulate the email send and update the inquiry status to 'responded'.
        if (empty($username) || empty($password)) {
            error_log("[Zeppelin Suites Dev Mail] Simulating email delivery to {$replyTo} for Inquiry #{$inqId}. Subject: {$subject}");

            $updateSql = "
                UPDATE inquiry_table
                SET status = 'responded'
                WHERE inq_id = ?
            ";
            $this->execute($updateSql, [$inqId]);

            return [
                'success' => true,
                'simulated' => true,
                'message' => 'Email simulated successfully (SMTP credentials are not configured in .env for local testing). Inquiry status updated to Responded.'
            ];
        }

        if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            return ['success' => false, 'error' => 'PHPMailer library unavailable.'];
        }

        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = defined('SMTP_HOST') ? SMTP_HOST : (string)env('SMTP_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = str_replace(' ', '', (string)$password);
            $port = defined('SMTP_PORT') ? (int)SMTP_PORT : (int)env('SMTP_PORT', 587);
            $mail->Port = $port;
            $mail->SMTPSecure = ($port === 465)
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            require_once dirname(__DIR__) . '/config/owner_notifications.php';
            $clientName = trim((string)($inquiry['sender_name'] ?? 'Valued Client'));
            $unitType = trim((string)($inquiry['inquiry_type'] ?? 'Condominium Unit'));
            $moveIn = trim((string)($inquiry['preferred_move_in_time'] ?? ''));
            $duration = trim((string)($inquiry['lease_duration'] ?? ''));

            $details = [
                ['label' => 'Inquiry Reference:', 'value' => '#' . $inqId],
                ['label' => 'Interested In:', 'value' => $unitType],
            ];
            if (!empty($moveIn)) {
                $details[] = ['label' => 'Target Move-In:', 'value' => $moveIn];
            }
            if (!empty($duration)) {
                $details[] = ['label' => 'Lease Duration:', 'value' => $duration];
            }

            $actionButton = null;
            if (!empty($reservationLink)) {
                $actionButton = [
                    'text' => 'Finalize Your Reservation',
                    'url'  => $reservationLink,
                ];
            }

            $escapedBody = nl2br(htmlspecialchars($emailBody, ENT_QUOTES, 'UTF-8'));

            $htmlBody = buildZeppelinEmailHtml([
                'badgeText'       => 'Inquiry Response',
                'badgeType'       => 'blue',
                'recipientName'   => $clientName,
                'greeting'        => "Hi " . htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') . ",",
                'messageHtml'     => "Thank you for inquiring about Zeppelin Suites. In response to your inquiry regarding <strong>" . htmlspecialchars($unitType, ENT_QUOTES, 'UTF-8') . "</strong>:<div style=\"background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin: 18px 0; color: #1e293b; font-size: 14px; line-height: 1.6;\">{$escapedBody}</div>",
                'details'         => $details,
                'actionButton'    => $actionButton,
                'reminders'       => [
                    'title' => 'Next Steps',
                    'items' => [
                        'You may reply directly to this email to continue your inquiry with our leasing specialists.',
                        'Unit viewings can be scheduled during regular building operating hours.',
                    ],
                ],
                'reassuranceText' => 'We look forward to welcoming you to Zeppelin Suites.',
                'footerSubtext'   => 'This is an official response from Zeppelin Suites Leasing Administration.',
            ]);

            $fromEmail = defined('MAIL_FROM_EMAIL') ? MAIL_FROM_EMAIL : (string)env('MAIL_FROM_EMAIL', 'noreply@zeppelinsuites.com');
            $fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : (string)env('MAIL_FROM_NAME', 'Zeppelin Suites');

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($replyTo);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $emailBody;

            $mail->send();

            // Update status
            $updateSql = "
                UPDATE inquiry_table
                SET status = 'responded'
                WHERE inq_id = ?
            ";
            $this->execute($updateSql, [$inqId]);

            return ['success' => true, 'message' => 'Reply email sent successfully. Inquiry status updated to Responded.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check available units for a given inquiry based on unit type and move-in timeline.
     */
    public function getAvailableUnits(int $inqId, string $unitType): array {
        $inquiry = $this->getById($inqId);
        if (!$inquiry) {
            return ['success' => false, 'message' => 'Inquiry not found.'];
        }

        $inquiryTypeNormalized = strtolower(trim((string)($inquiry['inquiry_type'] ?? '')));
        $isResale = ($inquiryTypeNormalized === 'resale inquiry')
            || strpos($inquiryTypeNormalized, 'resale') !== false
            || strpos($inquiryTypeNormalized, 'buy') !== false
            || strpos($inquiryTypeNormalized, 'purchase') !== false;

        if ($isResale) {
            $sql = "
                SELECT 
                    u.unit_id,
                    u.unit_number,
                    u.unit_type,
                    u.sqm,
                    COALESCE(u.reselling_price, u.lease_rate) AS lease_rate,
                    u.unit_owner_id,
                    u.unit_current_status,
                    owner.full_name AS owner_name
                FROM units_table u
                LEFT JOIN users_table owner ON u.unit_owner_id = owner.user_id
                WHERE u.unit_type = ?
                AND (u.unit_current_status = 'For Resale' OR u.unit_current_status = 'Resale')
                AND u.unit_owner_id IS NOT NULL
                AND u.unit_id NOT IN (
                    SELECT unit_id FROM owner_approval_table
                    WHERE inq_id = ? AND request_status IN ('pending', 'approved')
                )
                ORDER BY u.unit_number ASC
            ";
            $rows = $this->fetchAll($sql, [$unitType, $inqId]);

            $units = [];
            foreach ($rows as $row) {
                $units[] = [
                    'unit_id'              => (int)$row['unit_id'],
                    'unit_number'          => $row['unit_number'],
                    'unit_type'            => $row['unit_type'],
                    'sqm'                  => (float)($row['sqm'] ?? 0),
                    'lease_rate'           => $row['lease_rate'],
                    'unit_owner_id'        => (int)$row['unit_owner_id'],
                    'owner_name'           => $row['owner_name'] ?? 'Assigned Owner',
                    'unit_status'          => $row['unit_current_status'],
                    'is_resale'            => true,
                    'availability_start'   => 'Available for Resale',
                    'availability_end'     => 'For Resale',
                    'limited_availability' => false,
                    'next_booking_date'    => null,
                ];
            }

            return [
                'success'   => true,
                'is_resale' => true,
                'count'     => count($units),
                'units'     => $units,
            ];
        }

        // Rental / Lease Inquiry Flow
        $today = new \DateTime('today');
        $movePreference = strtolower(trim((string)($inquiry['preferred_move_in_time'] ?? '')));
        $isImmediately = in_array($movePreference, ['immediately', 'immediately (within 30 days)', 'immediate'], true)
            || strpos($movePreference, 'immediate') !== false;
        $earliestMoveIn = clone $today;
        $latestMoveIn = clone $today;

        if ($isImmediately) {
            // Immediate move-in requires the unit to be vacant/ready for occupancy today
            // $latestMoveIn remains today
        } else {
            switch ($movePreference) {
                case 'within 1 month':
                    $latestMoveIn->modify('+1 month');
                    break;
                case 'next month (1-2 months)':
                case 'next month (1–2 months)':
                    $earliestMoveIn->modify('+1 month');
                    $latestMoveIn->modify('+2 months');
                    break;
                case 'within 1–3 months':
                case 'within 1-3 months':
                    $earliestMoveIn->modify('+1 month');
                    $latestMoveIn->modify('+3 months');
                    break;
                case 'in 2-3 months':
                case 'in 2–3 months':
                    $earliestMoveIn->modify('+2 months');
                    $latestMoveIn->modify('+3 months');
                    break;
                case 'within 3–6 months':
                case 'within 3-6 months':
                case 'in 3-6 months':
                case 'in 3–6 months':
                    $earliestMoveIn->modify('+3 months');
                    $latestMoveIn->modify('+6 months');
                    break;
                case 'flexible / not sure yet':
                case 'flexible':
                default:
                    $latestMoveIn->modify('+6 months');
                    break;
            }
        }

        $leaseDuration = strtolower(trim((string)($inquiry['lease_duration'] ?? '')));
        $months = 12;
        if (strpos($leaseDuration, 'month') !== false) {
            if (preg_match('/\d+/', $leaseDuration, $matches)) {
                $months = (int)$matches[0];
            }
        } elseif (strpos($leaseDuration, 'year') !== false) {
            if (preg_match('/\d+/', $leaseDuration, $matches)) {
                $months = (int)$matches[0] * 12;
            }
        } elseif (strpos($leaseDuration, 'longer') !== false) {
            $months = 36;
        }

        $sql = "
            SELECT 
                u.unit_id,
                u.unit_number,
                u.unit_type,
                u.sqm,
                u.lease_rate,
                u.unit_owner_id,
                u.unit_current_status,
                owner.full_name AS owner_name,
                MAX(r.move_out_date) AS latest_move_out,
                MAX(b.end_date) AS latest_blocked_out
            FROM units_table u
            LEFT JOIN users_table owner
                ON u.unit_owner_id = owner.user_id
            LEFT JOIN reservation_table r
                ON u.unit_id = r.unit_id
                AND LOWER(r.reservation_status) NOT IN ('cancelled','rejected')
                AND r.move_in_date <= CURDATE()
                AND r.move_out_date >= CURDATE()
            LEFT JOIN unit_blocked_dates_table b
                ON u.unit_id = b.unit_id
                AND b.start_date <= CURDATE()
                AND b.end_date >= CURDATE()
            WHERE u.unit_type = ?
            AND u.unit_current_status NOT IN ('For Resale', 'Resale', 'On Hold', 'Under maintenance', 'Archived')
            AND u.unit_owner_id IS NOT NULL
            AND u.unit_id NOT IN (
                SELECT unit_id FROM owner_approval_table
                WHERE inq_id = ? AND request_status IN ('pending', 'approved')
            )
            GROUP BY u.unit_id
            ORDER BY u.unit_number ASC
        ";

        $rows = $this->fetchAll($sql, [$unitType, $inqId]);
        $candidates = [];

        foreach ($rows as $row) {
            $unitStatus = strtolower(trim((string)($row['unit_current_status'] ?? '')));
            $isOccupiedOrBusy = !empty($row['latest_move_out'])
                || !empty($row['latest_blocked_out'])
                || in_array($unitStatus, ['occupied', 'reserved'], true);

            // If unit is marked occupied or reserved with no active lease move-out date, it's indefinitely unavailable
            if (in_array($unitStatus, ['occupied', 'reserved'], true) && empty($row['latest_move_out'])) {
                continue;
            }

            $busyEndDates = [];
            if (!empty($row['latest_move_out'])) {
                $busyEndDates[] = new \DateTime($row['latest_move_out']);
            }
            if (!empty($row['latest_blocked_out'])) {
                $busyEndDates[] = new \DateTime($row['latest_blocked_out']);
            }

            $availableDate = empty($busyEndDates) ? clone $today : max($busyEndDates);
            $availableDate->setTime(0, 0, 0);

            if ($availableDate < $earliestMoveIn) {
                $availableDate = clone $earliestMoveIn;
            }
            if ($availableDate < $today) {
                $availableDate = clone $today;
            }

            // If tenant wants to move in immediately, occupied or busy units must not be suggested
            if ($isImmediately && ($isOccupiedOrBusy || $availableDate > $today)) {
                continue;
            }

            if ($availableDate > $latestMoveIn) {
                continue;
            }

            $candidates[(int)$row['unit_id']] = [
                'row' => $row,
                'availableDate' => $availableDate
            ];
        }

        $bookingsByUnit = [];
        if (!empty($candidates)) {
            $unitIds = array_keys($candidates);
            $placeholders = implode(',', array_fill(0, count($unitIds), '?'));

            $resSql = "
                SELECT unit_id, move_in_date, move_out_date
                FROM reservation_table
                WHERE unit_id IN ($placeholders)
                AND LOWER(reservation_status) NOT IN ('cancelled','rejected')
                AND move_in_date IS NOT NULL
                AND (move_out_date >= CURDATE() OR move_out_date IS NULL)
                ORDER BY move_in_date ASC
            ";
            $resRows = $this->fetchAll($resSql, $unitIds);
            foreach ($resRows as $nextRow) {
                $bookingsByUnit[(int)$nextRow['unit_id']][] = [
                    'move_in'  => new \DateTime($nextRow['move_in_date']),
                    'move_out' => !empty($nextRow['move_out_date']) ? new \DateTime($nextRow['move_out_date']) : null,
                    'source'   => 'reservation'
                ];
            }

            $blockSql = "
                SELECT unit_id, start_date, end_date, block_type, remarks
                FROM unit_blocked_dates_table
                WHERE unit_id IN ($placeholders)
                AND start_date IS NOT NULL
                AND (end_date >= CURDATE() OR end_date IS NULL)
                ORDER BY start_date ASC
            ";
            $blockRows = $this->fetchAll($blockSql, $unitIds);
            foreach ($blockRows as $bRow) {
                $bookingsByUnit[(int)$bRow['unit_id']][] = [
                    'move_in'    => new \DateTime($bRow['start_date']),
                    'move_out'   => !empty($bRow['end_date']) ? new \DateTime($bRow['end_date']) : null,
                    'source'     => 'blocked_date',
                    'block_type' => $bRow['block_type'] ?? 'Blocked'
                ];
            }

            foreach ($bookingsByUnit as $uId => &$intervals) {
                usort($intervals, function($a, $b) {
                    if ($a['move_in'] == $b['move_in']) return 0;
                    return ($a['move_in'] < $b['move_in']) ? -1 : 1;
                });
            }
            unset($intervals);
        }

        $minStayDays = 30;
        $units = [];
        foreach ($candidates as $unitId => $candidate) {
            $row = $candidate['row'];
            $availableDate = clone $candidate['availableDate'];

            $limitedAvailability = false;
            $nextBookingDate = null;
            $cappingBookingMoveIn = null;
            $cappingSource = 'reservation';
            $cappingType = null;

            foreach (($bookingsByUnit[$unitId] ?? []) as $booking) {
                if ($booking['move_in'] <= $availableDate) {
                    if ($booking['move_out'] !== null && $booking['move_out'] > $availableDate) {
                        $availableDate = clone $booking['move_out'];
                    }
                    continue;
                }

                $gapDays = (int)$availableDate->diff($booking['move_in'])->days;
                if ($gapDays < $minStayDays) {
                    if ($booking['move_out'] === null) {
                        $availableDate = null;
                        break;
                    }
                    $availableDate = clone $booking['move_out'];
                    continue;
                }

                $cappingBookingMoveIn = clone $booking['move_in'];
                $cappingSource = $booking['source'] ?? 'reservation';
                $cappingType = $booking['block_type'] ?? null;
                break;
            }

            if ($availableDate === null || $availableDate > $latestMoveIn) {
                continue;
            }

            $leaseEnd = clone $availableDate;
            $leaseEnd->modify("+{$months} months");

            if ($cappingBookingMoveIn !== null && $cappingBookingMoveIn < $leaseEnd) {
                $leaseEnd = clone $cappingBookingMoveIn;
                $limitedAvailability = true;
                $nextBookingDate = $cappingBookingMoveIn->format('F d, Y');
            }

            $units[] = [
                'unit_id'              => (int)$row['unit_id'],
                'unit_number'          => $row['unit_number'],
                'unit_type'            => $row['unit_type'],
                'sqm'                  => (float)($row['sqm'] ?? 0),
                'lease_rate'           => $row['lease_rate'],
                'unit_owner_id'        => (int)$row['unit_owner_id'],
                'owner_name'           => $row['owner_name'] ?? 'Assigned Owner',
                'availability_start'   => $availableDate->format('F d, Y'),
                'availability_end'     => $leaseEnd->format('F d, Y'),
                'limited_availability' => $limitedAvailability,
                'next_booking_date'    => $nextBookingDate,
                'limited_reason'       => ($cappingSource === 'blocked_date') ? ($cappingType ?: 'Blocked') : 'Reserved'
            ];
        }

        return [
            'success'   => true,
            'is_resale' => false,
            'count'     => count($units),
            'units'     => $units
        ];
    }

    /**
     * Send owner approval requests for selected units.
     */
    public function sendApprovalRequests(int $inqId, array $unitIds): array {
        if ($inqId <= 0 || empty($unitIds)) {
            return ['success' => false, 'message' => 'Invalid parameters.'];
        }

        $this->db->beginTransaction();

        try {
            // Delete existing pending request for this batch of units to reset cleanly
            $deleteStmt = $this->db->prepare("
                DELETE FROM owner_approval_table 
                WHERE inq_id = ? AND request_status = 'pending' AND unit_id = ?
            ");
            foreach ($unitIds as $uId) {
                $deleteStmt->execute([$inqId, (int)$uId]);
            }

            // Fetch unit owners for the selected units
            $unitSelectStmt = $this->db->prepare("
                SELECT u.unit_id, u.unit_number, u.unit_owner_id,
                       owner.full_name AS owner_name, owner.email AS owner_email
                FROM units_table u
                LEFT JOIN users_table owner ON u.unit_owner_id = owner.user_id
                WHERE u.unit_id = ? AND u.unit_owner_id IS NOT NULL
            ");

            $insertStmt = $this->db->prepare("
                INSERT INTO owner_approval_table (inq_id, unit_id, unit_owner_id, request_status)
                VALUES (?, ?, ?, 'pending')
            ");

            $inserted = 0;
            $notifyList = [];

            foreach ($unitIds as $uId) {
                $unitSelectStmt->execute([(int)$uId]);
                $row = $unitSelectStmt->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['unit_owner_id'])) {
                    $insertStmt->execute([$inqId, (int)$row['unit_id'], (int)$row['unit_owner_id']]);
                    $inserted++;

                    $notifyList[] = [
                        'unit_number' => $row['unit_number'],
                        'owner_name'  => $row['owner_name'] ?? 'Unit Owner',
                        'owner_email' => $row['owner_email'] ?? '',
                    ];
                }
            }

            if ($inserted === 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No valid units with assigned owners found.'];
            }

            $updateInqStmt = $this->db->prepare("
                UPDATE inquiry_table
                SET approval_status = 'requested'
                WHERE inq_id = ?
            ");
            $updateInqStmt->execute([$inqId]);

            $this->db->commit();

            // Send notification emails
            $notificationFile = dirname(__DIR__) . '/config/owner_notifications.php';
            if (file_exists($notificationFile)) {
                require_once $notificationFile;
                if (function_exists('notifyOwnerOfApprovalRequest')) {
                    foreach ($notifyList as $n) {
                        notifyOwnerOfApprovalRequest(
                            $n['owner_email'] ?? '',
                            $n['owner_name'] ?? 'Unit Owner',
                            $n['unit_number'] ?? ''
                        );
                    }
                }
            }

            // Retrieve updated requests list
            $requestsSql = "
                SELECT
                    r.request_id,
                    r.unit_id,
                    r.request_status,
                    r.owner_remarks,
                    r.requested_at,
                    r.responded_at,
                    DATE_FORMAT(r.requested_at, '%b %d, %Y %h:%i %p') AS requested_at_display,
                    DATE_FORMAT(r.responded_at, '%b %d, %Y %h:%i %p') AS responded_at_display,
                    u.unit_number,
                    u.unit_type,
                    owner.full_name AS owner_name
                FROM owner_approval_table r
                LEFT JOIN units_table u ON r.unit_id = u.unit_id
                LEFT JOIN users_table owner ON r.unit_owner_id = owner.user_id
                WHERE r.inq_id = ?
                  AND NOT EXISTS (
                    SELECT 1
                    FROM owner_approval_table newer
                    WHERE newer.inq_id = r.inq_id
                      AND newer.unit_id = r.unit_id
                      AND newer.request_id > r.request_id
                  )
                ORDER BY r.requested_at ASC
            ";
            $allRequests = $this->fetchAll($requestsSql, [$inqId]);
            $pendingCount = 0;
            foreach ($allRequests as &$req) {
                if (strtolower((string)($req['request_status'] ?? '')) === 'pending') {
                    $pendingCount++;
                }
            }
            unset($req);

            return [
                'success'         => true,
                'message'         => 'Approval requests sent successfully.',
                'inserted'        => $inserted,
                'approval_status' => 'requested',
                'pending_count'   => $pendingCount,
                'requests'        => $allRequests
            ];

        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Cancel a pending owner approval request.
     */
    public function cancelApprovalRequest(int $inqId, int $requestId): array {
        if ($inqId <= 0 || $requestId <= 0) {
            return ['success' => false, 'message' => 'Invalid parameters.'];
        }

        $this->db->beginTransaction();

        try {
            $checkSql = "
                SELECT request_id, request_status
                FROM owner_approval_table
                WHERE request_id = ? AND inq_id = ?
                FOR UPDATE
            ";
            $request = $this->fetchOne($checkSql, [$requestId, $inqId]);
            if (!$request) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Request not found.'];
            }

            if (strtolower((string)$request['request_status']) !== 'pending') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'This request has already been responded to and cannot be cancelled.'];
            }

            $delStmt = $this->db->prepare("DELETE FROM owner_approval_table WHERE request_id = ?");
            $delStmt->execute([$requestId]);

            $pendingRow = $this->fetchOne("
                SELECT COUNT(*) AS pending_count
                FROM owner_approval_table
                WHERE inq_id = ? AND request_status = 'pending'
            ", [$inqId]);
            $pendingCount = (int)($pendingRow['pending_count'] ?? 0);

            $totalRow = $this->fetchOne("
                SELECT COUNT(*) AS total_count
                FROM owner_approval_table
                WHERE inq_id = ?
            ", [$inqId]);
            $totalCount = (int)($totalRow['total_count'] ?? 0);

            if ($totalCount === 0) {
                $resetStmt = $this->db->prepare("
                    UPDATE inquiry_table
                    SET approval_status = 'not_requested'
                    WHERE inq_id = ? AND approval_status != 'approved'
                ");
                $resetStmt->execute([$inqId]);
            }

            $this->db->commit();

            $inqRow = $this->fetchOne("SELECT status, approval_status FROM inquiry_table WHERE inq_id = ?", [$inqId]);

            return [
                'success'         => true,
                'message'         => 'Approval request cancelled successfully.',
                'pending_count'   => $pendingCount,
                'approval_status' => $inqRow['approval_status'] ?? 'not_requested',
                'status'          => $inqRow['status'] ?? 'pending'
            ];

        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => 'Error cancelling request: ' . $e->getMessage()];
        }
    }

    /**
     * Assign a specific approved unit to an inquiry for reservation.
     */
    public function assignApprovedUnit(int $inqId, int $unitId): array {
        if ($inqId <= 0 || $unitId <= 0) {
            return ['success' => false, 'message' => 'Invalid parameters.'];
        }

        $this->db->beginTransaction();

        try {
            // Check that this unit has an approved request for this inquiry
            $checkSql = "
                SELECT r.request_id, r.request_status, r.owner_remarks, u.unit_number, u.unit_type, u.lease_rate
                FROM owner_approval_table r
                LEFT JOIN units_table u ON r.unit_id = u.unit_id
                WHERE r.inq_id = ? AND r.unit_id = ? AND r.request_status = 'approved'
                LIMIT 1
            ";
            $req = $this->fetchOne($checkSql, [$inqId, $unitId]);
            if (!$req) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'The selected unit has not been approved by its owner.'];
            }

            // Fetch current inquiry token if already present
            $inq = $this->fetchOne("SELECT reservation_token, approval_approved_at FROM inquiry_table WHERE inq_id = ?", [$inqId]);
            $token = !empty($inq['reservation_token']) ? $inq['reservation_token'] : bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

            $updateSql = "
                UPDATE inquiry_table
                SET approval_status = 'approved',
                    approved_unit_id = ?,
                    reservation_token = ?,
                    reservation_token_expires_at = ?,
                    approval_approved_at = COALESCE(approval_approved_at, NOW())
                WHERE inq_id = ?
            ";
            $this->execute($updateSql, [$unitId, $token, $expiresAt, $inqId]);

            $this->db->commit();

            $approvedAtRow = $this->fetchOne("SELECT DATE_FORMAT(approval_approved_at, '%b %d, %Y %h:%i %p') AS approved_at_display FROM inquiry_table WHERE inq_id = ?", [$inqId]);

            return [
                'success'              => true,
                'message'              => 'Unit ' . ($req['unit_number'] ?? '') . ' assigned successfully.',
                'approved_unit'        => $req['unit_number'] ?? '',
                'approved_unit_id'     => $unitId,
                'approved_at'          => $approvedAtRow['approved_at_display'] ?? date('M d, Y h:i A'),
                'owner_remarks'        => $req['owner_remarks'] ?? '',
                'approval_status'      => 'approved',
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => 'Error assigning unit: ' . $e->getMessage()];
        }
    }

    /**
     * Create a new public visitor inquiry submitted from the contact form.
     */
    public function createPublicInquiry(array $data): array {
        $senderName = trim((string)($data['sender_name'] ?? ''));
        $senderEmail = trim((string)($data['sender_email'] ?? ''));
        $senderContact = trim((string)($data['sender_contact'] ?? ''));
        $inquiryType = trim((string)($data['inquiry_type'] ?? ''));
        $preferredUnit = !empty($data['Preferred_unit_id']) ? trim((string)$data['Preferred_unit_id']) : null;
        $preferredMoveIn = !empty($data['preferred_move_in_time']) ? trim((string)$data['preferred_move_in_time']) : null;
        $leaseDuration = !empty($data['lease_duration']) ? trim((string)$data['lease_duration']) : null;
        if ($leaseDuration !== null && (stripos($leaseDuration, 'longer') !== false || stripos($leaseDuration, '3 year') !== false)) {
            $leaseDuration = '1 year';
        }
        $message = trim((string)($data['Message'] ?? $data['message'] ?? ''));

        if ($senderName === '' || $senderEmail === '' || $senderContact === '' || $inquiryType === '') {
            return ['success' => false, 'error' => 'Please fill in all required fields.'];
        }

        if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Please enter a valid email address.'];
        }

        if ($preferredMoveIn !== null) {
            $preferredMoveIn = str_replace(['–', '—', '?"', 'â€“'], '-', $preferredMoveIn);
        }

        $sql = "
            INSERT INTO inquiry_table (
                sender_name,
                sender_email,
                sender_contact,
                inquiry_type,
                Preferred_unit_id,
                preferred_move_in_time,
                lease_duration,
                message,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ";

        try {
            $this->execute($sql, [
                $senderName,
                $senderEmail,
                $senderContact,
                $inquiryType,
                $preferredUnit,
                $preferredMoveIn,
                $leaseDuration,
                $message
            ]);

            return ['success' => true, 'inq_id' => (int)$this->db->lastInsertId()];
        } catch (\Throwable $e) {
            error_log('createPublicInquiry error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error submitting inquiry. Please try again.'];
        }
    }
}

