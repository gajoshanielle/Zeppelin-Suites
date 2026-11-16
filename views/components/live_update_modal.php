<?php
/**
 * Zeppelin Suites - Simple Live Update Notification Modal
 * Reusable across Admin and Unit Owner portals (Inquiries & Lease Management).
 */
$updateModalTitle = $updateModalTitle ?? 'Update Available';
$updateModalMessage = $updateModalMessage ?? 'There is an update here. Please refresh the page.';
?>

<!-- SIMPLE LIVE UPDATE MODAL -->
<div id="liveUpdateModal" class="fixed inset-0 z-[120] hidden items-center justify-center p-4 bg-slate-900/30 backdrop-blur-xs transition-opacity duration-200" onclick="if(event.target===this)dismissLiveUpdateModal()">
  <div class="bg-white rounded-2xl shadow-xl border border-slate-100 max-w-sm w-full p-5 sm:p-6 text-center relative overflow-hidden transform transition-all duration-200 scale-95 opacity-0" id="liveUpdateCard" onclick="event.stopPropagation()">
    
    <!-- Top-right close button -->
    <button type="button" onclick="dismissLiveUpdateModal()" class="absolute top-3.5 right-3.5 w-7 h-7 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-colors text-base font-medium cursor-pointer" title="Close">
      &times;
    </button>

    <!-- Clean Refresh Icon -->
    <div class="w-11 h-11 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center mx-auto mb-3">
      <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
    </div>

    <!-- Title & Message -->
    <h3 class="text-base font-bold text-slate-900 mb-1" id="liveUpdateTitle">
      <?= htmlspecialchars($updateModalTitle, ENT_QUOTES, 'UTF-8') ?>
    </h3>

    <p class="text-xs text-slate-500 leading-relaxed mb-5" id="liveUpdateDesc">
      <?= htmlspecialchars($updateModalMessage, ENT_QUOTES, 'UTF-8') ?>
    </p>

    <!-- Action Buttons -->
    <div class="flex items-center gap-2">
      <button type="button" 
        onclick="dismissLiveUpdateModal()" 
        class="flex-1 py-2 px-3 rounded-xl border border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-600 transition-all cursor-pointer">
        Dismiss
      </button>
      <button type="button" 
        onclick="handleManualPageRefresh()" 
        class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-xs font-semibold text-white shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <span>Refresh</span>
      </button>
    </div>

  </div>
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
  };

  function showLiveUpdateModal(message) {
    modalShown = true;

    // Show indicator dot on the circular refresh button
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
            showLiveUpdateModal(data.message || 'There is an update here. Please refresh the page.');
          }
        }
      }
    } catch (_) {
      // Ignore network/offline failures
    } finally {
      isChecking = false;
    }
  }

  // Periodic poll every 20 seconds
  setInterval(checkForLiveUpdates, 20000);

  // Instant check when switching tabs back to this page
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
      checkForLiveUpdates();
    }
  });
})();
</script>
