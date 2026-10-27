<?php
declare(strict_types=1);

/**
 * Zeppelin Suites - Request Reservation Cancellation View
 *
 * @var array  $reservation
 * @var string $token
 * @var string $baseUrl
 * @var string $pageTitle
 * @var string $activePage
 */

$unitTitle = trim(($reservation['unit_type'] ?? 'Unit') . (!empty($reservation['unit_number']) ? ' — Unit ' . $reservation['unit_number'] : ''));
$isLease = stripos((string)($reservation['transaction_type'] ?? ''), 'resale') === false;
$submittedDate = !empty($reservation['created_at']) ? date('M d, Y h:i A', strtotime((string)$reservation['created_at'])) : '—';
$moveInFormatted = !empty($reservation['move_in_date']) ? date('M d, Y', strtotime((string)$reservation['move_in_date'])) : '—';
$moveOutFormatted = !empty($reservation['move_out_date']) ? date('M d, Y', strtotime((string)$reservation['move_out_date'])) : '—';
$signingFormatted = !empty($reservation['lease_signing_date'])
    ? date('M d, Y', strtotime((string)$reservation['lease_signing_date']))
    : (!empty($reservation['is_flexible_signing']) ? 'Flexible Schedule' : '—');

$paymentProofUrl = '';
if (!empty($reservation['payment_proof'])) {
    $proof = (string)$reservation['payment_proof'];
    $paymentProofUrl = (str_starts_with($proof, 'http://') || str_starts_with($proof, 'https://'))
        ? $proof
        : rtrim($baseUrl, '/') . '/' . ltrim($proof, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Cancel Reservation — Zeppelin Suites') ?></title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Google Font: DM Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&display=swap" rel="stylesheet">

  <style>
    * {
      font-family: 'DM Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col">

  <!-- TOP HEADER / NAVBAR -->
  <header class="bg-white border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex items-center gap-4">
        <a href="<?= htmlspecialchars($baseUrl) ?>/" class="inline-block shrink-0">
          <img
            src="<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/zeppelin-logo.png"
            alt="Zeppelin Suites"
            class="h-10 sm:h-11 w-auto object-contain"
            onerror="this.outerHTML='<span class=\'text-lg font-bold tracking-tight text-slate-900\'>ZEPPELIN SUITES</span>'"
          >
        </a>
        <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>
        <div>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
            Cancellation Request
          </span>
          <p class="text-xs text-slate-500 mt-0.5">Zeppelin Suites Reservation Management</p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <a href="<?= htmlspecialchars($baseUrl) ?>/" class="text-xs font-medium text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
          Return to Home
        </a>
      </div>
    </div>
  </header>

  <!-- MAIN CONTENT CONTAINER -->
  <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 flex-1 w-full">

    <!-- PAGE TITLE SECTION -->
    <div class="mb-8">
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-950 tracking-tight">
        Reservation Cancellation
      </h1>
      <p class="text-sm sm:text-base text-slate-600 mt-1 max-w-2xl">
        Review your submitted reservation details and unit owner information below before confirming your cancellation request.
      </p>
    </div>

    <!-- 2-COLUMN LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

      <!-- LEFT COLUMN: UNIT OWNER & SUBMITTED RESERVATION FORM (7 COLS) -->
      <div class="lg:col-span-7 space-y-6">

        <!-- 1. UNIT & UNIT OWNER INFORMATION CARD -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-slate-900 text-white flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h2 class="text-base font-bold text-slate-900 leading-tight">Unit &amp; Unit Owner Information</h2>
                <p class="text-xs text-slate-500">Property and owner contact details</p>
              </div>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-slate-100 text-slate-700">
              <?= htmlspecialchars($reservation['listing_type'] ?? ($isLease ? 'For Lease' : 'Resale')) ?>
            </span>
          </div>

          <!-- Unit Overview -->
          <div class="bg-slate-50 rounded-lg p-4 border border-slate-200/80 mb-5">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Reserved Unit</p>
            <p class="text-lg font-bold text-slate-900 mt-0.5">
              <?= htmlspecialchars($unitTitle) ?>
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-3 pt-3 border-t border-slate-200/60 text-xs">
              <div>
                <span class="text-slate-500 block">Floor Number:</span>
                <span class="font-semibold text-slate-800">Floor <?= htmlspecialchars((string)($reservation['floor_number'] ?? '—')) ?></span>
              </div>
              <div>
                <span class="text-slate-500 block">Floor Area:</span>
                <span class="font-semibold text-slate-800"><?= htmlspecialchars((string)($reservation['sqm'] ?? '—')) ?> sqm</span>
              </div>
              <div>
                <span class="text-slate-500 block">Stay Category:</span>
                <span class="font-semibold text-slate-800"><?= htmlspecialchars((string)($reservation['stay_category'] ?? 'Long term')) ?></span>
              </div>
            </div>
          </div>

          <!-- Owner Details -->
          <div>
            <p class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Unit Owner Contact</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="p-3 bg-white rounded-lg border border-slate-200">
                <span class="text-xs text-slate-500 block">Owner Name</span>
                <span class="text-sm font-bold text-slate-900 block mt-0.5">
                  <?= htmlspecialchars((string)($reservation['owner_name'] ?? 'Zeppelin Suites Administration')) ?>
                </span>
              </div>

              <div class="p-3 bg-white rounded-lg border border-slate-200">
                <span class="text-xs text-slate-500 block">Contact Number</span>
                <span class="text-sm font-semibold text-slate-900 block mt-0.5">
                  <?= htmlspecialchars((string)($reservation['owner_contact'] ?? '—')) ?>
                </span>
              </div>

              <div class="p-3 bg-white rounded-lg border border-slate-200">
                <span class="text-xs text-slate-500 block">Email Address</span>
                <span class="text-sm font-semibold text-slate-900 block mt-0.5 truncate" title="<?= htmlspecialchars((string)($reservation['owner_email'] ?? '—')) ?>">
                  <?= htmlspecialchars((string)($reservation['owner_email'] ?? '—')) ?>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. SUBMITTED RESERVATION FORM DETAILS CARD -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.5L19 9.5V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div>
                <h2 class="text-base font-bold text-slate-900 leading-tight">Submitted Reservation Form</h2>
                <p class="text-xs text-slate-500">Submitted on <?= htmlspecialchars($submittedDate) ?></p>
              </div>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-amber-50 text-amber-700 border border-amber-200">
              <?= htmlspecialchars(ucwords((string)($reservation['reservation_status'] ?? 'Submitted'))) ?>
            </span>
          </div>

          <div class="space-y-5 text-sm">

            <!-- Client Info Grid -->
            <div>
              <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Applicant Information</p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-lg border border-slate-200/70 text-xs sm:text-sm">
                <div>
                  <span class="text-slate-500 text-xs block">Applicant Name</span>
                  <span class="font-bold text-slate-900"><?= htmlspecialchars((string)($reservation['client_name'] ?? '—')) ?></span>
                </div>
                <div>
                  <span class="text-slate-500 text-xs block">Email Address</span>
                  <span class="font-medium text-slate-900"><?= htmlspecialchars((string)($reservation['client_email'] ?? '—')) ?></span>
                </div>
                <div>
                  <span class="text-slate-500 text-xs block">Contact Number</span>
                  <span class="font-medium text-slate-900"><?= htmlspecialchars((string)($reservation['client_contact'] ?? '—')) ?></span>
                </div>
                <div>
                  <span class="text-slate-500 text-xs block">Sex / Nationality</span>
                  <span class="font-medium text-slate-900">
                    <?= htmlspecialchars((string)($reservation['client_sex'] ?? '—')) ?> / <?= htmlspecialchars((string)($reservation['client_nationality'] ?? '—')) ?>
                  </span>
                </div>
              </div>
            </div>

            <!-- Schedule & Terms -->
            <div>
              <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Reservation Terms &amp; Dates</p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-lg border border-slate-200/70 text-xs sm:text-sm">
                <div>
                  <span class="text-slate-500 text-xs block">Transaction Type</span>
                  <span class="font-semibold text-slate-900"><?= htmlspecialchars((string)($reservation['transaction_type'] ?? 'Unit Lease')) ?></span>
                </div>
                <?php if ($isLease): ?>
                  <div>
                    <span class="text-slate-500 text-xs block">Lease Duration</span>
                    <span class="font-semibold text-slate-900"><?= htmlspecialchars((string)($reservation['lease_duration'] ?? '1 year')) ?></span>
                  </div>
                  <div>
                    <span class="text-slate-500 text-xs block">Move-in Date</span>
                    <span class="font-semibold text-slate-900"><?= htmlspecialchars($moveInFormatted) ?></span>
                  </div>
                  <div>
                    <span class="text-slate-500 text-xs block">Move-out Date</span>
                    <span class="font-semibold text-slate-900"><?= htmlspecialchars($moveOutFormatted) ?></span>
                  </div>
                <?php endif; ?>
                <div>
                  <span class="text-slate-500 text-xs block">Agreement Signing Schedule</span>
                  <span class="font-semibold text-slate-900"><?= htmlspecialchars($signingFormatted) ?></span>
                </div>
              </div>
            </div>

            <!-- Financial & Payment Summary -->
            <div>
              <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Financial &amp; Payment Summary</p>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50 p-3.5 rounded-lg border border-slate-200/70 text-xs sm:text-sm">
                <div>
                  <span class="text-slate-500 text-xs block"><?= $isLease ? 'Monthly Lease Rate' : 'Total Reselling Price' ?></span>
                  <span class="font-bold text-slate-900">₱<?= number_format((float)($reservation['price_basis'] ?? 0), 2) ?></span>
                </div>
                <div>
                  <span class="text-slate-500 text-xs block">Reservation Fee (35%)</span>
                  <span class="font-bold text-blue-700">₱<?= number_format((float)($reservation['required_amount'] ?? 0), 2) ?></span>
                </div>
                <div>
                  <span class="text-slate-500 text-xs block">Payment Method</span>
                  <span class="font-semibold text-slate-900"><?= htmlspecialchars((string)($reservation['payment_method'] ?? 'In-House')) ?></span>
                </div>
                <div>
                  <span class="text-slate-500 text-xs block">Payment Status</span>
                  <span class="font-semibold text-emerald-700"><?= htmlspecialchars(ucwords((string)($reservation['payment_status'] ?? 'Pending Review'))) ?></span>
                </div>
                <?php if ($paymentProofUrl !== ''): ?>
                  <div class="sm:col-span-2">
                    <span class="text-slate-500 text-xs block">Proof of Payment</span>
                    <a href="<?= htmlspecialchars($paymentProofUrl) ?>" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-blue-600 hover:underline inline-flex items-center gap-1 mt-0.5">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                      View Uploaded Proof
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Client Remarks / Notes -->
            <?php if (!empty($reservation['client_remarks'])): ?>
              <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Submitted Remarks / Requests</p>
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/70 text-xs text-slate-700 leading-relaxed">
                  <?= nl2br(htmlspecialchars((string)$reservation['client_remarks'])) ?>
                </div>
              </div>
            <?php endif; ?>

          </div>
        </div>

      </div>

      <!-- RIGHT COLUMN: CANCELLATION REQUEST FORM & POLICIES (5 COLS) -->
      <div class="lg:col-span-5 space-y-6">

        <!-- CANCELLATION FORM CARD -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
          <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-5">
            <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
            <div>
              <h2 class="text-base font-bold text-slate-900 leading-tight">Request Cancellation</h2>
              <p class="text-xs text-slate-500">Provide your reason for administration review</p>
            </div>
          </div>

          <!-- Policy Notice -->
          <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 mb-5 text-xs text-amber-800 leading-relaxed">
            <div class="flex items-start gap-2.5">
              <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              <div>
                <span class="font-bold block mb-0.5">Important Notice</span>
                Submitting this form does not automatically cancel your reservation. It sends a request to administration.
                Reservation fees are <span class="font-bold text-amber-900">non-refundable once verified and processed</span>.
              </div>
            </div>
          </div>

          <!-- Form -->
          <form id="cancelRequestForm" action="<?= htmlspecialchars($baseUrl) ?>/cancel-reservation/submit" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div>
              <label for="cancellationReason" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                Reason for Cancellation <span class="text-rose-600">*</span>
              </label>
              <textarea
                id="cancellationReason"
                name="reason"
                rows="5"
                required
                placeholder="Please describe why you would like to cancel this reservation..."
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 resize-none"
              ></textarea>
              <p class="text-[11px] text-slate-400 mt-1">Please provide clear details to assist the administration review.</p>
            </div>

            <!-- Acknowledgment Checkbox -->
            <label class="flex items-start gap-2.5 p-3 rounded-lg border border-slate-200 bg-slate-50 cursor-pointer select-none">
              <input
                type="checkbox"
                id="policyCheck"
                required
                class="mt-0.5 w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
              >
              <span class="text-xs text-slate-700 leading-relaxed">
                I confirm that I wish to request cancellation of this reservation, and I understand that reservation fees are non-refundable once verified and processed.
              </span>
            </label>

            <!-- Submit Button -->
            <div class="pt-2 space-y-2">
              <button
                type="button"
                onclick="openConfirmModal()"
                class="w-full py-3 px-4 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold text-sm transition-colors text-center"
              >
                Submit Cancellation Request
              </button>

              <a
                href="<?= htmlspecialchars($baseUrl) ?>/"
                class="block w-full py-2.5 px-4 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 font-medium text-xs text-center transition-colors"
              >
                Keep Reservation &amp; Exit
              </a>
            </div>
          </form>
        </div>

        <!-- HOW CANCELLATION WORKS CARD -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
          <h3 class="text-sm font-bold text-slate-900 mb-4">How the Cancellation Process Works</h3>

          <ol class="space-y-3.5 text-xs text-slate-600">
            <li class="flex items-start gap-3">
              <span class="w-5 h-5 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">1</span>
              <div>
                <span class="font-semibold text-slate-800 block">Submit Request</span>
                Fill in your reason above and submit the form for administrative processing.
              </div>
            </li>
            <li class="flex items-start gap-3">
              <span class="w-5 h-5 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">2</span>
              <div>
                <span class="font-semibold text-slate-800 block">Administration Review</span>
                Zeppelin Suites office verifies the reservation status, documents, and payments.
              </div>
            </li>
            <li class="flex items-start gap-3">
              <span class="w-5 h-5 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">3</span>
              <div>
                <span class="font-semibold text-slate-800 block">Email Notification</span>
                You and the unit owner will receive formal email confirmation of the outcome.
              </div>
            </li>
          </ol>
        </div>

        <!-- NEED HELP CARD -->
        <div class="bg-slate-50 rounded-xl border border-slate-200 p-5 text-xs text-slate-600">
          <p class="font-bold text-slate-900 mb-1">Need Assistance?</p>
          <p class="mb-3">Our office is available Monday to Saturday, 9:00 AM – 6:00 PM.</p>
          <div class="space-y-1.5 font-medium text-slate-800">
            <p>Email: <a href="mailto:sales@zeppelinsuites.com" class="text-blue-600 hover:underline">sales@zeppelinsuites.com</a></p>
            <p>Hotline: <span class="text-slate-900">+63 917 123 4567</span></p>
          </div>
        </div>

      </div>

    </div>

  </main>

  <!-- FOOTER -->
  <footer class="bg-white border-t border-slate-200 py-6 mt-12 text-center text-xs text-slate-500">
    <div class="max-w-6xl mx-auto px-4">
      <p>&copy; <?= date('Y') ?> Zeppelin Suites. All rights reserved.</p>
    </div>
  </footer>

  <!-- SIMPLE CONFIRMATION MODAL (NO EXTRA ANIMATIONS) -->
  <div id="confirmModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full border border-slate-200 overflow-hidden">
      <div class="p-6">
        <div class="w-10 h-10 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mb-4">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
        </div>
        <h3 class="text-base font-bold text-slate-900">Submit Cancellation Request?</h3>
        <p class="text-xs text-slate-600 mt-2 leading-relaxed">
          Are you sure you want to request cancellation for this reservation? This request will be submitted to Zeppelin Suites administration for review.
        </p>
        <div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-800">
          Reservation fees are non-refundable once verified and processed.
        </div>
      </div>
      <div class="flex items-center justify-end gap-3 px-6 py-3.5 bg-slate-50 border-t border-slate-100">
        <button
          type="button"
          onclick="closeConfirmModal()"
          class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors"
        >
          Cancel
        </button>
        <button
          type="button"
          id="confirmSubmitBtn"
          onclick="submitCancellationForm()"
          class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors"
        >
          Yes, Submit Request
        </button>
      </div>
    </div>
  </div>

  <!-- JAVASCRIPT LOGIC (SIMPLE SHOW/HIDE) -->
  <script>
    function openConfirmModal() {
      const reason = document.getElementById('cancellationReason');
      const policy = document.getElementById('policyCheck');

      if (!reason.value.trim()) {
        reason.focus();
        alert('Please provide your reason for requesting cancellation.');
        return;
      }

      if (!policy.checked) {
        policy.focus();
        alert('Please check the confirmation box acknowledging the cancellation policy.');
        return;
      }

      document.getElementById('confirmModal').classList.remove('hidden');
    }

    function closeConfirmModal() {
      document.getElementById('confirmModal').classList.add('hidden');
    }

    function submitCancellationForm() {
      const btn = document.getElementById('confirmSubmitBtn');
      btn.disabled = true;
      btn.textContent = 'Submitting...';
      document.getElementById('cancelRequestForm').submit();
    }
  </script>

</body>
</html>
