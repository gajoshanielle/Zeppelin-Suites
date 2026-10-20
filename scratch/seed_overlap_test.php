<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getConnection();

// Check if test inquiry exists or create one
$stmt = $db->query("SELECT inq_id FROM inquiry_table WHERE sender_name = 'Existing Tenant (Overlap Test)' LIMIT 1");
$existingInq = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$existingInq) {
    $db->prepare("
        INSERT INTO inquiry_table (
            sender_name, sender_email, sender_contact, inquiry_type,
            Preferred_unit_id, preferred_move_in_time, lease_duration,
            message, status, approval_status, approved_unit_id
        ) VALUES (
            'Existing Tenant (Overlap Test)', 'tenant.overlap@example.com', '+63 9123456789', 'Unit Lease / Rental Reservation',
            'Studio Type A', 'Immediately', '3 months',
            'Mock reservation for overlap testing', 'reservation submitted', 'approved', 5
        )
    ")->execute();
    $inqId = (int)$db->lastInsertId();
} else {
    $inqId = (int)$existingInq['inq_id'];
}

// Check if test reservation already exists on unit 5
$stmt = $db->prepare("SELECT reservation_id FROM reservation_table WHERE unit_id = 5 AND client_remarks = 'Overlap test seed reservation' LIMIT 1");
$stmt->execute();
$existingRes = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$existingRes) {
    $insertStmt = $db->prepare("
        INSERT INTO reservation_table (
            inq_id, unit_id, client_name, client_email, client_contact,
            client_sex, client_nationality, transaction_type,
            move_in_date, move_out_date, lease_signing_date,
            price_basis, required_amount, payment_method,
            payment_status, reservation_status, client_remarks
        ) VALUES (
            ?, 5, 'Active Tenant (Overlap Test)', 'tenant.overlap@example.com', '+63 9123456789',
            'Female', 'Filipino', 'Lease Inquiry',
            '2026-12-01', '2027-03-01', '2026-11-20',
            20000.00, 20000.00, 'GCash QR',
            'verified', 'reserved', 'Overlap test seed reservation'
        )
    ");
    $insertStmt->execute([$inqId]);
    $resId = (int)$db->lastInsertId();
    echo "SUCCESS: Seeded test reservation ID {$resId} for Unit A108 (2026-12-01 to 2027-03-01)\n";
} else {
    echo "EXISTS: Test reservation already present with ID {$existingRes['reservation_id']} for Unit A108 (2026-12-01 to 2027-03-01)\n";
}
