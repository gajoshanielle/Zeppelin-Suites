<?php
declare(strict_types=1);

/**
 * Zeppelin Suites - Secret Admin Impersonation Notice Banner
 * Displayed at the top of the viewport across Owner and Tenant portals
 * whenever a Superadmin is actively visiting/impersonating a resident account.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isImpersonating = !empty($_SESSION['is_impersonating']) && !empty($_SESSION['impersonator_admin_id']);

if (!$isImpersonating) {
    return;
}

$appBaseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');
$residentRole = (string)($_SESSION['role'] ?? 'Resident');
$residentName = (string)($_SESSION['full_name'] ?? 'Resident');
$impersonatorName = (string)($_SESSION['impersonator_name'] ?? 'Secret Admin');
$exitUrl = $appBaseUrl . '/admin/residents/exit-impersonation';
?>

<!-- SECRET ADMIN IMPERSONATION BANNER -->
<div id="secret-admin-impersonation-bar" class="w-full bg-slate-900 text-white shadow-md z-[100] sticky top-0 border-b border-slate-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2.5 flex flex-wrap items-center justify-between gap-3 text-xs sm:text-sm">
    <div class="flex items-center gap-2.5 min-w-0">
      <span class="flex h-2 w-2 relative shrink-0">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
      </span>
      <div class="flex items-center gap-1.5 flex-wrap truncate">
        <span class="font-bold tracking-wide uppercase text-[10px] bg-slate-800 border border-slate-700 px-2 py-0.5 rounded-full text-slate-300">
          Admin Shadow Mode
        </span>
        <span class="text-slate-400 font-medium">
          Logged in as:
        </span>
        <span class="font-semibold text-white underline decoration-slate-600 underline-offset-2">
          <?= htmlspecialchars($residentName, ENT_QUOTES, 'UTF-8') ?>
        </span>
        <span class="text-slate-400 text-xs capitalize">
          (<?= htmlspecialchars($residentRole === 'unit owner' ? 'Unit Owner' : ucfirst($residentRole), ENT_QUOTES, 'UTF-8') ?>)
        </span>
      </div>
    </div>
    
    <div class="flex items-center gap-2 shrink-0">
      <a href="<?= htmlspecialchars($exitUrl, ENT_QUOTES, 'UTF-8') ?>" 
         class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white text-slate-900 hover:bg-slate-100 active:scale-95 font-semibold text-xs rounded-lg shadow-sm hover:shadow transition-all border border-slate-200 group">
        <svg class="w-4 h-4 text-slate-700 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
        </svg>
        <span>← Back to Admin Residents</span>
      </a>
    </div>
  </div>
</div>
