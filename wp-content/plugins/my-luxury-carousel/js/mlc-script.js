/* mlc-script.js — My Luxury Carousel
 * Mantiene carrusel (desktop+mobile) y reescribe SOLO:
 * - anti-# en capture (sin matar handlers)
 * - modales custom + trigger + debug robusto
 * - FIXES de carrusel: infinito real + click-to-slide + threshold responsive + resize
 * - FIX swipe móvil: touch nativo con passive:false (jQuery touch suele romper en iOS/Chrome)
 */

jQuery(document).ready(function ($) {
  var I18N = window.MLC_I18N || {};

  function t(key, fallback) {
    return Object.prototype.hasOwnProperty.call(I18N, key) ? I18N[key] : fallback;
  }

  // =========================================================
  // MLC DEBUG
  // =========================================================
  var DBG = (window.MLC_SETTINGS && window.MLC_SETTINGS.debug !== undefined)
    ? !!window.MLC_SETTINGS.debug
    : true;

  function mlcLog() { if (DBG && window.console) console.log.apply(console, arguments); }
  function mlcWarn() { if (DBG && window.console) console.warn.apply(console, arguments); }
  function mlcErr() { if (DBG && window.console) console.error.apply(console, arguments); }

  mlcLog('[MLC] JS loaded ✅');
  mlcLog('[MLC] JS ready ✅', { MLC_CONFIG: window.MLC_CONFIG || null, MLC_SETTINGS: window.MLC_SETTINGS || null });

  window.addEventListener('error', function (ev) {
    mlcErr('[MLC] window.error ❌', ev.message, ev.filename + ':' + ev.lineno + ':' + ev.colno, ev.error);
  });
  window.addEventListener('unhandledrejection', function (ev) {
    mlcErr('[MLC] unhandledrejection ❌', ev.reason);
  });

  // =========================================================
  // CAPTURE anti-/# (NO mata handlers)
  // =========================================================
  document.addEventListener('click', function (e) {
    var trigger = e.target && e.target.closest
      ? e.target.closest('.mlc-rates-trigger')
      : null;

    if (!trigger) return;

    var href = trigger.getAttribute('href');
    var isHashLike = (href === '#' || href === '' || href === null);

    if (isHashLike) {
      if (DBG) mlcLog('[MLC] capture: preventDefault (anti-#) 🎯', {
        tag: trigger.tagName,
        href: href,
        dest: trigger.getAttribute('data-mlc-destination') || null
      });
      e.preventDefault();
    }
  }, true);

  // =========================================================
  // 1) CARRUSEL DESKTOP — FIXED (infinito real + click-to-slide + threshold + resize)
  // =========================================================
  var $track = $('.mlc-cards-track');

  if ($track.length) {
    var $cardsOriginal = $track.find('.mlc-card');
    var count = $cardsOriginal.length;
    var clonesCount = 2;

    // Clonar bordes
    var $lastClones = $cardsOriginal.slice(-clonesCount).clone(true).addClass('clone');
    var $firstClones = $cardsOriginal.slice(0, clonesCount).clone(true).addClass('clone');
    $track.prepend($lastClones).append($firstClones);

    var $allCards = $track.find('.mlc-card');
    var currentIndex = clonesCount;

    var $currentNum = $('.mlc-current-slide');
    var $totalNum = $('.mlc-total-slides');
    $totalNum.text(count);

    function logicalIndexFromCurrent() {
      var i = (currentIndex - clonesCount) % count;
      if (i < 0) i += count;
      return i + 1;
    }

    function cardMetrics() {
      var cardW = $allCards.eq(0).outerWidth(true) || 1;
      var wrapperW = $('.mlc-luxury-carousel-wrapper').width() || 1;
      var centerOffset = (wrapperW - cardW) / 2;
      return { cardW: cardW, wrapperW: wrapperW, centerOffset: centerOffset };
    }

    function setBgForIndex(idx) {
      var $c = $allCards.eq(idx);
      var bgUrl = $c.data('bg');
      if (bgUrl) $('.mlc-luxury-carousel-wrapper').css('--mlc-bg-image', 'url(' + bgUrl + ')');
    }

    function updateCarousel(instant) {
      var m = cardMetrics();
      var translateX = m.centerOffset - currentIndex * m.cardW;

      if (instant) $track.css('transition', 'none');

      $track.css('transform', 'translateX(' + translateX + 'px)');

      $allCards.removeClass('active');
      $allCards.eq(currentIndex).addClass('active');

      $currentNum.text(logicalIndexFromCurrent());
      setBgForIndex(currentIndex);

      if (instant) {
        setTimeout(function () {
          $track.css('transition', 'transform 0.5s ease-in-out');
        }, 20);
      }
    }

    // init
    $track.css('transition', 'none');
    updateCarousel(true);

    // snap infinito cuando caemos en clones
    $track.on('transitionend', function () {
      var $cur = $allCards.eq(currentIndex);
      if (!$cur.length) return;

      if ($cur.hasClass('clone')) {
        $track.css('transition', 'none');

        if (currentIndex < clonesCount) currentIndex += count;
        else if (currentIndex >= clonesCount + count) currentIndex -= count;

        updateCarousel(true);
      }
    });

    // arrows
    $('.mlc-prev-arrow').on('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      currentIndex--;
      $track.css('transition', 'transform 0.5s ease-in-out');
      updateCarousel(false);
    });

    $('.mlc-next-arrow').on('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      currentIndex++;
      $track.css('transition', 'transform 0.5s ease-in-out');
      updateCarousel(false);
    });

    // CLICK-TO-SLIDE
    $track.on('click', '.mlc-card', function (e) {
      var t = e.target;
      if (t && t.closest && (t.closest('.mlc-rates-trigger') || t.closest('[data-mlc-destination]') || t.closest('a') || t.closest('button'))) return;

      var idx = $allCards.index(this);
      if (idx < 0) return;

      if (idx !== currentIndex) {
        currentIndex = idx;
        $track.css('transition', 'transform 0.5s ease-in-out');
        updateCarousel(false);
      }
    });

    // ==========================
    // DRAG/SWIPE (DESKTOP)
    // - Mouse por jQuery
    // - Touch NATIVO (passive:false) => FIX real
    // ==========================
    var isDragging = false, startX = 0, startY = 0, isHorizontal = false;

    function dragStartPoint(px, py) {
      isDragging = true;
      isHorizontal = false;
      startX = px;
      startY = py;
      $track.css('transition', 'none');
      $track.addClass('is-grabbing');
    }

    function dragMovePoint(px, py, preventScrollFn) {
      if (!isDragging) return;

      var dx = px - startX;
      var dy = py - startY;

      if (!isHorizontal) {
        if (Math.abs(dx) > 6) isHorizontal = true;
        else if (Math.abs(dy) > 10) {
          // scroll vertical => cancelamos drag
          isDragging = false;
          $track.removeClass('is-grabbing');
          return;
        }
      }

      if (isHorizontal) {
        if (typeof preventScrollFn === 'function') preventScrollFn();

        var m = cardMetrics();
        var translateX = m.centerOffset - currentIndex * m.cardW + dx;
        $track.css('transform', 'translateX(' + translateX + 'px)');
      }
    }

    function dragEndPoint(px) {
      if (!isDragging) return;

      var dx = px - startX;

      isDragging = false;
      $track.removeClass('is-grabbing');

      var m = cardMetrics();
      var threshold = Math.max(40, m.cardW * 0.18);

      if (isHorizontal) {
        if (dx > threshold) currentIndex--;
        else if (dx < -threshold) currentIndex++;

        $track.css('transition', 'transform 0.5s ease-in-out');
        updateCarousel(false);
      } else {
        $track.css('transition', 'transform 0.35s ease-in-out');
        updateCarousel(false);
      }
    }

    // Mouse
    $track.on('mousedown', function (e) {
      var t = e.target;
      if (t && t.closest && (t.closest('.mlc-rates-trigger') || t.closest('[data-mlc-destination]') || t.closest('a') || t.closest('button'))) return;
      dragStartPoint(e.pageX, e.pageY);
    });

    $track.on('mousemove', function (e) {
      dragMovePoint(e.pageX, e.pageY, null);
    });

    $(document).on('mouseup', function (e) {
      dragEndPoint(e.pageX);
    });

    // Touch NATIVO (clave)
    var trackEl = $track.get(0);
    if (trackEl) {
      trackEl.addEventListener('touchstart', function (e) {
        var t = e.target;
        if (t && t.closest && (t.closest('.mlc-rates-trigger') || t.closest('[data-mlc-destination]') || t.closest('a') || t.closest('button'))) return;

        var touch = e.touches && e.touches[0];
        if (!touch) return;
        dragStartPoint(touch.pageX, touch.pageY);
      }, { passive: true });

      trackEl.addEventListener('touchmove', function (e) {
        var touch = e.touches && e.touches[0];
        if (!touch) return;

        dragMovePoint(touch.pageX, touch.pageY, function () {
          // evita scroll vertical solo cuando ya es swipe horizontal
          e.preventDefault();
        });
      }, { passive: false });

      window.addEventListener('touchend', function (e) {
        var touch = e.changedTouches && e.changedTouches[0];
        if (!touch) return;
        dragEndPoint(touch.pageX);
      }, { passive: true });

      window.addEventListener('touchcancel', function () {
        isDragging = false;
        $track.removeClass('is-grabbing');
      }, { passive: true });
    }

    // RESIZE/ORIENTATION
    var resizeTimer = null;
    $(window).on('resize orientationchange', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        mlcLog('[MLC] resize -> recalc carousel');
        updateCarousel(true);
      }, 80);
    });
  }

  // =========================================================
  // 2) CARRUSEL MOBILE — FIXED (infinito real + threshold + resize)
  // =========================================================
  var $mobileTrack = $('.mlc-mobile-track');

  if ($mobileTrack.length) {
    var $mobileCardsOriginal = $mobileTrack.find('.mlc-mobile-card');
    var mobileCount = $mobileCardsOriginal.length;
    var mobileClonesCount = 2;

    var $lastMobileClones = $mobileCardsOriginal.slice(-mobileClonesCount).clone(true).addClass('clone');
    var $firstMobileClones = $mobileCardsOriginal.slice(0, mobileClonesCount).clone(true).addClass('clone');
    $mobileTrack.prepend($lastMobileClones).append($firstMobileClones);

    var $allMobileCards = $mobileTrack.find('.mlc-mobile-card');
    var mobileIndex = mobileClonesCount;

    var $mobileCurrentNum = $('.mlc-mobile-current-slide');
    var $mobileTotalNum = $('.mlc-mobile-total-slides');
    $mobileTotalNum.text(mobileCount);

    function mobileLogicalIndex() {
      var i = (mobileIndex - mobileClonesCount) % mobileCount;
      if (i < 0) i += mobileCount;
      return i + 1;
    }

    function mobileMetrics() {
      var cardW = $allMobileCards.eq(0).outerWidth(true) || 1;
      var wrapperW = $('.mlc-mobile-carousel-wrapper').width() || 1;
      var centerOff = (wrapperW - cardW) / 2;
      return { cardW: cardW, wrapperW: wrapperW, centerOff: centerOff };
    }

    function updateMobileCarousel(instant) {
      var m = mobileMetrics();
      var translateX = m.centerOff - mobileIndex * m.cardW;

      if (instant) $mobileTrack.css('transition', 'none');
      $mobileTrack.css('transform', 'translateX(' + translateX + 'px)');

      $allMobileCards.removeClass('active').eq(mobileIndex).addClass('active');
      $mobileCurrentNum.text(mobileLogicalIndex());

      if (instant) {
        setTimeout(function () {
          $mobileTrack.css('transition', 'transform 0.5s ease-in-out');
        }, 20);
      }
    }

    $mobileTrack.css('transition', 'none');
    updateMobileCarousel(true);

    $mobileTrack.on('transitionend', function () {
      var $cur = $allMobileCards.eq(mobileIndex);
      if (!$cur.length) return;

      if ($cur.hasClass('clone')) {
        $mobileTrack.css('transition', 'none');

        if (mobileIndex < mobileClonesCount) mobileIndex += mobileCount;
        else if (mobileIndex >= mobileClonesCount + mobileCount) mobileIndex -= mobileCount;

        updateMobileCarousel(true);
      }
    });

    $('.mlc-mobile-prev-arrow').on('click', function (e) {
      e.preventDefault();
      mobileIndex--;
      $mobileTrack.css('transition', 'transform 0.5s ease-in-out');
      updateMobileCarousel(false);
    });

    $('.mlc-mobile-next-arrow').on('click', function (e) {
      e.preventDefault();
      mobileIndex++;
      $mobileTrack.css('transition', 'transform 0.5s ease-in-out');
      updateMobileCarousel(false);
    });

    // ==========================
    // DRAG/SWIPE (MOBILE)
    // - Mouse opcional (por si hay emulación)
    // - Touch NATIVO (passive:false) => FIX real
    // ==========================
    var mDragging = false, mStartX = 0, mStartY = 0, mIsHorizontal = false;

    function mStart(px, py) {
      mDragging = true;
      mIsHorizontal = false;
      mStartX = px;
      mStartY = py;
      $mobileTrack.css('transition', 'none');
    }

    function mMove(px, py, preventScrollFn) {
      if (!mDragging) return;

      var dx = px - mStartX;
      var dy = py - mStartY;

      if (!mIsHorizontal) {
        if (Math.abs(dx) > 6) mIsHorizontal = true;
        else if (Math.abs(dy) > 10) {
          mDragging = false;
          return;
        }
      }

      if (mIsHorizontal) {
        if (typeof preventScrollFn === 'function') preventScrollFn();

        var mm = mobileMetrics();
        var translateX = mm.centerOff - mobileIndex * mm.cardW + dx;
        $mobileTrack.css('transform', 'translateX(' + translateX + 'px)');
      }
    }

    function mEnd(px) {
      if (!mDragging) return;

      var dx = px - mStartX;
      mDragging = false;

      var mm = mobileMetrics();
      var threshold = Math.max(35, mm.cardW * 0.18);

      if (mIsHorizontal) {
        if (dx > threshold) mobileIndex--;
        else if (dx < -threshold) mobileIndex++;

        $mobileTrack.css('transition', 'transform 0.5s ease-in-out');
        updateMobileCarousel(false);
      } else {
        $mobileTrack.css('transition', 'transform 0.35s ease-in-out');
        updateMobileCarousel(false);
      }
    }

    // Mouse (no rompe)
    $mobileTrack.on('mousedown', function (e) {
      var t = e.target;
      if (t && t.closest && (t.closest('.mlc-rates-trigger') || t.closest('[data-mlc-destination]') || t.closest('a') || t.closest('button'))) return;
      mStart(e.pageX, e.pageY);
    });

    $mobileTrack.on('mousemove', function (e) {
      mMove(e.pageX, e.pageY, null);
    });

    $(document).on('mouseup', function (e) {
      mEnd(e.pageX);
    });

    // Touch NATIVO (clave)
    var mobEl = $mobileTrack.get(0);
    if (mobEl) {
      mobEl.addEventListener('touchstart', function (e) {
        var t = e.target;
        if (t && t.closest && (t.closest('.mlc-rates-trigger') || t.closest('[data-mlc-destination]') || t.closest('a') || t.closest('button'))) return;

        var touch = e.touches && e.touches[0];
        if (!touch) return;
        mStart(touch.pageX, touch.pageY);
      }, { passive: true });

      mobEl.addEventListener('touchmove', function (e) {
        var touch = e.touches && e.touches[0];
        if (!touch) return;

        mMove(touch.pageX, touch.pageY, function () {
          e.preventDefault();
        });
      }, { passive: false });

      window.addEventListener('touchend', function (e) {
        var touch = e.changedTouches && e.changedTouches[0];
        if (!touch) return;
        mEnd(touch.pageX);
      }, { passive: true });

      window.addEventListener('touchcancel', function () {
        mDragging = false;
      }, { passive: true });
    }

    // resize/orientation
    var mResizeTimer = null;
    $(window).on('resize orientationchange', function () {
      clearTimeout(mResizeTimer);
      mResizeTimer = setTimeout(function () {
        mlcLog('[MLC] resize -> recalc mobile carousel');
        updateMobileCarousel(true);
      }, 80);
    });
  }

  // =========================================================
  // 3) MODALES CUSTOM (overlay + destination + property)
  // =========================================================
  var CFG = window.MLC_CONFIG || {};
  var apiUrl = CFG.apiUrl || CFG.dataUrl || '';
  var fallbackDataUrl = CFG.fallbackDataUrl || '/data/rental-rates-data.json';

  var ratesData = null;
  var dataPromise = null;

  var $overlay = $('#mlc-modal-overlay');
  var $destModal = $('#mlc-destination-modal');
  var $ratesModal = $('#mlc-rates-modal');

  function domOk() {
    $overlay = $('#mlc-modal-overlay');
    $destModal = $('#mlc-destination-modal');
    $ratesModal = $('#mlc-rates-modal');

    var ok = ($overlay.length && $destModal.length && $ratesModal.length);

    if (DBG) mlcLog('[MLC] modal dom ok', {
      overlay: $overlay.length,
      destinationModal: $destModal.length,
      ratesModal: $ratesModal.length,
      ok: ok
    });

    if (!ok) {
      mlcErr('[MLC] modal dom missing ❌ (wp_footer)', {
        overlay: !!$overlay.length,
        destinationModal: !!$destModal.length,
        ratesModal: !!$ratesModal.length
      });
      return false;
    }
    return true;
  }

  function validateJsonShape(json) {
    if (!json) return { ok: false, reason: 'json=null' };

    var destinations = null;
    if (Array.isArray(json)) destinations = json;
    else if (json.destinations && Array.isArray(json.destinations)) destinations = json.destinations;

    if (!destinations) return { ok: false, reason: 'missing destinations[]' };

    return { ok: true, destinations: destinations };
  }

  function fetchRatesDataFrom(url, sourceName) {
    if (!url) {
      return $.Deferred().reject(sourceName + '_missing_url').promise();
    }

    if (DBG) mlcLog('[MLC] loading ' + sourceName + '...', url);

    return $.getJSON(url).then(function (json) {
      var shape = validateJsonShape(json);
      if (!shape.ok) {
        mlcErr('[MLC] ' + sourceName + ' structure invalid', {
          reason: shape.reason,
          json: json,
          url: url
        });
        return $.Deferred().reject(sourceName + '_invalid_shape').promise();
      }

      if (DBG) mlcLog('[MLC] ' + sourceName + ' loaded ok', {
        url: url,
        destinations: shape.destinations.length
      });
      console.log('[MLC] rental rates source in use:', sourceName, url);
      return json;
    }, function (xhr, status, err) {
      mlcErr('[MLC] ' + sourceName + ' load failed', {
        url: url,
        status: status,
        err: err,
        httpStatus: xhr && xhr.status,
        responseTextPreview: xhr && xhr.responseText ? String(xhr.responseText).slice(0, 180) : null
      });
      return $.Deferred().reject(sourceName + '_request_failed').promise();
    });
  }

  function loadRatesData() {
    if (ratesData) return $.Deferred().resolve(ratesData).promise();
    if (dataPromise) return dataPromise;

    dataPromise = fetchRatesDataFrom(apiUrl, 'api')
      .fail(function (apiReason) {
        mlcWarn('[MLC] API unavailable, using local JSON fallback', {
          apiUrl: apiUrl,
          fallbackDataUrl: fallbackDataUrl,
          reason: apiReason
        });
        return fetchRatesDataFrom(fallbackDataUrl, 'fallback_json');
      })
      .then(function (json) {
        ratesData = json;
        return ratesData;
      }, function (reason) {
        mlcErr('[MLC] unable to load rental rates from API or fallback', {
          apiUrl: apiUrl,
          fallbackDataUrl: fallbackDataUrl,
          reason: reason
        });
        return $.Deferred().reject(reason).promise();
      });

    return dataPromise;
  }

  function norm(s) {
    return (s || '')
      .toString()
      .trim()
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/\s+/g, ' ');
  }

  function formatMoney(amount, currency) {
    if (amount === null || amount === undefined || amount === '') return '—';
    var c = currency || 'USD';
    var locale = (window.MLC_SETTINGS && window.MLC_SETTINGS.lang === 'es') ? 'es-MX' : 'en-US';
    try {
      return new Intl.NumberFormat(locale, { style: 'currency', currency: c, maximumFractionDigits: 0 }).format(amount);
    } catch (e) {
      return c + ' ' + amount;
    }
  }

  function getDestinationsList() {
    if (!ratesData) return [];
    if (Array.isArray(ratesData)) return ratesData;
    if (ratesData.destinations && Array.isArray(ratesData.destinations)) return ratesData.destinations;
    return [];
  }

  function findDestinationByName(name) {
    var list = getDestinationsList();
    if (!list.length) return null;

    var n = norm(name);

    var exact = list.find(function (d) {
      return norm(d.destination_name || d.name) === n;
    });
    if (exact) return exact;

    return list.find(function (d) {
      var dn = norm(d.destination_name || d.name);
      return dn.indexOf(n) !== -1 || n.indexOf(dn) !== -1;
    }) || null;
  }

  // =========================================================
  // STACKED MODALS (Destination stays "open" behind Property)
  // =========================================================
  var mlcModalStack = { returnTo: null }; // 'destination' | null

  function closeAllModals() {
    if (!domOk()) return;
    $overlay.removeClass('is-open').attr('aria-hidden', 'true');
    $destModal.removeClass('is-open mlc-is-under').attr('aria-hidden', 'true');
    $ratesModal.removeClass('is-open mlc-is-under').attr('aria-hidden', 'true');
    $('html').removeClass('mlc-scroll-lock');
    mlcModalStack.returnTo = null;
    if (DBG) mlcLog('[MLC] modal closed ✅');
  }

  function openModal($m) {
    if (!domOk()) return;
    $overlay.addClass('is-open').attr('aria-hidden', 'false');
    $destModal.removeClass('is-open mlc-is-under').attr('aria-hidden', 'true');
    $ratesModal.removeClass('is-open mlc-is-under').attr('aria-hidden', 'true');
    $m.addClass('is-open').attr('aria-hidden', 'false');
    $('html').addClass('mlc-scroll-lock');
    mlcModalStack.returnTo = null;
    if (DBG) mlcLog('[MLC] modal opened ✅', { id: $m.attr('id') });
  }

  function openModalStacked($m, opts) {
    opts = opts || {};
    if (!domOk()) return;

    $overlay.addClass('is-open').attr('aria-hidden', 'false');
    $('html').addClass('mlc-scroll-lock');

    if ($m.is($ratesModal) && opts.keepDestinationOpen) {
      mlcModalStack.returnTo = 'destination';
      $destModal.addClass('is-open mlc-is-under').attr('aria-hidden', 'true');
    } else {
      mlcModalStack.returnTo = null;
      $destModal.removeClass('mlc-is-under');
    }

    // show only target
    $ratesModal.removeClass('is-open mlc-is-under').attr('aria-hidden', 'true');
    $destModal.removeClass('is-open').attr('aria-hidden', 'true');

    $m.addClass('is-open').attr('aria-hidden', 'false');

    if (DBG) mlcLog('[MLC] modal opened (stacked) ✅', { id: $m.attr('id'), returnTo: mlcModalStack.returnTo });
  }

  function closeTopModal() {
    if (!domOk()) return;

    if ($ratesModal.hasClass('is-open')) {
      $ratesModal.removeClass('is-open').attr('aria-hidden', 'true');

      if (mlcModalStack.returnTo === 'destination') {
        $destModal.addClass('is-open').removeClass('mlc-is-under').attr('aria-hidden', 'false');
        mlcModalStack.returnTo = null;
        if (DBG) mlcLog('[MLC] closed property, returned to destination ✅');
        return;
      }
    }
    closeAllModals();
  }

  $(document).on('click', '#mlc-modal-overlay, [data-mlc-close]', function (e) {
    e.preventDefault();
    closeTopModal();
  });

  $(document).on('keydown', function (e) {
    if (e.key === 'Escape' && ($destModal.hasClass('is-open') || $ratesModal.hasClass('is-open'))) {
      closeTopModal();
    }
  });

  function renderDestinationModal(destination) {
    if (DBG) mlcLog('[MLC] render destination', destination);

    $('#mlc-destination-title').text(destination.destination_name || destination.name || t('destination_title', 'Destination'));

    var $body = $('#mlc-destination-body');
    $body.empty();

    var resorts = destination.resorts || destination.resort_groups || [];
    if (!resorts.length) {
      $body.html('<div class="mlc-empty">' + escapeHtml(t('no_resorts', 'No resorts or properties found for this destination.')) + '</div>');
      return;
    }

    resorts.forEach(function (resort) {
      var resortName = resort.resort_name || resort.name || '';
      var $section = $('<section class="mlc-resort"></section>');
      $section.append('<h3 class="mlc-resort__title">' + escapeHtml(resortName) + '</h3>');

      var props = resort.properties || resort.items || [];
      if (!props.length) {
        $section.append('<div class="mlc-empty">' + escapeHtml(t('no_properties', 'No properties available.')) + '</div>');
      } else {
        var $grid = $('<div class="mlc-properties"></div>');
        props.forEach(function (prop) {
          var propName = prop.property_name || prop.name || t('property_title', 'Property');
          var propData = $.extend(true, {}, prop, {
            rates_notice: prop.rates_notice || resort.rates_notice || ''
          });
          var $btn = $('<button type="button" class="mlc-property-btn"></button>');
          $btn.text(propName);
          $btn.data('mlc-prop', propData);
          $grid.append($btn);
        });
        $section.append($grid);
      }

      $body.append($section);
    });
  }

  function openPropertyModal(prop) {
    if (DBG) mlcLog('[MLC] open property modal', prop);

    $('#mlc-property-title').text(prop.property_name || prop.name || t('property_title', 'Property'));

    var $ratesBody = $('#mlc-rates-body').empty();
    var rates = prop.rates || [];
    var ratesNotice = typeof prop.rates_notice === 'string' ? prop.rates_notice.trim() : '';

    if (ratesNotice) {
      $ratesBody.append('<div class="mlc-rates-notice">' + escapeHtml(ratesNotice) + '</div>');
    }

    if (!rates.length) {
      $ratesBody.append('<div class="mlc-empty">' + escapeHtml(t('no_rates', 'No rates available.')) + '</div>');
    } else {
      var $grid = $('<div class="mlc-rates-grid"></div>');
      rates.forEach(function (r) {
        var $card = $(
          '<div class="mlc-rate-card">' +
          '  <div class="mlc-rate-card__season"></div>' +
          '  <div class="mlc-rate-card__amount"></div>' +
          '</div>'
        );
        $card.find('.mlc-rate-card__season').text(r.season || t('season', 'Season'));
        $card.find('.mlc-rate-card__amount').text(formatMoney(r.amount, r.currency));
        $grid.append($card);
      });
      $ratesBody.append($grid);
    }

    var $seasBody = $('#mlc-seasonality-body').empty();
    var seasonality = prop.seasonality || [];

    if (!seasonality.length) {
      $seasBody.append('<div class="mlc-empty">' + escapeHtml(t('no_seasonality', 'No seasonality available.')) + '</div>');
    } else {
      seasonality.forEach(function (s) {
        var $block = $('<div class="mlc-season-block"></div>');
        $block.append('<div class="mlc-season-block__title">' + escapeHtml(s.season || t('season', 'Season')) + '</div>');

        var ranges = s.ranges || [];
        if (ranges.length) {
          var $list = $('<ul class="mlc-season-list"></ul>');
          ranges.forEach(function (range) {
            var raw = (range && range.raw) ? String(range.raw) : '';
            var parts = raw.split(/\s*-\s*/);
            var start = (parts[0] || '').trim();
            var end = (parts[1] || '').trim();

            if (!end && start) {
              var parts2 = start.split(/\s+to\s+/i);
              if (parts2.length >= 2) {
                start = (parts2[0] || '').trim();
                end = (parts2[1] || '').trim();
              }
            }

            $list.append(
              '<li class="mlc-season-row">' +
              '<div class="mlc-season-cell">' +
              '<div class="mlc-season-label">' + escapeHtml(t('season_start', 'Start')) + '</div>' +
              '<div class="mlc-season-value">' + escapeHtml(start || raw || '—') + '</div>' +
              '</div>' +
              '<div class="mlc-season-cell">' +
              '<div class="mlc-season-label">' + escapeHtml(t('season_end', 'End')) + '</div>' +
              '<div class="mlc-season-value">' + escapeHtml(end || '—') + '</div>' +
              '</div>' +
              '</li>'
            );
          });

          $block.append($list);
        }
        $seasBody.append($block);
      });
    }

    $('.mlc-tab').removeClass('is-active').first().addClass('is-active');
    $('.mlc-tabpanel').removeClass('is-active');
    $('.mlc-tabpanel[data-mlc-panel="rates"]').addClass('is-active');

    openModalStacked($ratesModal, { keepDestinationOpen: true });
  }

  $(document).on('click', '.mlc-tab', function (e) {
    e.preventDefault();
    var tab = $(this).data('mlc-tab');
    $('.mlc-tab').removeClass('is-active');
    $(this).addClass('is-active');
    $('.mlc-tabpanel').removeClass('is-active');
    $('.mlc-tabpanel[data-mlc-panel="' + tab + '"]').addClass('is-active');
  });

  $(document).on('click', '#mlc-destination-body .mlc-property-btn', function (e) {
    e.preventDefault();
    var prop = $(this).data('mlc-prop');
    if (!prop) {
      mlcWarn('[MLC] property click: missing prop data ❌', this);
      return;
    }
    openPropertyModal(prop);
  });

  // =========================================================
  // Drag/Swipe guard para triggers (NO abre modal si fue swipe)
  // =========================================================
  var clickGuard = { active: false, moved: false, downX: 0, downY: 0, lastWasDrag: false, resetTimer: null };

  function resetLastWasDragSoon(ms) {
    if (clickGuard.resetTimer) clearTimeout(clickGuard.resetTimer);
    clickGuard.resetTimer = setTimeout(function () { clickGuard.lastWasDrag = false; }, ms || 260);
  }

  function getPoint(evt) {
    var oe = evt && evt.originalEvent ? evt.originalEvent : evt;
    var t = oe && oe.touches && oe.touches[0] ? oe.touches[0] : null;
    var ct = oe && oe.changedTouches && oe.changedTouches[0] ? oe.changedTouches[0] : null;
    var p = t || ct || oe;
    return { x: (p && p.pageX) || 0, y: (p && p.pageY) || 0 };
  }

  $(document).on('mousedown touchstart', '.mlc-rates-trigger', function (e) {
    var pt = getPoint(e);
    clickGuard.active = true;
    clickGuard.moved = false;
    clickGuard.downX = pt.x;
    clickGuard.downY = pt.y;
  });

  $(document).on('mousemove touchmove', function (e) {
    if (!clickGuard.active) return;
    var pt = getPoint(e);
    var dx = Math.abs(pt.x - clickGuard.downX);
    var dy = Math.abs(pt.y - clickGuard.downY);
    if (dx > 10 || dy > 10) clickGuard.moved = true;
  });

  $(document).on('mouseup touchend touchcancel', function () {
    if (!clickGuard.active) return;
    clickGuard.active = false;
    if (clickGuard.moved) {
      clickGuard.lastWasDrag = true;
      resetLastWasDragSoon(280);
    }
  });

  $(document).on('mouseup touchend', function () {
    var $t = $('.mlc-cards-track');
    if ($t.length && $t.hasClass('is-grabbing')) {
      clickGuard.lastWasDrag = true;
      resetLastWasDragSoon(280);
    }
  });

  // =========================================================
  // TRIGGER FINAL — abre Destination modal
  // =========================================================
  $(document).on('click', '.mlc-rates-trigger', function (e) {
    if (DBG) mlcLog('[MLC] click detected ✅', { target: this });

    e.preventDefault();

    if (clickGuard.lastWasDrag) {
      if (DBG) mlcWarn('[MLC] click ignored (drag/swipe guard) ⚠️');
      return;
    }

    var $t = $(this);
    var destName = $t.attr('data-mlc-destination') || $t.data('mlcDestination');

    if (!destName) {
      mlcErr('[MLC] trigger missing destination ❌', { el: this });
      return;
    }

    if (!domOk()) return;

    loadRatesData()
      .then(function () {
        var dest = findDestinationByName(destName);

        if (!dest) {
          mlcErr('[MLC] destination not found ❌', {
            destName: destName,
            available: getDestinationsList().map(function (d) { return d.destination_name || d.name; })
          });
          return;
        }

        renderDestinationModal(dest);
        openModal($destModal);
      })
      .fail(function (reason) {
        mlcErr('[MLC] cannot open modal (json problem) ❌', { reason: reason });
      });
  });

  // diagnóstico post-load
  setTimeout(function () {
    if (!DBG) return;
    mlcLog('[MLC] post-load check ⏱️', {
      overlay: $('#mlc-modal-overlay').length,
      dest: $('#mlc-destination-modal').length,
      rates: $('#mlc-rates-modal').length,
      triggersFound: document.querySelectorAll('.mlc-rates-trigger').length,
      apiUrl: apiUrl,
      fallbackDataUrl: fallbackDataUrl
    });
  }, 800);

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

});
