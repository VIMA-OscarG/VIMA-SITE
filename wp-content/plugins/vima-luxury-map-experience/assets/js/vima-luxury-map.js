const VIMA_MAP_STYLES_COUNTRY_ONLY = [
  // Apaga TODOS los textos/labels
  { featureType: "all", elementType: "labels", stylers: [{ visibility: "off" }] },

  // Enciende SOLO el nombre del país
  { featureType: "administrative.country", elementType: "labels", stylers: [{ visibility: "on" }] },

  // (Opcional) Asegura que NO salgan estados/ciudades
  { featureType: "administrative.province", elementType: "labels", stylers: [{ visibility: "off" }] },
  { featureType: "administrative.locality", elementType: "labels", stylers: [{ visibility: "off" }] },

  // (Opcional) Extra: apaga comercios/POIs/transit/roads
  { featureType: "poi", elementType: "labels", stylers: [{ visibility: "off" }] },
  { featureType: "transit", elementType: "labels", stylers: [{ visibility: "off" }] },
  { featureType: "road", elementType: "labels", stylers: [{ visibility: "off" }] },
];

(function () {
  const CONFIG = window.VIMA_LUXURY_MAP_CONFIG || null;

  // Toggle general (apaga en producción)
  const DEBUG = true;

  function qs(root, sel) { return root.querySelector(sel); }
  function qsa(root, sel) { return Array.from(root.querySelectorAll(sel)); }
  function clamp(n, a, b) { return Math.max(a, Math.min(b, n)); }

  function log(...args) { if (DEBUG) console.log(...args); }
  function warn(...args) { console.warn(...args); }
  function err(...args) { console.error(...args); }

  function isMobile() {
    return window.matchMedia && window.matchMedia("(max-width: 1023px)").matches;
  }

  function escapeHtml(str) {
    return String(str ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function escapeXml(str) {
    return String(str ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&apos;");
  }

  function addCacheBuster(url) {
    const sep = url.includes("?") ? "&" : "?";
    return url + sep + "v=" + Date.now();
  }

 function normalizeOptionalUrl(value) {
    if (typeof value !== "string") return "";

    const url = value.trim();
    if (!url) return "";

    try {
      const parsed = new URL(url, window.location.origin);

      // Solo aceptar URLs reales http/https
      if (parsed.protocol !== "http:" && parsed.protocol !== "https:") return "";

      return parsed.href;
    } catch {
      return "";
    }
  }
  function setZoomSafe(map, z, fallback = 6) {
    const n = Number(z);
    const zi = Number.isFinite(n) ? Math.round(n) : Math.round(Number(fallback) || 6);
    map.setZoom(Math.max(3, zi));
  }

  // Workaround: después de pan/zoom/fitBounds programático, a veces Maps renderiza “raro”
  // hasta que el usuario hace zoom manual. Forzamos resize+recenter cuando el mapa queda idle.
  function refreshMapAfterMove(map, repaintFn) {
    if (!map || !google?.maps?.event) return;
    google.maps.event.addListenerOnce(map, "idle", () => {
      const c = map.getCenter();
      google.maps.event.trigger(map, "resize");
      if (c) map.setCenter(c);
      if (typeof repaintFn === "function") repaintFn();
    });
  }

  function getUILabels() {
    const labels = CONFIG?.ui?.labels || {};
    return {
      resorts: labels.resorts || "Current Resorts",
      destinations: labels.destinations || "Destinations",
      properties: labels.properties || "Properties",
      viewing: labels.viewing || "Viewing:",
      back: labels.back || "Back",
      change: labels.change || "Change",
      comingSoon: labels.comingSoon || "Coming Soon",
      bookingChannels: labels.bookingChannels || "Booking Channels",
      channels: labels.channels || "Channels",
      propertyGallery: labels.propertyGallery || "Property gallery",
      virtualTour: labels.virtualTour || "Virtual Tour",
      watchVideo: labels.watchVideo || "Watch Video",
      close: labels.close || "Close",
      closeGallery: labels.closeGallery || "Close gallery",
      galleryTitle: labels.galleryTitle || "Hotel Tour",
    };
  }

  /* =========================================================
     FETCH: REST URL + JSON (robusto + anti-cache + debug)
     ========================================================= */

  async function fetchDataUrl() {
    if (!CONFIG?.restDataEndpoint) throw new Error("CONFIG.restDataEndpoint missing");

    const endpoint = addCacheBuster(CONFIG.restDataEndpoint);

    const res = await fetch(endpoint, {
      credentials: "same-origin",
      cache: "no-store",
    });

    const txt = await res.text();

    log("[VIMA] REST status:", res.status, res.statusText);
    log("[VIMA] REST head:", txt.slice(0, 220));
    log("[VIMA] REST tail:", txt.slice(-220));

    if (!res.ok) throw new Error(`Failed to load data endpoint: ${res.status} ${res.statusText}`);

    let info;
    try {
      info = JSON.parse(txt);
    } catch (e) {
      err("[VIMA] REST JSON.parse failed. Payload looks like:", txt.slice(0, 600));
      throw e;
    }

    if (!info?.url) throw new Error("REST returned no .url");
    return info.url;
  }

  async function fetchData(url) {
    if (!url) throw new Error("fetchData() missing url");

    const busted = addCacheBuster(url);

    const res = await fetch(busted, {
      credentials: "same-origin",
      cache: "no-store",
    });

    const txt = await res.text();

    log("[VIMA] JSON url:", url);
    log("[VIMA] JSON busted:", busted);
    log("[VIMA] JSON HTTP status:", res.status, res.statusText);
    log("[VIMA] JSON length:", txt.length);

    if (!res.ok) {
      throw new Error(`Failed to fetch JSON data: ${res.status} ${res.statusText}`);
    }

    // Detectar HTML (WP warnings / 404 / login)
    const headTrim = txt.trimStart().slice(0, 80).toLowerCase();
    if (headTrim.startsWith("<!doctype") || headTrim.startsWith("<html") || headTrim.includes("<body")) {
      err("[VIMA] JSON response is HTML (not JSON). This usually means WP error/warning/404/auth.");
      err("[VIMA] First 800 chars:\n", txt.slice(0, 800));
      throw new Error("JSON endpoint returned HTML instead of JSON");
    }

    // Quitar BOM
    const cleaned = txt.replace(/^\uFEFF/, "");

    try {
      return JSON.parse(cleaned);
    } catch (e) {
      err("[VIMA] JSON.parse failed:", e);
      err("[VIMA] First 800 chars:\n", cleaned.slice(0, 800));
      err("[VIMA] Last 800 chars:\n", cleaned.slice(-800));
      throw e;
    }
  }

  /* =========================================================
     DATA NORMALIZATION
     ========================================================= */

  function normalizeLatLng(v) {
    if (!v) return null;

    // String "lat,lng"
    if (typeof v === "string") {
      const parts = v.split(",").map(s => s.trim()).filter(Boolean);
      if (parts.length >= 2) {
        const a = Number(parts[0]);
        const b = Number(parts[1]);
        if (Number.isFinite(a) && Number.isFinite(b)) return normalizeLatLng([a, b]);
      }
      return null;
    }

    // Array [lng,lat] GeoJSON or [lat,lng]
    if (Array.isArray(v) && v.length >= 2) {
      const a = Number(v[0]);
      const b = Number(v[1]);
      if (!Number.isFinite(a) || !Number.isFinite(b)) return null;

      if (Math.abs(a) > 90 && Math.abs(b) <= 90) return { lat: b, lng: a };
      if (Math.abs(b) > 90 && Math.abs(a) <= 90) return { lat: a, lng: b };

      return { lat: a, lng: b };
    }

    // Object {lat,lng} or {latitude,longitude}
    if (typeof v === "object") {
      const lat = (v.lat ?? v.latitude);
      const lng = (v.lng ?? v.lon ?? v.longitude);
      const la = Number(lat);
      const ln = Number(lng);
      if (Number.isFinite(la) && Number.isFinite(ln)) return { lat: la, lng: ln };
    }

    return null;
  }

  function normalizeBounds(b) {
    if (!b) return null;

    if (b.sw && b.ne) {
      const sw = normalizeLatLng(b.sw);
      const ne = normalizeLatLng(b.ne);
      if (sw && ne) return { sw, ne };
      return null;
    }

    if (Array.isArray(b) && b.length >= 2) {
      const sw = normalizeLatLng(b[0]);
      const ne = normalizeLatLng(b[1]);
      if (sw && ne) return { sw, ne };
    }

    return null;
  }

  function normalizeData(data) {
    function normalizeResortsArray(inputResorts) {
      const resorts = Array.isArray(inputResorts) ? inputResorts : [];

      return resorts.map((r, idx) => {
        const id = r?.id ?? `resort-${idx}`;

      // New schema
      if (Array.isArray(r?.destinations)) {
        return {
          ...r,
          id,
          center: normalizeLatLng(r?.center),
          bounds: normalizeBounds(r?.bounds),
          destinations: r.destinations.map((d, j) => ({
            ...d,
            id: d?.id ?? `${id}-dest-${j}`,
            center: normalizeLatLng(d?.center),
            bounds: normalizeBounds(d?.bounds),
            properties: (Array.isArray(d?.properties) ? d.properties : []).map((p, k) => ({
              ...p,
              id: p?.id ?? `${id}-dest-${j}-prop-${k}`,
              center: normalizeLatLng(p?.center),
            })),
          })),
        };
      }

      // Legacy fallback -> wrap as single destination
      const legacyProps = (Array.isArray(r?.properties) ? r.properties : [])
        .map((p, k) => ({ ...p, id: p?.id ?? `${id}-prop-${k}`, center: normalizeLatLng(p?.center) }));

      const legacyCenter = normalizeLatLng(r?.center) || null;
      const legacyBounds = normalizeBounds(r?.bounds) || null;

        return {
          ...r,
          id,
          center: legacyCenter,
          bounds: legacyBounds,
          destinations: [{
            id: `${id}-dest-legacy`,
            name: r?.name || "Destination",
            center: legacyCenter,
            bounds: legacyBounds,
            properties: legacyProps,
          }],
        };
      });
    }

    const normalizedResorts = normalizeResortsArray(data?.resorts);

    // Coming soon dataset (optional)
    const comingSoonRaw = data?.comingSoonResorts ?? data?.comingSoon ?? data?.coming_soon_resorts ?? [];
    const normalizedComingSoonResorts = normalizeResortsArray(comingSoonRaw);

    return {
      ...data,
      resorts: normalizedResorts,
      comingSoonResorts: normalizedComingSoonResorts,
    };
  }

  /* =========================================================
     MARKERS (google.maps.Marker)
     ========================================================= */

  function markerDotSvgDataUrl(scale = 1) {
    const s = Math.max(0.55, Math.min(1.15, Number(scale) || 1));

    const size = Math.round(14 * s);
    const c = size / 2;

    const rOuter = 4.9 * s;
    const rInner = 1.7 * s;

    const fill = "#111111";
    const stroke = "#FFFFFF";
    const center = "#FFFFFF";

    const strokeWidth = 1.4 * s;
    const shadowDy = 2.2 * s;
    const shadowBlur = 2.6 * s;
    const shadowAlpha = 0.22;

    const svg = `
<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
  <defs>
    <filter id="ds" x="-80%" y="-80%" width="260%" height="260%">
      <feDropShadow dx="0" dy="${shadowDy}" stdDeviation="${shadowBlur}" flood-color="rgba(0,0,0,${shadowAlpha})" />
    </filter>
  </defs>
  <circle cx="${c}" cy="${c}" r="${rOuter}" fill="${fill}" stroke="${stroke}" stroke-width="${strokeWidth}" filter="url(#ds)"/>
  <circle cx="${c}" cy="${c}" r="${rInner}" fill="${center}" opacity="0.95"/>
</svg>`.trim();

    return "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(svg);
  }

  function markerLabelSvgDataUrl(label, opts = {}) {
    const textRaw = String(label || "").trim();
    const text = textRaw.length ? textRaw : "Resort";

    const maxLen = opts.maxLen ?? 34;
    const clipped = text.length > maxLen ? (text.slice(0, maxLen - 1) + "…") : text;

    const H = opts.height ?? 18;
    const fontSize = opts.fontSize ?? 10;
    const fontWeight = opts.fontWeight ?? 700;
    const letterSpacing = opts.letterSpacing ?? 0.4;
    const padX = opts.paddingX ?? 10;
    const radius = opts.radius ?? 9;

    const strokeWidth = opts.strokeWidth ?? 1.0;
    const fill = "#111111";
    const stroke = "#FFFFFF";
    const textColor = "#FFFFFF";

    const shadowDy = opts.shadowDy ?? 2;
    const shadowBlur = opts.shadowBlur ?? 3;
    const shadowAlpha = opts.shadowAlpha ?? 0.22;

    const charW = opts.charW ?? (fontSize * 0.62);
    const spacingExtra = Math.max(0, (clipped.length - 1)) * letterSpacing * 0.65;

    const minW = opts.minW ?? 64;
    const maxW = opts.maxW ?? 240;

    const w = clamp(
      Math.round(clipped.length * charW + spacingExtra + padX * 2),
      minW,
      maxW
    );

    const SS = opts.supersample ?? 2;
    const W2 = w * SS;
    const H2 = H * SS;

    const stroke2 = strokeWidth * SS;
    const r2 = radius * SS;

    const fontSize2 = fontSize * SS;
    const letterSpacing2 = letterSpacing * SS;

    const textX2 = Math.round(W2 / 2);
    const textY2 = Math.round(H2 / 2) + Math.round(0.6 * SS);

    const svg = `
<svg xmlns="http://www.w3.org/2000/svg" width="${W2}" height="${H2}" viewBox="0 0 ${W2} ${H2}">
  <defs>
    <filter id="ds" x="-70%" y="-140%" width="260%" height="380%">
      <feDropShadow dx="0" dy="${shadowDy * SS}" stdDeviation="${shadowBlur * SS}" flood-color="rgba(0,0,0,${shadowAlpha})"/>
    </filter>
  </defs>

  <g filter="url(#ds)">
    <rect x="${stroke2}" y="${stroke2}"
      width="${W2 - stroke2 * 2}" height="${H2 - stroke2 * 2}"
      rx="${r2}" ry="${r2}"
      fill="${fill}" stroke="${stroke}" stroke-width="${stroke2}"
      vector-effect="non-scaling-stroke"/>
  </g>

  <text
    x="${textX2}"
    y="${textY2}"
    text-anchor="middle"
    dominant-baseline="middle"
    font-family="system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif"
    font-weight="${fontWeight}"
    font-size="${fontSize2}"
    letter-spacing="${letterSpacing2}"
    fill="${textColor}"
    text-rendering="geometricPrecision">
    ${escapeXml(clipped)}
  </text>
</svg>`.trim();

    return { url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(svg), w, h: H };
  }

  function buildDotIcon() {
    const size = 14;
    return {
      url: markerDotSvgDataUrl(1),
      scaledSize: new google.maps.Size(size, size),
      anchor: new google.maps.Point(Math.round(size / 2), Math.round(size / 2)),
    };
  }

  function buildLabelIcon(label, variant = "default") {
    const isSmall = variant === "small";

    const chip = markerLabelSvgDataUrl(label, {
      height: isSmall ? 20 : 24,
      fontSize: isSmall ? 9 : 10,
      letterSpacing: isSmall ? 0.25 : 0.4,
      paddingX: isSmall ? 8 : 10,
      radius: isSmall ? 8 : 9,
      strokeWidth: 1.0,
      supersample: 2,
      minW: isSmall ? 52 : 64,
      maxW: isSmall ? 210 : 240,
      charW: (isSmall ? 9 : 10) * 0.58,
    });

    return {
      url: chip.url,
      scaledSize: new google.maps.Size(chip.w, chip.h),
      anchor: new google.maps.Point(Math.round(chip.w / 2), chip.h),
    };
  }

  /* =========================================================
     FIT HELPERS
     ========================================================= */

  function fitBoundsFromPoints(map, points, padding = 40, opts = {}) {
    const pts = (points || []).filter(p => p && typeof p.lat === "number" && typeof p.lng === "number");
    if (!pts.length) return;

    // Single marker: avoid aggressive fitBounds
    if (pts.length === 1) {
      const p = pts[0];
      map.panTo(p);

      const override = Number(opts.singleZoom);
      if (Number.isFinite(override)) {
        setZoomSafe(map, override, CONFIG?.mapOptions?.defaultZoom);
        return;
      }

      const cfgZoom = Number(CONFIG?.mapOptions?.singleMarkerZoom);
      if (Number.isFinite(cfgZoom)) {
        setZoomSafe(map, cfgZoom, CONFIG?.mapOptions?.defaultZoom);
        return;
      }

      // Fallback: ~20% menos zoom-in (ligeramente más abierto)
      const current = Number(map.getZoom());
      const base = Number.isFinite(current) ? current : (Number(CONFIG?.mapOptions?.defaultZoom) || 6);
      const delta = 0.3219280949;
      setZoomSafe(map, base - delta, CONFIG?.mapOptions?.defaultZoom);
      return;
    }

    const bounds = new google.maps.LatLngBounds();
    pts.forEach(p => bounds.extend(new google.maps.LatLng(p.lat, p.lng)));
    map.fitBounds(bounds, padding);
  }

  function fitBoundsFromResorts(map, resorts) {
    // Legacy: resort.center
    const pts = (resorts || []).map(r => r?.center).filter(Boolean);
    if (pts.length) {
      fitBoundsFromPoints(map, pts, 40, { singleZoom: Number(CONFIG?.mapOptions?.singleMarkerZoom) });
      return;
    }

    // New schema: all destinations
    const pts2 = [];
    (resorts || []).forEach(r => (r?.destinations || []).forEach(d => { if (d?.center) pts2.push(d.center); }));
    if (pts2.length) {
      fitBoundsFromPoints(map, pts2, 40, { singleZoom: Number(CONFIG?.mapOptions?.singleMarkerZoom) });
      return;
    }

    map.setCenter(CONFIG.mapOptions.defaultCenter);
    setZoomSafe(map, CONFIG.mapOptions.defaultZoom, 6);
  }

  function fitBoundsFromResort(map, resort) {
    if (resort?.bounds?.sw && resort?.bounds?.ne) {
      const bounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(resort.bounds.sw.lat, resort.bounds.sw.lng),
        new google.maps.LatLng(resort.bounds.ne.lat, resort.bounds.ne.lng)
      );
      map.fitBounds(bounds, 60);
      return;
    }

    if (resort?.center) {
      map.panTo(resort.center);
      setZoomSafe(map, Number(CONFIG?.mapOptions?.resortCenterZoom) || 12, CONFIG?.mapOptions?.defaultZoom);
      return;
    }

    const pts = (resort?.destinations || []).map(d => d?.center).filter(Boolean);
    if (pts.length) {
      fitBoundsFromPoints(map, pts, 60, {
        singleZoom: Number(CONFIG?.mapOptions?.destinationsSingleZoom) || 12,
      });
      return;
    }

    map.setCenter(CONFIG.mapOptions.defaultCenter);
    setZoomSafe(map, CONFIG.mapOptions.defaultZoom, 6);
  }

  function fitBoundsFromDestination(map, destination) {
    if (destination?.bounds?.sw && destination?.bounds?.ne) {
      const bounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(destination.bounds.sw.lat, destination.bounds.sw.lng),
        new google.maps.LatLng(destination.bounds.ne.lat, destination.bounds.ne.lng)
      );
      map.fitBounds(bounds, 60);
      return;
    }

    if (destination?.center) {
      map.panTo(destination.center);
      const zProps = Number(CONFIG?.mapOptions?.propertiesSingleZoom);
      const zCenter = Number(CONFIG?.mapOptions?.destinationCenterZoom);
      const finalZoom = Number.isFinite(zProps) ? zProps : (Number.isFinite(zCenter) ? zCenter : 13);
      setZoomSafe(map, finalZoom, CONFIG?.mapOptions?.defaultZoom);
      return;
    }
  }



  function ensureModalOnBody(modal) {
    if (!modal || modal.parentElement === document.body) return modal;
    modal.dataset.vimaModalRestoreParent = "1";
    modal.__vimaRestoreParent = modal.parentElement;
    document.body.appendChild(modal);
    return modal;
  }

  function getBookingModal(root) {
    return qs(root, "[data-modal]") || document.querySelector("[data-modal]");
  }

  function getGalleryModal(root) {
    return qs(root, "[data-gallery-modal]") || document.querySelector("[data-gallery-modal]");
  }

  function syncBodyScrollLock() {
    const hasOpenModal = Array.from(document.querySelectorAll("[data-modal], [data-gallery-modal]"))
      .some((node) => node.getAttribute("aria-hidden") === "false");

    document.body.style.overflow = hasOpenModal ? "hidden" : "";
  }

  function slugify(value) {
    return String(value ?? "")
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-+|-+$/g, "") || "section";
  }

  function normalizeGallery(property) {
    const L = getUILabels();
    const rawGallery = property?.gallery;
    if (!rawGallery || rawGallery.enabled !== true) return null;

    const galleryVirtualTourUrl =
      normalizeOptionalUrl(rawGallery?.virtualTourUrl) ||
      "";

    const galleryVideoUrl =
      normalizeOptionalUrl(rawGallery?.videoUrl) ||
      "";

    const sections = (Array.isArray(rawGallery.sections) ? rawGallery.sections : [])
      .map((section, sectionIndex) => {
        const sectionTitle = String(section?.title || `${L.destinations} ${sectionIndex + 1}`);
        const sectionId = String(section?.id || slugify(sectionTitle) || `section-${sectionIndex + 1}`);
        const rawImages = Array.isArray(section?.images) ? section.images : [];

        const images = rawImages
          .filter((img) => img && (img.src || img.url))
          .map((img, imageIndex) => ({
            id: String(img?.id || `${sectionId}-${imageIndex + 1}`),
            src: String(img?.src || img?.url || ""),
            alt: String(img?.alt || img?.caption || sectionTitle || property?.name || L.propertyGallery),
            caption: String(img?.caption || ""),
            width: Number(img?.width) || 0,
            height: Number(img?.height) || 0,
          }))
          .filter((img) => !!img.src);

        const coverSrc = String(section?.cover?.src || images[0]?.src || property?.thumb || "");
        const coverAlt = String(section?.cover?.alt || images[0]?.alt || sectionTitle || property?.name || L.propertyGallery);

        if (!coverSrc && !images.length) return null;

        return {
          id: sectionId,
          title: sectionTitle,
          cover: {
            src: coverSrc,
            alt: coverAlt,
          },
          images: images.length ? images : [{
            id: `${sectionId}-cover`,
            src: coverSrc,
            alt: coverAlt,
            caption: sectionTitle,
            width: 0,
            height: 0,
          }],
        };
      })
      .filter(Boolean);

    if (!sections.length) return null;

    return {
      title: String(rawGallery.title || L.galleryTitle),
      subtitle: String(rawGallery.subtitle || property?.name || ""),
      virtualTourUrl: galleryVirtualTourUrl,
      videoUrl: galleryVideoUrl,
      sections,
    };
  }

  function getGalleryLayoutByRatio(ratio) {
    if (!Number.isFinite(ratio) || ratio <= 0) return "standard";
    if (ratio >= 2.05) return "panorama";
    if (ratio >= 1.42) return "wide";
    if (ratio <= 0.78) return "tall";
    return "standard";
  }

  function getGalleryScrollRoot(modal) {
    return qs(modal, "[data-gallery-scroll]") || modal;
  }

  function isGalleryCompactViewport() {
    return !!(window.matchMedia && window.matchMedia("(max-width: 1023px)").matches);
  }

  function getGalleryCollagePattern(index) {
    const patterns = ["a", "b", "c", "d", "e", "f"];
    return patterns[Math.max(0, index) % patterns.length];
  }

  function scrollActiveGalleryNavIntoView(modal, sectionId, { immediate = false } = {}) {
    const nav = qs(modal, "[data-gallery-nav]");
    const activeButton = qs(modal, `[data-gallery-jump="${CSS.escape(String(sectionId))}"]`);
    if (!nav || !activeButton || !isGalleryCompactViewport()) return;

    const navRect = nav.getBoundingClientRect();
    const buttonRect = activeButton.getBoundingClientRect();
    const buffer = 12;
    let nextLeft = null;

    if (buttonRect.left < (navRect.left + buffer)) {
      nextLeft = nav.scrollLeft - ((navRect.left + buffer) - buttonRect.left);
    } else if (buttonRect.right > (navRect.right - buffer)) {
      nextLeft = nav.scrollLeft + (buttonRect.right - (navRect.right - buffer));
    }

    if (nextLeft === null) return;

    nav.scrollTo({
      left: Math.max(0, nextLeft),
      behavior: immediate ? "auto" : "smooth",
    });
  }

  function setGalleryActionLink(linkEl, url) {
    if (!linkEl) return;

    const nextUrl = normalizeOptionalUrl(url);
    const hasUrl = !!nextUrl;

    linkEl.hidden = !hasUrl;
    linkEl.setAttribute("aria-hidden", hasUrl ? "false" : "true");

    if (hasUrl) {
      linkEl.href = nextUrl;
    } else {
      linkEl.removeAttribute("href");
    }
  }

function syncGalleryHeaderActions(modal, sectionId) {
  if (!modal) return;

  const gallery = modal.__vimaContext?.gallery || modal.__vimaGalleryData || null;

  const tourUrl = normalizeOptionalUrl(gallery?.virtualTourUrl) || "";
  const videoUrl = normalizeOptionalUrl(gallery?.videoUrl) || "";

  setGalleryActionLink(qs(modal, "[data-gallery-virtual-tour]"), tourUrl);
  setGalleryActionLink(qs(modal, "[data-gallery-video]"), videoUrl);
}

  function setActiveGalleryNav(modal, sectionId, options = {}) {
    const nextId = String(sectionId || "");
    const shouldUpdate = modal.__vimaGalleryActiveId !== nextId || !!options.force;

    qsa(modal, "[data-gallery-jump]").forEach((button) => {
      const isActive = String(button.dataset.galleryJump) === nextId;
      button.classList.toggle("is-active", isActive);
      button.setAttribute("aria-pressed", isActive ? "true" : "false");
    });

    if (shouldUpdate) {
      modal.__vimaGalleryActiveId = nextId;
      scrollActiveGalleryNavIntoView(modal, nextId, { immediate: !!options.immediate });
      syncGalleryHeaderActions(modal, nextId);
    }
  }

  function syncGalleryNavWithScroll(modal) {
    const scroller = getGalleryScrollRoot(modal);
    const sections = qsa(modal, "[data-gallery-section]");
    if (!sections.length) return;

    const currentTop = scroller.scrollTop;
    let activeId = sections[0].dataset.gallerySection || "";

    sections.forEach((section) => {
      const threshold = section.offsetTop - 180;
      if (currentTop >= threshold) {
        activeId = section.dataset.gallerySection || activeId;
      }
    });

    setActiveGalleryNav(modal, activeId);
  }

  function scrollGalleryToSection(modal, sectionId) {
    const scroller = getGalleryScrollRoot(modal);
    const section = qs(modal, `[data-gallery-section="${CSS.escape(String(sectionId))}"]`);
    if (!scroller || !section) return;

    const targetTop = Math.max(0, section.offsetTop - 8);

    scroller.scrollTo({ top: targetTop, behavior: "smooth" });
    setActiveGalleryNav(modal, sectionId);
  }

  function scheduleGalleryMasonry(modal) {
    if (!modal) return;

    if (modal.__vimaGalleryLayoutRaf) {
      cancelAnimationFrame(modal.__vimaGalleryLayoutRaf);
    }

    modal.__vimaGalleryLayoutRaf = requestAnimationFrame(() => {
      const isCompact = isGalleryCompactViewport();

      qsa(modal, "[data-gallery-masonry]").forEach((grid) => {
        const styles = window.getComputedStyle(grid);
        const rowHeight = parseFloat(styles.getPropertyValue("grid-auto-rows")) || 10;
        const gap = parseFloat(styles.getPropertyValue("gap")) || 16;

        qsa(grid, ".vima-gallery-masonry__item").forEach((item, itemIndex) => {
          const img = qs(item, "img");
          const frame = qs(item, ".vima-gallery-masonry__frame") || item;
          if (!img || !frame) return;

          const ratio = (img.naturalWidth && img.naturalHeight) ? (img.naturalWidth / img.naturalHeight) : 1;

          if (isCompact) {
            item.dataset.layout = getGalleryLayoutByRatio(ratio);
          } else {
            item.dataset.layout = "collage";
            item.dataset.collagePattern = item.dataset.collagePattern || getGalleryCollagePattern(itemIndex);
          }

          item.style.gridRowEnd = "span 1";
          const height = frame.getBoundingClientRect().height;
          const rowSpan = Math.max(1, Math.ceil((height + gap) / (rowHeight + gap)));
          item.style.gridRowEnd = `span ${rowSpan}`;
        });
      });
    });
  }

  function bindGalleryMasonry(modal) {
    if (!modal || modal.__vimaGalleryBound) return;
    modal.__vimaGalleryBound = true;

    const resizeHandler = () => scheduleGalleryMasonry(modal);
    modal.__vimaGalleryResizeHandler = resizeHandler;
    window.addEventListener("resize", resizeHandler);

    if ("ResizeObserver" in window) {
      const ro = new ResizeObserver(() => scheduleGalleryMasonry(modal));
      const scroller = getGalleryScrollRoot(modal);
      if (scroller) ro.observe(scroller);
      modal.__vimaGalleryResizeObserver = ro;
    }
  }

  function wireGalleryMedia(modal) {
    qsa(modal, ".vima-gallery-masonry__image").forEach((img) => {
      if (img.dataset.vimaGalleryBound === "1") return;
      img.dataset.vimaGalleryBound = "1";

      const onReady = () => scheduleGalleryMasonry(modal);

      if (img.complete) {
        onReady();
      } else {
        img.addEventListener("load", onReady, { once: true });
        img.addEventListener("error", onReady, { once: true });
      }
    });
  }

  function renderGalleryModal(root, property) {
    const modal = getGalleryModal(root);
    if (!modal) return null;

    const gallery = normalizeGallery(property);
    if (!gallery) return null;

    modal.__vimaGalleryData = gallery;

    const titleEl = qs(modal, "[data-gallery-title]");
    const subtitleEl = qs(modal, "[data-gallery-subtitle]");
    const navEl = qs(modal, "[data-gallery-nav]");
    const sectionsEl = qs(modal, "[data-gallery-sections]");

    if (!titleEl || !subtitleEl || !navEl || !sectionsEl) return null;

    titleEl.textContent = gallery.title;
    subtitleEl.textContent = gallery.subtitle || "";
    subtitleEl.hidden = !gallery.subtitle;

    navEl.innerHTML = gallery.sections.map((section) => {
      const coverSrc = section.cover?.src || section.images[0]?.src || "";
      const coverAlt = section.cover?.alt || section.title || property?.name || getUILabels().propertyGallery;

      return `
        <button class="vima-gallery-nav__card" type="button" data-gallery-jump="${escapeXml(section.id)}" aria-pressed="false">
          <span class="vima-gallery-nav__media">
            <img src="${escapeXml(coverSrc)}" alt="${escapeXml(coverAlt)}" loading="lazy" />
          </span>
          <span class="vima-gallery-nav__label">${escapeHtml(section.title)}</span>
        </button>
      `;
    }).join("");

    sectionsEl.innerHTML = gallery.sections.map((section) => {
      const imagesHtml = section.images.map((image, imageIndex) => `
        <figure
          class="vima-gallery-masonry__item"
          data-gallery-item="${escapeXml(image.id || `${section.id}-${imageIndex + 1}`)}"
          data-collage-pattern="${escapeXml(getGalleryCollagePattern(imageIndex))}"
        >
          <div class="vima-gallery-masonry__frame">
            <img
              class="vima-gallery-masonry__image"
              src="${escapeXml(image.src)}"
              alt="${escapeXml(image.alt || image.caption || section.title)}"
              loading="lazy"
              ${image.width > 0 ? `width="${escapeXml(image.width)}"` : ""}
              ${image.height > 0 ? `height="${escapeXml(image.height)}"` : ""}
            />
          </div>
        </figure>
      `).join("");

      return `
        <section class="vima-gallery-section" data-gallery-section="${escapeXml(section.id)}">
          <div class="vima-gallery-section__header">
            <h3 class="vima-gallery-section__title">${escapeHtml(section.title)}</h3>
          </div>
          <div class="vima-gallery-masonry" data-gallery-masonry>
            ${imagesHtml}
          </div>
        </section>
      `;
    }).join("");

    qsa(modal, "[data-gallery-jump]").forEach((button) => {
      button.addEventListener("click", () => {
        scrollGalleryToSection(modal, button.dataset.galleryJump || "");
      });
    });

    const scroller = getGalleryScrollRoot(modal);
    if (scroller && !scroller.__vimaGalleryScrollBound) {
      scroller.__vimaGalleryScrollBound = true;
      scroller.addEventListener("scroll", () => syncGalleryNavWithScroll(modal), { passive: true });
    }

    setActiveGalleryNav(modal, gallery.sections[0]?.id || "", { immediate: true, force: true });
    bindGalleryMasonry(modal);
    wireGalleryMedia(modal);
    scheduleGalleryMasonry(modal);
    syncGalleryNavWithScroll(modal);

    return gallery;
  }

  function openGalleryModal(root, property, resort, destination) {
    const modal = getGalleryModal(root);
    if (!modal) {
      warn("[VIMA] Gallery modal markup missing.");
      return;
    }

    const gallery = renderGalleryModal(root, property);
    if (!gallery) return;

    ensureModalOnBody(modal);

    modal.__vimaContext = { property, resort, destination, gallery };

    const scroller = getGalleryScrollRoot(modal);
    if (scroller) scroller.scrollTop = 0;

    modal.setAttribute("aria-hidden", "false");
    syncBodyScrollLock();
    syncGalleryHeaderActions(modal, gallery.sections[0]?.id || "");
    scheduleGalleryMasonry(modal);
    syncGalleryNavWithScroll(modal);
  }

  function closeGalleryModal(root) {
    const modal = getGalleryModal(root);
    if (!modal) return;

    modal.setAttribute("aria-hidden", "true");
    syncBodyScrollLock();
  }

  /* =========================================================
     UI: Cards + Modal
     ========================================================= */

  function renderCards(container, items, mode, { onHover, badgeText } = {}) {
    const lang = CONFIG?.ui?.lang === "es" ? "es" : "en";
    container.innerHTML = "";

    items.forEach(item => {
      const img = item.thumb || item.heroImage || "";
      const title = item.name || "";

      const propCount = (mode === "destinations") ? (item.properties || []).length : null;

      const sub = mode === "resorts"
        ? ""
        : (mode === "destinations"
          ? (propCount > 0 ? (lang === "es" ? `${propCount} propiedades` : `${propCount} properties`) : "")
          : (item.priceFrom ? (lang === "es" ? `Desde ${item.currency || ""} ${item.priceFrom}` : `From ${item.currency || ""} ${item.priceFrom}`) : (lang === "es" ? "Ver canales" : "View channels"))
        );

      const isDisabled = (mode === "destinations") && (propCount === 0);

      const resortDestChips = (mode === "resorts")
        ? (item.destinations || []).map(d => (d && d.name ? String(d.name) : "")).filter(Boolean)
        : [];

      const tags = (mode === "properties") ? (item.tags || []) : [];
      const hasGallery = mode === "properties" && !!normalizeGallery(item);

      const el = document.createElement("div");
      el.className = "vima-card" + (isDisabled ? " is-disabled" : "");
      el.setAttribute("tabindex", isDisabled ? "-1" : "0");
      el.setAttribute("aria-disabled", isDisabled ? "true" : "false");
      el.dataset.id = item.id;
      el.dataset.mode = mode;

      const badge = (badgeText && String(badgeText).trim()) ? String(badgeText).trim() : "";

      el.innerHTML = `
        <div class="vima-card__img" style="background-image:url('${img}')">
          ${badge ? `<span class="vima-card__badge">${escapeHtml(badge)}</span>` : ""}
          ${hasGallery ? `
            <button
              class="vima-card__media-btn"
              type="button"
              data-open-card-gallery
              aria-label="${escapeHtml(lang === "es" ? `Abrir galería multimedia de ${title || "propiedad"}` : `Open media gallery for ${title || "property"}`)}"
            >
              <svg class="vima-card__media-btn-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <rect x="3.5" y="5.5" width="17" height="13" rx="2.5" stroke="currentColor" stroke-width="1.7"/>
                <circle cx="8.25" cy="10" r="1.35" fill="currentColor"/>
                <path d="M6.5 16l4.2-4.2a1 1 0 0 1 1.42 0L14 13.68l1.35-1.35a1 1 0 0 1 1.42 0L19 14.56V16H6.5Z" fill="currentColor"/>
              </svg>
              <span>${lang === "es" ? "Media" : "Media"}</span>
            </button>
          ` : ""}
        </div>
        <div class="vima-card__body">
          <div class="vima-card__title">${escapeHtml(title)}</div>
          ${sub ? `<div class="vima-card__sub">${escapeHtml(sub)}</div>` : ""}

          ${mode === "resorts" && resortDestChips.length ? `
            <div class="vima-card__meta vima-card__meta--chips">
              ${resortDestChips.slice(0, 4).map(t => `<span class="vima-chip">${escapeHtml(t)}</span>`).join("")}
            </div>
          ` : ""}

          ${mode === "properties" && tags.length ? `
            <div class="vima-card__meta">
              ${tags.slice(0, 4).map(t => `<span class="vima-pill">${escapeHtml(t)}</span>`).join("")}
            </div>
          ` : ""}
        </div>
      `;

      if (typeof onHover === "function") {
        el.addEventListener("mouseenter", () => onHover(el, true));
        el.addEventListener("mouseleave", () => onHover(el, false));
      }

      container.appendChild(el);
    });
  }

  // Modal: booking channels
  function openModal(root, property, resort, destination) {
    const L = getUILabels();
    const lang = CONFIG?.ui?.lang === "es" ? "es" : "en";
    const modal = getBookingModal(root);
    const modalTitle = qs(root, "[data-modal-title]");
    const hero = qs(root, "[data-modal-hero]");
    const propTitle = qs(root, "[data-modal-prop-title]");
    const tagsWrap = qs(root, "[data-modal-tags]");
    const notesWrap = qs(root, "[data-modal-notes]");
    const channelsWrap = qs(root, "[data-modal-channels]");
    // Channel CTA (appears after selecting a channel)
    const ctaWrap = qs(root, "[data-channel-cta]");
    const ctaTitle = qs(root, "[data-channel-cta-title]");
    const ctaBtn = qs(root, "[data-channel-cta-btn]");

    if (!modal || !modalTitle || !hero || !propTitle || !tagsWrap || !channelsWrap) return;

    ensureModalOnBody(modal);

    const viewChannelsBtn = modal.querySelector("[data-modal-view-channels]");
    const backToPropertyBtn = modal.querySelector("[data-modal-back-to-property]");
    const setUltraSmallView = (view) => {
      modal.dataset.vimaModalView = view;
    };

    modalTitle.textContent = L.bookingChannels;
    propTitle.textContent = property?.name || (lang === "es" ? "Propiedad" : "Property");

    const heroUrl = property?.thumb || destination?.heroImage || resort?.heroImage || "";
    hero.style.backgroundImage = heroUrl ? `url('${heroUrl}')` : "none";

    tagsWrap.innerHTML = (property?.tags || [])
      .map(t => `<span class="vima-pill">${escapeHtml(t)}</span>`)
      .join("");

    if (viewChannelsBtn) {
      viewChannelsBtn.textContent = lang === "es" ? "Ver canales" : "View channels";
      viewChannelsBtn.onclick = () => setUltraSmallView("channels");
    }

    if (backToPropertyBtn) {
      backToPropertyBtn.textContent = lang === "es" ? "Volver a propiedad" : "Back to property";
      backToPropertyBtn.onclick = () => setUltraSmallView("property");
    }

    if (notesWrap) {
      notesWrap.textContent = lang === "es"
        ? "Selecciona un canal para abrir el listado en una nueva pestaña."
        : "Select a channel to open the listing in a new tab.";
    }

    // Reset CTA (hidden until selection)
    if (ctaWrap) ctaWrap.hidden = true;
    if (ctaTitle) ctaTitle.textContent = "";
    if (ctaBtn) {
      ctaBtn.href = "#";
      ctaBtn.classList.remove("is-disabled");
      ctaBtn.setAttribute("aria-disabled", "false");
    }

    // Render channels
    channelsWrap.innerHTML = "";
    (property?.channels || []).forEach(ch => {
      const el = document.createElement("div");
      el.className = "vima-channel";
      el.dataset.channelId = ch.id || "";
      el.setAttribute("role", "button");
      el.setAttribute("tabindex", "0");
      el.setAttribute("aria-pressed", "false");
      el.setAttribute("aria-expanded", "false");

      const labelSafe = escapeHtml(ch.label || (lang === "es" ? "Canal" : "Channel"));

el.innerHTML = `
  <div class="vima-channel__logo" style="background-image:url('${ch.logo || ""}')"></div>

  <div class="vima-channel__main">
    <div class="vima-channel__name">${labelSafe}</div>
    <div class="vima-channel__desc">${escapeHtml(ch.previewDescription || "")}</div>
  </div>

  <div class="vima-channel__footer" aria-hidden="true">
    <div class="vima-channel__footer-row">
      <div class="vima-channel__footer-title">${escapeHtml(lang === "es" ? `Ir a ${ch.label || "Canal"}` : `Go to ${ch.label || "Channel"}`)}</div>

      <button class="vima-channel__cta" type="button">
        ${lang === "es" ? "Abrir en nueva pestaña" : "Open in new tab"}
        <svg class="vima-channel__cta-svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M14 5h5v5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M10 14L19 5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M19 14v5H5V5h5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

    <div class="vima-channel__footer-hint">${lang === "es" ? "Se abre en una nueva pestaña del navegador." : "Opens in a new browser tab."}</div>
  </div>
`;


      const footer = el.querySelector(".vima-channel__footer");
      const ctaInlineBtn = el.querySelector(".vima-channel__cta");

      function collapseAllExcept(current) {
        qsa(channelsWrap, ".vima-channel").forEach(node => {
          if (node === current) return;
          node.classList.remove("is-expanded");
          node.setAttribute("aria-expanded", "false");
          const f = node.querySelector(".vima-channel__footer");
          if (f) f.setAttribute("aria-hidden", "true");
        });
      }

      function toggleExpanded(channelEl) {
        const isOn = channelEl.classList.contains("is-expanded");

        if (isOn) {
          channelEl.classList.remove("is-expanded");
          channelEl.setAttribute("aria-expanded", "false");
          if (footer) footer.setAttribute("aria-hidden", "true");
          return;
        }

        collapseAllExcept(channelEl);
        channelEl.classList.add("is-expanded");
        channelEl.setAttribute("aria-expanded", "true");
        if (footer) footer.setAttribute("aria-hidden", "false");
      }

      const activate = () => {
        selectChannel(el, ch);
        toggleExpanded(el);
      };

      el.addEventListener("click", activate);
      el.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          activate();
        }
      });

      // Inline CTA: open in new tab, no toggle/collapse
      if (ctaInlineBtn) {
        ctaInlineBtn.addEventListener("click", (e) => {
          e.preventDefault();
          e.stopPropagation();

          const url = ch?.url ? String(ch.url) : "";
          if (!url) return;

          window.open(url, "_blank", "noopener,noreferrer");
        });
      }

      channelsWrap.appendChild(el);
    });

    function selectChannel(channelEl, channel) {
      // Selected state
      qsa(channelsWrap, ".vima-channel").forEach((n) => {
        n.classList.remove("is-selected");
        n.setAttribute("aria-pressed", "false");
      });
      channelEl.classList.add("is-selected");
      channelEl.setAttribute("aria-pressed", "true");

      // CTA copy + link (si todavía lo usas en tu HTML, lo dejamos intacto)
      const label = channel?.label ? String(channel.label) : (lang === "es" ? "sitio" : "site");
      const url = channel?.url ? String(channel.url) : "";

      if (ctaTitle) ctaTitle.textContent = lang === "es" ? `Ir a ${label}` : `Go to ${label}`;
      if (ctaBtn) {
        ctaBtn.href = url || "#";
        ctaBtn.classList.toggle("is-disabled", !url);
        ctaBtn.setAttribute("aria-disabled", url ? "false" : "true");
      }
      if (ctaWrap) ctaWrap.hidden = false;
    }

    setUltraSmallView("property");

    modal.setAttribute("aria-hidden", "false");
    syncBodyScrollLock();
  }

