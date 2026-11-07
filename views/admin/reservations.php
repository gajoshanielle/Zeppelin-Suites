<?php
declare(strict_types=1);

/**
 * Zeppelin Suites - Admin Lease & Reservation Management View
 * Pure presentation: strictly NO SQL queries or database connections.
 */
if (!function_exists('e')) {
    function e($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('peso')) {
    function peso($amount): string {
        if ($amount === null || $amount === '') {
            return '-';
        }
        return '₱' . number_format((float)$amount, 2);
    }
}

if (!function_exists('format_date_only')) {
    function format_date_only($value): string {
        if (empty($value) || $value === '0000-00-00') {
            return '-';
        }
        $time = strtotime((string)$value);
        return $time ? date('Y-m-d', $time) : '-';
    }
}

if (!function_exists('format_timeline_datetime')) {
    function format_timeline_datetime($value): string {
        if (empty($value) || $value === '0000-00-00 00:00:00' || $value === '0000-00-00') {
            return '';
        }
        $time = strtotime((string)$value);
        return $time ? date('M d, Y \• h:i A', $time) : '';
    }
}

if (!function_exists('badge')) {
    function badge(string $value): string {
        $text = trim($value);
        $status = strtolower($text);

        if ($status === 'officially booked') {
            return "<span class='text-xs font-semibold px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-200'>Officially Booked</span>";
        }

        if (in_array($status, ['verified', 'reserved', 'requirements completed', 'active'], true)) {
            $label = ($status === 'verified') ? 'Verified' : 'Reserved';
            return "<span class='text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200'>" . e($label) . "</span>";
        }

        if (in_array($status, ['pending review', 'under review', 'requirements pending', 'requested', 'flagged for review', 'pending', 'in progress', 'reservation form pending', 'submitted'], true)) {
            return "<span class='text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200'>Pending</span>";
        }

        if (in_array($status, ['rejected', 'cancelled', 'declined'], true)) {
            return "<span class='text-xs font-semibold px-2.5 py-1 rounded-full bg-red-50 text-red-700 border border-red-200'>" . e(ucwords($status)) . "</span>";
        }

        if ($status === 'approved') {
            return "<span class='text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200'>" . e(ucwords($text)) . "</span>";
        }

        return "<span class='text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-50 text-slate-600 border border-slate-200'>" . e($text !== '' ? ucwords($text) : '-') . "</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Zeppelin Suites Admin - Lease Management') ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['DM Sans','sans-serif'],mono:['DM Mono','monospace']}}}}</script>
<style>
* { font-family: 'DM Sans', sans-serif; }
.sidebar { width:256px; transition:width 0.3s cubic-bezier(0.4,0,0.2,1),transform 0.3s cubic-bezier(0.4,0,0.2,1); background:rgba(255,255,255,0.92); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); }
.sidebar.collapsed { width:68px; }
@media (max-width:767px) { .sidebar { transform:translateX(-100%); position:fixed; z-index:50; height:100vh; width:256px !important; } .sidebar.open { transform:translateX(0); } }
.main-wrapper { margin-left:256px; transition:margin-left 0.3s cubic-bezier(0.4,0,0.2,1); }
.main-wrapper.sidebar-collapsed { margin-left:68px; }
@media (max-width:767px) { .main-wrapper { margin-left:0 !important; } }
.overlay { display:none; pointer-events:none; }
.overlay.show { display:block; pointer-events:auto; }
.sidebar-logo { transition:opacity 0.2s ease,width 0.2s ease; }
.sidebar.collapsed .sidebar-logo { opacity:0; width:0; overflow:hidden; pointer-events:none; }
.sidebar-link { position:relative; transition:all 0.18s ease; white-space:nowrap; overflow:hidden; }
.sidebar-link.active { background:#0f172a; color:#fff; }
.sidebar-link.active .nav-icon { color:#60a5fa; }
.sidebar-link:not(.active):hover { background:#eff6ff; color:#1d4ed8; }
.sidebar-link:not(.active):hover .nav-icon { color:#3b82f6; }
.sidebar.collapsed .nav-label,.sidebar.collapsed .nav-badge,.sidebar.collapsed .notice-section { display:none; }
.sidebar.collapsed .sidebar-link { justify-content:center; padding-left:0; padding-right:0; }
.sidebar.collapsed .collapse-icon { transform:rotate(180deg); }
.sidebar.collapsed .sidebar-link:hover::after { content:attr(data-tooltip); position:absolute; left:calc(100% + 10px); top:50%; transform:translateY(-50%); background:#0f172a; color:#fff; font-size:12px; padding:5px 10px; border-radius:8px; white-space:nowrap; z-index:999; box-shadow:0 4px 16px rgba(0,0,0,0.18); pointer-events:none; }
.res-row { transition:background 0.15s ease; }
.res-row:hover { background:#f1f5f9; }
.res-row .res-name { transition:color 0.15s ease; }
.res-row:hover .res-name { color:#1d4ed8; }
.view-btn { opacity:0; transform:translateX(6px); transition:opacity 0.18s ease,transform 0.18s ease; }
.res-row:hover .view-btn { opacity:1; transform:translateX(0); }
::-webkit-scrollbar { width:4px; height:4px; }
::-webkit-scrollbar-track { background:#f1f5f9; }
::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:4px; }
::-webkit-scrollbar-thumb:hover { background:#94a3b8; }
.btn-press { transition:all 0.15s ease; }
.btn-press:active { transform:scale(0.95); }
.zep-input:focus { outline:none; border-color:#0f172a; box-shadow:0 0 0 3px rgba(15,23,42,0.07); }
.glass-header { background:rgba(255,255,255,0.85); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); }
.main-scroll { height:calc(100vh - 65px); overflow-y:auto; }
</style>
</head>
<body class="bg-slate-50 text-slate-800 overflow-hidden">

<!-- Overlay and Sidebar -->
<?php include dirname(__DIR__) . '/components/admin_sidebar.php'; ?>

<!-- MAIN WRAPPER -->
<div class="main-wrapper h-screen flex flex-col" id="mainWrapper">
  <!-- TOP BAR / NAVBAR -->
  <?php 
  $navSearchId = 'searchInput';
  $navSearchPlaceholder = 'Search reservations...';
  $navSearchHandler = 'oninput="filterSearch(this.value)" onkeyup="filterSearch(this.value)"';
  include dirname(__DIR__) . '/components/admin_navbar.php'; 
  ?>

  <main class="main-scroll p-4 md:p-6 space-y-6">
    <div class="max-w-screen-2xl mx-auto space-y-6">

      <!-- Page header -->
      <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Lease Management</h1>
          <p class="text-xs text-slate-400 mt-0.5">Manage reservations, payment verifications, and tenant handover workflows.</p>
        </div>
        <div class="flex items-center gap-2">
          <div class="flex bg-slate-100 rounded-full p-1 gap-0.5 text-xs font-semibold overflow-x-auto max-w-full">
            <button type="button"
              class="res-filter-btn active px-3.5 py-1.5 rounded-full bg-white text-slate-900 shadow-sm active:scale-95 transition-all cursor-pointer font-semibold whitespace-nowrap"
              data-filter="pending" onclick="setReservationFilter('pending')">
              Pending
            </button>
            <button type="button"
              class="res-filter-btn px-3.5 py-1.5 rounded-full text-slate-500 hover:text-slate-900 hover:bg-white/60 active:scale-95 transition-all cursor-pointer font-semibold whitespace-nowrap"
              data-filter="reserved" onclick="setReservationFilter('reserved')">
              Reserved
            </button>
            <button type="button"
              class="res-filter-btn px-3.5 py-1.5 rounded-full text-slate-500 hover:text-slate-900 hover:bg-white/60 active:scale-95 transition-all cursor-pointer font-semibold whitespace-nowrap"
              data-filter="officially booked" onclick="setReservationFilter('officially booked')">
              Officially Booked
            </button>
            <button type="button"
              class="res-filter-btn px-3.5 py-1.5 rounded-full text-slate-500 hover:text-slate-900 hover:bg-white/60 active:scale-95 transition-all cursor-pointer font-semibold whitespace-nowrap"
              data-filter="cancelled" onclick="setReservationFilter('cancelled')">
              Cancelled
            </button>
            <button type="button"
              class="res-filter-btn px-3.5 py-1.5 rounded-full text-slate-500 hover:text-slate-900 hover:bg-white/60 active:scale-95 transition-all cursor-pointer font-semibold whitespace-nowrap"
              data-filter="all" onclick="setReservationFilter('all')">
              All
            </button>
          </div>
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-sm" id="resTable">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50/60">
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wide whitespace-nowrap align-middle">Res. #</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wide whitespace-nowrap align-middle">Client</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wide whitespace-nowrap align-middle">Unit</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wide whitespace-nowrap align-middle">Transaction</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wide whitespace-nowrap align-middle">Amount</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wide whitespace-nowrap align-middle">Payment</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wide whitespace-nowrap align-middle">Reservation</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wide whitespace-nowrap align-middle">Submitted</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50" id="resBody">
              <?php if (empty($reservations)): ?>
                <tr>
                  <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-400">
                    No reservations found.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($reservations as $row): ?>
                  <?php
                  $reservationId = str_pad((string)$row['reservation_id'], 3, '0', STR_PAD_LEFT);
                  $unitParts = array_filter([
                      $row['unit_type'] ?? '',
                      !empty($row['unit_number']) ? 'Unit ' . $row['unit_number'] : ''
                  ]);
                  $unitDisplay = trim(implode(' ', $unitParts)) ?: 'Unit not found';

                  $submittedDate = format_date_only($row['created_at'] ?? null);
                  $resStatus = strtolower(trim((string)($row['reservation_status'] ?? '')));
                  $pStatus = strtolower(trim((string)($row['payment_status'] ?? '')));
                  $hasOfficiallyBooked = !empty($row['officially_booked_at']) || in_array($resStatus, ['officially booked', 'handover', 'moved in', 'active', 'completed'], true);
                  $isMovedIn = in_array($resStatus, ['handover', 'moved in', 'active', 'completed'], true);

                  if (in_array($resStatus, ['rejected', 'cancelled'], true) || strtolower((string)($row['reservation_status'] ?? '')) === 'cancelled') {
                      $resDisplay = 'Cancelled';
                      $filterCategory = 'cancelled';
                  } elseif ($hasOfficiallyBooked) {
                      $resDisplay = 'Officially Booked';
                      $filterCategory = 'officially booked';
                  } elseif (in_array($resStatus, ['submitted', 'pending review', 'pending', 'under review', 'in progress', 'reservation form pending'], true) || $pStatus === 'pending review') {
                      $resDisplay = 'Pending';
                      $filterCategory = 'pending';
                  } else {
                      $resDisplay = 'Reserved';
                      $filterCategory = 'reserved';
                  }

                  $isResale = (stripos((string)($row['transaction_type'] ?? ''), 'resell') !== false || stripos((string)($row['transaction_type'] ?? ''), 'resale') !== false);
                  $cancellationRequested = (strtolower((string)($row['reservation_status'] ?? '')) === 'cancellation requested');
                  $hasSignedLease = !empty($row['lease_signed_at']);
                  $paymentMethod = (string)($row['payment_method'] ?? 'GCash QR');

                  $timelineData = [
                      'reservation_id'          => (int)$row['reservation_id'],
                      'formatted_id'            => $reservationId,
                      'client_name'             => $row['client_name'] ?? 'Client',
                      'client_email'            => $row['client_email'] ?? '',
                      'client_contact'          => $row['client_contact'] ?? '',
                      'unit_display'            => $unitDisplay,
                      'transaction_type'        => $row['transaction_type'] ?? 'For Lease',
                      'is_resale'               => $isResale,
                      'created_at'              => format_timeline_datetime($row['created_at'] ?? null),
                      'payment_status'          => strtolower(trim((string)($row['payment_status'] ?? 'pending review'))),
                      'payment_method'          => $paymentMethod,
                      'payment_reviewed_at'     => format_timeline_datetime($row['payment_reviewed_at'] ?? null),
                      'lease_signed_at'         => format_timeline_datetime($row['lease_signed_at'] ?? null),
                      'has_signed_lease'        => $hasSignedLease,
                      'reservation_status'      => $resStatus,
                      'cancellation_requested'  => $cancellationRequested,
                      'officially_booked_at'    => format_timeline_datetime($row['officially_booked_at'] ?? null),
                      
                      'move_in_date'            => !empty($row['move_in_date']) && $row['move_in_date'] !== '0000-00-00' ? date('M d, Y', strtotime((string)$row['move_in_date'])) : '',
                      'total_docs'              => (int)($row['total_docs'] ?? 0),
                      'completed_docs'          => (int)($row['completed_docs'] ?? 0),
                      'is_moved_in'             => $isMovedIn,
                      'view_url'                => $baseUrl . '/admin/reservations/view?reservation_id=' . urlencode((string)$row['reservation_id'])
                  ];
                  $timelineJson = htmlspecialchars(json_encode($timelineData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
                  ?>
                  <tr class="res-row cursor-pointer hover:bg-slate-50 transition-colors"
                      data-timeline="<?= $timelineJson ?>"
                      data-filter-status="<?= e($filterCategory) ?>"
                      data-search="<?= e(strtolower($reservationId . ' ' . ($row['client_name'] ?? '') . ' ' . ($row['client_email'] ?? '') . ' ' . $unitDisplay . ' ' . ($row['transaction_type'] ?? '') . ' ' . $resDisplay)) ?>"
                      onclick="openActivityTimelineModal(this)">

                    <td class="px-5 py-3.5 font-semibold text-slate-700 whitespace-nowrap align-middle" style="font-family:'DM Mono',monospace">
                      <?= e($reservationId) ?>
                    </td>

                    <td class="px-4 py-3.5 whitespace-nowrap align-middle">
                      <p class="font-semibold res-name text-slate-800"><?= e($row['client_name']) ?></p>
                      <p class="text-xs text-slate-400"><?= e($row['client_email']) ?></p>
                    </td>

                    <td class="px-4 py-3.5 text-slate-700 text-xs font-medium whitespace-nowrap align-middle">
                      <?= e($unitDisplay) ?>
                    </td>

                    <td class="px-4 py-3.5 text-slate-600 text-xs whitespace-nowrap align-middle">
                      <?= e($row['transaction_type'] ?? '-') ?>
                    </td>

                    <td class="px-4 py-3.5 font-semibold text-slate-700 whitespace-nowrap align-middle" style="font-family:'DM Mono',monospace">
                      <?= e(peso($row['required_amount'])) ?>
                    </td>

                    <td class="px-4 py-3.5 whitespace-nowrap align-middle">
                      <?= badge((string)$row['payment_status']) ?>
                    </td>

                    <td class="px-4 py-3.5 whitespace-nowrap align-middle">
                      <div class="flex items-center gap-2">
                        <?= badge($resDisplay) ?>
                        <?php if (strtolower((string)($row['reservation_status'] ?? '')) === 'cancellation requested'): ?>
                          <a href="<?= htmlspecialchars($baseUrl) ?>/admin/reservations/view?reservation_id=<?= e($row['reservation_id']) ?>"
                             title="Cancellation requested"
                             class="w-6 h-6 inline-flex items-center justify-center rounded-full bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition-all"
                             onclick="event.stopPropagation();">
                            <span class="text-xs font-black">!</span>
                          </a>
                        <?php endif; ?>
                      </div>
                    </td>

                    <td class="px-5 py-3.5 text-slate-500 text-xs whitespace-nowrap align-middle" style="font-family:'DM Mono',monospace">
                      <?= e($submittedDate) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <tr id="resNoMatchRow" class="hidden">
                  <td colspan="8" class="px-4 py-12 text-center text-sm text-slate-400">
                    <div class="flex flex-col items-center justify-center gap-2">
                      <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      </div>
                      <p class="text-sm font-semibold text-slate-700">No reservations match your filter</p>
                      <p class="text-xs text-slate-400">Try selecting a different status filter or clearing your search.</p>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Table Pagination & Count Footer -->
        <div class="flex items-center justify-between px-5 py-3.5 border-t border-slate-100 flex-wrap gap-3" id="resPaginationContainer">
          <p class="text-xs text-slate-500" id="resPaginationInfo">
            Showing <span class="font-semibold text-slate-700" id="resShowingStart">0</span>–<span class="font-semibold text-slate-700" id="resShowingEnd">0</span> of <span class="font-semibold text-slate-700" id="resTotalCount"><?= count($reservations) ?></span> reservations
          </p>
          <div class="flex items-center gap-1" id="resPaginationControls">
            <!-- Dynamically populated by JS -->
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- HANDOVER MODAL -->
<div id="handoverModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 p-4" onclick="if(event.target===this) closeHandoverModal()">
  <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-4 flex items-center justify-between text-white">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
          <h2 class="text-base font-bold">Unit Handover & Move In</h2>
          <p class="text-xs text-emerald-100">Activate tenant account and occupy unit</p>
        </div>
      </div>
      <button type="button" onclick="closeHandoverModal()" class="p-1.5 rounded-lg hover:bg-white/10 text-white/80 hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <form id="handoverForm" onsubmit="submitHandover(event)" class="p-6 space-y-4">
      <input type="hidden" id="handoverResId" name="reservation_id" value="">

      <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-2 text-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs text-slate-500 font-medium">Client:</span>
          <span id="handoverClientName" class="font-bold text-slate-800"></span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-xs text-slate-500 font-medium">Email:</span>
          <span id="handoverClientEmail" class="font-semibold text-slate-700"></span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-xs text-slate-500 font-medium">Assigned Unit:</span>
          <span id="handoverUnitDisplay" class="font-bold text-emerald-700"></span>
        </div>
      </div>

      <div class="rounded-xl bg-emerald-50/80 border border-emerald-200 p-4 text-xs text-emerald-900 leading-relaxed">
        <p class="text-emerald-800">
          The client will be provided an account to be an official tenant under Zeppelin Suites (visible in <a href="<?= htmlspecialchars($baseUrl) ?>/admin/residents" target="_blank" class="underline font-bold text-emerald-950 hover:text-emerald-700">Residents</a>). Once the client logs in for the first time, their account will be activated. Default password: <code class="bg-emerald-100 font-mono font-bold text-emerald-950 px-1.5 py-0.5 rounded text-[11px]">tenantzepellinsuites</code>. They can change their password at any time.
        </p>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
        <button type="button" onclick="closeHandoverModal()" class="btn-press px-4 py-2 text-sm font-semibold border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50">
          Cancel
        </button>
        <button type="submit" id="btnConfirmHandover" class="btn-press px-5 py-2 text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-md flex items-center gap-2">
          <span>Complete Handover</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- RESERVATION CHECKLIST SUMMARY MODAL -->
<div id="activityTimelineModal" onclick="if(event.target===this) closeActivityTimelineModal()" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[99] hidden items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 max-w-xl sm:max-w-2xl w-full p-6 sm:p-7 relative max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
    
    <!-- Top Header -->
    <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100 shrink-0">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100 shadow-sm">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
        </div>
        <div>
          <h3 class="text-base font-bold text-slate-900 leading-tight">Reservation Checklist</h3>
          <p class="text-xs text-slate-400 mt-0.5">Requirements &amp; booking status checklist.</p>
        </div>
      </div>
      <button type="button" onclick="closeActivityTimelineModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors" title="Close">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <!-- Client Info Card -->
    <div class="mt-4 mb-3 bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center justify-between gap-4 shrink-0">
      <div class="min-w-0">
        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Client Name</p>
        <p id="timelineClientName" class="text-sm sm:text-base font-bold text-slate-900 leading-snug break-words">—</p>
        <p id="timelineClientEmail" class="text-xs text-slate-500 break-all mt-0.5">—</p>
      </div>
      <div class="text-right shrink-0 pl-3">
        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Assigned Unit</span>
        <span id="timelineUnitDisplay" class="text-xs sm:text-sm font-bold text-slate-800 font-mono block mt-0.5">—</span>
        <div id="timelineStatusPillSlot" class="mt-1 flex justify-end"></div>
      </div>
    </div>

    <!-- Checklist Scrollable Items -->
    <div class="overflow-y-auto pr-1 flex-1 py-1 space-y-2.5" id="timelineStepsContainer">
      <!-- Dynamic via JavaScript -->
    </div>

    <!-- Modal Footer -->
    <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center justify-between gap-2.5 shrink-0">
      <div id="timelineHandoverActionSlot"></div>
      <div class="flex items-center gap-2">
        <button type="button" onclick="closeActivityTimelineModal()" class="btn-press px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Close
        </button>
        <a id="timelineViewDetailsBtn" href="#" class="btn-press inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-sm transition-all active:scale-95">
          <span>View Details</span>
          <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>

  </div>
</div>

<script>
const BASE_URL = <?= json_encode($baseUrl) ?>;

function filterSearch() {
  const q = (document.getElementById('searchInput')?.value || '').toLowerCase();
  document.querySelectorAll('#resBody tr').forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

function openHandoverModal(resId, clientName, clientEmail, unitDisplay) {
  document.getElementById('handoverResId').value = resId;
  document.getElementById('handoverClientName').textContent = clientName || '—';
  document.getElementById('handoverClientEmail').textContent = clientEmail || '—';
  document.getElementById('handoverUnitDisplay').textContent = unitDisplay || '—';

  const modal = document.getElementById('handoverModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function closeHandoverModal() {
  const modal = document.getElementById('handoverModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

function submitHandover(e) {
  e.preventDefault();
  const btn = document.getElementById('btnConfirmHandover');
  const originalText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing Handover...';

  const formData = new FormData(document.getElementById('handoverForm'));

  fetch(BASE_URL + '/admin/reservations/handover', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    btn.disabled = false;
    btn.innerHTML = originalText;

    if (data.success) {
      closeHandoverModal();
      const roleLabel = (data.tenant && data.tenant.role === 'unit owner') ? 'Unit Owner' : 'Tenant';
      showStatusModal(true, 'Handover Successful!',
        data.message + ' ' + roleLabel + ' credentials (Email: ' + data.tenant.email + ') dispatched via email.',
        true);
    } else {
      showStatusModal(false, 'Handover Failed', data.message || 'Unable to process handover.');
    }
  })
  .catch(err => {
    btn.disabled = false;
    btn.innerHTML = originalText;
    console.error(err);
    showStatusModal(false, 'Network Error', 'Network error while processing handover. Please try again.');
  });
}

function openActivityTimelineModal(source) {
  let data = null;
  if (typeof source === 'string') {
    try { data = JSON.parse(source); } catch (e) { console.error(e); }
  } else if (source && source.dataset && source.dataset.timeline) {
    try { data = JSON.parse(source.dataset.timeline); } catch (e) { console.error(e); }
  } else if (typeof source === 'object' && source !== null && !source.dataset) {
    data = source;
  }

  if (!data) return;

  const name = data.client_name || 'Client';
  document.getElementById('timelineClientName').textContent = name;
  document.getElementById('timelineClientEmail').textContent = data.client_email || 'No email provided';
  document.getElementById('timelineUnitDisplay').textContent = data.unit_display || 'Unit';
  document.getElementById('timelineViewDetailsBtn').href = data.view_url || (BASE_URL + '/admin/reservations/view?reservation_id=' + data.reservation_id);

  const resStatus = (data.reservation_status || '').toLowerCase();
  const isOfficiallyBooked = ['officially booked', 'reserved', 'handover', 'moved in', 'active', 'completed'].includes(resStatus) || !!data.officially_booked_at;
  const isHandedOver = ['handover', 'moved in', 'active', 'completed'].includes(resStatus) || !!data.is_moved_in;

  // Status Pill in Client Card
  const statusPillSlot = document.getElementById('timelineStatusPillSlot');
  if (statusPillSlot) {
    if (['cancelled', 'rejected'].includes(resStatus)) {
      statusPillSlot.innerHTML = '<span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700 border border-red-200">Cancelled</span>';
    } else if (isHandedOver) {
      statusPillSlot.innerHTML = '<span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Moved In</span>';
    } else if (isOfficiallyBooked) {
      statusPillSlot.innerHTML = '<span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200"><svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Officially Booked</span>';
    } else {
      statusPillSlot.innerHTML = '<span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200">Pending Review</span>';
    }
  }

  // If ready for handover, show quick turnover trigger in modal
  const handoverSlot = document.getElementById('timelineHandoverActionSlot');
  if (isOfficiallyBooked && !data.is_moved_in) {
    handoverSlot.innerHTML = `
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
          <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          Officially Booked
        </span>
        <button type="button" onclick="closeActivityTimelineModal(); openHandoverModal(${data.reservation_id}, '${escapeQuote(data.client_name)}', '${escapeQuote(data.client_email)}', '${escapeQuote(data.unit_display)}');" class="btn-press px-3 py-1.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-xl transition-all" title="Conduct unit turnover & release resident portal access on move-in day">
          Turnover / Handover
        </button>
      </div>
    `;
  } else {
    handoverSlot.innerHTML = '';
  }

  renderActivityTimelineSteps(data);

  const modal = document.getElementById('activityTimelineModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function escapeQuote(str) {
  return (str || '').replace(/'/g, "\\'");
}

function closeActivityTimelineModal() {
  const modal = document.getElementById('activityTimelineModal');
  if (!modal) return;
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

function renderActivityTimelineSteps(data) {
  const container = document.getElementById('timelineStepsContainer');
  if (!container) return;

  const resStatus = (data.reservation_status || '').toLowerCase();
  const paymentStatus = (data.payment_status || 'pending review').toLowerCase();
  const paymentMethod = (data.payment_method || '').toLowerCase();
  const isInHousePayment = paymentMethod.includes('in-house') || paymentMethod.includes('in house');
  
  const isCancelled = ['cancelled', 'rejected'].includes(resStatus);
  const isCancellationRequested = !!data.cancellation_requested || resStatus === 'cancellation requested';
  const isOfficiallyBooked = ['officially booked', 'handover', 'moved in', 'active', 'completed'].includes(resStatus) || !!data.officially_booked_at;
  const isHandedOver = ['handover', 'moved in', 'active', 'completed'].includes(resStatus) || !!data.is_moved_in;
  
  const isPaymentVerified = paymentStatus === 'verified' || isOfficiallyBooked || isHandedOver;
  const isPaymentRejected = paymentStatus === 'rejected';

  const hasSignedLease = !!data.has_signed_lease || !!data.lease_signed_at || isOfficiallyBooked || isHandedOver;

  const totalDocs = parseInt(data.total_docs, 10) || 0;
  const completedDocs = parseInt(data.completed_docs, 10) || 0;
  const areDocsComplete = isOfficiallyBooked || isHandedOver || (totalDocs > 0 && completedDocs >= totalDocs);
  const areDocsInProgress = !areDocsComplete && completedDocs > 0;

  const isResale = !!data.is_resale;

  // 1. Lease / Resale & Client Details
  let item1 = {};
  if (isCancelled) {
    item1 = {
      state: 'rejected',
      title: isResale ? 'Resale & Client Details' : 'Lease & Client Details',
      desc: 'Reservation has been cancelled.',
      badge: '<span class="inline-flex items-center text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200">Cancelled</span>'
    };
  } else if (isCancellationRequested) {
    item1 = {
      state: 'incomplete',
      title: isResale ? 'Resale & Client Details' : 'Lease & Client Details',
      desc: 'Client submitted a cancellation request.',
      badge: '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200"><svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>Cancellation Requested</span>'
    };
  } else {
    item1 = {
      state: 'complete',
      title: isResale ? 'Resale & Client Details' : 'Lease & Client Details',
      desc: data.created_at ? 'Submitted on ' + data.created_at : 'Reservation form submitted with client details.',
      badge: '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200"><svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Submitted</span>'
    };
  }

  // 2. Downpayment
  let item2 = {};
  if (isPaymentVerified) {
    item2 = {
      state: 'complete',
      title: 'Downpayment',
      desc: data.payment_reviewed_at ? 'Payment verified on ' + data.payment_reviewed_at : 'Payment proof has been verified.',
      badge: '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200"><svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Verified</span>'
    };
  } else if (isPaymentRejected) {
    item2 = {
      state: 'rejected',
      title: 'Downpayment',
      desc: 'Payment proof was rejected upon review.',
      badge: '<span class="inline-flex items-center text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200">Rejected</span>'
    };
  } else if (isInHousePayment) {
    item2 = {
      state: 'incomplete',
      title: 'Downpayment',
      desc: 'In-house collection — to be paid directly during signing or move-in.',
      badge: '<span class="inline-flex items-center text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">Pay In-House</span>'
    };
  } else {
    item2 = {
      state: 'incomplete',
      title: 'Downpayment',
      desc: 'Proof of downpayment submitted, awaiting review.',
      badge: '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200"><svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>Pending Review</span>'
    };
  }

  // 3. Lease Signing
  let item3 = {};
  if (hasSignedLease) {
    item3 = {
      state: 'complete',
      title: isResale ? 'Contract Signing' : 'Lease Signing',
      desc: data.lease_signed_at ? 'Agreement signed on ' + data.lease_signed_at : 'Agreement electronically signed by client.',
      badge: '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200"><svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Signed</span>'
    };
  } else {
    item3 = {
      state: 'incomplete',
      title: isResale ? 'Contract Signing' : 'Lease Signing',
      desc: 'Agreement prepared and awaiting client signature.',
      badge: '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200"><svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>Awaiting Signature</span>'
    };
  }

  // 4. Documents
  let item4 = {};
  if (areDocsComplete) {
    item4 = {
      state: 'complete',
      title: 'Documents',
      desc: totalDocs > 0 ? `All required documents (${completedDocs}/${totalDocs}) verified.` : 'All required documents verified.',
      badge: '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200"><svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Complete</span>'
    };
  } else if (areDocsInProgress) {
    item4 = {
      state: 'incomplete',
      title: 'Documents',
      desc: `${completedDocs} of ${totalDocs} required documents verified.`,
      badge: '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200"><svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>In Progress</span>'
    };
  } else {
    item4 = {
      state: 'incomplete',
      title: 'Documents',
      desc: totalDocs > 0 ? `0 of ${totalDocs} documents verified.` : 'Required client documents pending submission.',
      badge: '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200"><svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>Pending</span>'
    };
  }

  // 5. Turnover & Move-In
  let item5 = {};
  if (isHandedOver) {
    item5 = {
      state: 'complete',
      title: 'Turnover & Move-In',
      desc: 'Turnover complete. Resident portal account activated.',
      badge: '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200"><svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Moved In</span>'
    };
  } else if (isOfficiallyBooked) {
    item5 = {
      state: 'neutral',
      title: 'Turnover & Move-In',
      desc: data.move_in_date ? 'Move-in scheduled for ' + data.move_in_date : 'Officially booked. Scheduled for move-in day.',
      badge: '<span class="inline-flex items-center text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">Scheduled</span>'
    };
  } else {
    item5 = {
      state: 'neutral',
      title: 'Turnover & Move-In',
      desc: 'Turnover will be scheduled once prior requirements are completed.',
      badge: '<span class="inline-flex items-center text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200">Pending</span>'
    };
  }

  const items = [item1, item2, item3, item4, item5];

  let html = '';
  for (let i = 0; i < items.length; i++) {
    const item = items[i];
    let iconHtml = '';
    let bgClass = 'bg-white border-slate-200/80 hover:border-slate-300';

    if (item.state === 'complete') {
      iconHtml = `
        <div class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-xs">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
      `;
    } else if (item.state === 'incomplete') {
      iconHtml = `
        <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 border border-amber-300 flex items-center justify-center shrink-0 shadow-xs">
          <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9" stroke-width="2"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
          </svg>
        </div>
      `;
      bgClass = 'bg-amber-50/20 border-amber-200/60';
    } else if (item.state === 'rejected') {
      iconHtml = `
        <div class="w-7 h-7 rounded-lg bg-red-500 text-white flex items-center justify-center shrink-0 shadow-xs">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </div>
      `;
      bgClass = 'bg-red-50/20 border-red-200/60';
    } else {
      iconHtml = `
        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center shrink-0 border border-slate-200">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="6" stroke-width="2"/>
          </svg>
        </div>
      `;
    }

    html += `
      <div class="p-3 sm:p-3.5 rounded-xl border ${bgClass} transition-all flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          ${iconHtml}
          <div class="min-w-0">
            <h4 class="text-xs sm:text-sm font-bold text-slate-800 leading-tight truncate">${item.title}</h4>
            <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 leading-snug break-words">${item.desc}</p>
          </div>
        </div>
        <div class="shrink-0 text-right">
          ${item.badge}
        </div>
      </div>
    `;
  }

  container.innerHTML = html;
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeActivityTimelineModal();
    closeHandoverModal();
    closeStatusModal();
  }
});

// --- Stylized Status Modal ---
let _resShouldReload = false;
function showStatusModal(isSuccess, title, message, reloadOnClose = false) {
  _resShouldReload = reloadOnClose;
  const modal = document.getElementById('resStatusModal');
  const icon = document.getElementById('resStatusIcon');
  const t = document.getElementById('resStatusTitle');
  const m = document.getElementById('resStatusMessage');
  const btn = document.getElementById('resStatusBtn');
  if (t) t.textContent = title;
  if (m) m.textContent = message;
  if (icon) {
    if (isSuccess) {
      icon.className = 'w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4 border border-emerald-100';
      icon.innerHTML = '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
    } else {
      icon.className = 'w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-4 border border-rose-100';
      icon.innerHTML = '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
    }
  }
  if (btn) btn.textContent = isSuccess ? 'Continue' : 'Dismiss';
  if (modal) {
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('open'));
  }
}
function closeStatusModal() {
  const modal = document.getElementById('resStatusModal');
  if (!modal || !modal.classList.contains('open')) return;
  modal.classList.remove('open');
  setTimeout(() => {
    if (!modal.classList.contains('open')) modal.classList.add('hidden');
    if (_resShouldReload) window.location.reload();
  }, 250);
}

// --- Dynamic Filter, Search, and Pagination Logic ---
let currentReservationFilter = 'pending';
let currentSearchQuery = '';
let currentResPage = 1;
const reservationPageSize = 10;

function setReservationFilter(filter) {
  currentReservationFilter = (filter || 'pending').toLowerCase().trim();
  currentResPage = 1;
  document.querySelectorAll('.res-filter-btn').forEach(btn => {
    const isActive = (btn.dataset.filter || '').toLowerCase() === currentReservationFilter;
    if (isActive) {
      btn.className = 'res-filter-btn active px-3.5 py-1.5 rounded-full bg-white text-slate-900 shadow-sm active:scale-95 transition-all cursor-pointer font-semibold whitespace-nowrap';
    } else {
      btn.className = 'res-filter-btn px-3.5 py-1.5 rounded-full text-slate-500 hover:text-slate-900 hover:bg-white/60 active:scale-95 transition-all cursor-pointer font-semibold whitespace-nowrap';
    }
  });
  applyReservationFilters();
}

function filterSearch(val) {
  currentSearchQuery = (val || '').toLowerCase().trim();
  currentResPage = 1;
  applyReservationFilters();
}

function applyReservationFilters() {
  const rows = Array.from(document.querySelectorAll('#resBody tr.res-row'));
  const noMatchRow = document.getElementById('resNoMatchRow');

  const matchingRows = rows.filter(row => {
    const rowStatus = (row.dataset.filterStatus || '').toLowerCase();
    const searchData = (row.dataset.search || row.textContent || '').toLowerCase();

    const matchesFilter = (currentReservationFilter === 'all') 
      || (rowStatus === currentReservationFilter)
      || (currentReservationFilter === 'pending' && (rowStatus === 'pending' || rowStatus === 'reservation form pending' || rowStatus === 'submitted'));
    const matchesSearch = !currentSearchQuery || searchData.includes(currentSearchQuery);

    return matchesFilter && matchesSearch;
  });

  const totalMatching = matchingRows.length;
  const totalPages = Math.ceil(totalMatching / reservationPageSize) || 1;
  if (currentResPage > totalPages) currentResPage = totalPages;
  if (currentResPage < 1) currentResPage = 1;

  const startIndex = (currentResPage - 1) * reservationPageSize;
  const endIndex = Math.min(startIndex + reservationPageSize, totalMatching);

  rows.forEach(row => {
    row.style.display = 'none';
  });

  matchingRows.slice(startIndex, endIndex).forEach(row => {
    row.style.display = '';
  });

  if (noMatchRow) {
    noMatchRow.classList.toggle('hidden', totalMatching > 0 || rows.length === 0);
  }

  const showingStartEl = document.getElementById('resShowingStart');
  const showingEndEl = document.getElementById('resShowingEnd');
  const totalCountEl = document.getElementById('resTotalCount');

  if (showingStartEl && showingEndEl && totalCountEl) {
    showingStartEl.textContent = totalMatching === 0 ? '0' : (startIndex + 1);
    showingEndEl.textContent = endIndex;
    totalCountEl.textContent = totalMatching;
  }

  renderReservationPagination(totalPages, totalMatching);
}

function goToPage(page) {
  currentResPage = page;
  applyReservationFilters();
}

function renderReservationPagination(totalPages, totalMatching) {
  const container = document.getElementById('resPaginationControls');
  if (!container) return;

  if (totalMatching === 0 || totalPages <= 1) {
    container.innerHTML = `<span class="px-2 py-1 text-xs font-semibold text-slate-400">1 / 1</span>`;
    return;
  }

  let html = '';
  const prevDisabled = currentResPage <= 1;
  html += `
    <button type="button" onclick="goToPage(${currentResPage - 1})" 
      class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 transition-all active:scale-95 ${prevDisabled ? 'opacity-30 cursor-not-allowed text-slate-300' : 'text-slate-500 hover:bg-slate-50 cursor-pointer'}" 
      ${prevDisabled ? 'disabled' : ''} title="Previous">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </button>
  `;

  for (let p = 1; p <= totalPages; p++) {
    const isCurrent = p === currentResPage;
    html += `
      <button type="button" onclick="goToPage(${p})" 
        class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold transition-all active:scale-95 cursor-pointer ${isCurrent ? 'bg-slate-900 text-white shadow-xs' : 'border border-slate-200 text-slate-600 hover:bg-slate-50'}">
        ${p}
      </button>
    `;
  }

  const nextDisabled = currentResPage >= totalPages;
  html += `
    <button type="button" onclick="goToPage(${currentResPage + 1})" 
      class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 transition-all active:scale-95 ${nextDisabled ? 'opacity-30 cursor-not-allowed text-slate-300' : 'text-slate-500 hover:bg-slate-50 cursor-pointer'}" 
      ${nextDisabled ? 'disabled' : ''} title="Next">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>
  `;

  container.innerHTML = html;
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', applyReservationFilters);
} else {
  applyReservationFilters();
}
</script>

<!-- Reservations Status Modal -->
<div id="resStatusModal"
  class="modal-backdrop fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden"
  onclick="if(event.target===this)closeStatusModal()">
  <div class="modal-card bg-white rounded-3xl shadow-2xl border border-slate-100 p-6 sm:p-7 max-w-sm w-full text-center" onclick="event.stopPropagation()">
    <div id="resStatusIcon" class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4 border"></div>
    <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-1" id="resStatusTitle">Notification</h3>
    <p class="text-xs text-slate-500 mb-6 leading-relaxed" id="resStatusMessage"></p>
    <button type="button" id="resStatusBtn" onclick="closeStatusModal()"
      class="btn-press w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-all shadow-sm active:scale-95">
      Continue
    </button>
  </div>
</div>

</body>
</html>
