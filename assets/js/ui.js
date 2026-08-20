(() => {
  'use strict';

  const ROOT_SELECTOR =
    '[data-goosialize-consent-root]';

  const OPTIONAL_CATEGORIES = [
    'preferences',
    'analytics',
    'marketing'
  ];

  let initialized = false;
  let root = null;
  let banner = null;
  let bannerDescription = null;
  let backdrop = null;
  let modal = null;
  let settings = null;
  let status = null;
  let returnFocus = null;

  function consent() {
    return window.GoosializeConsent ?? null;
  }

  function query(selector) {
    return root?.querySelector(selector) ?? null;
  }

  function queryAll(selector) {
    return [
      ...(root?.querySelectorAll(selector) ?? [])
    ];
  }

  function focusableElements() {
    if (!modal) {
      return [];
    }

    return [
      ...modal.querySelectorAll(
        [
          'button:not([disabled])',
          'input:not([disabled])',
          'a[href]',
          '[tabindex]:not([tabindex="-1"])'
        ].join(',')
      )
    ].filter(element => {
      return !element.hidden;
    });
  }

  function setHidden(element, hidden) {
    if (!element) {
      return;
    }

    element.hidden = hidden;
  }

  function snapshot() {
    return consent()?.getSnapshot?.() ?? null;
  }

  function state() {
    return consent()?.getState?.() ?? 'unknown';
  }

  function lifecycleStatus() {
    return consent()?.getLifecycleStatus?.()
      ?? 'missing';
  }

  function syncBannerMessage() {
    if (!bannerDescription || !root) {
      return;
    }

    const lifecycle =
      lifecycleStatus();

    let message =
      root.dataset.bannerDefaultMessage
      ?? '';

    if (lifecycle === 'expired') {
      message =
        root.dataset.reconsentExpiredMessage
        ?? message;
    } else if (
      lifecycle === 'version_mismatch'
    ) {
      message =
        root.dataset.reconsentVersionMessage
        ?? message;
    }

    bannerDescription.textContent =
      message;
  }

  function syncToggles() {
    const current = snapshot();

    for (const category of OPTIONAL_CATEGORIES) {
      const input = query(
        `[data-goosialize-consent-category="${category}"]`
      );

      if (!input) {
        continue;
      }

      input.checked =
        current?.categories?.[category] === true;
    }
  }

  function syncVisibility() {
    const unknown =
      state() === 'unknown';

    setHidden(banner, !unknown);
    setHidden(settings, unknown);

    if (unknown && backdrop && !backdrop.hidden) {
      closeModal(false);
    }
  }

  function sync() {
    syncToggles();
    syncBannerMessage();
    syncVisibility();
  }

  function openModal(trigger = null) {
    if (!backdrop || !modal) {
      return;
    }

    returnFocus =
      trigger instanceof HTMLElement
        ? trigger
        : document.activeElement;

    syncToggles();

    setHidden(backdrop, false);

    document.documentElement.classList.add(
      'goo-consent-modal-open'
    );

    modal.focus();
  }

  function closeModal(restoreFocus = true) {
    if (!backdrop) {
      return;
    }

    setHidden(backdrop, true);

    document.documentElement.classList.remove(
      'goo-consent-modal-open'
    );

    if (
      restoreFocus &&
      returnFocus &&
      typeof returnFocus.focus === 'function'
    ) {
      returnFocus.focus();
    }

    returnFocus = null;
  }

  function selectedPreferences() {
    const selected = {
      necessary: true
    };

    for (const category of OPTIONAL_CATEGORIES) {
      const input = query(
        `[data-goosialize-consent-category="${category}"]`
      );

      selected[category] =
        input?.checked === true;
    }

    return selected;
  }

  function announce(message = '') {
    if (!status) {
      return;
    }

    status.textContent = '';

    window.setTimeout(() => {
      status.textContent = message;
    }, 10);
  }

  function completeDecision() {
    closeModal(false);
    sync();

    window.requestAnimationFrame(() => {
      settings?.focus();
    });
  }

  function acceptAll() {
    consent()?.acceptAll?.();
    completeDecision();
  }

  function rejectAll() {
    consent()?.rejectAll?.();
    completeDecision();
  }

  function savePreferences() {
    consent()?.savePreferences?.(
      selectedPreferences()
    );

    completeDecision();

    announce(
      root?.dataset.savedMessage ?? ''
    );
  }

  function actionFrom(target) {
    return target.closest(
      '[data-goosialize-consent-action]'
    );
  }

  function onClick(event) {
    const button =
      actionFrom(event.target);

    if (!button || !root.contains(button)) {
      return;
    }

    const action =
      button.dataset.goosializeConsentAction;

    switch (action) {
      case 'accept-all':
        acceptAll();
        break;

      case 'reject-all':
        rejectAll();
        break;

      case 'manage':
        openModal(button);
        break;

      case 'save':
        savePreferences();
        break;

      case 'close':
        closeModal();
        break;

      default:
        break;
    }
  }

  function trapFocus(event) {
    if (
      event.key !== 'Tab' ||
      !backdrop ||
      backdrop.hidden
    ) {
      return;
    }

    const elements =
      focusableElements();

    if (elements.length === 0) {
      event.preventDefault();
      modal?.focus();
      return;
    }

    const first = elements[0];
    const last =
      elements[elements.length - 1];

    if (
      event.shiftKey &&
      document.activeElement === first
    ) {
      event.preventDefault();
      last.focus();
      return;
    }

    if (
      !event.shiftKey &&
      document.activeElement === last
    ) {
      event.preventDefault();
      first.focus();
    }
  }

  function onKeydown(event) {
    if (
      event.key === 'Escape' &&
      backdrop &&
      !backdrop.hidden
    ) {
      event.preventDefault();
      closeModal();
      return;
    }

    trapFocus(event);
  }

  function onBackdropClick(event) {
    if (
      event.target === backdrop
    ) {
      closeModal();
    }
  }

  function bindConsentEvents() {
    window.addEventListener(
      'goosialize:consent-ready',
      sync
    );

    window.addEventListener(
      'goosialize:consent-changed',
      sync
    );
  }

  function init() {
    if (initialized) {
      sync();
      return;
    }

    root = document.querySelector(
      ROOT_SELECTOR
    );

    if (!root) {
      return;
    }

    banner = query(
      '[data-goosialize-consent-banner]'
    );

    backdrop = query(
      '[data-goosialize-consent-backdrop]'
    );

    bannerDescription = query(
      '[data-goosialize-consent-banner-description]'
    );

    modal = query(
      '[data-goosialize-consent-modal]'
    );

    settings = query(
      '[data-goosialize-consent-settings]'
    );

    status = query(
      '[data-goosialize-consent-status]'
    );

    root.addEventListener(
      'click',
      onClick
    );

    backdrop?.addEventListener(
      'click',
      onBackdropClick
    );

    document.addEventListener(
      'keydown',
      onKeydown
    );

    bindConsentEvents();

    initialized = true;

    sync();
  }

  window.GoosializeConsentUI =
    Object.freeze({
      init,
      open() {
        init();
        openModal(settings);
      },
      close() {
        closeModal();
      },
      sync
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
