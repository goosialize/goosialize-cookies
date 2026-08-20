(() => {
  'use strict';

  const DEFAULT_SIGNALS = Object.freeze({
    analytics_storage: 'denied',
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied'
  });

  let initialized = false;

  function ensureQueue() {
    window.dataLayer =
      window.dataLayer || [];

    if (
      typeof window.gtag !== 'function'
    ) {
      window.gtag = function gtag() {
        window.dataLayer.push(
          arguments
        );
      };
    }

    return window.gtag;
  }

  function command(
    action,
    signals
  ) {
    ensureQueue()(
      'consent',
      action,
      signals
    );
  }

  function mapSnapshot(snapshot) {
    const categories =
      snapshot?.categories ?? {};

    const analytics =
      categories.analytics === true;

    const marketing =
      categories.marketing === true;

    return {
      analytics_storage:
        analytics
          ? 'granted'
          : 'denied',

      ad_storage:
        marketing
          ? 'granted'
          : 'denied',

      ad_user_data:
        marketing
          ? 'granted'
          : 'denied',

      ad_personalization:
        marketing
          ? 'granted'
          : 'denied'
    };
  }

  function applySnapshot(snapshot) {
    command(
      'update',
      mapSnapshot(snapshot)
    );
  }

  function onReady(event) {
    applySnapshot(
      event?.detail?.current
      ?? null
    );
  }

  function onChanged(event) {
    applySnapshot(
      event?.detail?.current
      ?? null
    );
  }

  function init() {
    if (initialized) {
      return;
    }

    /*
     * Consent Mode default must exist before
     * any Google consumer is allowed to run.
     */
    command(
      'default',
      {
        ...DEFAULT_SIGNALS
      }
    );

    window.addEventListener(
      'goosialize:consent-ready',
      onReady
    );

    window.addEventListener(
      'goosialize:consent-changed',
      onChanged
    );

    initialized = true;
  }

  window.GoosializeGoogleConsentMode =
    Object.freeze({
      init,

      mapSnapshot(snapshot) {
        return {
          ...mapSnapshot(snapshot)
        };
      },

      getDefaultSignals() {
        return {
          ...DEFAULT_SIGNALS
        };
      }
    });

  init();
})();
