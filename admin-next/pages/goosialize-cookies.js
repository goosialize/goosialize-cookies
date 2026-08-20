(() => {
  'use strict';

  const TAG =
    window.__GRAV_PAGE_TAG;

  if (
    typeof TAG !== 'string' ||
    TAG === '' ||
    customElements.get(TAG)
  ) {
    return;
  }

  function apiUrl(path) {
    const server = String(
      window.__GRAV_API_SERVER_URL || ''
    ).replace(/\/+$/, '');

    const prefix = String(
      window.__GRAV_API_PREFIX || '/api/v1'
    )
      .replace(/^\/?/, '/')
      .replace(/\/+$/, '');

    const suffix =
      String(path || '').startsWith('/')
        ? String(path)
        : `/${String(path || '')}`;

    return `${server}${prefix}${suffix}`;
  }

  function apiHeaders() {
    const headers = {
      Accept: 'application/json',
    };

    if (window.__GRAV_API_TOKEN) {
      headers['X-API-Token'] =
        window.__GRAV_API_TOKEN;
    }

    return headers;
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(
        /[&<>"']/g,
        (character) => ({
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;',
        })[character]
      );
  }

  function categoryLabel(category) {
    switch (category) {
      case 'preferences':
        return 'Preferences';

      case 'analytics':
        return 'Analytics';

      case 'marketing':
        return 'Marketing';

      default:
        return String(category || '—');
    }
  }

  class GoosializeCookiesPage
    extends HTMLElement {
    constructor() {
      super();

      this._mounted = false;
      this._state = 'idle';
      this._error = '';
      this._services = [];
    }

    connectedCallback() {
      if (this._mounted) {
        return;
      }

      this._mounted = true;

      this.attachShadow({
        mode: 'open',
      });

      this._render();
      this._load();
    }

    async _load() {
      this._state = 'loading';
      this._error = '';

      this._render();

      try {
        const response = await fetch(
          apiUrl(
            '/goosialize-cookies/services'
          ),
          {
            method: 'GET',
            headers: apiHeaders(),
            credentials: 'same-origin',
          }
        );

        if (!response.ok) {
          throw new Error(
            `Service registry request failed (${response.status}).`
          );
        }

        const payload =
          await response.json();

        const data =
          payload?.data ?? payload;

        this._services =
          Array.isArray(data?.services)
            ? data.services
            : [];

        this._state = 'ready';
      } catch (error) {
        this._services = [];
        this._state = 'error';

        this._error =
          error instanceof Error
            ? error.message
            : 'Unable to load services.';
      }

      this._render();
    }

    _serviceRow(service) {
      const cookies =
        Array.isArray(service.cookies)
          ? service.cookies.length
          : 0;

      const storage =
        Array.isArray(service.storage)
          ? service.storage.length
          : 0;

      const enabled =
        service.enabled === true;

      return `
        <tr>
          <td>
            <strong>
              ${escapeHtml(service.name)}
            </strong>

            <span class="provider">
              ${escapeHtml(service.provider)}
            </span>
          </td>

          <td>
            <span class="category">
              ${escapeHtml(
                categoryLabel(
                  service.category
                )
              )}
            </span>
          </td>

          <td class="purpose">
            ${escapeHtml(service.purpose)}
          </td>

          <td class="numeric">
            ${cookies}
          </td>

          <td class="numeric">
            ${storage}
          </td>

          <td>
            <span
              class="status
                     ${enabled
                       ? 'status--enabled'
                       : 'status--disabled'}"
            >
              ${enabled
                ? 'Enabled'
                : 'Disabled'}
            </span>
          </td>
        </tr>
      `;
    }

    _content() {
      if (this._state === 'loading') {
        return `
          <div
            class="state-card"
            role="status"
          >
            Loading consent services…
          </div>
        `;
      }

      if (this._state === 'error') {
        return `
          <div
            class="state-card state-card--error"
            role="alert"
          >
            <strong>
              Unable to load services
            </strong>

            <span>
              ${escapeHtml(this._error)}
            </span>

            <button
              type="button"
              class="retry-button"
            >
              Try again
            </button>
          </div>
        `;
      }

      const rows =
        this._services.length > 0
          ? this._services
              .map(
                (service) =>
                  this._serviceRow(service)
              )
              .join('')
          : `
            <tr>
              <td
                colspan="6"
                class="empty-cell"
              >
                No consent services configured.
              </td>
            </tr>
          `;

      const total =
        this._services.length;

      return `
        <div class="summary">
          <strong>
            ${total}
            ${total === 1
              ? 'service'
              : 'services'}
          </strong>

          <span>
            Read-only registry
          </span>
        </div>

        <div class="notice">
          Service editing is not enabled yet.
          Script management remains separately
          permissioned and unavailable.
        </div>

        <div class="table-shell">
          <table>
            <thead>
              <tr>
                <th>Service</th>
                <th>Category</th>
                <th>Purpose</th>
                <th>Cookies</th>
                <th>Storage</th>
                <th>Status</th>
              </tr>
            </thead>

            <tbody>
              ${rows}
            </tbody>
          </table>
        </div>
      `;
    }

    _render() {
      if (!this.shadowRoot) {
        return;
      }

      this.shadowRoot.innerHTML = `
        <style>
          :host {
            display: block;
            color:
              var(--foreground, #202124);
          }

          * {
            box-sizing: border-box;
          }

          .page {
            display: grid;
            gap: 1rem;
          }

          .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
          }

          h1 {
            margin: 0;
            font-size: 1.6rem;
            line-height: 1.2;
          }

          .subtitle {
            margin: 0.35rem 0 0;
            opacity: 0.7;
          }

          .summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.8rem 1rem;
            background:
              var(--card, #fff);
            border:
              1px solid
              var(--border, #d5d7da);
            border-radius: 0.55rem;
          }

          .summary span {
            font-size: 0.82rem;
            opacity: 0.68;
          }

          .notice {
            padding: 0.8rem 1rem;
            border:
              1px solid
              var(--border, #d5d7da);
            border-radius: 0.55rem;
            background:
              var(--background, #fff);
            font-size: 0.9rem;
          }

          .state-card {
            display: grid;
            gap: 0.45rem;
            padding: 2rem 1.25rem;
            text-align: center;
            background:
              var(--card, #fff);
            border:
              1px solid
              var(--border, #d5d7da);
            border-radius: 0.6rem;
          }

          .state-card--error {
            border-color: #dc2626;
          }

          .retry-button {
            justify-self: center;
            margin-top: 0.35rem;
            min-height: 2.5rem;
            padding: 0.55rem 0.9rem;
            color: #fff;
            background: #292330;
            border: 1px solid #4b3a5d;
            border-radius: 0.45rem;
            font: inherit;
            font-weight: 650;
            cursor: pointer;
          }

          .retry-button:focus-visible {
            outline:
              2px solid
              var(--primary, #a855f7);
            outline-offset: 2px;
          }

          .table-shell {
            overflow-x: auto;
            background:
              var(--card, #fff);
            border:
              1px solid
              var(--border, #d5d7da);
            border-radius: 0.6rem;
          }

          table {
            width: 100%;
            min-width: 48rem;
            border-collapse: collapse;
          }

          th,
          td {
            padding: 0.8rem 0.9rem;
            text-align: left;
            vertical-align: top;
            border-bottom:
              1px solid
              var(--border, #e1e3e6);
          }

          th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            opacity: 0.72;
          }

          tbody tr:last-child td {
            border-bottom: 0;
          }

          .provider {
            display: block;
            margin-top: 0.2rem;
            font-size: 0.8rem;
            opacity: 0.64;
          }

          .purpose {
            max-width: 30rem;
          }

          .numeric {
            font-variant-numeric:
              tabular-nums;
          }

          .category,
          .status {
            display: inline-flex;
            align-items: center;
            min-height: 1.8rem;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 650;
          }

          .category {
            background:
              color-mix(
                in srgb,
                var(--primary, #8b5cf6)
                10%,
                transparent
              );
          }

          .status--enabled {
            background:
              color-mix(
                in srgb,
                #16a34a 12%,
                transparent
              );
          }

          .status--disabled {
            opacity: 0.65;
            background:
              color-mix(
                in srgb,
                #64748b 12%,
                transparent
              );
          }

          .empty-cell {
            padding: 2rem 1rem;
            text-align: center;
            opacity: 0.68;
          }

          @media (max-width: 640px) {
            .summary {
              align-items: flex-start;
              flex-direction: column;
              gap: 0.25rem;
            }
          }

          @media (
            prefers-reduced-motion:
            reduce
          ) {
            *,
            *::before,
            *::after {
              scroll-behavior:
                auto !important;
              transition:
                none !important;
            }
          }
        </style>

        <section class="page">
          <header class="header">
            <div>
              <h1>
                Goosialize Cookies
              </h1>

              <p class="subtitle">
                Consent services and
                browser-storage registry.
              </p>
            </div>
          </header>

          ${this._content()}
        </section>
      `;

      this.shadowRoot
        .querySelector(
          '.retry-button'
        )
        ?.addEventListener(
          'click',
          () => this._load()
        );
    }
  }

  customElements.define(
    TAG,
    GoosializeCookiesPage
  );
})();
