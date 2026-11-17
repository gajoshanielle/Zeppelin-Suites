<?php
/**
 * Zeppelin Suites - Universal Live Update Notification Component
 * Loaded automatically in the component layer (sidebar/navbar) on all Admin & Owner pages.
 *
 * Parameters:
 * - $notifierRole: 'admin' or 'owner' (defaults to 'admin')
 */
$notifierRole = $notifierRole ?? 'admin';
$baseUrl = rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');
?>

<!-- UNIVERSAL LIVE UPDATE MODAL -->
<div id="globalUpdateModal" class="fixed inset-0 z-[120] hidden items-center justify-center p-4 bg-slate-900/30 backdrop-blur-xs transition-opacity duration-200" onclick="if(event.target===this)dismissGlobalUpdateModal()">
  <div class="bg-white rounded-2xl shadow-xl border border-slate-100 max-w-sm w-full p-5 sm:p-6 text-center relative overflow-hidden transform transition-all duration-200 scale-95 opacity-0" id="globalUpdateCard" onclick="event.stopPropagation()">
    
    <!-- Top-right close button -->
    <button type="button" onclick="dismissGlobalUpdateModal()" class="absolute top-3.5 right-3.5 w-7 h-7 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-colors text-base font-medium cursor-pointer" title="Close">
      &times;
    </button>

    <!-- Clean Refresh Icon Container -->
    <div id="globalUpdateIconWrap" class="w-11 h-11 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center mx-auto mb-3">
      <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
    </div>

    <!-- Title & Message -->
    <h3 class="text-base font-bold text-slate-900 mb-1" id="globalUpdateTitle">
      Update Available
    </h3>

    <p class="text-xs text-slate-500 leading-relaxed mb-5" id="globalUpdateDesc">
      There is an update here. Please refresh the page.
    </p>

    <!-- Action Buttons -->
    <div class="flex items-center gap-2">
      <button type="button" 
        onclick="dismissGlobalUpdateModal()" 
        class="flex-1 py-2 px-3 rounded-xl border border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-600 transition-all cursor-pointer">
        Dismiss
      </button>
      <button type="button" 
        id="globalUpdateActionBtn"
        onclick="handleGlobalUpdateAction()" 
        class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-xs font-semibold text-white shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <span id="globalUpdateActionBtnText">Refresh</span>
      </button>
    </div>

  </div>
</div>

<script>
(function() {
  const role = '<?= $notifierRole === "owner" ? "owner" : "admin" ?>';
  const baseUrl = '<?= htmlspecialchars($baseUrl, ENT_QUOTES, "UTF-8") ?>';
  const checkUrl = `${baseUrl}/${role}/live-updates`;
  
  let hashes = {
    inquiry: null,
    reservation: null,
    maintenance: null
  };
  let isChecking = false;
  let modalDismissedForSession = false;
  let activeTargetUrl = '';

  window.handleManualGlobalRefresh = function() {
    const icons = document.querySelectorAll('#refreshBtnIcon, #navbarRefreshIcon');
    icons.forEach(ic => ic.classList.add('animate-spin'));
    const btns = document.querySelectorAll('#pageRefreshBtn, #navbarRefreshBtn');
    btns.forEach(b => {
      b.disabled = true;
      b.classList.add('opacity-75');
    });
    window.location.reload();
  };

  // Alias for backward compatibility with existing views
  window.handleManualPageRefresh = window.handleManualGlobalRefresh;

  window.dismissGlobalUpdateModal = function() {
    const modal = document.getElementById('globalUpdateModal');
    const card = document.getElementById('globalUpdateCard');
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
    modalDismissedForSession = true;
  };

  // Alias for backward compatibility
  window.dismissLiveUpdateModal = window.dismissGlobalUpdateModal;

  window.handleGlobalUpdateAction = function() {
    if (activeTargetUrl) {
      const currentPath = window.location.pathname.toLowerCase().replace(/\/+$/, '');
      const targetPath = new URL(activeTargetUrl, window.location.origin).pathname.toLowerCase().replace(/\/+$/, '');
      
      if (currentPath === targetPath) {
        window.handleManualGlobalRefresh();
      } else {
        window.location.href = activeTargetUrl;
      }
    } else {
      window.handleManualGlobalRefresh();
    }
  };

  function showNotificationModal(title, message, targetUrl, actionLabel) {
    activeTargetUrl = targetUrl || '';
    
    // Light up badges on all refresh buttons
    const badges = document.querySelectorAll('#refreshBtnBadge, #navbarRefreshBadge');
    badges.forEach(b => b.classList.remove('hidden'));

    if (modalDismissedForSession) return;

    const modal = document.getElementById('globalUpdateModal');
    const card = document.getElementById('globalUpdateCard');
    const titleEl = document.getElementById('globalUpdateTitle');
    const descEl = document.getElementById('globalUpdateDesc');
    const btnTextEl = document.getElementById('globalUpdateActionBtnText');

    if (titleEl) titleEl.textContent = title || 'Update Available';
    if (descEl) descEl.textContent = message || 'There is an update available. Please refresh the page.';

    // Decide button text based on current page
    const currentPath = window.location.pathname.toLowerCase().replace(/\/+$/, '');
    const targetPath = targetUrl ? new URL(targetUrl, window.location.origin).pathname.toLowerCase().replace(/\/+$/, '') : '';

    if (currentPath === targetPath || !targetUrl) {
      if (btnTextEl) btnTextEl.textContent = 'Refresh Page';
    } else {
      if (btnTextEl) btnTextEl.textContent = actionLabel || 'Go to Page';
    }

    if (modal && card) {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      requestAnimationFrame(() => {
        card.classList.remove('scale-95', 'opacity-0');
        card.classList.add('scale-100', 'opacity-100');
      });
    }
  }

  async function checkLiveUpdates() {
    if (isChecking) return;
    isChecking = true;

    try {
      const params = new URLSearchParams();
      if (hashes.inquiry) params.append('inquiry_hash', hashes.inquiry);
      if (hashes.reservation) params.append('reservation_hash', hashes.reservation);
      if (hashes.maintenance) params.append('maintenance_hash', hashes.maintenance);
      params.append('t', Date.now());

      const res = await fetch(`${checkUrl}?${params.toString()}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      if (!res.ok) return;

      const data = await res.json();
      if (data && data.success && data.hashes) {
        // First run establishes initial baseline so we don't alert prematurely
        if (hashes.inquiry === null && hashes.reservation === null && hashes.maintenance === null) {
          hashes = data.hashes;
          return;
        }

        if (data.has_update) {
          hashes = data.hashes; // update baseline so we don't repeat alert for the same record
          showNotificationModal(data.title, data.message, data.target_url, data.action_label);
        }
      }
    } catch (_) {
      // Ignore background network blips
    } finally {
      isChecking = false;
    }
  }

  // Initial check after 3 seconds, then recurring every 15 seconds
  setTimeout(checkLiveUpdates, 3000);
  setInterval(checkLiveUpdates, 15000);

  // Check immediately when user switches tabs back
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
      checkLiveUpdates();
    }
  });
})();
</script>
