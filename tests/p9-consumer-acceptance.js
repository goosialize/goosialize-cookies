'use strict';

const fs =
  require('fs');

const vm =
  require('vm');

const source =
  fs.readFileSync(
    'assets/js/consumer.js',
    'utf8'
  );

const listeners =
  new Map();

function addEventListener(
  type,
  listener,
  options = {}
) {
  if (!listeners.has(type)) {
    listeners.set(type, []);
  }

  listeners
    .get(type)
    .push({
      listener,
      once:
        options?.once === true
    });
}

function removeEventListener(
  type,
  listener
) {
  const current =
    listeners.get(type) ?? [];

  listeners.set(
    type,
    current.filter(
      item =>
        item.listener !== listener
    )
  );
}

function dispatch(
  type,
  detail
) {
  const current = [
    ...(listeners.get(type) ?? [])
  ];

  for (const item of current) {
    item.listener({
      type,
      detail
    });

    if (item.once) {
      removeEventListener(
        type,
        item.listener
      );
    }
  }
}

const consentState = {
  ready: false,
  snapshot: null
};

const window = {
  addEventListener,
  removeEventListener,

  GoosializeConsent: {
    isReady() {
      return consentState.ready;
    },

    getSnapshot() {
      return consentState.snapshot;
    },

    has(category) {
      if (
        category === 'necessary'
      ) {
        return true;
      }

      return consentState
        .snapshot
        ?.categories
        ?.[category] === true;
    }
  }
};

const context =
  vm.createContext({
    window,
    console
  });

vm.runInContext(
  source,
  context,
  {
    filename:
      'consumer.js'
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

const consumer =
  window
    .GoosializeConsentConsumer;

ok(
  consumer
  && typeof consumer === 'object',
  'CONSUMER_AVAILABLE'
);

ok(
  consumer.isReady() === false,
  'UNKNOWN_NOT_READY'
);

ok(
  consumer.getSnapshot() === null,
  'UNKNOWN_SNAPSHOT_NULL'
);

ok(
  consumer.has('analytics')
    === false,
  'UNKNOWN_ANALYTICS_FAIL_CLOSED'
);

ok(
  consumer.has('marketing')
    === false,
  'UNKNOWN_MARKETING_FAIL_CLOSED'
);

let readySnapshot = 'unset';

consumer.whenReady(
  snapshot => {
    readySnapshot = snapshot;
  }
);

consentState.ready = true;

consentState.snapshot = {
  categories: {
    necessary: true,
    preferences: false,
    analytics: true,
    marketing: false
  }
};

dispatch(
  'goosialize:consent-ready',
  {
    current:
      consentState.snapshot
  }
);

ok(
  readySnapshot
    === consentState.snapshot,
  'WHEN_READY_RECEIVES_SNAPSHOT'
);

ok(
  consumer.has('analytics')
    === true,
  'ANALYTICS_CONSUMER_GRANTED'
);

ok(
  consumer.has('marketing')
    === false,
  'MARKETING_CONSUMER_DENIED'
);

let changedCurrent = null;
let changedPrevious = null;

const unsubscribe =
  consumer.onChange(
    (current, previous) => {
      changedCurrent = current;
      changedPrevious = previous;
    }
  );

const previous =
  consentState.snapshot;

consentState.snapshot = {
  categories: {
    necessary: true,
    preferences: false,
    analytics: false,
    marketing: false
  }
};

dispatch(
  'goosialize:consent-changed',
  {
    previous,
    current:
      consentState.snapshot
  }
);

ok(
  changedCurrent
    === consentState.snapshot,
  'CHANGE_CURRENT_FORWARDING'
);

ok(
  changedPrevious === previous,
  'CHANGE_PREVIOUS_FORWARDING'
);

ok(
  consumer.has('analytics')
    === false,
  'WITHDRAWAL_ANALYTICS_DENIED'
);

unsubscribe();

ok(
  !source.includes(
    'acceptAll'
  )
  && !source.includes(
    'rejectAll'
  )
  && !source.includes(
    'savePreferences'
  ),
  'CONSUMER_WRITE_API_NONE'
);

console.log(
  'P9_CONSUMER_ACCEPTANCE=PASS'
);
