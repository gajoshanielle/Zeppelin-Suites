<?php
declare(strict_types=1);

/**
 * Zeppelin Suites - Country & Phone Utilities Component
 * Provides free API-backed country datasets, landline/dial codes,
 * smart auto-highlighting phone input, and searchable nationality selector with flag icons.
 */
?>
<style>
  /* Styling for Country & Nationality dropdown components */
  .country-code-btn {
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .country-code-btn.highlight-pulse {
    animation: countryHighlightPulse 0.8s ease-out;
  }
  @keyframes countryHighlightPulse {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
    50% { transform: scale(1.04); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.2); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
  }
  .custom-country-menu {
    max-height: 280px;
    scrollbar-width: thin;
  }
  .custom-country-menu::-webkit-scrollbar {
    width: 5px;
  }
  .custom-country-menu::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
  }
  .nationality-active-item {
    background-color: #f1f5f9;
    color: #0f172a;
    font-weight: 600;
  }
</style>

<script>
(function() {
  // Built-in comprehensive country dataset with phone dial codes, demonyms, and flags
  const BASE_COUNTRIES = [
    { code: "PH", name: "Philippines", dial: "+63", demonym: "Filipino", flag: "🇵🇭" },
    { code: "US", name: "United States", dial: "+1", demonym: "American", flag: "🇺🇸" },
    { code: "GB", name: "United Kingdom", dial: "+44", demonym: "British", flag: "🇬🇧" },
    { code: "CA", name: "Canada", dial: "+1", demonym: "Canadian", flag: "🇨🇦" },
    { code: "AU", name: "Australia", dial: "+61", demonym: "Australian", flag: "🇦🇺" },
    { code: "JP", name: "Japan", dial: "+81", demonym: "Japanese", flag: "🇯🇵" },
    { code: "KR", name: "South Korea", dial: "+82", demonym: "Korean", flag: "🇰🇷" },
    { code: "SG", name: "Singapore", dial: "+65", demonym: "Singaporean", flag: "🇸🇬" },
    { code: "CN", name: "China", dial: "+86", demonym: "Chinese", flag: "🇨🇳" },
    { code: "HK", name: "Hong Kong", dial: "+852", demonym: "Hongkonger", flag: "🇭🇰" },
    { code: "TW", name: "Taiwan", dial: "+886", demonym: "Taiwanese", flag: "🇹🇼" },
    { code: "MY", name: "Malaysia", dial: "+60", demonym: "Malaysian", flag: "🇲🇾" },
    { code: "ID", name: "Indonesia", dial: "+62", demonym: "Indonesian", flag: "🇮🇩" },
    { code: "TH", name: "Thailand", dial: "+66", demonym: "Thai", flag: "🇹🇭" },
    { code: "VN", name: "Vietnam", dial: "+84", demonym: "Vietnamese", flag: "🇻🇳" },
    { code: "IN", name: "India", dial: "+91", demonym: "Indian", flag: "🇮🇳" },
    { code: "DE", name: "Germany", dial: "+49", demonym: "German", flag: "🇩🇪" },
    { code: "FR", name: "France", dial: "+33", demonym: "French", flag: "🇫🇷" },
    { code: "IT", name: "Italy", dial: "+39", demonym: "Italian", flag: "🇮🇹" },
    { code: "ES", name: "Spain", dial: "+34", demonym: "Spanish", flag: "🇪🇸" },
    { code: "NL", name: "Netherlands", dial: "+31", demonym: "Dutch", flag: "🇳🇱" },
    { code: "CH", name: "Switzerland", dial: "+41", demonym: "Swiss", flag: "🇨🇭" },
    { code: "SE", name: "Sweden", dial: "+46", demonym: "Swedish", flag: "🇸🇪" },
    { code: "NO", name: "Norway", dial: "+47", demonym: "Norwegian", flag: "🇳🇴" },
    { code: "DK", name: "Denmark", dial: "+45", demonym: "Danish", flag: "🇩🇰" },
    { code: "NZ", name: "New Zealand", dial: "+64", demonym: "New Zealander", flag: "🇳🇿" },
    { code: "IE", name: "Ireland", dial: "+353", demonym: "Irish", flag: "🇮🇪" },
    { code: "AE", name: "United Arab Emirates", dial: "+971", demonym: "Emirati", flag: "🇦🇪" },
    { code: "SA", name: "Saudi Arabia", dial: "+966", demonym: "Saudi", flag: "🇸🇦" },
    { code: "QA", name: "Qatar", dial: "+974", demonym: "Qatari", flag: "🇶🇦" },
    { code: "KW", name: "Kuwait", dial: "+965", demonym: "Kuwaiti", flag: "🇰🇼" },
    { code: "IL", name: "Israel", dial: "+972", demonym: "Israeli", flag: "🇮🇱" },
    { code: "BR", name: "Brazil", dial: "+55", demonym: "Brazilian", flag: "🇧🇷" },
    { code: "MX", name: "Mexico", dial: "+52", demonym: "Mexican", flag: "🇲🇽" },
    { code: "AR", name: "Argentina", dial: "+54", demonym: "Argentine", flag: "🇦🇷" },
    { code: "RU", name: "Russia", dial: "+7", demonym: "Russian", flag: "🇷🇺" },
    { code: "ZA", name: "South Africa", dial: "+27", demonym: "South African", flag: "🇿🇦" },
    { code: "AT", name: "Austria", dial: "+43", demonym: "Austrian", flag: "🇦🇹" },
    { code: "BE", name: "Belgium", dial: "+32", demonym: "Belgian", flag: "🇧🇪" },
    { code: "PT", name: "Portugal", dial: "+351", demonym: "Portuguese", flag: "🇵🇹" },
    { code: "GR", name: "Greece", dial: "+30", demonym: "Greek", flag: "🇬🇷" },
    { code: "TR", name: "Turkey", dial: "+90", demonym: "Turkish", flag: "🇹🇷" },
    { code: "EG", name: "Egypt", dial: "+20", demonym: "Egyptian", flag: "🇪🇬" },
    { code: "PL", name: "Poland", dial: "+48", demonym: "Polish", flag: "🇵🇱" },
    { code: "CZ", name: "Czech Republic", dial: "+420", demonym: "Czech", flag: "🇨🇿" },
    { code: "HU", name: "Hungary", dial: "+36", demonym: "Hungarian", flag: "🇭🇺" },
    { code: "FI", name: "Finland", dial: "+358", demonym: "Finnish", flag: "🇫🇮" },
    { code: "IE", name: "Ireland", dial: "+353", demonym: "Irish", flag: "🇮🇪" },
    { code: "CL", name: "Chile", dial: "+56", demonym: "Chilean", flag: "🇨🇱" },
    { code: "CO", name: "Colombia", dial: "+57", demonym: "Colombian", flag: "🇨🇴" },
    { code: "PE", name: "Peru", dial: "+51", demonym: "Peruvian", flag: "🇵🇪" }
  ];

  window.ZepCountries = {
    list: [...BASE_COUNTRIES],
    loadedFromApi: false,

    // Helper to get flag image HTML
    getFlagImg(code, fallbackFlag = '🌐') {
      const lower = (code || '').toLowerCase();
      if (!lower) return `<span class="inline-block mr-1.5 text-base leading-none">${fallbackFlag}</span>`;
      return `<img src="https://flagcdn.com/w40/${lower}.png" alt="${code}" class="inline-block w-4 h-3 object-cover rounded-xs mr-2 shadow-2xs shrink-0" onerror="this.outerHTML='<span class=\\'mr-1.5 text-sm\\'>${fallbackFlag}</span>'">`;
    },

    // Background fetch to enrich data from free RestCountries API
    async loadApiData() {
      if (this.loadedFromApi) return;
      try {
        const cache = localStorage.getItem('zep_restcountries_cache');
        if (cache) {
          const parsed = JSON.parse(cache);
          if (Array.isArray(parsed) && parsed.length > 50) {
            this.list = parsed;
            this.loadedFromApi = true;
            return;
          }
        }

        const res = await fetch('https://restcountries.com/v3.1/all?fields=name,cca2,idd,flags,demonyms', { cache: 'force-cache' });
        if (!res.ok) throw new Error('API status ' + res.status);
        const data = await res.json();
        if (Array.isArray(data)) {
          const formatted = [];
          data.forEach(c => {
            const code = c.cca2;
            const name = c.name?.common || '';
            const root = c.idd?.root || '';
            const suffixes = c.idd?.suffixes || [];
            const dial = suffixes.length === 1 ? root + suffixes[0] : root;
            const demonym = c.demonyms?.eng?.m || c.demonyms?.eng?.f || name;
            const flag = c.flag || '🌐';
            if (code && name && dial) {
              formatted.push({ code, name, dial, demonym, flag });
            }
          });

          // Sort prioritizing Philippines and top countries, then alphabetical
          const priority = ['PH', 'US', 'GB', 'CA', 'AU', 'JP', 'KR', 'SG', 'CN', 'MY', 'DE', 'AE'];
          formatted.sort((a, b) => {
            const idxA = priority.indexOf(a.code);
            const idxB = priority.indexOf(b.code);
            if (idxA !== -1 && idxB !== -1) return idxA - idxB;
            if (idxA !== -1) return -1;
            if (idxB !== -1) return 1;
            return a.name.localeCompare(b.name);
          });

          if (formatted.length > 50) {
            this.list = formatted;
            this.loadedFromApi = true;
            try {
              localStorage.setItem('zep_restcountries_cache', JSON.stringify(formatted.slice(0, 150)));
            } catch (e) {}
          }
        }
      } catch (err) {
        // Fallback to built-in list gracefully
        console.info('RestCountries background sync info: Using bundled country dataset.');
      }
    }
  };

  // Fire async API prefetch
  window.ZepCountries.loadApiData();

  /**
   * Initializes the Phone Country Dial Code Dropdown + Auto-Highlighting
   */
  window.initPhoneCountryDropdown = function(options) {
    const {
      selectBtnId,
      dropdownId,
      phoneInputId,
      hiddenFieldId,
      initialCode = 'PH',
      initialPhone = ''
    } = options;

    const selectBtn = document.getElementById(selectBtnId);
    const dropdown = document.getElementById(dropdownId);
    const phoneInput = document.getElementById(phoneInputId);
    const hiddenField = document.getElementById(hiddenFieldId);

    if (!selectBtn || !dropdown || !phoneInput) return;

    let selectedCountry = window.ZepCountries.list.find(c => c.code === initialCode) || window.ZepCountries.list[0];

    function updateSelectedUI(country, animate = false) {
      selectedCountry = country;
      selectBtn.innerHTML = `
        <span class="flex items-center gap-1.5 shrink-0">
          ${window.ZepCountries.getFlagImg(country.code, country.flag)}
          <span class="font-bold text-xs sm:text-sm text-zinc-900 font-mono">${country.dial}</span>
        </span>
        <svg class="w-3.5 h-3.5 text-zinc-400 shrink-0 ml-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      `;

      if (animate) {
        selectBtn.classList.add('highlight-pulse');
        selectBtn.classList.add('border-emerald-500', 'bg-emerald-50/40');
        setTimeout(() => {
          selectBtn.classList.remove('highlight-pulse', 'border-emerald-500', 'bg-emerald-50/40');
        }, 800);
      }

      syncHiddenValue();
    }

    function syncHiddenValue() {
      const raw = phoneInput.value.trim();
      if (!raw) {
        if (hiddenField) hiddenField.value = '';
        return;
      }

      // If user already typed the dial code prefix, don't duplicate it
      if (raw.startsWith('+')) {
        if (hiddenField) hiddenField.value = raw;
      } else {
        // Strip leading local zero if present for dial code (e.g. 0917 -> +63 917)
        let cleaned = raw;
        if (selectedCountry.code === 'PH' && cleaned.startsWith('09')) {
          cleaned = cleaned.substring(1);
        }
        if (hiddenField) hiddenField.value = `${selectedCountry.dial} ${cleaned}`.trim();
      }
    }

    // Initialize dropdown shell with persistent search input
    dropdown.innerHTML = `
      <div class="p-2 border-b border-zinc-100 sticky top-0 bg-white z-10">
        <input type="text" id="${dropdownId}_search" placeholder="Search code or country..."
          class="w-full text-xs px-3 py-1.5 border border-zinc-200 rounded-lg outline-none focus:border-zinc-900 bg-zinc-50"
          autocomplete="off">
      </div>
      <div id="${dropdownId}_list" class="custom-country-menu overflow-y-auto py-1 max-h-56"></div>
    `;

    const searchInput = document.getElementById(`${dropdownId}_search`);
    const listContainer = document.getElementById(`${dropdownId}_list`);

    // Render filtered country list items
    function renderCountryList(searchTerm = '') {
      if (!listContainer) return;
      const term = searchTerm.trim().toLowerCase();
      const filtered = window.ZepCountries.list.filter(c =>
        c.name.toLowerCase().includes(term) ||
        c.dial.includes(term) ||
        c.code.toLowerCase().includes(term)
      );

      if (filtered.length === 0) {
        listContainer.innerHTML = `<div class="px-4 py-3 text-xs text-zinc-400 text-center">No matching country code</div>`;
        return;
      }

      let html = '';
      filtered.forEach((c, index) => {
        const isSelected = c.code === selectedCountry.code;
        const isTopMatch = term.length > 0 && index === 0;
        html += `
          <button type="button" data-code="${c.code}"
            class="country-opt w-full px-3 py-2 text-left text-xs flex items-center justify-between hover:bg-zinc-100 transition-colors ${isSelected ? 'bg-zinc-50 font-bold' : ''} ${isTopMatch ? 'bg-zinc-100/70 font-semibold' : ''}">
            <span class="flex items-center gap-2 truncate">
              ${window.ZepCountries.getFlagImg(c.code, c.flag)}
              <span class="truncate text-zinc-800">${c.name}</span>
            </span>
            <span class="font-mono text-zinc-500 font-semibold shrink-0 ml-2">${c.dial}</span>
          </button>
        `;
      });
      listContainer.innerHTML = html;

      // Event listeners for option items
      listContainer.querySelectorAll('.country-opt').forEach(opt => {
        opt.addEventListener('click', (e) => {
          e.stopPropagation();
          const cCode = opt.getAttribute('data-code');
          const found = window.ZepCountries.list.find(c => c.code === cCode);
          if (found) {
            updateSelectedUI(found, true);
            closeDropdown();
            phoneInput.focus();
          }
        });
      });
    }

    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        renderCountryList(e.target.value);
      });

      searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          const firstOpt = listContainer.querySelector('.country-opt');
          if (firstOpt) {
            const cCode = firstOpt.getAttribute('data-code');
            const found = window.ZepCountries.list.find(c => c.code === cCode);
            if (found) {
              updateSelectedUI(found, true);
              closeDropdown();
              phoneInput.focus();
            }
          }
        } else if (e.key === 'Escape') {
          closeDropdown();
          selectBtn.focus();
        }
      });
    }

    function openDropdown() {
      dropdown.classList.remove('hidden');
      if (searchInput) {
        searchInput.value = '';
        renderCountryList('');
        setTimeout(() => searchInput.focus(), 30);
      } else {
        renderCountryList('');
      }
    }

    function closeDropdown() {
      dropdown.classList.add('hidden');
    }

    selectBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (dropdown.classList.contains('hidden')) {
        openDropdown();
      } else {
        closeDropdown();
      }
    });

    document.addEventListener('click', (e) => {
      if (!dropdown.contains(e.target) && !selectBtn.contains(e.target)) {
        closeDropdown();
      }
    });

    // Synchronize hidden submission input when typing phone number without altering selected country
    phoneInput.addEventListener('input', () => {
      syncHiddenValue();
    });

    // Initial pre-fill handling
    if (initialPhone) {
      phoneInput.value = initialPhone;
      // Detect country from initial phone if prefixed
      if (initialPhone.startsWith('+')) {
        const dDigits = initialPhone.substring(1).replace(/[^0-9]/g, '');
        for (const c of window.ZepCountries.list) {
          const cd = c.dial.replace(/[^0-9]/g, '');
          if (dDigits.startsWith(cd)) {
            selectedCountry = c;
            break;
          }
        }
      }
    }

    updateSelectedUI(selectedCountry, false);
  };

  /**
   * Initializes the Searchable Nationality Selector with Flag Icons
   */
  window.initNationalityDropdown = function(options) {
    const {
      containerId,
      hiddenInputId,
      defaultNationality = 'Filipino'
    } = options;

    const container = document.getElementById(containerId);
    const hiddenInput = document.getElementById(hiddenInputId);
    if (!container || !hiddenInput) return;

    let selectedNationality = defaultNationality || 'Filipino';
    let isOpen = false;

    // Find country matching default nationality
    let selectedCountry = window.ZepCountries.list.find(c =>
      c.demonym.toLowerCase() === selectedNationality.toLowerCase() ||
      c.name.toLowerCase() === selectedNationality.toLowerCase()
    ) || window.ZepCountries.list[0];

    // Render container DOM
    container.innerHTML = `
      <div class="relative w-full">
        <button type="button" id="${containerId}_btn"
          class="w-full flex items-center justify-between px-3.5 py-2 bg-white border border-slate-200 rounded-lg text-xs text-slate-800 hover:border-slate-400 focus:outline-none focus:border-slate-900 transition-all cursor-pointer">
          <span id="${containerId}_label" class="flex items-center gap-2 truncate">
            ${window.ZepCountries.getFlagImg(selectedCountry.code, selectedCountry.flag)}
            <span class="font-medium text-slate-900 truncate">${selectedCountry.demonym} (${selectedCountry.name})</span>
          </span>
          <svg class="w-4 h-4 text-slate-400 shrink-0 ml-1.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div id="${containerId}_menu"
          class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden text-xs">
          <div class="p-2 border-b border-slate-100 bg-slate-50 sticky top-0">
            <div class="relative">
              <input type="text" id="${containerId}_search" placeholder="Type country or nationality..."
                class="w-full pl-8 pr-3 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-800 outline-none focus:border-slate-900 bg-white">
              <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </div>
          </div>
          <div id="${containerId}_list" class="custom-country-menu max-h-56 overflow-y-auto py-1"></div>
        </div>
      </div>
    `;

    const btn = document.getElementById(`${containerId}_btn`);
    const menu = document.getElementById(`${containerId}_menu`);
    const searchInput = document.getElementById(`${containerId}_search`);
    const listElem = document.getElementById(`${containerId}_list`);
    const labelElem = document.getElementById(`${containerId}_label`);

    function updateSelection(country) {
      selectedCountry = country;
      selectedNationality = country.demonym;
      hiddenInput.value = country.demonym;

      labelElem.innerHTML = `
        ${window.ZepCountries.getFlagImg(country.code, country.flag)}
        <span class="font-medium text-slate-900 truncate">${country.demonym} (${country.name})</span>
      `;
      closeMenu();
    }

    function renderList(query = '') {
      const q = query.trim().toLowerCase();
      const filtered = window.ZepCountries.list.filter(c =>
        c.demonym.toLowerCase().includes(q) ||
        c.name.toLowerCase().includes(q) ||
        c.code.toLowerCase().includes(q)
      );

      if (filtered.length === 0) {
        listElem.innerHTML = `<div class="px-4 py-3 text-slate-400 text-center">No matching country or nationality</div>`;
        return;
      }

      listElem.innerHTML = filtered.map((c, index) => {
        const isCurrent = c.code === selectedCountry.code;
        const isTopMatch = (q.length > 0 && index === 0);
        return `
          <div data-code="${c.code}"
            class="nat-item px-3.5 py-2 cursor-pointer flex items-center justify-between hover:bg-slate-100 transition-colors ${isCurrent ? 'bg-slate-50 font-bold' : ''} ${isTopMatch ? 'nationality-active-item' : ''}">
            <span class="flex items-center gap-2 truncate">
              ${window.ZepCountries.getFlagImg(c.code, c.flag)}
              <span class="text-slate-900 truncate"><strong>${c.demonym}</strong> <span class="text-slate-500 font-normal">(${c.name})</span></span>
            </span>
            <span class="text-[10px] uppercase font-mono tracking-wider text-slate-400 shrink-0 ml-2">${c.code}</span>
          </div>
        `;
      }).join('');

      listElem.querySelectorAll('.nat-item').forEach(item => {
        item.addEventListener('click', () => {
          const code = item.getAttribute('data-code');
          const found = window.ZepCountries.list.find(c => c.code === code);
          if (found) updateSelection(found);
        });
      });
    }

    function openMenu() {
      menu.classList.remove('hidden');
      isOpen = true;
      searchInput.value = '';
      renderList();
      setTimeout(() => searchInput.focus(), 50);
    }

    function closeMenu() {
      menu.classList.add('hidden');
      isOpen = false;
    }

    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (isOpen) closeMenu();
      else openMenu();
    });

    searchInput.addEventListener('input', (e) => {
      renderList(e.target.value);
    });

    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        const topItem = listElem.querySelector('.nat-item');
        if (topItem) {
          const code = topItem.getAttribute('data-code');
          const found = window.ZepCountries.list.find(c => c.code === code);
          if (found) updateSelection(found);
        }
      } else if (e.key === 'Escape') {
        closeMenu();
      }
    });

    document.addEventListener('click', (e) => {
      if (!container.contains(e.target)) {
        closeMenu();
      }
    });

    // Set initial value
    hiddenInput.value = selectedCountry.demonym;
  };
})();
</script>
