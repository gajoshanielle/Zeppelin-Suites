<?php
declare(strict_types=1);

/**
 * Zeppelin Suites - System Logs & Error Audit View
 * Pure presentation: strictly NO SQL queries or direct database connections.
 */
if (!function_exists('e')) {
    function e($value): string {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

$baseUrl = $baseUrl ?? rtrim((string) env('APP_URL', '/Zeppelin-Suites'), '/');
$activeTab = $activeTab ?? 'logs';
$initials = strtoupper(substr((string) ($userSession['full_name'] ?? 'Admin'), 0, 1));
$csrfToken = Csrf::token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'Zeppelin Suites - System Logs & Error Audit') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['DM Sans', 'sans-serif'],
            mono: ['DM Mono', 'monospace']
          }
        }
      }
    }
  </script>
  <style>
    * { font-family: 'DM Sans', sans-serif; }
    .sidebar { width: 256px; transition: width 0.3s cubic-bezier(0.4,0,0.2,1), transform 0.3s cubic-bezier(0.4,0,0.2,1); background: rgba(255,255,255,0.92); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }
    .sidebar.collapsed { width: 68px; }
    @media (max-width: 767px) {
      .sidebar { transform: translateX(-100%); position: fixed; z-index: 50; height: 100vh; width: 256px !important; }
      .sidebar.open { transform: translateX(0); }
    }
    .main-wrapper { margin-left: 256px; transition: margin-left 0.3s cubic-bezier(0.4,0,0.2,1); }
    .main-wrapper.sidebar-collapsed { margin-left: 68px; }
    @media (max-width: 767px) { .main-wrapper { margin-left: 0 !important; } }
    .overlay { display: none; pointer-events: none; }
    .overlay.show { display: block; pointer-events: auto; }
    .sidebar-logo { transition: opacity 0.2s ease, width 0.2s ease; }
    .sidebar.collapsed .sidebar-logo { opacity: 0; width: 0; overflow: hidden; pointer-events: none; }
    .sidebar-link { position: relative; transition: all 0.18s ease; white-space: nowrap; overflow: hidden; }
    .sidebar-link.active { background: #0f172a; color: #fff; }
    .sidebar-link.active .nav-icon { color: #60a5fa; }
    .sidebar-link:not(.active):hover { background: #eff6ff; color: #1d4ed8; }
    .sidebar-link:not(.active):hover .nav-icon { color: #3b82f6; }
    .sidebar.collapsed .nav-label, .sidebar.collapsed .nav-badge, .sidebar.collapsed .notice-section { display: none; }
    .sidebar.collapsed .sidebar-link { justify-content: center; padding-left: 0; padding-right: 0; }
    .sidebar.collapsed .collapse-icon { transform: rotate(180deg); }
    .collapse-icon { transition: transform 0.3s ease; }
    .sidebar.collapsed .sidebar-link:hover::after { content: attr(data-tooltip); position: absolute; left: calc(100% + 10px); top: 50%; transform: translateY(-50%); background: #0f172a; color: #fff; font-size: 12px; padding: 5px 10px; border-radius: 8px; white-space: nowrap; z-index: 999; box-shadow: 0 4px 16px rgba(0,0,0,0.18); pointer-events: none; }
    .btn-press { transition: all 0.15s ease; }
    .btn-press:active { transform: scale(0.95); }
    .glass-header { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); }
    .stat-card { transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -8px rgba(0,0,0,0.08); }
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: #f8fafc; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    .modal-backdrop { transition: opacity 0.2s ease; }
    .modal-backdrop.open { opacity: 1; }
    .modal-backdrop:not(.open) { opacity: 0; pointer-events: none; }
    .modal-card { transition: transform 0.25s cubic-bezier(.34,1.56,.64,1), opacity 0.2s ease; }
    .modal-backdrop:not(.open) .modal-card { transform: scale(0.94); opacity: 0; }
    .modal-backdrop.open .modal-card { transform: scale(1); opacity: 1; }
  </style>
</head>
<body class="bg-slate-50 text-slate-800 overflow-hidden">

<!-- Overlay and Sidebar -->
<?php include dirname(__DIR__) . '/components/admin_sidebar.php'; ?>

<!-- MAIN WRAPPER -->
<div class="main-wrapper h-screen flex flex-col" id="mainWrapper">
  <!-- TOP BAR / NAVBAR -->
  <?php 
  $navSearchId = 'logsTopSearch';
  $navSearchPlaceholder = 'Search errors, URIs, IPs...';
  $navSearchHandler = 'onkeydown="if(event.key===\'Enter\')applySearch(this.value)"';
  include dirname(__DIR__) . '/components/admin_navbar.php'; 
  ?>

  <!-- CONTENT AREA -->
  <div class="content-area flex-1 max-w-screen-2xl mx-auto w-full overflow-y-auto" id="contentArea">
    <div class="p-4 md:p-6 space-y-6">

      <!-- Breadcrumb & Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
            <span>Admin</span>
            <span>&bull;</span>
            <span class="text-slate-600">System Logs & Audit Trail</span>
          </div>
          <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">System & Error Logs</h1>
          <p class="text-xs text-slate-500 mt-0.5">Real-time audit records, exceptions, stack traces, and runtime diagnostic events.</p>
        </div>

        <div class="flex items-center gap-2">
          <!-- Refresh Button -->
          <a href="<?= e($baseUrl) ?>/admin/logs?date=<?= e($selectedDate) ?>&level=<?= e($selectedLevel) ?>&q=<?= urlencode($searchQuery) ?>&per_page=<?= e($perPage) ?>&page=<?= e($currentPage) ?>" 
             class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all shadow-xs">
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Refresh
          </a>

          <!-- Clear Log Button -->
          <button type="button" onclick="openClearModal()"
                  class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition-all shadow-xs">
            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Clear Date Logs
          </button>
        </div>
      </div>

      <!-- Toast Notification -->
      <?php if (!empty($toast)): ?>
        <div class="p-4 rounded-2xl border <?= $toast['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' ?> text-sm font-medium flex items-center justify-between shadow-xs">
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $toast['type'] === 'success' ? 'M5 13l4 4L19 7' : 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' ?>"/>
            </svg>
            <span><?= e($toast['msg']) ?></span>
          </div>
          <button type="button" onclick="this.parentElement.remove()" class="text-xs font-bold hover:opacity-70">&times;</button>
        </div>
      <?php endif; ?>

      <!-- Stat Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Total Logs -->
        <div class="stat-card bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Records</span>
            <span class="p-2 bg-slate-100 rounded-xl text-slate-700">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
          </div>
          <p class="text-2xl font-bold text-slate-900 mt-2 font-mono"><?= number_format($counts['total']) ?></p>
          <p class="text-[11px] text-slate-400 mt-1">Logged on <?= e($selectedDate) ?></p>
        </div>

        <!-- Errors -->
        <div class="stat-card bg-white p-4 rounded-2xl border border-rose-200/80 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Errors & Crashes</span>
            <span class="p-2 bg-rose-50 rounded-xl text-rose-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
          </div>
          <p class="text-2xl font-bold text-rose-700 mt-2 font-mono"><?= number_format($counts['errors']) ?></p>
          <p class="text-[11px] text-rose-500 mt-1"><?= $counts['errors'] > 0 ? 'Action may be required' : 'Zero unhandled errors' ?></p>
        </div>

        <!-- Warnings -->
        <div class="stat-card bg-white p-4 rounded-2xl border border-amber-200/80 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Warnings</span>
            <span class="p-2 bg-amber-50 rounded-xl text-amber-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </span>
          </div>
          <p class="text-2xl font-bold text-amber-700 mt-2 font-mono"><?= number_format($counts['warnings']) ?></p>
          <p class="text-[11px] text-amber-500 mt-1">PHP notices or deprecations</p>
        </div>

        <!-- Info / Routine -->
        <div class="stat-card bg-white p-4 rounded-2xl border border-blue-200/80 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Info & Routine</span>
            <span class="p-2 bg-blue-50 rounded-xl text-blue-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
          </div>
          <p class="text-2xl font-bold text-blue-700 mt-2 font-mono"><?= number_format($counts['info']) ?></p>
          <p class="text-[11px] text-blue-500 mt-1">Normal system operational logs</p>
        </div>
      </div>

      <!-- Filter Controls Bar -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="<?= e($baseUrl) ?>/admin/logs" class="flex flex-wrap items-center gap-3 w-full lg:w-auto" id="filterForm">
          
          <!-- Date Selector -->
          <div class="flex items-center gap-1.5">
            <label for="dateSelect" class="text-xs font-semibold text-slate-500 shrink-0">Log Date:</label>
            <select name="date" id="dateSelect" onchange="this.form.submit()"
                    class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-slate-900/10">
              <?php foreach ($availableDates as $d): ?>
                <option value="<?= e($d) ?>" <?= $d === $selectedDate ? 'selected' : '' ?>>
                  <?= e($d) ?><?= $d === date('Y-m-d') ? ' (Today)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Severity Level Filter -->
          <div class="flex items-center gap-1.5">
            <label for="levelSelect" class="text-xs font-semibold text-slate-500 shrink-0">Severity:</label>
            <select name="level" id="levelSelect" onchange="this.form.submit()"
                    class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-slate-900/10">
              <option value="ALL" <?= $selectedLevel === 'ALL' ? 'selected' : '' ?>>All Levels</option>
              <option value="ERROR" <?= $selectedLevel === 'ERROR' ? 'selected' : '' ?>>Errors & Critical</option>
              <option value="WARNING" <?= $selectedLevel === 'WARNING' ? 'selected' : '' ?>>Warnings & Notices</option>
              <option value="INFO" <?= $selectedLevel === 'INFO' ? 'selected' : '' ?>>Info Only</option>
              <option value="DEBUG" <?= $selectedLevel === 'DEBUG' ? 'selected' : '' ?>>Debug Only</option>
            </select>
          </div>

          <!-- Per Page Selector -->
          <div class="flex items-center gap-1.5">
            <label for="perPageSelect" class="text-xs font-semibold text-slate-500 shrink-0">Show:</label>
            <select name="per_page" id="perPageSelect" onchange="this.form.submit()"
                    class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-slate-900/10">
              <option value="15" <?= $perPage === 15 ? 'selected' : '' ?>>15 / page</option>
              <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 / page</option>
              <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 / page</option>
              <option value="100" <?= $perPage === 100 ? 'selected' : '' ?>>100 / page</option>
            </select>
          </div>

          <!-- Search Input -->
          <div class="relative flex-1 min-w-[200px]">
            <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="Filter by message, file, or IP..."
                   class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-slate-900/10">
            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>

          <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-all">
            Filter
          </button>

          <?php if ($selectedLevel !== 'ALL' || !empty($searchQuery) || $perPage !== 15): ?>
            <a href="<?= e($baseUrl) ?>/admin/logs?date=<?= e($selectedDate) ?>" class="text-xs text-slate-500 hover:text-slate-800 underline font-medium">
              Reset Filters
            </a>
          <?php endif; ?>
        </form>

        <div class="text-xs text-slate-500 font-medium flex items-center gap-2">
          <span>Showing <strong class="font-mono font-bold text-slate-800"><?= $startIndex ?>–<?= $endIndex ?></strong> of <strong class="font-mono font-bold text-slate-800"><?= number_format($totalLogs) ?></strong></span>
          <?php if ($totalPages > 1): ?>
            <span class="text-slate-300">&bull;</span>
            <span class="font-mono text-slate-400">Page <?= $currentPage ?> of <?= $totalPages ?></span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Logs Stream Table / Cards -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <?php if (empty($logs)): ?>
          <div class="py-16 px-4 text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900 mb-1">No Log Entries Found</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
              <?= !empty($searchQuery) || $selectedLevel !== 'ALL' ? 'No records match your current filter criteria.' : "No system events or errors recorded for {$selectedDate}. Everything ran cleanly." ?>
            </p>
          </div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-slate-50/75 border-b border-slate-200/80 text-slate-500 uppercase tracking-wider text-[11px] font-semibold">
                  <th class="py-3 px-4 w-28">Severity</th>
                  <th class="py-3 px-4 w-44">Timestamp</th>
                  <th class="py-3 px-4 w-48">Request Route</th>
                  <th class="py-3 px-4">Message & Details</th>
                  <th class="py-3 px-4 w-24 text-right">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 font-sans">
                <?php foreach ($logs as $log): ?>
                  <?php 
                    $lvl = strtoupper($log['level']);
                    $badgeBg = match ($lvl) {
                        'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 'bg-rose-50 text-rose-700 border-rose-200 ring-rose-500/20',
                        'WARNING', 'NOTICE'                       => 'bg-amber-50 text-amber-700 border-amber-200 ring-amber-500/20',
                        'DEBUG'                                   => 'bg-slate-100 text-slate-700 border-slate-200 ring-slate-500/20',
                        default                                   => 'bg-blue-50 text-blue-700 border-blue-200 ring-blue-500/20',
                    };
                    $hasDetails = !empty($log['trace']) || !empty($log['context']);
                  ?>
                  <tr class="hover:bg-slate-50/60 transition-colors">
                    <!-- Severity Badge -->
                    <td class="py-3 px-4 align-top">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border ring-1 <?= $badgeBg ?>">
                        <?= e($lvl) ?>
                      </span>
                    </td>

                    <!-- Timestamp -->
                    <td class="py-3 px-4 align-top font-mono text-slate-600 whitespace-nowrap text-[11px]">
                      <?= e($log['timestamp']) ?>
                    </td>

                    <!-- Request Route & IP -->
                    <td class="py-3 px-4 align-top whitespace-nowrap">
                      <?php if (!empty($log['method']) && !empty($log['uri'])): ?>
                        <div class="flex items-center gap-1.5 font-mono text-[11px]">
                          <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-bold text-[9px]"><?= e($log['method']) ?></span>
                          <span class="text-slate-800 truncate max-w-[130px]" title="<?= e($log['uri']) ?>"><?= e($log['uri']) ?></span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono"><?= e($log['ip']) ?></span>
                      <?php else: ?>
                        <span class="text-[11px] text-slate-400 font-mono">System Process</span>
                      <?php endif; ?>
                    </td>

                    <!-- Message -->
                    <td class="py-3 px-4 align-top">
                      <p class="font-medium text-slate-800 break-words leading-relaxed"><?= e($log['message']) ?></p>

                      <?php if (!empty($log['context']['file'])): ?>
                        <p class="text-[11px] font-mono text-rose-600 mt-1">
                          <?= e($log['context']['file']) ?>
                        </p>
                      <?php endif; ?>
                    </td>

                    <!-- Action Details -->
                    <td class="py-3 px-4 align-top text-right">
                      <?php if ($hasDetails): ?>
                        <button type="button" 
                                onclick="viewLogDetail(<?= htmlspecialchars(json_encode($log), ENT_QUOTES, 'UTF-8') ?>)"
                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold transition-all shadow-2xs">
                          Inspect
                        </button>
                      <?php else: ?>
                        <span class="text-slate-300 text-[11px]">-</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <?php if ($totalLogs > 0): ?>
            <!-- Pagination Controls Footer -->
            <div class="flex flex-col sm:flex-row items-center justify-between px-5 py-3.5 border-t border-slate-100 bg-slate-50/50 gap-3">
              <div class="text-xs text-slate-500">
                Showing <span class="font-semibold text-slate-800"><?= $startIndex ?>–<?= $endIndex ?></span> of <span class="font-semibold text-slate-800"><?= number_format($totalLogs) ?></span> log entries
              </div>

              <?php if ($totalPages > 1): ?>
                <?php
                  $buildPageUrl = function(int $p) use ($baseUrl, $selectedDate, $selectedLevel, $searchQuery, $perPage): string {
                    $params = [
                      'date' => $selectedDate,
                      'level' => $selectedLevel,
                      'q' => $searchQuery,
                      'per_page' => $perPage,
                      'page' => $p,
                    ];
                    return e($baseUrl) . '/admin/logs?' . http_build_query($params);
                  };
                ?>
                <nav class="flex items-center gap-1" aria-label="Logs pagination">
                  <!-- Prev -->
                  <?php if ($currentPage > 1): ?>
                    <a href="<?= $buildPageUrl($currentPage - 1) ?>" 
                       class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 transition-all active:scale-95 shadow-2xs"
                       title="Previous page">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                  <?php else: ?>
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200/60 text-slate-300 cursor-not-allowed">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                  <?php endif; ?>

                  <!-- Page Number Buttons -->
                  <?php
                    $range = 2;
                    $startPage = max(1, $currentPage - $range);
                    $endPage = min($totalPages, $currentPage + $range);

                    if ($startPage > 1) {
                      echo '<a href="' . $buildPageUrl(1) . '" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 text-xs font-semibold transition-all">1</a>';
                      if ($startPage > 2) {
                        echo '<span class="w-8 h-8 flex items-center justify-center text-slate-400 text-xs font-bold">&hellip;</span>';
                      }
                    }

                    for ($p = $startPage; $p <= $endPage; $p++) {
                      if ($p === $currentPage) {
                        echo '<span class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-900 text-white text-xs font-bold shadow-xs">' . $p . '</span>';
                      } else {
                        echo '<a href="' . $buildPageUrl($p) . '" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 text-xs font-semibold transition-all">' . $p . '</a>';
                      }
                    }

                    if ($endPage < $totalPages) {
                      if ($endPage < $totalPages - 1) {
                        echo '<span class="w-8 h-8 flex items-center justify-center text-slate-400 text-xs font-bold">&hellip;</span>';
                      }
                      echo '<a href="' . $buildPageUrl($totalPages) . '" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 text-xs font-semibold transition-all">' . $totalPages . '</a>';
                    }
                  ?>

                  <!-- Next -->
                  <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= $buildPageUrl($currentPage + 1) ?>" 
                       class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 transition-all active:scale-95 shadow-2xs"
                       title="Next page">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                  <?php else: ?>
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200/60 text-slate-300 cursor-not-allowed">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                  <?php endif; ?>
                </nav>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<!-- Log Inspection Detail Modal -->
<div id="logDetailModal"
     class="modal-backdrop fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden"
     onclick="if(event.target===this)closeDetailModal()">
  <div class="modal-card bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-3xl w-full max-h-[85vh] flex flex-col overflow-hidden" onclick="event.stopPropagation()">
    <!-- Modal Header -->
    <div class="p-5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/50">
      <div class="flex items-center gap-2.5">
        <span id="modalLevelBadge" class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase border"></span>
        <h3 class="text-base font-bold text-slate-900">Event Inspection</h3>
      </div>
      <button type="button" onclick="closeDetailModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 font-bold transition-all">&times;</button>
    </div>

    <!-- Modal Content -->
    <div class="p-6 overflow-y-auto space-y-4 text-xs font-sans">
      <div>
        <label class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Timestamp & Source</label>
        <p id="modalTimestamp" class="font-mono text-slate-700 mt-0.5"></p>
      </div>

      <div>
        <label class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Message</label>
        <p id="modalMessage" class="text-sm font-semibold text-slate-900 mt-0.5 p-3 rounded-xl bg-slate-50 border border-slate-100 leading-relaxed"></p>
      </div>

      <div id="modalContextWrapper">
        <label class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Context Payload</label>
        <pre id="modalContextJson" class="font-mono text-[11px] p-3 rounded-xl bg-slate-900 text-slate-200 overflow-x-auto mt-1"></pre>
      </div>

      <div id="modalTraceWrapper">
        <div class="flex items-center justify-between mb-1">
          <label class="font-bold text-rose-600 uppercase tracking-wider text-[10px]">Stack Trace</label>
          <button type="button" onclick="copyTrace()" class="text-[11px] text-blue-600 hover:text-blue-700 font-semibold underline">Copy Trace</button>
        </div>
        <pre id="modalTrace" class="font-mono text-[11px] p-3.5 rounded-xl bg-slate-900 text-emerald-400 overflow-x-auto max-h-60 leading-relaxed"></pre>
      </div>
    </div>

    <!-- Modal Footer -->
    <div class="p-4 border-t border-slate-100 flex justify-end shrink-0 bg-slate-50/50">
      <button type="button" onclick="closeDetailModal()" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold text-xs hover:bg-slate-800 transition-all">Close</button>
    </div>
  </div>
</div>

<!-- Clear Log Confirmation Modal -->
<div id="clearLogModal"
     class="modal-backdrop fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden"
     onclick="if(event.target===this)closeClearModal()">
  <div class="modal-card bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-sm w-full p-6 text-center" onclick="event.stopPropagation()">
    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3 border border-rose-100">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
    </div>
    <h3 class="text-base font-bold text-slate-900 mb-1">Clear Logs for <?= e($selectedDate) ?>?</h3>
    <p class="text-xs text-slate-500 mb-6 leading-relaxed">
      This will permanently delete the log records for this date. This action cannot be reversed.
    </p>

    <form method="POST" action="<?= e($baseUrl) ?>/admin/logs/clear" class="flex gap-2">
      <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
      <input type="hidden" name="date" value="<?= e($selectedDate) ?>">
      
      <button type="button" onclick="closeClearModal()"
              class="flex-1 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all">
        Cancel
      </button>
      <button type="submit"
              class="flex-1 py-2.5 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold transition-all shadow-sm">
        Yes, Clear
      </button>
    </form>
  </div>
</div>

<script>
function applySearch(val) {
  const url = new URL(window.location.href);
  url.searchParams.set('q', val);
  url.searchParams.set('page', '1');
  window.location.href = url.toString();
}

function viewLogDetail(log) {
  const modal = document.getElementById('logDetailModal');
  const badge = document.getElementById('modalLevelBadge');
  const ts = document.getElementById('modalTimestamp');
  const msg = document.getElementById('modalMessage');
  const ctxWrapper = document.getElementById('modalContextWrapper');
  const ctxJson = document.getElementById('modalContextJson');
  const traceWrapper = document.getElementById('modalTraceWrapper');
  const trace = document.getElementById('modalTrace');

  const lvl = (log.level || 'INFO').toUpperCase();
  badge.textContent = lvl;

  if (['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT'].includes(lvl)) {
    badge.className = 'px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200';
  } else if (['WARNING', 'NOTICE'].includes(lvl)) {
    badge.className = 'px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-amber-50 text-amber-700 border border-amber-200';
  } else {
    badge.className = 'px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-blue-50 text-blue-700 border border-blue-200';
  }

  ts.textContent = `${log.timestamp || 'N/A'} • Route: ${log.method || 'GET'} ${log.uri || '/'} • IP: ${log.ip || '127.0.0.1'}`;
  msg.textContent = log.message || '';

  if (log.context && Object.keys(log.context).length > 0) {
    ctxWrapper.classList.remove('hidden');
    ctxJson.textContent = JSON.stringify(log.context, null, 2);
  } else {
    ctxWrapper.classList.add('hidden');
  }

  if (log.trace) {
    traceWrapper.classList.remove('hidden');
    trace.textContent = log.trace;
  } else {
    traceWrapper.classList.add('hidden');
  }

  modal.classList.remove('hidden');
  requestAnimationFrame(() => modal.classList.add('open'));
}

function closeDetailModal() {
  const modal = document.getElementById('logDetailModal');
  if (!modal) return;
  modal.classList.remove('open');
  setTimeout(() => modal.classList.add('hidden'), 200);
}

function copyTrace() {
  const trace = document.getElementById('modalTrace').textContent;
  navigator.clipboard.writeText(trace).then(() => {
    alert('Stack trace copied to clipboard!');
  });
}

function openClearModal() {
  const modal = document.getElementById('clearLogModal');
  modal.classList.remove('hidden');
  requestAnimationFrame(() => modal.classList.add('open'));
}

function closeClearModal() {
  const modal = document.getElementById('clearLogModal');
  if (!modal) return;
  modal.classList.remove('open');
}
</script>

</body>
</html>
