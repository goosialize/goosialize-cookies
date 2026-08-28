(() => {
  'use strict';

  const ROOT_SELECTOR =
    '[data-goosialize-consent-root]'
    + '[data-goosialize-presentation="appearance"]';

  function integer(value, fallback) {
    const parsed = Number.parseInt(value ?? '', 10);

    return Number.isFinite(parsed)
      ? parsed
      : fallback;
  }

  function boolean(value, fallback = false) {
    if (value === 'true') {
      return true;
    }

    if (value === 'false') {
      return false;
    }

    return fallback;
  }

  function apply(root) {
    const width = window.innerWidth;

    const baseMode =
      root.dataset.goosializeBannerMode
      ?? 'corner-banner';

    let mode = baseMode;

    let edge =
      integer(
        getComputedStyle(root)
          .getPropertyValue('--goo-p-banner-edge'),
        24
      );

    let stack = false;
    let fullWidth = false;

    let alignment =
      root.dataset.goosializeBannerContentAlignment
      ?? 'left';

    const tabletBreakpoint =
      integer(
        root.dataset.goosializeTabletBreakpoint,
        1024
      );

    const mobileBreakpoint =
      integer(
        root.dataset.goosializeMobileBreakpoint,
        760
      );

    if (width <= mobileBreakpoint) {
      const requested =
        root.dataset.goosializeMobileBannerMode
        ?? 'bottom-card';

      mode =
        requested === 'inherit'
          ? baseMode
          : requested;

      edge =
        integer(
          root.dataset.goosializeMobileEdgeSpacing,
          12
        );

      stack =
        boolean(
          root.dataset.goosializeMobileStackButtons,
          true
        );

      fullWidth =
        boolean(
          root.dataset.goosializeMobileFullWidth,
          true
        );

      alignment =
        root.dataset.goosializeMobileContentAlignment
        ?? alignment;
    } else if (width <= tabletBreakpoint) {
      const requested =
        root.dataset.goosializeTabletBannerMode
        ?? 'inherit';

      mode =
        requested === 'inherit'
          ? baseMode
          : requested;

      edge =
        integer(
          root.dataset.goosializeTabletEdgeSpacing,
          20
        );

      stack =
        boolean(
          root.dataset.goosializeTabletStackButtons,
          false
        );

      fullWidth =
        boolean(
          root.dataset.goosializeTabletFullWidth,
          false
        );

      alignment =
        root.dataset.goosializeTabletContentAlignment
        ?? alignment;
    }

    root.dataset.goosializeEffectiveBannerMode =
      mode;

    root.dataset.goosializeEffectiveStackButtons =
      stack ? 'true' : 'false';

    root.dataset.goosializeEffectiveFullWidth =
      fullWidth ? 'true' : 'false';

    root.dataset.goosializeEffectiveContentAlignment =
      alignment;

    root.style.setProperty(
      '--goo-p-effective-edge',
      `${Math.max(0, Math.min(edge, 96))}px`
    );
  }

  function init() {
    const root =
      document.querySelector(ROOT_SELECTOR);

    if (!root) {
      return;
    }

    apply(root);

    let queued = false;

    window.addEventListener(
      'resize',
      () => {
        if (queued) {
          return;
        }

        queued = true;

        window.requestAnimationFrame(() => {
          queued = false;
          apply(root);
        });
      },
      { passive: true }
    );
  }

  if (document.readyState === 'loading') {
    document.addEventListener(
      'DOMContentLoaded',
      init,
      { once: true }
    );
  } else {
    init();
  }
})();
