'use strict';

const fs = require('fs');
const vm = require('vm');

const source = fs.readFileSync(
  'assets/js/google-consent-mode.js',
  'utf8'
);

const listeners = new Map();

class CustomEvent {
  constructor(type, options = {}) {
    this.type = type;
    this.detail = options.detail;
  }
}

const window = {
  dataLayer: undefined,

  addEventListener(type, listener) {
    if (!listeners.has(type)) {
      listeners.set(type, []);
    }

    listeners.get(type).push(
      listener
    );
  },

  dispatchEvent(event) {
    for (
      const listener of
      listeners.get(event.type) ?? []
    ) {
      listener(event);
    }
  }
};

const context = vm.createContext({
  window,
  CustomEvent,
  console
});

vm.runInContext(
  source,
  context,
  {
    filename:
      'google-consent-mode.js'
  }
);

function ok(
  condition,
  label
) {
  if (!condition) {
    throw new Error(
      `FAIL=${label}`
    );
  }

  console.log(
    `${label}=PASS`
  );
}

function entry(index) {
  return Array.from(
    window.dataLayer[index]
  );
}

ok(
  Array.isArray(
    window.dataLayer
  ),
  'DATALAYER_INITIALIZED'
);

ok(
  typeof window.gtag === 'function',
  'GTAG_QUEUE_FUNCTION_AVAILABLE'
);

ok(
  window.dataLayer.length === 1,
  'DEFAULT_COMMAND_FIRST'
);

const defaultCommand =
  entry(0);

ok(
  defaultCommand[0] ===
    'consent'
  && defaultCommand[1] ===
    'default',
  'DEFAULT_COMMAND_SHAPE'
);

ok(
  defaultCommand[2]
    .analytics_storage ===
    'denied'
  && defaultCommand[2]
    .ad_storage ===
    'denied'
  && defaultCommand[2]
    .ad_user_data ===
    'denied'
  && defaultCommand[2]
    .ad_personalization ===
    'denied',
  'DEFAULT_ALL_DENIED'
);

window.dispatchEvent(
  new CustomEvent(
    'goosialize:consent-ready',
    {
      detail: {
        current: null
      }
    }
  )
);

const unknownUpdate =
  entry(1);

ok(
  unknownUpdate[1] ===
    'update'
  && unknownUpdate[2]
    .analytics_storage ===
    'denied'
  && unknownUpdate[2]
    .ad_storage ===
    'denied',
  'UNKNOWN_REMAINS_DENIED'
);

window.dispatchEvent(
  new CustomEvent(
    'goosialize:consent-changed',
    {
      detail: {
        previous: null,
        current: {
          categories: {
            necessary: true,
            preferences: false,
            analytics: true,
            marketing: false
          }
        }
      }
    }
  )
);

const analyticsUpdate =
  entry(2);

ok(
  analyticsUpdate[2]
    .analytics_storage ===
    'granted',
  'ANALYTICS_GRANTED'
);

ok(
  analyticsUpdate[2]
    .ad_storage ===
    'denied'
  && analyticsUpdate[2]
    .ad_user_data ===
    'denied'
  && analyticsUpdate[2]
    .ad_personalization ===
    'denied',
  'MARKETING_REMAINS_DENIED'
);

window.dispatchEvent(
  new CustomEvent(
    'goosialize:consent-changed',
    {
      detail: {
        current: {
          categories: {
            necessary: true,
            preferences: true,
            analytics: true,
            marketing: true
          }
        }
      }
    }
  )
);

const allUpdate =
  entry(3);

ok(
  allUpdate[2]
    .analytics_storage ===
    'granted'
  && allUpdate[2]
    .ad_storage ===
    'granted'
  && allUpdate[2]
    .ad_user_data ===
    'granted'
  && allUpdate[2]
    .ad_personalization ===
    'granted',
  'ANALYTICS_MARKETING_GRANTED'
);

window.dispatchEvent(
  new CustomEvent(
    'goosialize:consent-changed',
    {
      detail: {
        current: null
      }
    }
  )
);

const withdrawalUpdate =
  entry(4);

ok(
  withdrawalUpdate[2]
    .analytics_storage ===
    'denied'
  && withdrawalUpdate[2]
    .ad_storage ===
    'denied'
  && withdrawalUpdate[2]
    .ad_user_data ===
    'denied'
  && withdrawalUpdate[2]
    .ad_personalization ===
    'denied',
  'WITHDRAWAL_ALL_DENIED'
);

const preferencesOnly =
  window
    .GoosializeGoogleConsentMode
    .mapSnapshot({
      categories: {
        necessary: true,
        preferences: true,
        analytics: false,
        marketing: false
      }
    });

ok(
  preferencesOnly
    .analytics_storage ===
    'denied'
  && preferencesOnly
    .ad_storage ===
    'denied',
  'PREFERENCES_NO_GOOGLE_SIGNAL'
);

ok(
  !source.includes(
    'googletagmanager.com'
  )
  && !source.includes(
    'google-analytics.com'
  )
  && !source.includes(
    'src='
  ),
  'GOOGLE_SCRIPT_LOADING_NONE'
);

console.log(
  'P8_GOOGLE_CONSENT_MODE_ACCEPTANCE=PASS'
);
