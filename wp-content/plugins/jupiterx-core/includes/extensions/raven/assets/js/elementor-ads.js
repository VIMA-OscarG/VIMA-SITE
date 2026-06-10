(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
/* eslint no-undef: 0 */

(function ($, window) {
  /**
   * Hide Pro/subscription kit items in the Kit Library app.
   * Polls for React-rendered badges and removes their parent cards.
   */
  function hideProKits() {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const page = urlParams.get('page');
    if ('elementor-app' !== page) {
      return;
    }
    if (window.elementorAppConfig && true === window.elementorAppConfig.is_pro) {
      return;
    }

    // Use a MutationObserver so we catch React re-renders without polling.
    const observer = new MutationObserver(() => {
      // Current class name (as of Elementor 3.x / 4.x).
      $('.e-kit-library__kit-item-subscription-plan-badge').closest('.e-kit-library__kit-item').hide();

      // Fallback: any element that contains "upgrade-badge" descendant inside kit items.
      $('.upgrade-badge').closest('[class*="kit-item"]').hide();

      // Import / Export → Export customization: Pro upsell row for "Templates" kit part.
      $('[data-testid="KitPartsSelectionRow-templates"]').hide();
    });
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });

    // Stop observing after 30 s to avoid overhead on long sessions.
    setTimeout(() => observer.disconnect(), 30000);
  }

  /**
   * Switch to the Jupiter X templates tab when the template library opens.
   */
  function forceJxTemplateTab() {
    $(document).on('click', '.elementor-add-template-button', function () {
      const interval = setInterval(() => {
        const $menu = $(window.parent.document).find('#elementor-template-library-header-menu');
        if ($menu.length > 0) {
          $menu.find('div[data-tab="library/templatesJX"]').trigger('click');
          clearInterval(interval);
        }
      }, 100);
    });
  }

  /**
   * Remove any remaining upgrade/connect links from the admin top bar
   * that CSS alone may not catch due to dynamic rendering.
   */
  function cleanAdminTopBar() {
    const $root = $('#e-admin-top-bar-root');
    if (!$root.length) {
      return;
    }

    // Hide "Upgrade Now" / crown-icon button.
    $root.find('.eicon-upgrade-crown').closest('a, button').hide();

    // Hide secondary-area buttons without a data-info attribute (Apps, Connect…).
    $root.find('.e-admin-top-bar__secondary-area-buttons a:not([data-info])').hide();
    $root.find('.e-admin-top-bar__secondary-area > a[data-info*="Connect"]').hide();
  }

  /**
   * Hide the "Upgrade plan" CTA in the editor-one sidebar navigation.
   * Works as a JS fallback alongside the CSS and the PHP config override.
   */
  function suppressSidebarUpgradeCta() {
    if (!document.getElementById('editor-one-sidebar-navigation')) {
      return;
    }

    // Override the localized config so the React component never renders the CTA.
    if (window.editorOneSidebarConfig) {
      window.editorOneSidebarConfig.hasPro = true;
      window.editorOneSidebarConfig.upgradeUrl = '';
      window.editorOneSidebarConfig.upgradeText = '';
    }
    const hideEditorSidebarHeaderRow = root => {
      // DOM: #editor-one-sidebar-navigation > nav.MuiBox-root > div (first) — Editor title row.
      // jQuery .hide() loses to MUI/Emotion; !important matches other suppressors in this file.
      const nav = root.querySelector(':scope > nav');
      if (!nav) {
        return;
      }
      const headerRow = nav.querySelector(':scope > div');
      if (headerRow) {
        headerRow.style.setProperty('display', 'none', 'important');
      }
    };
    const runSidebarSuppress = () => {
      const root = document.getElementById('editor-one-sidebar-navigation');
      if (!root) {
        return;
      }
      $(root).find('.MuiButton-colorPromotion, .MuiButton-containedPromotion, a[href*="go-pro"], a[href*="go.elementor.com"]').each(function () {
        this.style.setProperty('display', 'none', 'important');
      });
      hideEditorSidebarHeaderRow(root);
    };
    runSidebarSuppress();
    const observer = new MutationObserver(runSidebarSuppress);
    observer.observe(document.getElementById('editor-one-sidebar-navigation'), {
      childList: true,
      subtree: true
    });
    setTimeout(() => observer.disconnect(), 60000);
  }

  /**
   * Remove "Upgrade" items from the WP admin Elementor menu.
   * Targets both legacy and editor-one slugs in the standard WP menu DOM.
   */
  function removeAdminMenuUpgradeItem() {
    const upgradeHrefPatterns = ['go_elementor_pro', 'admin_menu_promo', 'go.elementor.com'];
    const $menus = $('#adminmenu #toplevel_page_elementor, #adminmenu #toplevel_page_elementor-home, #adminmenu #toplevel_page_elementor-settings');
    $menus.find('a').each(function () {
      const href = $(this).attr('href') || '';
      if (upgradeHrefPatterns.some(p => href.includes(p))) {
        $(this).closest('li').hide();
      }
    });
  }

  /**
   * Hide React-rendered promotion cards that may mount after page load.
   */
  function hideReactPromotions() {
    const observer = new MutationObserver(() => {
      $('[data-testid="e-promotion-card"]').closest('.e-promotion-react-wrapper, [data-promotion]').hide();
    });
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
    setTimeout(() => observer.disconnect(), 30000);
  }

  /**
   * Editor V2 — Elementor logo popover: hide Theme Builder, Help Center, My Elementor / Connect.
   * The menu is rendered in a MUI portal on `document.body`, not under `#elementor-editor-wrapper-v2`,
   * so we run when the user opens it (logo toggle click) and retry after paint.
   */
  function suppressEditorV2LogoDropdown() {
    if (!document.body.classList.contains('elementor-editor-active')) {
      return;
    }
    const hideInMenu = menu => {
      if (!menu || !menu.matches('[role="menu"]')) {
        return;
      }

      // Help Center + My Elementor (stable go.elementor.com URLs from Elementor core).
      menu.querySelectorAll('a.MuiMenuItem-root[href*="go.elementor.com/editor-top-bar-learn"], ' + 'a.MuiMenuItem-root[href*="go.elementor.com/wp-dash-top-bar-account"]').forEach(el => {
        el.style.setProperty('display', 'none', 'important');
      });

      // "Connect my account" (Elementor Connect authorize URL).
      menu.querySelectorAll('a.MuiMenuItem-root[href*="elementor.com"][href*="authorize"]').forEach(el => {
        el.style.setProperty('display', 'none', 'important');
      });

      // Theme Builder — second row after "Site Settings" (registerAction id `open-theme-builder`).
      const directItems = menu.querySelectorAll(':scope > .MuiMenuItem-root');
      if (directItems.length >= 2) {
        directItems[1].style.setProperty('display', 'none', 'important');
      }

      // Divider that only introduced the Help Center block.
      menu.querySelectorAll(':scope > hr.MuiDivider').forEach(hr => {
        const next = hr.nextElementSibling;
        if (next && next.matches('a[href*="editor-top-bar-learn"]')) {
          hr.style.setProperty('display', 'none', 'important');
        }
      });
    };
    const findLogoMenus = () => document.querySelectorAll('.MuiPopover-paper.MuiMenu-paper .MuiMenu-list[role="menu"], ' + '.MuiPopover-paper .MuiMenu-list[role="menu"]');
    const runHide = () => {
      findLogoMenus().forEach(hideInMenu);
    };
    const isElementorLogoButton = target => {
      const btn = target.closest('button[aria-haspopup="true"].MuiToggleButton-root');
      if (!btn) {
        return false;
      }
      const titleEl = btn.querySelector('svg title');
      return titleEl && /elementor\s*logo/i.test((titleEl.textContent || '').trim());
    };
    let afterClickObserver = null;
    document.addEventListener('click', event => {
      if (!isElementorLogoButton(event.target)) {
        return;
      }
      [0, 16, 32, 64, 100, 200, 400].forEach(ms => setTimeout(runHide, ms));
      if (afterClickObserver) {
        afterClickObserver.disconnect();
      }
      afterClickObserver = new MutationObserver(runHide);
      afterClickObserver.observe(document.body, {
        childList: true,
        subtree: true
      });
      setTimeout(() => {
        if (afterClickObserver) {
          afterClickObserver.disconnect();
          afterClickObserver = null;
        }
      }, 4000);
    }, true);
  }
  function init() {
    // Kit library + kit import/export screens live under elementor-app (not always `sticky-menu`).
    hideProKits();

    // Editor V2 logo menu (runs even if `#elementor-editor-wrapper-v2` mounts after `load`).
    suppressEditorV2LogoDropdown();
    if ($('body').hasClass('theme-jupiterx')) {
      forceJxTemplateTab();
      cleanAdminTopBar();
      hideReactPromotions();
      suppressSidebarUpgradeCta();
      removeAdminMenuUpgradeItem();
    }
  }
  $(window).on('load', init);
})(jQuery, window);

},{}]},{},[1]);
