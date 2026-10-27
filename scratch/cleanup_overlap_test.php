<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getConnection();

$db->prepare("DELETE FROM reservation_table WHERE client_remarks = 'Overlap test seed reservation'")->execute();
$db->prepare("DELETE FROM inquiry_table WHERE sender_name = 'Existing Tenant (Overlap Test)'")->execute();
echo "CLEANUP: Removed test seed reservations and inquiries.\n";
