<?php
declare(strict_types=1);

/**
 * Zeppelin Suites - Reservation Confirmation View
 *
 * @var string $baseUrl
 * @var string $pageTitle
 * @var string $activePage
 * @var string $token
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Reservation Confirmed — Zeppelin Suites') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Geist:wght@100..900&display=swap');
    * { font-family: "Geist", sans-serif; }

    body {
      background-color: #f8fafc;
      min-height: 100vh;
      overflow-x: hidden;
    }

    .bg-shape {
      position: fixed;
      pointer-events: none;
      z-index: 0;
    }

    .dot-grid {
      width: 200px; height: 200px;
      background-image: radial-gradient(circle, #cbd5e1 1.5px, transparent 1.5px);
      background-size: 16px 16px;
      opacity: 0.6;
    }

    .geo-circle-lg {
      width: 400px; height: 400px;
      border: 1px solid #e2e8f0;
      border-radius: 50%;
    }

    .geo-circle-md {
      width: 250px; height: 250px;
      border: 1px solid #e2e8f0;
      border-radius: 50%;
    }

    .ambient-shade-left {
      width: 500px; height: 500px;
      background: radial-gradient(circle, #f1f5f9 0%, transparent 70%);
      opacity: 0.8;
    }

    .ambient-shade-right {
      width: 400px; height: 400px;
      background: radial-gradient(circle, #e2e8f0 0%, transparent 70%);
      opacity: 0.5;
    }

    .top-bar, .bottom-bar {
      position: fixed; left: 0; right: 0;
      height: 8px;
      background: #0f172a;
      z-index: 100;
    }
    .top-bar { top: 0; }
    .bottom-bar { bottom: 0; }

    .card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .icon-circle {
      width: 64px; height: 64px;
      background: #f1f5f9;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 20px;
    }

    .divider {
      border: none;
      border-top: 1px solid #e2e8f0;
      margin: 24px 0;
    }
  </style>
</head>
<body>

  <div class="top-bar"></div>
  <div class="bottom-bar"></div>

  <div class="bg-shape ambient-shade-left" style="top: -10%; left: -10%;"></div>
  <div class="bg-shape ambient-shade-right" style="bottom: -5%; right: -5%;"></div>

  <div class="bg-shape geo-circle-lg" style="top: -100px; right: -50px;"></div>
  <div class="bg-shape geo-circle-md" style="bottom: 10%; left: -80px;"></div>

  <div class="bg-shape dot-grid" style="top: 80px; left: 60px;"></div>
  <div class="bg-shape dot-grid" style="bottom: 80px; right: 60px;"></div>

  <!-- Main content -->
  <div class="relative z-10 min-h-screen flex items-center justify-center px-4 py-24">
    <div class="card w-full max-w-lg p-10 text-center">

      <!-- Logo -->
      <div class="mb-6">
        <a href="<?= htmlspecialchars($baseUrl) ?>/">
          <img
            src="<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/zeppelin-logo.png"
            alt="Zeppelin Suites"
            style="height:70px; margin:0 auto;"
            onerror="this.outerHTML='<div style=\'font-size:22px;font-weight:800;letter-spacing:0.04em;color:#111;\'>ZEPPELIN<br><span style=\'font-size:10px;font-weight:400;letter-spacing:0.25em;color:#6b7280;\'>SUITES</span></div>'"
          >
        </a>
      </div>

      <hr class="divider">

      <?php
      $resStatus = strtolower(trim((string)($reservation['reservation_status'] ?? 'submitted')));
      $isCancelling = ($resStatus === 'cancellation requested');
      $isCancelled = in_array($resStatus, ['cancelled', 'rejected'], true);
      $isConfirmed = in_array($resStatus, ['handover', 'moved in', 'officially booked', 'confirmed', 'completed'], true);
      ?>

      <!-- Icon -->
      <div class="icon-circle <?= $isCancelling ? 'bg-amber-50' : ($isCancelled ? 'bg-red-50' : '') ?>">
        <?php if ($isCancelling): ?>
          <svg width="32" height="32" fill="none" stroke="#d97706" stroke-width="1.8" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"/>
            <polyline points="12,6 12,12 16,14"/>
          </svg>
        <?php elseif ($isCancelled): ?>
          <svg width="32" height="32" fill="none" stroke="#dc2626" stroke-width="1.8" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"/>
            <line x1="15" y1="9" x2="9" y2="15"/>
            <line x1="9" y1="9" x2="15" y2="15"/>
          </svg>
        <?php else: ?>
          <svg width="32" height="32" fill="none" stroke="#16a34a" stroke-width="1.8" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
            <polyline points="9,16 11,18 15,14"/>
          </svg>
        <?php endif; ?>
      </div>

      <!-- Heading -->
      <h1 class="text-2xl font-bold text-zinc-900 mb-3">
        <?php if ($isCancelling): ?>
          Cancellation Request Under Review
        <?php elseif ($isCancelled): ?>
          Reservation Cancelled
        <?php elseif ($isConfirmed): ?>
          Reservation Confirmed!
        <?php else: ?>
          Reservation Form Received!
        <?php endif; ?>
      </h1>

      <!-- Body text -->
      <p class="text-zinc-500 text-sm leading-relaxed mb-6">
        <?php if ($isCancelling): ?>
          Your reservation was previously submitted, and a cancellation request is currently under review by the Zeppelin Suites administration team.
        <?php elseif ($isCancelled): ?>
          This reservation has been cancelled. If you have questions regarding this decision, please reach out to administration.
        <?php else: ?>
          Your reservation at <strong class="text-zinc-800">Zeppelin Suites</strong> has been received and is now being processed by our team. Verification will be completed within <strong class="text-zinc-800">1 business day</strong>.
        <?php endif; ?>
      </p>

      <!-- Booking detail summary -->
      <div class="bg-slate-50 rounded-xl p-5 text-left mb-6 space-y-3">
        <p class="text-xs font-semibold tracking-widest text-zinc-400 uppercase mb-2">Reservation Summary</p>
        <div class="flex items-center justify-between text-sm">
          <span class="text-zinc-500">Confirmation No.</span>
          <span class="font-mono font-bold text-zinc-900" id="conf-num">
            <?= !empty($reservation['reservation_id']) ? '#' . htmlspecialchars((string)$reservation['reservation_id']) : '#ZS-PENDING' ?>
          </span>
        </div>
        <?php if (!empty($reservation['unit_type'])): ?>
          <div class="flex items-center justify-between text-sm">
            <span class="text-zinc-500">Assigned Unit</span>
            <span class="font-semibold text-zinc-800">
              <?= htmlspecialchars($reservation['unit_type'] . (!empty($reservation['unit_number']) ? ' — Unit ' . $reservation['unit_number'] : '')) ?>
            </span>
          </div>
        <?php endif; ?>
        <div class="flex items-center justify-between text-sm">
          <span class="text-zinc-500">Status</span>
          <?php if ($isCancelling): ?>
            <span class="text-xs font-semibold text-amber-800 bg-amber-100 px-3 py-1 rounded-full">Cancellation Requested</span>
          <?php elseif ($isCancelled): ?>
            <span class="text-xs font-semibold text-red-800 bg-red-100 px-3 py-1 rounded-full">Cancelled</span>
          <?php elseif ($isConfirmed): ?>
            <span class="text-xs font-semibold text-emerald-800 bg-emerald-100 px-3 py-1 rounded-full">Confirmed</span>
          <?php else: ?>
            <span class="text-xs font-semibold text-amber-800 bg-amber-100 px-3 py-1 rounded-full">Tentative (Awaiting Review)</span>
          <?php endif; ?>
        </div>
      </div>

      <p class="text-zinc-500 text-sm leading-relaxed mb-4">
        Official updates will be coordinated via email from <strong class="text-zinc-800"><?= htmlspecialchars(!empty($reservation['owner_email']) ? $reservation['owner_email'] : 'sales@zeppelinsuites.com') ?></strong>.
      </p>

      <!-- Spam note -->
      <p class="text-amber-600 text-xs font-medium mb-6">
        If you cannot find our notification in your inbox, please check your promotions or spam folder.
      </p>

      <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-center gap-3">
        <?php if (!empty($reservation['client_cancel_token'])): ?>
          <a href="<?= htmlspecialchars($baseUrl) ?>/cancel-reservation?token=<?= urlencode((string)$reservation['client_cancel_token']) ?>" class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg transition-all">
            <?= $isCancelling ? 'View Cancellation Request' : 'Manage Reservation' ?>
          </a>
        <?php endif; ?>
        <a href="<?= htmlspecialchars($baseUrl) ?>/" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-all">
          Return to Homepage
        </a>
      </div>

    </div>
  </div>

  <?php if (empty($reservation['reservation_id'])): ?>
  <script>
    document.getElementById('conf-num').textContent =
      '#ZS-' + String(Math.floor(100000 + Math.random() * 900000));
  </script>
  <?php endif; ?>

</body>
</html>
