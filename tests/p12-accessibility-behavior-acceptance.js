'use strict';

const fs =
  require('fs');

const vm =
  require('vm');

const source =
  fs.readFileSync(
    'assets/js/ui.js',
    'utf8'
  );

const listeners =
  new Map();

function addListener(
  target,
  type,
  listener
) {
  const key =
    `${target}:${type}`;

  if (!listeners.has(key)) {
    listeners.set(
      key,
      []
    );
  }

  listeners
    .get(key)
    .push(listener);
}

function fire(
  target,
  type,
  event
) {
  for (
    const listener
    of listeners.get(
      `${target}:${type}`
    ) ?? []
  ) {
    listener(event);
  }
}

let activeElement = null;

function focusable(name) {
  return {
    name,
    hidden: false,
    disabled: false,

    focus() {
      activeElement = this;
    }
  };
}

const closeButton =
  focusable('close');

const analyticsToggle =
  focusable('analytics');

const saveButton =
  focusable('save');

const modalElements = [
  closeButton,
  analyticsToggle,
  saveButton
];

const modal = {
  hidden: false,

  focus() {
    activeElement = this;
  },

  contains(element) {
    return (
      element === this
      || modalElements.includes(
        element
      )
    );
  },

  querySelectorAll() {
    return modalElements;
  }
};

const backdrop = {
  hidden: true,

  addEventListener(
    type,
    listener
  ) {
    addListener(
      'backdrop',
      type,
      listener
    );
  }
};

const settings =
  focusable('settings');

const banner = {
  hidden: true
};

const bannerDescription = {
  textContent: ''
};

const status = {
  textContent: ''
};

const root = {
  dataset: {
    savedMessage: 'Saved',
    bannerDefaultMessage: 'Default',
    reconsentExpiredMessage:
      'Expired',
    reconsentVersionMessage:
      'Version changed'
  },

  addEventListener(
    type,
    listener
  ) {
    addListener(
      'root',
      type,
      listener
    );
  },

  contains() {
    return true;
  },

  querySelector(selector) {
    const map = {
      '[data-goosialize-consent-banner]':
        banner,

      '[data-goosialize-consent-banner-description]':
        bannerDescription,

      '[data-goosialize-consent-backdrop]':
        backdrop,

      '[data-goosialize-consent-modal]':
        modal,

      '[data-goosialize-consent-settings]':
        settings,

      '[data-goosialize-consent-status]':
        status
    };

    return map[selector]
      ?? null;
  },

  querySelectorAll() {
    return [];
  }
};

const document = {
  readyState: 'complete',

  documentElement: {
    classList: {
      add() {},
      remove() {}
    }
  },

  addEventListener(
    type,
    listener
  ) {
    addListener(
      'document',
      type,
      listener
    );
  },

  querySelector(selector) {
    if (
      selector
      === '[data-goosialize-consent-root]'
    ) {
      return root;
    }

    return null;
  }
};

Object.defineProperty(
  document,
  'activeElement',
  {
    get() {
      return activeElement;
    }
  }
);

const consent = {
  getState() {
    return 'accepted_all';
  },

  getSnapshot() {
    return {
      categories: {
        necessary: true,
        preferences: true,
        analytics: true,
        marketing: true
      }
    };
  },

  getLifecycleStatus() {
    return 'valid';
  },

  acceptAll() {},
  rejectAll() {},
  savePreferences() {}
};

const window = {
  GoosializeConsent:
    consent,

  addEventListener() {},

  requestAnimationFrame(
    callback
  ) {
    callback();
  },

  setTimeout(
    callback
  ) {
    callback();
  }
};

class HTMLElement {}

for (const element of [
  settings,
  closeButton,
  analyticsToggle,
  saveButton
]) {
  Object.setPrototypeOf(
    element,
    HTMLElement.prototype
  );
}

const context =
  vm.createContext({
    window,
    document,
    HTMLElement,
    console
  });

vm.runInContext(
  source,
  context,
  {
    filename:
      'ui.js'
  }
);

const ui =
  window.GoosializeConsentUI;

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

function keyEvent(
  key,
  shiftKey = false
) {
  let prevented = false;

  return {
    key,
    shiftKey,

    preventDefault() {
      prevented = true;
    },

    get prevented() {
      return prevented;
    }
  };
}

/*
 * Open from settings.
 */
activeElement = settings;

ui.open();

ok(
  backdrop.hidden === false,
  'MODAL_OPEN'
);

ok(
  activeElement === modal,
  'MODAL_INITIAL_FOCUS'
);

/*
 * Focus outside modal must be recovered.
 */
activeElement = settings;

fire(
  'document',
  'focusin',
  {
    target: settings
  }
);

ok(
  activeElement === closeButton,
  'FOCUS_ESCAPE_RECOVERED'
);

/*
 * Shift+Tab on first wraps to last.
 */
activeElement = closeButton;

const backwards =
  keyEvent(
    'Tab',
    true
  );

fire(
  'document',
  'keydown',
  backwards
);

ok(
  backwards.prevented,
  'SHIFT_TAB_PREVENTED'
);

ok(
  activeElement === saveButton,
  'SHIFT_TAB_WRAP_LAST'
);

/*
 * Tab on last wraps to first.
 */
activeElement = saveButton;

const forwards =
  keyEvent(
    'Tab',
    false
  );

fire(
  'document',
  'keydown',
  forwards
);

ok(
  forwards.prevented,
  'TAB_PREVENTED'
);

ok(
  activeElement === closeButton,
  'TAB_WRAP_FIRST'
);

/*
 * Escape closes and returns focus.
 */
const escape =
  keyEvent(
    'Escape'
  );

fire(
  'document',
  'keydown',
  escape
);

ok(
  escape.prevented,
  'ESCAPE_PREVENTED'
);

ok(
  backdrop.hidden === true,
  'ESCAPE_CLOSE'
);

ok(
  activeElement === settings,
  'ESCAPE_FOCUS_RETURN'
);

/*
 * Backdrop click closes.
 */
activeElement = settings;
ui.open();

fire(
  'backdrop',
  'click',
  {
    target: backdrop
  }
);

ok(
  backdrop.hidden === true,
  'BACKDROP_CLOSE'
);

ok(
  activeElement === settings,
  'BACKDROP_FOCUS_RETURN'
);

/*
 * No focusables fallback.
 */
modal.querySelectorAll =
  () => [];

activeElement = settings;
ui.open();

const emptyTab =
  keyEvent(
    'Tab'
  );

fire(
  'document',
  'keydown',
  emptyTab
);

ok(
  emptyTab.prevented,
  'EMPTY_TAB_PREVENTED'
);

ok(
  activeElement === modal,
  'EMPTY_MODAL_FOCUS_FALLBACK'
);

console.log(
  'P12_ACCESSIBILITY_BEHAVIOR_ACCEPTANCE=PASS'
);