function closeModal(root) {
  closeGalleryModal(root);

  const modal = getBookingModal(root);
  if (!modal) return;

  const channelsWrap = modal.querySelector("[data-modal-channels]");

  if (modal.dataset.vimaModalRestoreParent === "1" && modal.__vimaRestoreParent) {
    modal.__vimaRestoreParent.appendChild(modal);
    delete modal.__vimaRestoreParent;
    delete modal.dataset.vimaModalRestoreParent;
  }

  modal.setAttribute("aria-hidden", "true");
  syncBodyScrollLock();

  modal.dataset.vimaModalView = "property";

  if (channelsWrap) {
    channelsWrap.querySelectorAll(".vima-channel").forEach((n) => {
      n.classList.remove("is-selected", "is-expanded");
      n.setAttribute("aria-pressed", "false");
      n.setAttribute("aria-expanded", "false");
      const f = n.querySelector(".vima-channel__footer");
      if (f) f.setAttribute("aria-hidden", "true");
    });
  }
}


  /* =========================================================
     APP: initInstance + layers (3 capas)
     ========================================================= */

  function initInstance(root, data) {
    const cards = qs(root, "[data-cards]");
    const mapEl = qs(root, "[data-map]");
    const crumbLabel = qs(root, "[data-crumb-label]");
    const backBtn = qs(root, '[data-action="back"]');
    const datasetTabs = qs(root, "[data-dataset-tabs]");
    const datasetTabBtns = datasetTabs ? qsa(datasetTabs, "[data-dataset]") : [];
    const mobilePill = qs(root, "[data-mobile-pill]");
    const pillText = qs(root, "[data-pill-text]");
    const changeResortBtn = qs(root, '[data-action="change-resort"]');

    const L = getUILabels();

    if (backBtn) backBtn.textContent = L.back;
    if (changeResortBtn) changeResortBtn.textContent = L.change;

    const normalized = normalizeData(data);

    const datasets = {
      available: normalized.resorts || [],
      comingSoon: normalized.comingSoonResorts || [],
    };

    const state = {
      datasetKey: "available", // available | comingSoon
      datasets,
      layer: "resorts", // resorts | destinations | properties
      selectedResortId: null,
      selectedDestinationId: null,

      resorts: datasets.available,
      resortById: new Map(),

      // Destinations index (key: `${resortId}::${destId}`)
      destinationByKey: new Map(),

      markers: new Map(), // key: resortId OR destKey
      map: null,

      hoverKey: null,
      hoverKeys: new Set(),
      activeMarkerKey: null,
      markerMode: "destinations", // destinations | resorts | singleDestination
    };

    function rebuildIndexes() {
      state.resortById.clear();
      state.destinationByKey.clear();

      state.resorts.forEach(r => {
        state.resortById.set(r.id, r);
        (r.destinations || []).forEach(d => {
          const key = `${r.id}::${d.id}`;
          state.destinationByKey.set(key, { ...d, __key: key, __resortId: r.id });
        });
      });
    }

    rebuildIndexes();

    function updateDatasetTabsUI() {
      if (!datasetTabBtns.length) return;

      datasetTabBtns.forEach(btn => {
        const key = btn.dataset.dataset;
        if (key === "comingSoon" && (!state.datasets.comingSoon || state.datasets.comingSoon.length === 0)) {
          btn.hidden = true;
          return;
        }

        btn.hidden = false;
        btn.textContent = (key === "comingSoon") ? L.comingSoon : L.resorts;
        btn.classList.toggle("is-active", key === state.datasetKey);
        btn.setAttribute("aria-pressed", (key === state.datasetKey) ? "true" : "false");
      });
    }

    

    function updateLayerChrome() {
      const isLayer1 = state.layer === "resorts";
      // Only show dataset tabs in layer 1
      if (datasetTabs) datasetTabs.hidden = !isLayer1;
      // Hide/show the big title so layer 1 looks like tabs
      root.classList.toggle("vima-layer--resorts", isLayer1);
    }

function setDataset(nextKey) {
      const key = String(nextKey || "").trim();
      if (!key || !(key in state.datasets)) return;
      if (state.datasetKey === key) return;

      state.datasetKey = key;
      state.resorts = state.datasets[key] || [];

      state.selectedResortId = null;
      state.selectedDestinationId = null;
      state.layer = "resorts";

      updateLayerChrome();
      rebuildIndexes();
      updateDatasetTabsUI();
    updateLayerChrome();
      setLayerResorts();
    }

    // Wire dataset tabs
    if (datasetTabBtns.length) {
      datasetTabBtns.forEach(btn => {
        btn.setAttribute("aria-pressed", btn.dataset.dataset === state.datasetKey ? "true" : "false");
        btn.addEventListener("click", () => setDataset(btn.dataset.dataset));
      });
    }

    updateDatasetTabsUI();
    updateLayerChrome();

    function getResortByIdLoose(id) {
      if (state.resortById.has(id)) return state.resortById.get(id);
      const n = Number(id);
      if (Number.isFinite(n) && state.resortById.has(n)) return state.resortById.get(n);
      const s = String(id);
      return state.resorts.find(r => String(r?.id) === s) || null;
    }

    state.map = new google.maps.Map(mapEl, {
      center: CONFIG.mapOptions.defaultCenter,
      zoom: CONFIG.mapOptions.defaultZoom,
      disableDefaultUI: false,
      clickableIcons: false,
      gestureHandling: "greedy",
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: true,
      styles: VIMA_MAP_STYLES_COUNTRY_ONLY,
    });

    function clearMarkers() {
      state.markers.forEach(m => m.setMap(null));
      state.markers.clear();
      state.hoverKey = null;
      state.hoverKeys.clear();
      state.activeMarkerKey = null;
    }

    function setMarkerVisual(key, label, { active = false, hover = false } = {}) {
      const marker = state.markers.get(key);
      if (!marker) return;

      const showLabel = !!active || !!hover;

      const variant = (state.layer === "destinations" || state.layer === "properties")
        ? "small"
        : "default";

      // Stabiliza SVG chips (evita scale raro hasta zoom manual)
      marker.setOptions({ optimized: !showLabel });

      marker.setIcon(showLabel ? buildLabelIcon(label, variant) : buildDotIcon());
      marker.setZIndex(active ? 1000 : (hover ? 900 : undefined));
    }

    function repaintMarkers(getLabelFn) {
      state.markers.forEach((marker, key) => {
        const active = (key === state.activeMarkerKey);
        const hover = (key === state.hoverKey) || state.hoverKeys.has(key);
        const label = typeof getLabelFn === "function" ? (getLabelFn(key) || "") : "";
        setMarkerVisual(key, label, { active, hover });
      });
    }

    function setHoverMarker(key, on, getLabelFn) {
      state.hoverKeys.clear();
      state.hoverKey = on ? key : null;
      repaintMarkers(getLabelFn);
    }

    function clearHoverMarker(getLabelFn) {
      state.hoverKey = null;
      state.hoverKeys.clear();
      repaintMarkers(getLabelFn);
    }

    function setHoverMarkers(keys, on, getLabelFn) {
      state.hoverKey = null;
      state.hoverKeys.clear();

      if (on && Array.isArray(keys)) keys.forEach(k => state.hoverKeys.add(k));
      repaintMarkers(getLabelFn);
    }

    function buildMarkersForDestinations(destinations, onMarkerClick) {
      clearMarkers();
      let created = 0;

      destinations.forEach(d => {
        const center = d?.center;
        if (!center || typeof center.lat !== "number" || typeof center.lng !== "number") return;

        const key = d.__key || `${d.__resortId || ""}::${d.id}`;
        const marker = new google.maps.Marker({
          map: state.map,
          position: center,
          title: d.name,
          icon: buildDotIcon(),
          optimized: true,
        });

        marker.addListener("click", () => onMarkerClick && onMarkerClick(d));
        state.markers.set(key, marker);
        created += 1;
      });

      state.activeMarkerKey = null;
      state.hoverKey = null;
      state.hoverKeys.clear();

      repaintMarkers((key) => state.destinationByKey.get(key)?.name || "");
      return created;
    }

    function buildMarkersForResorts(resorts, onMarkerClick) {
      clearMarkers();
      let created = 0;

      resorts.forEach(r => {
        const center = r?.center;
        if (!center || typeof center.lat !== "number" || typeof center.lng !== "number") return;

        const key = r.id;
        const marker = new google.maps.Marker({
          map: state.map,
          position: center,
          title: r.name,
          icon: buildDotIcon(),
          optimized: true,
        });

        marker.addListener("click", () => onMarkerClick && onMarkerClick(r));
        state.markers.set(key, marker);
        created += 1;
      });

      repaintMarkers((key) => getResortByIdLoose(key)?.name || "");
      return created;
    }

    /* -------------------------
       LAYER: Resorts (Capa 1)
       ------------------------- */

    function setLayerResorts() {
      state.layer = "resorts";
      state.selectedResortId = null;
      state.selectedDestinationId = null;

      updateLayerChrome();

      if (backBtn) backBtn.disabled = true;

      const layer1Label = (state.datasetKey === "comingSoon") ? L.comingSoon : L.resorts;
      if (crumbLabel) crumbLabel.textContent = layer1Label;
      if (pillText) pillText.textContent = `${L.viewing} ${layer1Label}`;

      renderCards(cards, state.resorts, "resorts", {
        badgeText: (state.datasetKey === "comingSoon") ? L.comingSoon : "",
        onHover: (cardEl, on) => {
          if (isMobile()) return;

          const id = cardEl.dataset.id;
          cardEl.classList.toggle("is-hover", !!on);

          // Hover en resort: muestra labels de TODOS los destinos del resort
          const resort = getResortByIdLoose(id);
          const rid = resort?.id ?? id;
          const keys = (resort?.destinations || []).map(d => `${rid}::${d.id}`);
          setHoverMarkers(keys, !!on, (k) => state.destinationByKey.get(k)?.name || "");
        },
      });

      // Markers por defecto (capa Resorts): TODOS los destinos (sin labels)
      const allDestinations = Array.from(state.destinationByKey.values());
      const created = buildMarkersForDestinations(allDestinations, (dest) => {
        if (dest && dest.__resortId != null) selectResort(dest.__resortId);
      });

      if (created > 0) {
        state.markerMode = "destinations";
      } else {
        buildMarkersForResorts(state.resorts, (resort) => selectResort(resort.id));
        state.markerMode = "resorts";
      }

      fitBoundsFromResorts(state.map, state.resorts);

      // Fix render “raro” post-fit
      refreshMapAfterMove(state.map, () => {
        if (state.markerMode === "destinations") {
          repaintMarkers((k) => state.destinationByKey.get(k)?.name || "");
        } else {
          repaintMarkers((k) => getResortByIdLoose(k)?.name || "");
        }
      });

      if (state.markerMode === "destinations") {
        clearHoverMarker((k) => state.destinationByKey.get(k)?.name || "");
      } else {
        clearHoverMarker((k) => getResortByIdLoose(k)?.name || "");
      }
    }

    /* -------------------------
       LAYER: Destinations (Capa 2)
       ------------------------- */

    function setLayerDestinations(resortIdRaw) {
      const resort = getResortByIdLoose(resortIdRaw);
      if (!resort) return;
      const resortId = resort.id;

      state.layer = "destinations";
      state.selectedResortId = resortId;
      state.selectedDestinationId = null;

      updateLayerChrome();

      if (backBtn) backBtn.disabled = false;
      if (crumbLabel) crumbLabel.textContent = (resort.name || L.destinations);
      if (pillText) pillText.textContent = `${L.viewing} ${resort.name || ""}`;

      const destinations = (resort.destinations || []).map(d => {
        const key = `${resortId}::${d.id}`;
        return { ...d, __key: key, __resortId: resortId };
      });

      renderCards(cards, destinations, "destinations", {
        badgeText: (state.datasetKey === "comingSoon") ? L.comingSoon : "",
        onHover: (cardEl, on) => {
          if (isMobile()) return;

          const destId = cardEl.dataset.id;
          const key = `${resortId}::${destId}`;
          cardEl.classList.toggle("is-hover", !!on);
          setHoverMarker(key, !!on, (k) => state.destinationByKey.get(k)?.name || "");
        },
      });

      buildMarkersForDestinations(destinations, (dest) => {
        if ((dest?.properties || []).length) selectDestination(dest.id);
      });
      state.markerMode = "destinations";

      fitBoundsFromResort(state.map, resort);

      refreshMapAfterMove(state.map, () => {
        repaintMarkers((k) => state.destinationByKey.get(k)?.name || "");
      });

      clearHoverMarker((k) => state.destinationByKey.get(k)?.name || "");
    }

    /* -------------------------
       LAYER: Properties (Capa 3)
       ------------------------- */

    function setLayerProperties(resortIdRaw, destinationId) {
      const resort = getResortByIdLoose(resortIdRaw);
      if (!resort) return;
      const resortId = resort.id;

      const key = `${resortId}::${destinationId}`;
      const destination = state.destinationByKey.get(key);
      if (!destination) return;

      state.layer = "properties";
      state.selectedResortId = resortId;
      state.selectedDestinationId = destinationId;

      updateLayerChrome();

      if (backBtn) backBtn.disabled = false;
      const resortName = resort?.name || "";
      const destName = destination?.name || "";
      const layerTitle = (resortName && destName) ? `${resortName} - ${destName}` : (destName || resortName || "");

      if (crumbLabel) crumbLabel.textContent = (layerTitle || L.properties);
      if (pillText) pillText.textContent = `${L.viewing} ${layerTitle || ""}`;

      // SOLO marker del destino seleccionado
      buildMarkersForDestinations([destination], (dest) => {
        if ((dest?.properties || []).length) selectDestination(dest.id);
      });
      state.markerMode = "singleDestination";

      state.activeMarkerKey = key;
      repaintMarkers((k) => state.destinationByKey.get(k)?.name || "");

      fitBoundsFromDestination(state.map, destination);

      refreshMapAfterMove(state.map, () => {
        repaintMarkers((k) => state.destinationByKey.get(k)?.name || "");
      });

      renderCards(cards, destination.properties || [], "properties", {
        badgeText: (state.datasetKey === "comingSoon") ? L.comingSoon : "",
        onHover: (cardEl, on) => {
          if (isMobile()) return;
          cardEl.classList.toggle("is-hover", !!on);

          // Mantén el marker activo/visible
          setHoverMarker(state.activeMarkerKey, !!on, (k) => state.destinationByKey.get(k)?.name || "");
          if (!on) clearHoverMarker((k) => state.destinationByKey.get(k)?.name || "");
        },
      });
    }

    /* -------------------------
       SELECTORS (Flow)
       ------------------------- */

    function selectResort(resortId) {
      // Coming Soon: NO drilldown to Destinations. Only zoom the map to the resort area.
      if (state.datasetKey === "comingSoon") {
        const resort = getResortByIdLoose(resortId);
        if (resort) {
          // Keep layer 1 UI (cards stay as Resorts with destination chips)
          state.layer = "resorts";
          state.selectedResortId = null;
          state.selectedDestinationId = null;
          updateLayerChrome();
          fitBoundsFromResort(state.map, resort);
          refreshMapAfterMove(state.map, () => {
            if (state.markerMode === "destinations") repaintMarkers((k) => state.destinationByKey.get(k)?.name || "");
            else repaintMarkers((k) => getResortByIdLoose(k)?.name || "");
          });
        }
        return;
      }

      setLayerDestinations(resortId);
      if (isMobile()) cards.scrollTo({ left: 0, behavior: "smooth" });
    }

    function selectDestination(destinationId) {
      if (state.datasetKey === "comingSoon") return;
      if (!state.selectedResortId) return;

      const key = `${state.selectedResortId}::${destinationId}`;
      const dest = state.destinationByKey.get(key);
      const nProps = (dest?.properties || []).length;
      if (!nProps) return;

      setLayerProperties(state.selectedResortId, destinationId);
      if (isMobile()) cards.scrollTo({ left: 0, behavior: "smooth" });
    }

    /* -------------------------
       CLICK HANDLERS
       ------------------------- */

    cards.addEventListener("click", (e) => {
      const mediaBtn = e.target.closest("[data-open-card-gallery]");
      if (mediaBtn) {
        e.preventDefault();
        e.stopPropagation();

        const card = mediaBtn.closest(".vima-card");
        if (!card) return;
        if (card.classList.contains("is-disabled") || card.getAttribute("aria-disabled") === "true") return;
        if (card.dataset.mode !== "properties") return;

        const resort = getResortByIdLoose(state.selectedResortId);
        if (!resort) return;

        const dKey = `${state.selectedResortId}::${state.selectedDestinationId}`;
        const destination = state.destinationByKey.get(dKey);
        if (!destination) return;

        const property = (destination.properties || []).find(p => String(p.id) === String(card.dataset.id));
        if (!property || !normalizeGallery(property)) return;

        openGalleryModal(root, property, resort, destination);
        return;
      }

      const card = e.target.closest(".vima-card");
      if (!card) return;
      if (card.classList.contains("is-disabled") || card.getAttribute("aria-disabled") === "true") return;

      const id = card.dataset.id;
      const mode = card.dataset.mode;

      if (mode === "resorts") {
        selectResort(id);
        return;
      }

      if (mode === "destinations") {
        if (state.datasetKey === "comingSoon") return;
        if (!state.selectedResortId) return;
        const key = `${state.selectedResortId}::${id}`;
        const dest = state.destinationByKey.get(key);
        const nProps = (dest?.properties || []).length;
        if (!nProps) return;

        selectDestination(id);
        return;
      }

      if (mode === "properties") {
        const resort = getResortByIdLoose(state.selectedResortId);
        if (!resort) return;

        const dKey = `${state.selectedResortId}::${state.selectedDestinationId}`;
        const destination = state.destinationByKey.get(dKey);
        if (!destination) return;

        const property = (destination.properties || []).find(p => String(p.id) === String(id));
        if (!property) return;

        openModal(root, property, resort, destination);
      }
    });

    cards.addEventListener("keydown", (e) => {
      if (e.key !== "Enter") return;
      if (e.target.closest("[data-open-card-gallery]")) return;
      const card = e.target.closest(".vima-card");
      if (!card) return;
      card.click();
    });

    if (backBtn) {
      backBtn.addEventListener("click", () => {
        if (state.layer === "properties") {
          if (state.selectedResortId) setLayerDestinations(state.selectedResortId);
          else setLayerResorts();
          return;
        }
        if (state.layer === "destinations") {
          setLayerResorts();
          return;
        }
        setLayerResorts();
      });
    }

    if (changeResortBtn) changeResortBtn.addEventListener("click", () => setLayerResorts());

    qsa(root, "[data-modal-close]").forEach(btn => btn.addEventListener("click", () => closeModal(root)));
    qsa(root, "[data-gallery-close]").forEach(btn => btn.addEventListener("click", () => closeGalleryModal(root)));
    document.addEventListener("keydown", (e) => {
      if (e.key !== "Escape") return;
      const galleryModal = document.querySelector("[data-gallery-modal]");
      if (galleryModal && galleryModal.getAttribute("aria-hidden") === "false") {
        closeGalleryModal(root);
        return;
      }
      closeModal(root);
    });

    /* -------------------------
       MOBILE: scroll focus -> pan to nearest item
       ------------------------- */

    if (mobilePill) {
      let t = null;
      cards.addEventListener("scroll", () => {
        if (!isMobile()) return;
        if (t) clearTimeout(t);

        t = setTimeout(() => {
          const list = qsa(cards, ".vima-card");
          if (!list.length) return;

          const containerLeft = cards.scrollLeft;
          let best = null;
          let bestDist = Infinity;

          list.forEach(el => {
            const dist = Math.abs(el.offsetLeft - containerLeft);
            if (dist < bestDist) { bestDist = dist; best = el; }
          });

          if (!best) return;
          const id = best.dataset.id;
          const mode = best.dataset.mode;

          if (mode === "resorts") {
            const resort = getResortByIdLoose(id);
            if (!resort) return;

            const firstDest = (resort.destinations || []).find(d => d?.center);
            if (firstDest?.center) {
              state.map.panTo(firstDest.center);
              setZoomSafe(state.map, clamp(state.map.getZoom() || 6, 6, 10), 6);
            }
            return;
          }

          if (mode === "destinations") {
            const key = `${state.selectedResortId}::${id}`;
            const dest = state.destinationByKey.get(key);
            if (dest?.center) {
              setHoverMarker(key, true, (k) => state.destinationByKey.get(k)?.name || "");
              state.map.panTo(dest.center);
              setZoomSafe(state.map, clamp(state.map.getZoom() || 6, 6, 10), 6);
              setTimeout(() => clearHoverMarker((k) => state.destinationByKey.get(k)?.name || ""), 450);
            }
          }
        }, 140);
      });
    }

    setLayerResorts();
  }

  /* =========================================================
     BOOTSTRAP
     ========================================================= */

  async function bootstrap() {
    if (!CONFIG) {
      warn("[VIMA] CONFIG is null. window.VIMA_LUXURY_MAP_CONFIG no existe.");
      return;
    }

    const roots = qsa(document, '[data-vima-luxury-map="1"]');
    if (!roots.length) {
      warn('[VIMA] No roots found: [data-vima-luxury-map="1"]');
      return;
    }

    log("[VIMA] CONFIG:", CONFIG);
    log("[VIMA] restDataEndpoint:", CONFIG.restDataEndpoint);

    // Ensure Google maps loaded
    if (!window.google || !window.google.maps) {
      await new Promise((resolve, reject) => {
        const start = Date.now();
        const tick = () => {
          if (window.google && window.google.maps) return resolve();
          if (Date.now() - start > 8000) return reject(new Error("Google Maps did not load"));
          requestAnimationFrame(tick);
        };
        tick();
      });
    }

    // 1) URL del JSON via REST
    let dataUrl;
    try {
      dataUrl = await fetchDataUrl();
    } catch (e) {
      err("[VIMA] fetchDataUrl() failed:", e);
      return;
    }

    log("[VIMA] dataUrl returned by restDataEndpoint:", dataUrl);

    // 2) Carga JSON real
    let data;
    try {
      data = await fetchData(dataUrl);
    } catch (e) {
      err("[VIMA] fetchData(dataUrl) failed:", e);
      return;
    }

    log("[VIMA] data loaded:", data);

    // 3) Validación
    const resorts = Array.isArray(data?.resorts) ? data.resorts : null;
    if (!resorts) {
      err("[VIMA] JSON inválido: se esperaba { resorts: [] }. Recibido:", data);
      return;
    }

    log("[VIMA] resorts count:", resorts.length);

    // 4) Debug deep East Cape (opcional)
    const east = resorts.find(r => r?.id === "east-cape");
    log("[VIMA] east-cape object:", east);

    // 5) Init
    roots.forEach(root => initInstance(root, data));
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bootstrap);
  } else {
    bootstrap();
  }
})();
