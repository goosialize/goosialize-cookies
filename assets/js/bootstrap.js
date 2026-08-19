(() => {
  'use strict';

  function boot() {
    if (!window.GoosializeConsent) {
      return;
    }

    const config =
      window.GoosializeConsentConfig ?? {};

    window.GoosializeConsent.initialize(
      config
    );
  }

  if (document.readyState === 'loading') {
    document.addEventListener(
      'DOMContentLoaded',
      boot,
      { once: true }
    );
  } else {
    boot();
  }
})();
