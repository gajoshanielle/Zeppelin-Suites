<?php
declare(strict_types=1);

/**
 * Zeppelin Suites - Facilities & Amenities View
 *
 * @var string $baseUrl
 * @var string $pageTitle
 * @var string $activePage
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Zeppelin Suites — Facilities & Amenities') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Geist:wght@100..900&display=swap');

    * {
      font-family: "Geist", sans-serif;
    }

    .hero-bg {
      background: linear-gradient(180deg, rgba(15, 23, 42, 0.72) 0%, rgba(9, 13, 22, 0.88) 100%),
        url('<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/zeppelin-suites-slider-pool-area.jpg');
      background-size: cover;
      background-position: center;
    }

    .btn-primary {
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-primary:active {
      transform: scale(0.96);
    }

    .filter-btn.active {
      background-color: #0f172a;
      color: #ffffff;
      border-color: #0f172a;
    }
  </style>
</head>

<body class="bg-white text-zinc-900 overflow-x-hidden antialiased">

  <!-- ── PUBLIC NAVBAR ────────────────────────────────────────── -->
  <?php include __DIR__ . '/../components/public_navbar.php'; ?>



  <!-- ── KEY STATS HIGHLIGHT BAR ──────────────────────────────── -->
  <section class="bg-zinc-50 border-b border-zinc-200/80 py-8 px-6 md:px-16 lg:px-24">
    <div class="max-w-6xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
      <div class="p-3">
        <p class="text-2xl md:text-3xl font-extrabold text-zinc-900">24 / 7</p>
        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mt-1">Concierge &amp; Security</p>
      </div>
      <div class="p-3">
        <p class="text-2xl md:text-3xl font-extrabold text-zinc-900">7 Storeys</p>
        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mt-1">Prime Tower</p>
      </div>
      <div class="p-3">
        <p class="text-2xl md:text-3xl font-extrabold text-zinc-900">Helipad</p>
        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mt-1">Rooftop Landing Pad</p>
      </div>
      <div class="p-3">
        <p class="text-2xl md:text-3xl font-extrabold text-zinc-900">Resort Pool</p>
        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mt-1">&amp; Landscaped Gardens</p>
      </div>
    </div>
  </section>

  <!-- ── AMENITIES OVERVIEW INTRO ──────────────────────────────── -->
  <section class="py-16 md:py-24 px-6 md:px-16 lg:px-24 xl:px-32">
    <div class="max-w-5xl mx-auto text-center">
      <h1 class="text-4xl md:text-5xl font-black uppercase tracking-wide text-zinc-900 mb-6">Facilities & Amenities</h1>
      <p class="text-zinc-600 leading-relaxed text-sm md:text-base max-w-3xl mx-auto">
        At Zeppelin Suites, our amenities are an extension of your private home. Whether you are swimming under the
        tropical sky, taking high-speed elevators directly to your floor, or having our concierge arrange private
        chauffeur and airport transfers, living here means seamless ease at every turn.
      </p>
    </div>
  </section>

  <!-- ── AMENITIES CATALOG GRID ────────────────────────────────── -->
  <section id="amenities-catalog" class="pb-24 px-6 md:px-16 lg:px-24 xl:px-32">
    <div class="max-w-7xl mx-auto">

      <!-- Category Filter Tabs -->
      <div class="flex flex-wrap items-center justify-center gap-2 mb-12">
        <button type="button" onclick="filterAmenities('all')"
          class="filter-btn active px-5 py-2.5 rounded-full text-xs font-semibold border border-zinc-200 transition-all">
          All Amenities
        </button>
        <button type="button" onclick="filterAmenities('building')"
          class="filter-btn px-5 py-2.5 rounded-full text-xs font-semibold text-zinc-600 border border-zinc-200 hover:bg-zinc-100 transition-all">
          Building &amp; Environment
        </button>
        <button type="button" onclick="filterAmenities('suite')"
          class="filter-btn px-5 py-2.5 rounded-full text-xs font-semibold text-zinc-600 border border-zinc-200 hover:bg-zinc-100 transition-all">
          In-Suite Luxury
        </button>
        <button type="button" onclick="filterAmenities('services')"
          class="filter-btn px-5 py-2.5 rounded-full text-xs font-semibold text-zinc-600 border border-zinc-200 hover:bg-zinc-100 transition-all">
          Exclusive Services
        </button>
      </div>

      <!-- Grid Cards -->
      <div id="amenitiesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch">

        <!-- 1. Environment -->
        <div
          class="amenity-card group bg-white rounded-3xl overflow-hidden border border-zinc-200/80 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col h-full"
          data-category="building">
          <div class="relative aspect-[16/10] overflow-hidden bg-zinc-100 cursor-pointer shrink-0"
            onclick="openPhotoModal('<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/environment.jpg', 'Environment')">
            <img src="<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/environment.jpg" alt="Environment"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
              onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-zinc-200 text-zinc-400 font-bold text-xs\'>ENVIRONMENT</div>'">
            <div
              class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-90 group-hover:opacity-100 transition-opacity">
            </div>
            <div
              class="absolute top-4 right-4 bg-black/40 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-semibold text-white">
              Building
            </div>
            <div class="absolute bottom-4 left-6 right-6 text-white">
              <h3 class="text-2xl font-bold tracking-tight leading-tight">Environment</h3>
            </div>
          </div>
          <div class="p-6 sm:p-7 flex flex-col flex-1 bg-white">
            <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed h-11 flex items-start overflow-hidden mb-5">
              Tranquil landscaped gardens and open-air resort spaces providing a refreshing haven in the city center.
            </p>
            <div class="pt-5 border-t border-zinc-100">
              <ul class="space-y-3.5 flex flex-col">
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>24-hour concierge lobby</strong> with staffed front desk</span>
                </li>
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Landscaped gardens</strong> with shaded walkways</span>
                </li>
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Swimming pool</strong> with sunbathing lounge area</span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- 2. Apartment Features -->
        <div
          class="amenity-card group bg-white rounded-3xl overflow-hidden border border-zinc-200/80 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col h-full"
          data-category="suite">
          <div class="relative aspect-[16/10] overflow-hidden bg-zinc-100 cursor-pointer shrink-0"
            onclick="openPhotoModal('<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/apartment-features.jpg', 'Apartment Features')">
            <img src="<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/apartment-features.jpg"
              alt="Apartment Features"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
              onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-zinc-200 text-zinc-400 font-bold text-xs\'>APARTMENT FEATURES</div>'">
            <div
              class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-90 group-hover:opacity-100 transition-opacity">
            </div>
            <div
              class="absolute top-4 right-4 bg-black/40 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-semibold text-white">
              In-Suite
            </div>
            <div class="absolute bottom-4 left-6 right-6 text-white">
              <h3 class="text-2xl font-bold tracking-tight leading-tight">Apartment Features</h3>
            </div>
          </div>
          <div class="p-6 sm:p-7 flex flex-col flex-1 bg-white">
            <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed h-11 flex items-start overflow-hidden mb-5">
              Thoughtful craftsmanship, acoustic engineering, and tailored interior elements for personal retreat.
            </p>
            <div class="pt-5 border-t border-zinc-100">
              <ul class="space-y-3.5 flex flex-col">
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Private balconies</strong> offering scenic city views</span>
                </li>
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Central air conditioning</strong> for climate perfection</span>
                </li>
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Premium finishes</strong> with contemporary palettes</span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- 3. Convenience -->
        <div
          class="amenity-card group bg-white rounded-3xl overflow-hidden border border-zinc-200/80 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col h-full"
          data-category="building">
          <div class="relative aspect-[16/10] overflow-hidden bg-zinc-100 cursor-pointer shrink-0"
            onclick="openPhotoModal('<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/elevators.png', 'Convenience & Access')">
            <img src="<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/elevators.png" alt="Convenience"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
              onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-zinc-200 text-zinc-400 font-bold text-xs\'>ELEVATORS</div>'">
            <div
              class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-90 group-hover:opacity-100 transition-opacity">
            </div>
            <div
              class="absolute top-4 right-4 bg-black/40 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-semibold text-white">
              Building
            </div>
            <div class="absolute bottom-4 left-6 right-6 text-white">
              <h3 class="text-2xl font-bold tracking-tight leading-tight">Convenience</h3>
            </div>
          </div>
          <div class="p-6 sm:p-7 flex flex-col flex-1 bg-white">
            <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed h-11 flex items-start overflow-hidden mb-5">
              Seamless daily movement and multi-layered security infrastructure ensuring privacy and peace of mind.
            </p>
            <div class="pt-5 border-t border-zinc-100">
              <ul class="space-y-3.5 flex flex-col">
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>High-speed elevators</strong> with quiet, rapid transit</span>
                </li>
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Smart security access</strong> with RFID keycard &amp; CCTV</span>
                </li>
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Underground parking</strong> with allocated resident bays</span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- 4. Living & Dining -->
        <div
          class="amenity-card group bg-white rounded-3xl overflow-hidden border border-zinc-200/80 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col h-full"
          data-category="suite">
          <div class="relative aspect-[16/10] overflow-hidden bg-zinc-100 cursor-pointer shrink-0"
            onclick="openPhotoModal('<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/living-and-dining.jpg', 'Living & Dining')">
            <img src="<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/living-and-dining.jpg"
              alt="Living and Dining"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
              onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-zinc-200 text-zinc-400 font-bold text-xs\'>LIVING & DINING</div>'">
            <div
              class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-90 group-hover:opacity-100 transition-opacity">
            </div>
            <div
              class="absolute top-4 right-4 bg-black/40 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-semibold text-white">
              In-Suite
            </div>
            <div class="absolute bottom-4 left-6 right-6 text-white">
              <h3 class="text-2xl font-bold tracking-tight leading-tight">Living &amp; Dining</h3>
            </div>
          </div>
          <div class="p-6 sm:p-7 flex flex-col flex-1 bg-white">
            <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed h-11 flex items-start overflow-hidden mb-5">
              Expansive living environments built for effortless entertaining and everyday relaxing with daylight.
            </p>
            <div class="pt-5 border-t border-zinc-100">
              <ul class="space-y-3.5 flex flex-col">
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Spacious open layouts</strong> optimizing space and airflow</span>
                </li>
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Ceramic tiled flooring</strong> for cooling luxury and care</span>
                </li>
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Large glass windows</strong> framing scenic city views</span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- 5. Kitchen -->
        <div
          class="amenity-card group bg-white rounded-3xl overflow-hidden border border-zinc-200/80 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col h-full"
          data-category="suite">
          <div class="relative aspect-[16/10] overflow-hidden bg-zinc-100 cursor-pointer shrink-0"
            onclick="openPhotoModal('<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/kitchen.jpg', 'Kitchen Amenities')">
            <img src="<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/kitchen.jpg" alt="Kitchen"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
              onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-zinc-200 text-zinc-400 font-bold text-xs\'>KITCHEN</div>'">
            <div
              class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-90 group-hover:opacity-100 transition-opacity">
            </div>
            <div
              class="absolute top-4 right-4 bg-black/40 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-semibold text-white">
              In-Suite
            </div>
            <div class="absolute bottom-4 left-6 right-6 text-white">
              <h3 class="text-2xl font-bold tracking-tight leading-tight">Kitchen</h3>
            </div>
          </div>
          <div class="p-6 sm:p-7 flex flex-col flex-1 bg-white">
            <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed h-11 flex items-start overflow-hidden mb-5">
              Equipped for culinary creativity with durable designer surfaces and ergonomic storage solutions.
            </p>
            <div class="pt-5 border-t border-zinc-100">
              <ul class="space-y-3.5 flex flex-col">
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Stone countertops</strong> resistant to heat and wear</span>
                </li>
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Modern cabinetry</strong> with soft-close hardware</span>
                </li>
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Stainless steel appliances</strong> built for performance</span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- 6. Bathroom -->
        <div
          class="amenity-card group bg-white rounded-3xl overflow-hidden border border-zinc-200/80 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col h-full"
          data-category="suite">
          <div class="relative aspect-[16/10] overflow-hidden bg-zinc-100 cursor-pointer shrink-0"
            onclick="openPhotoModal('<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/bathroom.jpg', 'Bathroom')">
            <img src="<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/bathroom.jpg" alt="Bathroom"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
              onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-zinc-200 text-zinc-400 font-bold text-xs\'>BATHROOM</div>'">
            <div
              class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-90 group-hover:opacity-100 transition-opacity">
            </div>
            <div
              class="absolute top-4 right-4 bg-black/40 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-semibold text-white">
              In-Suite
            </div>
            <div class="absolute bottom-4 left-6 right-6 text-white">
              <h3 class="text-2xl font-bold tracking-tight leading-tight">Bathroom</h3>
            </div>
          </div>
          <div class="p-6 sm:p-7 flex flex-col flex-1 bg-white">
            <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed h-11 flex items-start overflow-hidden mb-5">
              Spa-inspired sanctuaries fitted with European hardware, gentle warm lighting, and rain fixtures.
            </p>
            <div class="pt-5 border-t border-zinc-100">
              <ul class="space-y-3.5 flex flex-col">
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Luxury sanitary fittings</strong> and polished fixtures</span>
                </li>
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Ceramic tiles</strong> with anti-slip textures</span>
                </li>
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Rain shower system</strong> with instant temperature control</span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- 7. Exclusive Services -->
        <div
          class="amenity-card group bg-white rounded-3xl overflow-hidden border border-zinc-200/80 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col h-full"
          data-category="services">
          <div class="relative aspect-[16/10] overflow-hidden bg-zinc-100 cursor-pointer shrink-0"
            onclick="openPhotoModal('<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/exclusive-services.jpg', 'Exclusive Services')">
            <img src="<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/exclusive-services.jpg" alt="Exclusive Services"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
              onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-zinc-200 text-zinc-400 font-bold text-xs\'>EXCLUSIVE SERVICES</div>'">
            <div
              class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-90 group-hover:opacity-100 transition-opacity">
            </div>
            <div
              class="absolute top-4 right-4 bg-black/40 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-semibold text-white">
              Exclusive
            </div>
            <div class="absolute bottom-4 left-6 right-6 text-white">
              <h3 class="text-2xl font-bold tracking-tight leading-tight">Exclusive Services</h3>
            </div>
          </div>
          <div class="p-6 sm:p-7 flex flex-col flex-1 bg-white">
            <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed h-11 flex items-start overflow-hidden mb-5">
              First-class hospitality privileges with dedicated aviation access and on-demand resident assistance.
            </p>
            <div class="pt-5 border-t border-zinc-100">
              <ul class="space-y-3.5 flex flex-col">
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Helicopter landing pad</strong> for rooftop aerial transit</span>
                </li>
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Airport transfer service</strong> with Clark airport shuttles</span>
                </li>
                <li class="flex items-start gap-3 text-zinc-700 text-xs sm:text-sm h-12">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                  <span class="leading-snug"><strong>Concierge &amp; maid service</strong> with on-demand assistance</span>
                </li>
              </ul>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ── PHOTO LIGHTBOX MODAL ──────────────────────────────────── -->
  <div id="photoModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4"
    onclick="closePhotoModal()">
    <div class="relative max-w-4xl w-full bg-zinc-900 rounded-3xl overflow-hidden shadow-2xl"
      onclick="event.stopPropagation()">
      <button type="button" onclick="closePhotoModal()"
        class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-black/60 hover:bg-black/90 text-white flex items-center justify-center text-xl transition-all">
        ✕
      </button>
      <div class="relative aspect-video bg-black flex items-center justify-center">
        <img id="modalImg" src="" alt="Amenity Preview" class="max-h-[80vh] w-auto max-w-full object-contain">
      </div>
      <div class="p-6 bg-zinc-950 text-white flex items-center justify-between">
        <h4 id="modalCaption" class="text-lg font-bold"></h4>
        <span class="text-xs text-zinc-400">Zeppelin Suites Residence</span>
      </div>
    </div>
  </div>

  <!-- ── FOOTER ────────────────────────────────────────────────── -->
  <?php include __DIR__ . '/../components/public_footer.php'; ?>

  <!-- ── SCRIPTS ───────────────────────────────────────────────── -->
  <script>
    function filterAmenities(category) {
      // Toggle button active classes
      document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-slate-900', 'text-white', 'border-slate-900');
        btn.classList.add('text-zinc-600', 'border-zinc-200');
      });
      event.currentTarget.classList.add('active');

      // Filter cards
      const cards = document.querySelectorAll('.amenity-card');
      cards.forEach(card => {
        const cardCat = card.getAttribute('data-category');
        if (category === 'all' || cardCat === category) {
          card.style.display = 'flex';
          card.classList.remove('hidden');
        } else {
          card.style.display = 'none';
          card.classList.add('hidden');
        }
      });
    }

    function openPhotoModal(imgUrl, caption) {
      const modal = document.getElementById('photoModal');
      const img = document.getElementById('modalImg');
      const cap = document.getElementById('modalCaption');
      if (!modal || !img) return;

      img.src = imgUrl;
      cap.textContent = caption || 'Zeppelin Suites Amenity';
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closePhotoModal() {
      const modal = document.getElementById('photoModal');
      if (!modal) return;
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closePhotoModal();
    });
  </script>
</body>

</html>