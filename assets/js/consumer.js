(() => {
  'use strict';

  const READY_EVENT =
    'goosialize:consent-ready';

  const CHANGE_EVENT =
    'goosialize:consent-changed';

  function runtime() {
    return window.GoosializeConsent
      ?? null;
  }

  function isReady() {
    const consent = runtime();

    return Boolean(
      consent
      && typeof consent.isReady === 'function'
      && consent.isReady()
    );
  }

  function getSnapshot() {
    const consent = runtime();

    if (
      !consent
      || typeof consent.getSnapshot
        !== 'function'
    ) {
      return null;
    }

    return consent.getSnapshot();
  }

  function has(category) {
    const consent = runtime();

    if (
      !consent
      || typeof consent.has
        !== 'function'
    ) {
      return false;
    }

    return consent.has(category) === true;
  }

  function whenReady(callback) {
    if (typeof callback !== 'function') {
      throw new TypeError(
        'Consent consumer callback must be a function.'
      );
    }

    if (isReady()) {
      callback(
        getSnapshot()
      );

      return () => {};
    }

    const listener = (event) => {
      callback(
        event?.detail?.current
        ?? null
      );
    };

    window.addEventListener(
      READY_EVENT,
      listener,
      {
        once: true
      }
    );

    return () => {
      window.removeEventListener(
        READY_EVENT,
        listener
      );
    };
  }

  function onChange(callback) {
    if (typeof callback !== 'function') {
      throw new TypeError(
        'Consent consumer callback must be a function.'
      );
    }

    const listener = (event) => {
      callback(
        event?.detail?.current
        ?? null,
        event?.detail?.previous
        ?? null
      );
    };

    window.addEventListener(
      CHANGE_EVENT,
      listener
    );

    return () => {
      window.removeEventListener(
        CHANGE_EVENT,
        listener
      );
    };
  }

  window.GoosializeConsentConsumer =
    Object.freeze({
      isReady,
      getSnapshot,
      has,
      whenReady,
      onChange
    });
})();
