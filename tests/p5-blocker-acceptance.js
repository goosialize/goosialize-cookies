'use strict';

const fs = require('fs');
const vm = require('vm');

const source = fs.readFileSync(
  'assets/js/blocker.js',
  'utf8'
);

class Element {
  querySelectorAll() {
    return [];
  }
}

class HTMLScriptElement extends Element {
  constructor() {
    super();

    this.dataset = {};
    this.attributes = new Map();
    this.textContent = '';
    this.src = '';
    this.parentNode = null;
    this.nextSibling = null;
  }

  hasAttribute(name) {
    return this.attributes.has(name);
  }

  getAttribute(name) {
    return this.attributes.get(name) ?? null;
  }

  setAttribute(name, value) {
    this.attributes.set(
      name,
      String(value)
    );
  }

  matches(selector) {
    return (
      selector ===
        'script[type="text/plain"][data-goosialize-consent]' &&
      this.getAttribute('type') === 'text/plain' &&
      typeof this.dataset.goosializeConsent === 'string'
    );
  }
}

const listeners = new Map();
const definitions = [];
const executed = [];

const documentObject = {
  readyState: 'complete',
  documentElement: new Element(),

  createElement(name) {
    if (name === 'script') {
      return new HTMLScriptElement();
    }

    return new Element();
  },

  querySelectorAll(selector) {
    return definitions.filter(
      item => item.matches(selector)
    );
  },

  addEventListener() {}
};

const grants = {
  preferences: false,
  analytics: false,
  marketing: false
};

const windowObject = {
  GoosializeConsent: {
    has(category) {
      return grants[category] === true;
    }
  },

  addEventListener(type, callback) {
    if (!listeners.has(type)) {
      listeners.set(type, []);
    }

    listeners.get(type).push(callback);
  },

  dispatchEvent(event) {
    for (
      const callback of
      listeners.get(event.type) ?? []
    ) {
      callback(event);
    }

    return true;
  }
};

class CustomEvent {
  constructor(type, options = {}) {
    this.type = type;
    this.detail = options.detail;
  }
}

class MutationObserver {
  observe() {}
}

function addDefinition({
  category,
  id,
  inline = '',
  src = null
}) {
  const script =
    new HTMLScriptElement();

  script.setAttribute(
    'type',
    'text/plain'
  );

  script.dataset.goosializeConsent =
    category;

  if (id) {
    script.dataset.goosializeConsentId =
      id;
  }

  if (src) {
    script.dataset.goosializeConsentSrc =
      src;
  }

  script.textContent = inline;

  script.parentNode = {
    insertBefore(node) {
      executed.push(node);
    }
  };

  definitions.push(script);

  return script;
}

const context = vm.createContext({
  window: windowObject,
  document: documentObject,
  HTMLScriptElement,
  Element,
  CustomEvent,
  MutationObserver,
  WeakSet,
  Set,
  Object,
  Array,
  String,
  console
});

vm.runInContext(
  source,
  context,
  {
    filename: 'blocker.js'
  }
);

const blocker =
  windowObject.GoosializeConsentBlocker;

function assert(condition, label) {
  if (!condition) {
    throw new Error(
      `FAIL=${label}`
    );
  }

  console.log(`${label}=PASS`);
}

assert(
  blocker.isAllowedCategory('preferences'),
  'PREFERENCES_ALLOWED'
);

assert(
  blocker.isAllowedCategory('analytics'),
  'ANALYTICS_ALLOWED'
);

assert(
  blocker.isAllowedCategory('marketing'),
  'MARKETING_ALLOWED'
);

assert(
  !blocker.isAllowedCategory('necessary'),
  'NECESSARY_NOT_BLOCKABLE'
);

assert(
  !blocker.isAllowedCategory('mystery'),
  'UNKNOWN_CATEGORY_FAIL_CLOSED'
);

const analytics =
  addDefinition({
    category: 'analytics',
    id: 'analytics-a',
    inline:
      'window.__analytics = true;'
  });

blocker.scan(documentObject);

assert(
  executed.length === 0,
  'DENIED_INLINE_BLOCKED'
);

assert(
  analytics.dataset
    .goosializeConsentState ===
    'blocked',
  'DENIED_INLINE_STATE'
);

grants.analytics = true;

windowObject.dispatchEvent(
  new CustomEvent(
    'goosialize:consent-granted',
    {
      detail: {
        category: 'analytics'
      }
    }
  )
);

assert(
  executed.length === 1,
  'LATE_GRANT_ACTIVATES'
);

assert(
  executed[0]
    .textContent
    .includes('__analytics'),
  'INLINE_CODE_COPIED'
);

blocker.scan(documentObject);

assert(
  executed.length === 1,
  'INLINE_ACTIVATE_ONCE'
);

const marketing =
  addDefinition({
    category: 'marketing',
    id: 'marketing-a',
    src:
      'https://example.com/provider.js'
  });

marketing.setAttribute(
  'async',
  ''
);

blocker.scan(documentObject);

assert(
  executed.length === 1,
  'DENIED_EXTERNAL_BLOCKED'
);

grants.marketing = true;

blocker.activateCategory(
  'marketing'
);

assert(
  executed.length === 2,
  'EXTERNAL_GRANT_ACTIVATES'
);

assert(
  executed[1].src ===
    'https://example.com/provider.js',
  'EXTERNAL_SRC_COPIED'
);

assert(
  executed[1].hasAttribute('async'),
  'SAFE_ATTRIBUTE_COPIED'
);

const ambiguous =
  addDefinition({
    category: 'analytics',
    id: 'ambiguous',
    inline:
      'window.__bad = true;',
    src:
      'https://example.com/bad.js'
  });

blocker.scan(documentObject);

assert(
  executed.length === 2,
  'AMBIGUOUS_FAIL_CLOSED'
);

assert(
  ambiguous.dataset
    .goosializeConsentState ===
    'invalid',
  'AMBIGUOUS_MARKED_INVALID'
);

const empty =
  addDefinition({
    category: 'analytics',
    id: 'empty'
  });

blocker.scan(documentObject);

assert(
  executed.length === 2,
  'EMPTY_FAIL_CLOSED'
);

assert(
  empty.dataset
    .goosializeConsentState ===
    'invalid',
  'EMPTY_MARKED_INVALID'
);

grants.analytics = false;

windowObject.dispatchEvent(
  new CustomEvent(
    'goosialize:consent-revoked',
    {
      detail: {
        category: 'analytics'
      }
    }
  )
);

const afterRevoke =
  addDefinition({
    category: 'analytics',
    id: 'after-revoke',
    inline:
      'window.__afterRevoke = true;'
  });

blocker.scan(documentObject);

assert(
  executed.length === 2,
  'POST_REVOKE_BLOCKED'
);

assert(
  afterRevoke.dataset
    .goosializeConsentState ===
    'blocked',
  'POST_REVOKE_STATE'
);

grants.analytics = true;

blocker.activateCategory(
  'analytics'
);

assert(
  executed.length === 3,
  'REGRANT_ACTIVATES_PENDING'
);

blocker.activateCategory(
  'analytics'
);

assert(
  executed.length === 3,
  'REGRANT_ACTIVATE_ONCE'
);

console.log(
  'P5_BLOCKER_ACCEPTANCE=PASS'
);
