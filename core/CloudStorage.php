<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';

/**
 * Zeppelin Suites - CloudStorage Service (Supabase Object Storage)
 * Handles uploading, retrieving, and deleting files across the organized zepellin-buckets folders.
 */
class CloudStorage {

    /**
     * Upload raw binary content to Supabase Storage at a specific path.
     *
     * @param string $content Raw file content / binary string
     * @param string $storagePath Relative path inside the bucket (e.g. 'payment_qr/qr_1.png')
     * @param string $mimeType Content MIME type (e.g. 'image/png', 'application/pdf')
     * @return string|null Public URL of the uploaded file on success, null on failure
     */
    public static function uploadRaw(string $content, string $storagePath, string $mimeType = 'application/octet-stream'): ?string {
        $supabaseUrl = rtrim((string)env('SUPABASE_URL'), '/');
        $supabaseKey = (string)env('SUPABASE_KEY');
        $bucket = (string)env('SUPABASE_BUCKET', 'zepellin-buckets');

        if (empty($supabaseUrl) || empty($supabaseKey)) {
            error_log("CloudStorage: Missing SUPABASE_URL or SUPABASE_KEY in .env");
            return null;
        }

        $cleanPath = ltrim($storagePath, '/');
        $endpoint = "{$supabaseUrl}/storage/v1/object/{$bucket}/{$cleanPath}";

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$supabaseKey}",
            "apiKey: {$supabaseKey}",
            "Content-Type: {$mimeType}",
            "x-upsert: true"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            return "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$cleanPath}";
        }

        error_log("CloudStorage Upload Failed [HTTP {$httpCode}]: {$response} (cURL: {$curlError})");
        return null;
    }

    /**
     * Upload a local temporary file (e.g. from $_FILES['input']['tmp_name']) to Supabase.
     */
    public static function uploadFile(string $tmpFilePath, string $storagePath, ?string $mimeType = null): ?string {
        if (!file_exists($tmpFilePath) || !is_readable($tmpFilePath)) {
            error_log("CloudStorage: File does not exist or unreadable: {$tmpFilePath}");
            return null;
        }

        $content = file_get_contents($tmpFilePath);
        if ($content === false) {
            return null;
        }

        if ($mimeType === null) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tmpFilePath) ?: 'application/octet-stream';
            finfo_close($finfo);
        }

        return self::uploadRaw($content, $storagePath, $mimeType);
    }

    /**
     * 1. Upload Unit Owner GCash QR Code
     * Folder: payment_qr/
     */
    public static function uploadPaymentQr(string $tmpPath, string $origName, int $ownerId): ?string {
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION)) ?: 'png';
        $filename = "qr_owner_{$ownerId}_" . time() . '_' . bin2hex(random_bytes(4)) . ".{$ext}";
        $storagePath = "payment_qr/{$filename}";
        return self::uploadFile($tmpPath, $storagePath);
    }

    /**
     * 2. Upload Reservation Payment Proof
     * Folder: reservations/payment_proofs/
     */
    public static function uploadPaymentProof(string $tmpPath, string $origName, int $inqId): ?string {
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION)) ?: 'jpg';
        $filename = "payment_inq_{$inqId}_" . time() . '_' . bin2hex(random_bytes(4)) . ".{$ext}";
        $storagePath = "reservations/payment_proofs/{$filename}";
        return self::uploadFile($tmpPath, $storagePath);
    }

    /**
     * 3. Upload Reservation Documents (Valid ID, TIN, Agreement)
     * Folders: 
     *   - reservations/documents/valid_ids/
     *   - reservations/documents/tin_number/
     *   - reservations/documents/agreement_forms/
     */
    public static function uploadDocument(string $tmpPath, string $origName, string $docCategory, int $reservationId): ?string {
        $subfolder = match (strtolower($docCategory)) {
            'valid_id', 'valid_ids' => 'valid_ids',
            'tin', 'tin_number'     => 'tin_number',
            'agreement', 'agreement_forms' => 'agreement_forms',
            default => 'valid_ids'
        };

        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION)) ?: 'pdf';
        $filename = "doc_res_{$reservationId}_" . time() . '_' . bin2hex(random_bytes(4)) . ".{$ext}";
        $storagePath = "reservations/documents/{$subfolder}/{$filename}";
        return self::uploadFile($tmpPath, $storagePath);
    }

    /**
     * 4. Upload Maintenance Request Photo
     * Folder: maintenances/
     */
    public static function uploadMaintenancePhoto(string $tmpPath, string $origName, int $ticketId): ?string {
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION)) ?: 'jpg';
        $filename = "maint_{$ticketId}_" . time() . '_' . bin2hex(random_bytes(4)) . ".{$ext}";
        $storagePath = "maintenances/{$filename}";
        return self::uploadFile($tmpPath, $storagePath);
    }

    /**
     * 5. Upload Unit Showcase Photo
     * Folder: units/
     */
    public static function uploadUnitPhoto(string $tmpPath, string $origName, string $unitNumber): ?string {
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION)) ?: 'jpg';
        $filename = "unit_{$unitNumber}_" . time() . '_' . bin2hex(random_bytes(4)) . ".{$ext}";
        $storagePath = "units/{$filename}";
        return self::uploadFile($tmpPath, $storagePath);
    }

    /**
     * 6. Upload Database Backup Snapshot
     * Folder: backups/
     */
    public static function uploadBackup(string $sqlContent, string $backupFilename): ?string {
        $storagePath = "backups/{$backupFilename}";
        return self::uploadRaw($sqlContent, $storagePath, 'application/sql');
    }

    /**
     * Delete an object from Supabase by full public URL or relative path.
     */
    public static function deleteObject(string $urlOrPath): bool {
        $supabaseUrl = rtrim((string)env('SUPABASE_URL'), '/');
        $supabaseKey = (string)env('SUPABASE_KEY');
        $bucket = (string)env('SUPABASE_BUCKET', 'zepellin-buckets');

        // Extract relative storage path if full URL passed
        $prefix = "{$supabaseUrl}/storage/v1/object/public/{$bucket}/";
        $relativePath = str_starts_with($urlOrPath, $prefix) 
            ? substr($urlOrPath, strlen($prefix)) 
            : ltrim($urlOrPath, '/');

        $endpoint = "{$supabaseUrl}/storage/v1/object/{$bucket}/{$relativePath}";

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$supabaseKey}",
            "apiKey: {$supabaseKey}"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode === 200 || $httpCode === 204);
    }
}
