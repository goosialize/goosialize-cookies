'use strict';

const fs = require('fs');
const vm = require('vm');

const source = fs.readFileSync(
  'assets/js/consent.js',
  'utf8'
);

let cookieStore = '';
let fakeNow =
  Date.parse(
    '2026-08-20T12:00:00Z'
  );

class FakeDate extends Date {
  constructor(...args) {
    if (args.length === 0) {
      super(fakeNow);
    } else {
      super(...args);
    }
  }

  static now() {
    return fakeNow;
  }
}

Object.defineProperty(
  FakeDate,
  'parse',
  {
    value: Date.parse
  }
);

const document = {};

Object.defineProperty(
  document,
  'cookie',
  {
    get() {
      return cookieStore;
    },

    set(value) {
      const first =
        value.split(';')[0];

      const [name, ...rest] =
        first.split('=');

      const cookieValue =
        rest.join('=');

      if (
        value.includes(
          'Expires=Thu, 01 Jan 1970'
        )
      ) {
        cookieStore = '';
        return;
      }

      cookieStore =
        `${name}=${cookieValue}`;
    }
  }
);

const listeners = new Map();

const window = {
  location: {
    protocol: 'https:'
  },

  addEventListener(
    name,
    listener
  ) {
    if (!listeners.has(name)) {
      listeners.set(name, []);
    }

    listeners
      .get(name)
      .push(listener);
  },

  dispatchEvent() {}
};

class CustomEvent {
  constructor(name, options = {}) {
    this.type = name;
    this.detail = options.detail;
  }
}

const context = vm.createContext({
  window,
  document,
  CustomEvent,
  Date: FakeDate,
  JSON,
  Number,
  Object,
  Array,
  encodeURIComponent,
  decodeURIComponent,
  console
});

vm.runInContext(
  source,
  context,
  {
    filename: 'consent.js'
  }
);

const consent =
  window.GoosializeConsent;

function ok(condition, label) {
  if (!condition) {
    throw new Error(
      `FAIL=${label}`
    );
  }

  console.log(
    `${label}=PASS`
  );
}

function setCookie(payload) {
  cookieStore =
    'goosialize_consent='
    + encodeURIComponent(
      JSON.stringify(payload)
    );
}

const config = {
  consentVersion: 2,
  lifetimeDays: 180,
  cookieName: 'goosialize_consent'
};

cookieStore = '';

consent.initialize(config);

ok(
  consent.getLifecycleStatus()
    === 'missing',
  'MISSING_STATUS'
);

ok(
  consent.getState()
    === 'unknown',
  'MISSING_FAIL_CLOSED'
);

consent.acceptAll();

ok(
  consent.getLifecycleStatus()
    === 'valid',
  'DECISION_VALID_STATUS'
);

consent.clear();

ok(
  consent.getLifecycleStatus()
    === 'missing',
  'WITHDRAWAL_MISSING_STATUS'
);

setCookie({
  version: 1,
  timestamp:
    '2026-08-20T11:00:00.000Z',
  categories: {
    necessary: true,
    preferences: true,
    analytics: true,
    marketing: true
  }
});

consent.initialize(config);

ok(
  consent.getLifecycleStatus()
    === 'version_mismatch',
  'VERSION_MISMATCH_STATUS'
);

ok(
  consent.getState()
    === 'unknown',
  'VERSION_MISMATCH_FAIL_CLOSED'
);

ok(
  cookieStore === '',
  'VERSION_MISMATCH_COOKIE_REMOVED'
);

setCookie({
  version: 2,
  timestamp:
    '2026-01-01T00:00:00.000Z',
  categories: {
    necessary: true,
    preferences: true,
    analytics: true,
    marketing: true
  }
});

consent.initialize(config);

ok(
  consent.getLifecycleStatus()
    === 'expired',
  'EXPIRED_STATUS'
);

ok(
  consent.getState()
    === 'unknown',
  'EXPIRED_FAIL_CLOSED'
);

ok(
  cookieStore === '',
  'EXPIRED_COOKIE_REMOVED'
);

setCookie({
  version: 2,
  timestamp:
    '2026-08-21T00:00:00.000Z',
  categories: {
    necessary: true,
    preferences: true,
    analytics: true,
    marketing: true
  }
});

consent.initialize(config);

ok(
  consent.getLifecycleStatus()
    === 'future_timestamp',
  'FUTURE_TIMESTAMP_STATUS'
);

ok(
  consent.getState()
    === 'unknown',
  'FUTURE_TIMESTAMP_FAIL_CLOSED'
);

ok(
  cookieStore === '',
  'FUTURE_TIMESTAMP_COOKIE_REMOVED'
);

cookieStore =
  'goosialize_consent=%7Bbad-json';

consent.initialize(config);

ok(
  consent.getLifecycleStatus()
    === 'malformed',
  'MALFORMED_STATUS'
);

ok(
  cookieStore === '',
  'MALFORMED_COOKIE_REMOVED'
);

ok(
  consent.getState()
    === 'unknown',
  'MALFORMED_FAIL_CLOSED'
);

console.log(
  'P11_BROWSER_LIFECYCLE_ACCEPTANCE=PASS'
);
