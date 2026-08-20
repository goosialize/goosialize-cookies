(() => {
  'use strict';

  const RUNTIME_VERSION = '1.0.0';

  const DEFAULT_CONFIG = Object.freeze({
    consentVersion: 1,
    lifetimeDays: 180,
    cookieName: 'goosialize_consent'
  });

  const CATEGORIES = Object.freeze([
    'necessary',
    'preferences',
    'analytics',
    'marketing'
  ]);

  const OPTIONAL_CATEGORIES = Object.freeze([
    'preferences',
    'analytics',
    'marketing'
  ]);

  const MALFORMED_COOKIE =
    Symbol('goosialize-consent-malformed-cookie');

  let config = { ...DEFAULT_CONFIG };

  let snapshot = null;
  let ready = false;
  let lifecycleStatus = 'missing';

  function nowIso() {
    return new Date().toISOString();
  }

  function defaultCategories() {
    return {
      necessary: true,
      preferences: false,
      analytics: false,
      marketing: false
    };
  }

  function normalizeCategories(input = {}) {
    if (
      input === null ||
      typeof input !== 'object' ||
      Array.isArray(input)
    ) {
      throw new TypeError(
        'Consent categories must be an object.'
      );
    }

    for (const key of Object.keys(input)) {
      if (!CATEGORIES.includes(key)) {
        throw new RangeError(
          `Unknown consent category: ${key}`
        );
      }
    }

    const normalized = defaultCategories();

    for (const category of OPTIONAL_CATEGORIES) {
      if (!(category in input)) {
        continue;
      }

      if (typeof input[category] !== 'boolean') {
        throw new TypeError(
          `Consent category ${category} must be boolean.`
        );
      }

      normalized[category] = input[category];
    }

    normalized.necessary = true;

    return normalized;
  }

  function stateFromCategories(categories) {
    const optionalGranted = OPTIONAL_CATEGORIES.filter(
      category => categories[category] === true
    );

    if (optionalGranted.length === OPTIONAL_CATEGORIES.length) {
      return 'accepted_all';
    }

    if (optionalGranted.length === 0) {
      return 'rejected_optional';
    }

    return 'custom';
  }

  function createSnapshot(categories, timestamp = nowIso()) {
    const normalized = normalizeCategories(categories);

    return {
      version: config.consentVersion,
      timestamp,
      categories: normalized,
      state: stateFromCategories(normalized)
    };
  }

  function clone(value) {
    return JSON.parse(JSON.stringify(value));
  }

  function isValidVersion(value) {
    return Number.isInteger(value) &&
      value >= 1 &&
      value === config.consentVersion;
  }

  function isValidTimestamp(value) {
    if (typeof value !== 'string' || value === '') {
      return false;
    }

    const time = Date.parse(value);

    if (!Number.isFinite(time)) {
      return false;
    }

    const maxAgeMs =
      config.lifetimeDays * 24 * 60 * 60 * 1000;

    const ageMs = Date.now() - time;

    if (ageMs < 0) {
      return false;
    }

    return ageMs <= maxAgeMs;
  }

  function validateStoredSnapshot(value) {
    if (
      value === null ||
      typeof value !== 'object' ||
      Array.isArray(value)
    ) {
      return null;
    }

    if (!isValidVersion(value.version)) {
      return null;
    }

    if (!isValidTimestamp(value.timestamp)) {
      return null;
    }

    let categories;

    try {
      categories = normalizeCategories(
        value.categories
      );
    } catch {
      return null;
    }

    const sourceKeys = Object.keys(
      value.categories ?? {}
    ).sort();

    const expectedKeys = [...CATEGORIES].sort();

    if (
      JSON.stringify(sourceKeys) !==
      JSON.stringify(expectedKeys)
    ) {
      return null;
    }

    if (value.categories.necessary !== true) {
      return null;
    }

    return createSnapshot(
      categories,
      value.timestamp
    );
  }

  function cookieAttributes() {
    const attributes = [
      'Path=/',
      'SameSite=Lax'
    ];

    if (window.location.protocol === 'https:') {
      attributes.push('Secure');
    }

    return attributes;
  }

  function cookieExpiry() {
    const expires = new Date();

    expires.setTime(
      expires.getTime() +
      config.lifetimeDays * 24 * 60 * 60 * 1000
    );

    return expires.toUTCString();
  }

  function writeCookie(value) {
    const encoded = encodeURIComponent(
      JSON.stringify(value)
    );

    document.cookie = [
      `${config.cookieName}=${encoded}`,
      `Expires=${cookieExpiry()}`,
      ...cookieAttributes()
    ].join('; ');
  }

  function deleteCookie() {
    document.cookie = [
      `${config.cookieName}=`,
      'Expires=Thu, 01 Jan 1970 00:00:00 GMT',
      ...cookieAttributes()
    ].join('; ');
  }

  function readCookie() {
    const prefix = `${config.cookieName}=`;

    const item = document.cookie
      .split(';')
      .map(part => part.trim())
      .find(part => part.startsWith(prefix));

    if (!item) {
      return null;
    }

    const encoded = item.substring(prefix.length);

    try {
      return JSON.parse(
        decodeURIComponent(encoded)
      );
    } catch {
      return MALFORMED_COOKIE;
    }
  }

  function dispatch(name, detail) {
    window.dispatchEvent(
      new CustomEvent(name, {
        detail: clone(detail)
      })
    );
  }

  function categoryChanges(previous, next) {
    const changes = [];

    for (const category of OPTIONAL_CATEGORIES) {
      const before =
        previous?.categories?.[category] ?? false;

      const after =
        next?.categories?.[category] ?? false;

      if (before !== after) {
        changes.push({
          category,
          before,
          after
        });
      }
    }

    return changes;
  }

  function apply(next, persist = true) {
    const previous = snapshot
      ? clone(snapshot)
      : null;

    snapshot = clone(next);
    lifecycleStatus = 'valid';

    if (persist) {
      writeCookie(snapshot);
    }

    const changes = categoryChanges(
      previous,
      snapshot
    );

    for (const change of changes) {
      dispatch(
        change.after
          ? 'goosialize:consent-granted'
          : 'goosialize:consent-revoked',
        {
          category: change.category,
          previous,
          current: snapshot
        }
      );
    }

    dispatch(
      'goosialize:consent-changed',
      {
        previous,
        current: snapshot
      }
    );

    return clone(snapshot);
  }

  function restore() {
    const raw = readCookie();

    if (raw === null) {
      snapshot = null;
      lifecycleStatus = 'missing';

      return null;
    }

    if (raw === MALFORMED_COOKIE) {
      snapshot = null;
      lifecycleStatus = 'malformed';

      deleteCookie();

      return null;
    }

    if (
      typeof raw !== 'object'
      || Array.isArray(raw)
    ) {
      snapshot = null;
      lifecycleStatus = 'malformed';

      deleteCookie();

      return null;
    }

    if (
      !Number.isInteger(raw.version)
      || raw.version < 1
    ) {
      snapshot = null;
      lifecycleStatus = 'malformed';

      deleteCookie();

      return null;
    }

    if (
      raw.version !== config.consentVersion
    ) {
      snapshot = null;
      lifecycleStatus = 'version_mismatch';

      deleteCookie();

      return null;
    }

    if (
      typeof raw.timestamp !== 'string'
      || raw.timestamp === ''
    ) {
      snapshot = null;
      lifecycleStatus = 'malformed';

      deleteCookie();

      return null;
    }

    const time =
      Date.parse(raw.timestamp);

    if (!Number.isFinite(time)) {
      snapshot = null;
      lifecycleStatus = 'malformed';

      deleteCookie();

      return null;
    }

    const ageMs =
      Date.now() - time;

    if (ageMs < 0) {
      snapshot = null;
      lifecycleStatus = 'future_timestamp';

      deleteCookie();

      return null;
    }

    const maxAgeMs =
      config.lifetimeDays
      * 24
      * 60
      * 60
      * 1000;

    if (ageMs > maxAgeMs) {
      snapshot = null;
      lifecycleStatus = 'expired';

      deleteCookie();

      return null;
    }

    const restored =
      validateStoredSnapshot(raw);

    if (!restored) {
      snapshot = null;
      lifecycleStatus = 'malformed';

      deleteCookie();

      return null;
    }

    snapshot = restored;
    lifecycleStatus = 'valid';

    return clone(snapshot);
  }

  function initialize(options = {}) {
    if (
      options === null ||
      typeof options !== 'object' ||
      Array.isArray(options)
    ) {
      throw new TypeError(
        'Consent configuration must be an object.'
      );
    }

    const next = {
      ...DEFAULT_CONFIG,
      ...options
    };

    if (
      !Number.isInteger(next.consentVersion) ||
      next.consentVersion < 1
    ) {
      throw new RangeError(
        'Consent version must be >= 1.'
      );
    }

    if (
      !Number.isInteger(next.lifetimeDays) ||
      next.lifetimeDays < 1
    ) {
      throw new RangeError(
        'Consent lifetime must be >= 1 day.'
      );
    }

    if (
      typeof next.cookieName !== 'string' ||
      next.cookieName.trim() === ''
    ) {
      throw new TypeError(
        'Consent cookie name cannot be empty.'
      );
    }

    config = {
      consentVersion: next.consentVersion,
      lifetimeDays: next.lifetimeDays,
      cookieName: next.cookieName
    };

    restore();

    ready = true;

    dispatch(
      'goosialize:consent-ready',
      {
        current: snapshot
          ? clone(snapshot)
          : null
      }
    );

    return snapshot
      ? clone(snapshot)
      : null;
  }

  function getLifecycleStatus() {
    return lifecycleStatus;
  }

  function getState() {
    if (!snapshot) {
      return 'unknown';
    }

    return snapshot.state;
  }

  function getSnapshot() {
    return snapshot
      ? clone(snapshot)
      : null;
  }

  function has(category) {
    if (!CATEGORIES.includes(category)) {
      return false;
    }

    if (category === 'necessary') {
      return true;
    }

    return snapshot?.categories?.[category] === true;
  }

  function acceptAll() {
    return apply(
      createSnapshot({
        necessary: true,
        preferences: true,
        analytics: true,
        marketing: true
      })
    );
  }

  function rejectAll() {
    return apply(
      createSnapshot(defaultCategories())
    );
  }

  function savePreferences(categories) {
    return apply(
      createSnapshot(categories)
    );
  }

  function clear() {
    const previous = snapshot
      ? clone(snapshot)
      : null;

    if (previous) {
      for (const category of OPTIONAL_CATEGORIES) {
        if (previous.categories[category] === true) {
          dispatch(
            'goosialize:consent-revoked',
            {
              category,
              previous,
              current: null
            }
          );
        }
      }
    }

    deleteCookie();
    snapshot = null;
    lifecycleStatus = 'missing';

    dispatch(
      'goosialize:consent-changed',
      {
        previous,
        current: null
      }
    );
  }

  window.GoosializeConsent = Object.freeze({
    version: RUNTIME_VERSION,

    categories: CATEGORIES,

    initialize,

    isReady() {
      return ready;
    },

    getState,
    getLifecycleStatus,
    getSnapshot,
    has,
    acceptAll,
    rejectAll,
    savePreferences,
    clear
  });
})();
