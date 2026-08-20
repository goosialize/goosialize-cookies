(() => {
  'use strict';

  const SELECTOR =
    'script[type="text/plain"][data-goosialize-consent]';

  const ALLOWED_CATEGORIES = Object.freeze([
    'preferences',
    'analytics',
    'marketing'
  ]);

  const activatedNodes = new WeakSet();
  const activatedIds = new Set();

  let initialized = false;
  let observer = null;

  function consent() {
    return window.GoosializeConsent ?? null;
  }

  function isKnownCategory(category) {
    return ALLOWED_CATEGORIES.includes(category);
  }

  function isGranted(category) {
    return (
      isKnownCategory(category) &&
      consent()?.has?.(category) === true
    );
  }

  function definitionId(script) {
    const value =
      script.dataset.goosializeConsentId;

    if (
      typeof value !== 'string' ||
      value.trim() === ''
    ) {
      return null;
    }

    return value.trim();
  }

  function externalSource(script) {
    const value =
      script.dataset.goosializeConsentSrc;

    if (
      typeof value !== 'string' ||
      value.trim() === ''
    ) {
      return null;
    }

    return value.trim();
  }

  function inlineCode(script) {
    const value =
      script.textContent ?? '';

    return value.trim() === ''
      ? null
      : value;
  }

  function alreadyActivated(script) {
    if (activatedNodes.has(script)) {
      return true;
    }

    const id = definitionId(script);

    return (
      id !== null &&
      activatedIds.has(id)
    );
  }

  function rememberActivation(script) {
    activatedNodes.add(script);

    const id = definitionId(script);

    if (id !== null) {
      activatedIds.add(id);
    }
  }

  function copyAllowedAttributes(
    source,
    target
  ) {
    const attributes = [
      'async',
      'defer',
      'crossorigin',
      'integrity',
      'nonce',
      'referrerpolicy'
    ];

    for (const name of attributes) {
      if (!source.hasAttribute(name)) {
        continue;
      }

      target.setAttribute(
        name,
        source.getAttribute(name) ?? ''
      );
    }
  }

  function executableFrom(script) {
    const src =
      externalSource(script);

    const code =
      inlineCode(script);

    if (
      (src !== null && code !== null) ||
      (src === null && code === null)
    ) {
      return null;
    }

    const executable =
      document.createElement('script');

    copyAllowedAttributes(
      script,
      executable
    );

    if (src !== null) {
      executable.src = src;
    } else {
      executable.textContent = code;
    }

    executable.dataset
      .goosializeConsentActivated =
      'true';

    const id = definitionId(script);

    if (id !== null) {
      executable.dataset
        .goosializeConsentId =
        id;
    }

    return executable;
  }

  function markBlocked(script) {
    if (
      !(script instanceof HTMLScriptElement) ||
      alreadyActivated(script)
    ) {
      return;
    }

    const category =
      script.dataset.goosializeConsent;

    script.dataset
      .goosializeConsentState =
      isKnownCategory(category)
        ? 'blocked'
        : 'invalid';
  }

  function activate(script) {
    if (
      !(script instanceof HTMLScriptElement) ||
      alreadyActivated(script)
    ) {
      return false;
    }

    const category =
      script.dataset.goosializeConsent;

    if (
      !isKnownCategory(category) ||
      !isGranted(category)
    ) {
      markBlocked(script);
      return false;
    }

    const executable =
      executableFrom(script);

    if (!executable) {
      script.dataset
        .goosializeConsentState =
        'invalid';

      return false;
    }

    rememberActivation(script);

    script.dataset
      .goosializeConsentState =
      'activated';

    script.parentNode?.insertBefore(
      executable,
      script.nextSibling
    );

    window.dispatchEvent(
      new CustomEvent(
        'goosialize:script-activated',
        {
          detail: {
            category,
            id: definitionId(script),
            external:
              externalSource(script) !== null
          }
        }
      )
    );

    return true;
  }

  function scan(scope = document) {
    const definitions = [];

    if (
      scope instanceof HTMLScriptElement &&
      scope.matches(SELECTOR)
    ) {
      definitions.push(scope);
    }

    if (
      typeof scope.querySelectorAll ===
      'function'
    ) {
      definitions.push(
        ...scope.querySelectorAll(SELECTOR)
      );
    }

    let count = 0;

    for (const script of definitions) {
      if (activate(script)) {
        count += 1;
      }
    }

    return count;
  }

  function activateCategory(category) {
    if (
      !isKnownCategory(category) ||
      !isGranted(category)
    ) {
      return 0;
    }

    let count = 0;

    for (
      const script of
      document.querySelectorAll(SELECTOR)
    ) {
      if (
        script.dataset
          .goosializeConsent !== category
      ) {
        continue;
      }

      if (activate(script)) {
        count += 1;
      }
    }

    return count;
  }

  function onConsentReady() {
    scan(document);
  }

  function onConsentGranted(event) {
    activateCategory(
      event?.detail?.category
    );
  }

  function onConsentRevoked(event) {
    const category =
      event?.detail?.category;

    if (!isKnownCategory(category)) {
      return;
    }

    for (
      const script of
      document.querySelectorAll(SELECTOR)
    ) {
      if (
        script.dataset
          .goosializeConsent !== category
      ) {
        continue;
      }

      if (!alreadyActivated(script)) {
        markBlocked(script);
      }
    }
  }

  function observeDynamicDefinitions() {
    if (
      typeof MutationObserver !==
      'function'
    ) {
      return;
    }

    observer =
      new MutationObserver(records => {
        for (const record of records) {
          for (
            const node of
            record.addedNodes
          ) {
            if (!(node instanceof Element)) {
              continue;
            }

            scan(node);
          }
        }
      });

    observer.observe(
      document.documentElement,
      {
        childList: true,
        subtree: true
      }
    );
  }

  function init() {
    if (initialized) {
      scan(document);
      return;
    }

    window.addEventListener(
      'goosialize:consent-ready',
      onConsentReady
    );

    window.addEventListener(
      'goosialize:consent-granted',
      onConsentGranted
    );

    window.addEventListener(
      'goosialize:consent-revoked',
      onConsentRevoked
    );

    scan(document);
    observeDynamicDefinitions();

    initialized = true;
  }

  window.GoosializeConsentBlocker =
    Object.freeze({
      init,
      scan,
      activateCategory,

      isAllowedCategory(category) {
        return isKnownCategory(category);
      }
    });

  if (
    document.readyState === 'loading'
  ) {
    document.addEventListener(
      'DOMContentLoaded',
      init,
      { once: true }
    );
  } else {
    init();
  }
})();
