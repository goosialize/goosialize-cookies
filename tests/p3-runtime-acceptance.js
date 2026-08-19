'use strict';

const fs = require('fs');
const vm = require('vm');

const runtimeSource = fs.readFileSync(
  'assets/js/consent.js',
  'utf8'
);

const listeners = new Map();

let cookieStore = '';

class CustomEvent {
  constructor(type, options = {}) {
    this.type = type;
    this.detail = options.detail;
  }
}

const windowObject = {
  location: {
    protocol: 'https:'
  },

  addEventListener(type, callback) {
    if (!listeners.has(type)) {
      listeners.set(type, []);
    }

    listeners.get(type).push(callback);
  },

  dispatchEvent(event) {
    const callbacks =
      listeners.get(event.type) ?? [];

    for (const callback of callbacks) {
      callback(event);
    }

    return true;
  }
};

const documentObject = {};

Object.defineProperty(
  documentObject,
  'cookie',
  {
    get() {
      return cookieStore;
    },

    set(value) {
      const first = value.split(';')[0];
      const [name, ...rest] = first.split('=');
      const cookieValue = rest.join('=');

      if (value.includes(
        'Expires=Thu, 01 Jan 1970'
      )) {
        cookieStore = '';
        return;
      }

      /*
       * Preserve relevant attributes as well so that the
       * acceptance harness can verify the Secure contract.
       */
      cookieStore = value;
    }
  }
);

const context = vm.createContext({
  window: windowObject,
  document: documentObject,
  CustomEvent,
  console,
  Date,
  JSON,
  Object,
  Array,
  Number,
  String,
  Boolean,
  TypeError,
  RangeError,
  encodeURIComponent,
  decodeURIComponent
});

vm.runInContext(
  runtimeSource,
  context,
  {
    filename: 'consent.js'
  }
);

const consent =
  windowObject.GoosializeConsent;

function assert(condition, label) {
  if (!condition) {
    throw new Error(`FAIL=${label}`);
  }

  console.log(`${label}=PASS`);
}

consent.initialize({
  consentVersion: 1,
  lifetimeDays: 180,
  cookieName: 'goosialize_consent'
});

assert(
  consent.isReady() === true,
  'RUNTIME_READY'
);

assert(
  consent.getState() === 'unknown',
  'INITIAL_STATE_UNKNOWN'
);

assert(
  consent.has('necessary') === true,
  'UNKNOWN_NECESSARY_GRANTED'
);

assert(
  consent.has('analytics') === false,
  'UNKNOWN_ANALYTICS_DENIED'
);

assert(
  consent.has('unknown') === false,
  'UNKNOWN_CATEGORY_FAIL_CLOSED'
);

const rejected =
  consent.rejectAll();

assert(
  rejected.state === 'rejected_optional',
  'REJECT_ALL_STATE'
);

assert(
  consent.has('necessary') === true,
  'REJECT_NECESSARY_GRANTED'
);

assert(
  consent.has('preferences') === false &&
  consent.has('analytics') === false &&
  consent.has('marketing') === false,
  'REJECT_OPTIONAL_DENIED'
);

assert(
  cookieStore.includes(
    'goosialize_consent='
  ),
  'COOKIE_WRITTEN'
);

const accepted =
  consent.acceptAll();

assert(
  accepted.state === 'accepted_all',
  'ACCEPT_ALL_STATE'
);

assert(
  consent.has('preferences') === true &&
  consent.has('analytics') === true &&
  consent.has('marketing') === true,
  'ACCEPT_ALL_OPTIONAL_GRANTED'
);

const custom =
  consent.savePreferences({
    necessary: false,
    preferences: true,
    analytics: false,
    marketing: true
  });

assert(
  custom.state === 'custom',
  'CUSTOM_STATE'
);

assert(
  custom.categories.necessary === true,
  'CUSTOM_NECESSARY_INVARIANT'
);

assert(
  custom.categories.preferences === true &&
  custom.categories.analytics === false &&
  custom.categories.marketing === true,
  'CUSTOM_SELECTION'
);

let unknownRejected = false;

try {
  consent.savePreferences({
    analytics: true,
    mystery: true
  });
} catch (error) {
  unknownRejected =
    error instanceof RangeError;
}

assert(
  unknownRejected,
  'CUSTOM_UNKNOWN_CATEGORY_REJECTED'
);

let nonBooleanRejected = false;

try {
  consent.savePreferences({
    analytics: 1
  });
} catch (error) {
  nonBooleanRejected =
    error instanceof TypeError;
}

assert(
  nonBooleanRejected,
  'CUSTOM_NON_BOOLEAN_REJECTED'
);

/*
 * Browser document.cookie only exposes name=value pairs on reads.
 * Simulate that behavior before restore.
 */
cookieStore = cookieStore
  .split(';')[0];

consent.initialize({
  consentVersion: 1,
  lifetimeDays: 180,
  cookieName: 'goosialize_consent'
});

assert(
  consent.getState() === 'custom',
  'COOKIE_RESTORE_STATE'
);

consent.initialize({
  consentVersion: 2,
  lifetimeDays: 180,
  cookieName: 'goosialize_consent'
});

assert(
  consent.getState() === 'unknown',
  'VERSION_INVALIDATION'
);

assert(
  cookieStore === '',
  'INVALID_COOKIE_DELETED'
);

consent.acceptAll();

assert(
  cookieStore.includes('Secure'),
  'HTTPS_COOKIE_SECURE'
);

assert(
  cookieStore.includes('SameSite=Lax'),
  'COOKIE_SAMESITE_LAX'
);

assert(
  cookieStore.includes('Path=/'),
  'COOKIE_PATH_ROOT'
);

consent.clear();

assert(
  consent.getState() === 'unknown',
  'CLEAR_STATE_UNKNOWN'
);

assert(
  cookieStore === '',
  'CLEAR_COOKIE_REMOVED'
);

console.log('P3_RUNTIME_ACCEPTANCE=PASS');
