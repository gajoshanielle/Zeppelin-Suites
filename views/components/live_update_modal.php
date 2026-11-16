<?php
/**
 * Zeppelin Suites - Live Update Notification Modal & Auto-Check Component
 * Reusable across Admin and Unit Owner portals (Inquiries & Lease Management).
 *
 * Expected variables (optional):
 * - $updateCheckUrl (string) Endpoint for checking updates
 * - $stateHash (string) Initial state hash from backend model
 * - $updateModalTitle (string) Header title for the modal
 * - $updateModalMessage (string) Message body for the modal
 */
$updateModalTitle = $updateModalTitle ?? 'Page Update Available';
$updateModalMessage = $updateModalMessage ?? 'There is a new update available. Please refresh the page to view the latest data.';
?>

<!-- LIVE UPDATE NOTIFICATION MODAL -->
<div id="liveUpdateModal" class="fixed inset-0 z-[120] hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs transition-opacity duration-200" onclick="if(event.target===this)dismissLiveUpdateModal()">
  <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-md w-full p-6 sm:p-7 text-center overflow-hidden transform transition-all duration-200 scale-95 opacity-0" id="liveUpdateCard" onclick="event.stopPropagation()">
    
    <!-- Animated Refresh Icon Badge -->
    <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mx-auto mb-4 relative shadow-sm">
      <svg class="w-8 h-8 animate-spin text-amber-600" style="animation-duration: 3s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
      <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-amber-500 border-2 border-white"></span>
      </span>
    </div>

    <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200/80 mb-2">
      Live System Update
    </span>
    
    <h3 class="text-lg font-bold text-slate-900 mb-1.5" id="liveUpdateTitle">
      <?= htmlspecialchars($updateModalTitle, ENT_QUOTES, 'UTF-8') ?>
    </h3>

    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-6" id="liveUpdateDesc">
      <?= htmlspecialchars($updateModalMessage, ENT_QUOTES, 'UTF-8') ?>
    </p>

    <div class="flex items-center gap-2.5">
      <button type="button" 
        onclick="dismissLiveUpdateModal()" 
        class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 hover:border-slate-300 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-all active:scale-95 cursor-pointer">
        Dismiss
      </button>
      <button type="button" 
        onclick="handleManualPageRefresh()" 
        class="flex-1 py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-xs font-semibold text-white shadow-sm transition-all active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <span>Refresh Page</span>
      </button>
    </div>
  </div>
</div>

<!-- SUBTLE FLOATING TOAST SNACKBAR (Shows after modal dismissal) -->
<div id="liveUpdateToast" class="fixed bottom-5 right-5 z-[110] hidden items-center gap-3 bg-slate-900/95 text-white px-4 py-3 rounded-2xl shadow-2xl border border-slate-800 backdrop-blur-sm transition-all duration-200">
  <div class="flex items-center gap-2">
    <span class="relative flex h-2.5 w-2.5">
      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
    </span>
    <span class="text-xs font-semibold text-slate-200">New updates available</span>
  </div>
  <button type="button" onclick="handleManualPageRefresh()" class="text-xs font-bold text-amber-400 hover:text-amber-300 underline underline-offset-2 ml-1 cursor-pointer">
    Refresh Now
  </button>
  <button type="button" onclick="document.getElementById('liveUpdateToast').classList.add('hidden')" class="text-slate-400 hover:text-white text-xs ml-1 font-mono cursor-pointer" title="Close">&times;</button>
</div>

<script>
(function() {
  let currentHash = '<?= htmlspecialchars((string)($stateHash ?? ''), ENT_QUOTES, 'UTF-8') ?>';
  const checkUrl = '<?= htmlspecialchars((string)($updateCheckUrl ?? ''), ENT_QUOTES, 'UTF-8') ?>';
  let isChecking = false;
  let modalShown = false;

  window.handleManualPageRefresh = function() {
    const icon = document.getElementById('refreshBtnIcon');
    if (icon) {
      icon.classList.add('animate-spin');
    }
    const btn = document.getElementById('pageRefreshBtn');
    if (btn) {
      btn.disabled = true;
      btn.classList.add('opacity-75');
    }
    window.location.reload();
  };

  window.dismissLiveUpdateModal = function() {
    const modal = document.getElementById('liveUpdateModal');
    const card = document.getElementById('liveUpdateCard');
    if (card) {
      card.classList.remove('scale-100', 'opacity-100');
      card.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
      if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    }, 200);

    const toast = document.getElementById('liveUpdateToast');
    if (toast) {
      toast.classList.remove('hidden');
      toast.classList.add('flex');
    }
  };

  function showLiveUpdateModal(message) {
    modalShown = true;

    // Activate the pinging badge on the circular refresh button
    const badge = document.getElementById('refreshBtnBadge');
    if (badge) {
      badge.classList.remove('hidden');
    }

    if (message) {
      const descEl = document.getElementById('liveUpdateDesc');
      if (descEl) descEl.textContent = message;
    }

    const modal = document.getElementById('liveUpdateModal');
    const card = document.getElementById('liveUpdateCard');
    if (modal && card) {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      requestAnimationFrame(() => {
        card.classList.remove('scale-95', 'opacity-0');
        card.classList.add('scale-100', 'opacity-100');
      });
    }
  }

  async function checkForLiveUpdates() {
    if (!checkUrl || isChecking) return;
    isChecking = true;

    try {
      const glue = checkUrl.includes('?') ? '&' : '?';
      const url = checkUrl + glue + 'hash=' + encodeURIComponent(currentHash) + '&t=' + Date.now();
      const res = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      if (!res.ok) return;

      const data = await res.json();
      if (data && data.success) {
        if (!currentHash && data.state && data.state.state_hash) {
          currentHash = data.state.state_hash;
        } else if (data.has_update || (data.state && data.state.state_hash && data.state.state_hash !== currentHash)) {
          if (!modalShown) {
            showLiveUpdateModal(data.message || 'There is an update available on this page. Please refresh the page to view the latest data.');
          }
        }
      }
    } catch (_) {
      // Ignore network/offline failures
    } finally {
      isChecking = false;
    }
  }

  // Periodic poll every 15 seconds
  setInterval(checkForLiveUpdates, 15000);

  // Instant check when switching tabs back to this page
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
      checkForLiveUpdates();
    }
  });
})();
</script>

