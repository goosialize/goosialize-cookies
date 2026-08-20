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

  const CATEGORIES = [
    'preferences',
    'analytics',
    'marketing',
  ];

  const STORAGE_TYPES = [
    'cookie',
    'local_storage',
    'session_storage',
    'indexed_db',
  ];

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

  function apiHeaders(
    includeContentType = false
  ) {
    const headers = {
      Accept: 'application/json',
    };

    if (includeContentType) {
      headers['Content-Type'] =
        'application/json';
    }

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

  function clone(value) {
    return JSON.parse(
      JSON.stringify(value)
    );
  }

  function emptyService(index = 1) {
    return {
      id: `service.${index}`,
      name: '',
      provider: '',
      category: 'preferences',
      purpose: '',
      privacy_url: null,
      enabled: true,
      cookies: [],
      storage: [],
    };
  }

  function normalizeService(service) {
    return {
      id: String(
        service?.id ?? ''
      ),
      name: String(
        service?.name ?? ''
      ),
      provider: String(
        service?.provider ?? ''
      ),
      category:
        CATEGORIES.includes(
          service?.category
        )
          ? service.category
          : 'preferences',
      purpose: String(
        service?.purpose ?? ''
      ),
      privacy_url:
        service?.privacy_url
          ? String(service.privacy_url)
          : null,
      enabled:
        service?.enabled === true,
      cookies:
        Array.isArray(service?.cookies)
          ? clone(service.cookies)
          : [],
      storage:
        Array.isArray(service?.storage)
          ? clone(service.storage)
          : [],
    };
  }

  class GoosializeCookiesPage
    extends HTMLElement {
    constructor() {
      super();

      this._mounted = false;
      this._state = 'idle';
      this._error = '';
      this._notice = '';
      this._services = [];
      this._editingIndex = null;
      this._saving = false;
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
      this._notice = '';

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
            ? data.services.map(
                normalizeService
              )
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

    _serviceMap() {
      const services = {};

      for (
        const service of
        this._services
      ) {
        services[service.id] = {
          name: service.name,
          provider: service.provider,
          category: service.category,
          purpose: service.purpose,
          privacy_url:
            service.privacy_url || null,
          enabled:
            service.enabled === true,
          cookies:
            clone(service.cookies),
          storage:
            clone(service.storage),
        };
      }

      return services;
    }

    _validateLocal() {
      const seen = new Set();

      for (
        let index = 0;
        index < this._services.length;
        index++
      ) {
        const service =
          this._services[index];

        if (
          !/^[a-z0-9][a-z0-9._-]*$/
            .test(service.id)
        ) {
          return `Invalid service ID at row ${index + 1}.`;
        }

        if (seen.has(service.id)) {
          return `Duplicate service ID: ${service.id}`;
        }

        seen.add(service.id);

        if (
          !service.name.trim()
          || !service.provider.trim()
          || !service.purpose.trim()
        ) {
          return `Name, provider and purpose are required for ${service.id}.`;
        }

        if (
          !CATEGORIES.includes(
            service.category
          )
        ) {
          return `Invalid category for ${service.id}.`;
        }


        for (
          let cookieIndex = 0;
          cookieIndex < service.cookies.length;
          cookieIndex++
        ) {
          const cookie =
            service.cookies[
              cookieIndex
            ];

          if (
            !String(
              cookie?.name ?? ''
            ).trim()
            || !String(
              cookie?.purpose ?? ''
            ).trim()
          ) {
            return `Cookie name and purpose are required for ${service.id}.`;
          }
        }

        for (
          let storageIndex = 0;
          storageIndex < service.storage.length;
          storageIndex++
        ) {
          const item =
            service.storage[
              storageIndex
            ];

          if (
            !STORAGE_TYPES.includes(
              item?.type
            )
          ) {
            return `Invalid storage type for ${service.id}.`;
          }

          if (
            !String(
              item?.key ?? ''
            ).trim()
            || !String(
              item?.purpose ?? ''
            ).trim()
          ) {
            return `Storage key and purpose are required for ${service.id}.`;
          }
        }
      }

      return '';
    }

    async _save() {
      if (this._saving) {
        return;
      }

      this._error = '';
      this._notice = '';

      const validation =
        this._validateLocal();

      if (validation) {
        this._error = validation;
        this._render();
        return;
      }

      this._saving = true;
      this._render();

      try {
        const response = await fetch(
          apiUrl(
            '/goosialize-cookies/services'
          ),
          {
            method: 'PATCH',
            headers:
              apiHeaders(true),
            credentials: 'same-origin',
            body: JSON.stringify({
              services:
                this._serviceMap(),
            }),
          }
        );

        const payload =
          await response.json();

        if (!response.ok) {
          const detail =
            payload?.detail
            || payload?.message
            || `Save failed (${response.status}).`;

          throw new Error(
            String(detail)
          );
        }

        const data =
          payload?.data ?? payload;

        this._services =
          Array.isArray(data?.services)
            ? data.services.map(
                normalizeService
              )
            : this._services;

        this._notice =
          'Service registry saved.';

        this._editingIndex = null;
      } catch (error) {
        this._error =
          error instanceof Error
            ? error.message
            : 'Unable to save services.';
      } finally {
        this._saving = false;
        this._render();
      }
    }

    _addService() {
      const next =
        this._services.length + 1;

      this._services.push(
        emptyService(next)
      );

      this._editingIndex =
        this._services.length - 1;

      this._render();
    }

    _removeService(index) {
      if (
        index < 0 ||
        index >= this._services.length
      ) {
        return;
      }

      this._services.splice(
        index,
        1
      );

      this._editingIndex = null;
      this._notice =
        'Service removed locally. Save to persist.';

      this._render();
    }

    _field(
      label,
      name,
      value,
      type = 'text'
    ) {
      return `
        <label class="field">
          <span>${escapeHtml(label)}</span>

          <input
            type="${escapeHtml(type)}"
            name="${escapeHtml(name)}"
            value="${escapeHtml(value)}"
          >
        </label>
      `;
    }

    _cookieRows(service) {
      if (!service.cookies.length) {
        return `
          <div class="declaration-empty">
            No cookies declared.
          </div>
        `;
      }

      return service.cookies
        .map(
          (cookie, index) => `
            <div
              class="declaration-row"
              data-cookie-index="${index}"
            >
              <input
                type="text"
                data-cookie-field="name"
                value="${escapeHtml(
                  cookie.name ?? ''
                )}"
                placeholder="Cookie name"
              >

              <input
                type="text"
                data-cookie-field="purpose"
                value="${escapeHtml(
                  cookie.purpose ?? ''
                )}"
                placeholder="Purpose"
              >

              <input
                type="text"
                data-cookie-field="duration"
                value="${escapeHtml(
                  cookie.duration ?? ''
                )}"
                placeholder="Duration"
              >

              <button
                type="button"
                class="link-button link-button--danger"
                data-action="remove-cookie"
                data-index="${index}"
              >
                Remove
              </button>
            </div>
          `
        )
        .join('');
    }

    _storageRows(service) {
      if (!service.storage.length) {
        return `
          <div class="declaration-empty">
            No browser storage declared.
          </div>
        `;
      }

      return service.storage
        .map(
          (item, index) => {
            const options =
              STORAGE_TYPES
                .map(
                  (type) => `
                    <option
                      value="${escapeHtml(type)}"
                      ${item.type === type
                        ? 'selected'
                        : ''}
                    >
                      ${escapeHtml(type)}
                    </option>
                  `
                )
                .join('');

            return `
              <div
                class="declaration-row
                       declaration-row--storage"
                data-storage-index="${index}"
              >
                <select
                  data-storage-field="type"
                >
                  ${options}
                </select>

                <input
                  type="text"
                  data-storage-field="key"
                  value="${escapeHtml(
                    item.key ?? ''
                  )}"
                  placeholder="Storage key"
                >

                <input
                  type="text"
                  data-storage-field="purpose"
                  value="${escapeHtml(
                    item.purpose ?? ''
                  )}"
                  placeholder="Purpose"
                >

                <button
                  type="button"
                  class="link-button link-button--danger"
                  data-action="remove-storage"
                  data-index="${index}"
                >
                  Remove
                </button>
              </div>
            `;
          }
        )
        .join('');
    }

    _editor(service, index) {
      const categoryOptions =
        CATEGORIES
          .map(
            (category) => `
              <option
                value="${escapeHtml(category)}"
                ${service.category === category
                  ? 'selected'
                  : ''}
              >
                ${escapeHtml(category)}
              </option>
            `
          )
          .join('');

      return `
        <section
          class="editor"
          data-editor-index="${index}"
        >
          <div class="editor-grid">
            ${this._field(
              'Service ID',
              'id',
              service.id
            )}

            ${this._field(
              'Name',
              'name',
              service.name
            )}

            ${this._field(
              'Provider',
              'provider',
              service.provider
            )}

            <label class="field">
              <span>Category</span>

              <select name="category">
                ${categoryOptions}
              </select>
            </label>

            ${this._field(
              'Privacy URL',
              'privacy_url',
              service.privacy_url ?? '',
              'url'
            )}

            <label class="field field--wide">
              <span>Purpose</span>

              <textarea
                name="purpose"
                rows="3"
              >${escapeHtml(
                service.purpose
              )}</textarea>
            </label>

            <label class="checkbox-field">
              <input
                type="checkbox"
                name="enabled"
                ${service.enabled
                  ? 'checked'
                  : ''}
              >

              <span>Enabled</span>
            </label>
          </div>

          <section class="declarations">
            <div class="declaration-section">
              <div class="declaration-header">
                <div>
                  <strong>Cookies</strong>
                  <span>
                    Expected cookie declarations.
                  </span>
                </div>

                <button
                  type="button"
                  class="link-button"
                  data-action="add-cookie"
                >
                  Add cookie
                </button>
              </div>

              <div class="declaration-list">
                ${this._cookieRows(service)}
              </div>
            </div>

            <div class="declaration-section">
              <div class="declaration-header">
                <div>
                  <strong>Browser storage</strong>
                  <span>
                    Local, session or IndexedDB
                    metadata declarations.
                  </span>
                </div>

                <button
                  type="button"
                  class="link-button"
                  data-action="add-storage"
                >
                  Add storage
                </button>
              </div>

              <div class="declaration-list">
                ${this._storageRows(service)}
              </div>
            </div>
          </section>

          <div class="editor-actions">
            <button
              type="button"
              class="button"
              data-action="done"
            >
              Done
            </button>

            <button
              type="button"
              class="button button--danger"
              data-action="remove"
            >
              Remove service
            </button>
          </div>
        </section>
      `;
    }

    _serviceRow(service, index) {
      const cookies =
        Array.isArray(service.cookies)
          ? service.cookies.length
          : 0;

      const storage =
        Array.isArray(service.storage)
          ? service.storage.length
          : 0;

      return `
        <tr>
          <td>
            <strong>
              ${escapeHtml(service.name || service.id)}
            </strong>

            <span class="provider">
              ${escapeHtml(service.provider || '—')}
            </span>

            <code>
              ${escapeHtml(service.id)}
            </code>
          </td>

          <td>
            ${escapeHtml(service.category)}
          </td>

          <td>
            ${escapeHtml(service.purpose || '—')}
          </td>

          <td>${cookies}</td>
          <td>${storage}</td>

          <td>
            ${service.enabled
              ? 'Enabled'
              : 'Disabled'}
          </td>

          <td>
            <button
              type="button"
              class="link-button"
              data-action="edit"
              data-index="${index}"
            >
              Edit
            </button>
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

      if (
        this._state === 'error'
        && this._services.length === 0
      ) {
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
              class="button"
              data-action="retry"
            >
              Try again
            </button>
          </div>
        `;
      }

      const editor =
        this._editingIndex !== null
        && this._services[
          this._editingIndex
        ]
          ? this._editor(
              this._services[
                this._editingIndex
              ],
              this._editingIndex
            )
          : '';

      const rows =
        this._services.length
          ? this._services
              .map(
                (service, index) =>
                  this._serviceRow(
                    service,
                    index
                  )
              )
              .join('')
          : `
            <tr>
              <td
                colspan="7"
                class="empty"
              >
                No consent services configured.
              </td>
            </tr>
          `;

      return `
        ${this._error
          ? `
            <div
              class="notice notice--error"
              role="alert"
            >
              ${escapeHtml(this._error)}
            </div>
          `
          : ''}

        ${this._notice
          ? `
            <div
              class="notice notice--success"
              role="status"
            >
              ${escapeHtml(this._notice)}
            </div>
          `
          : ''}

        <div class="toolbar">
          <div>
            <strong>
              ${this._services.length}
              ${this._services.length === 1
                ? 'service'
                : 'services'}
            </strong>
          </div>

          <div class="toolbar-actions">
            <button
              type="button"
              class="button button--secondary"
              data-action="add"
            >
              Add service
            </button>

            <button
              type="button"
              class="button"
              data-action="save"
              ${this._saving
                ? 'disabled'
                : ''}
            >
              ${this._saving
                ? 'Saving…'
                : 'Save services'}
            </button>
          </div>
        </div>

        ${editor}

        <div class="notice">
          Service metadata only.
          Executable script management is
          not available in this interface.
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
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>
              ${rows}
            </tbody>
          </table>
        </div>
      `;
    }

    _bind() {
      if (!this.shadowRoot) {
        return;
      }

      this.shadowRoot
        .querySelector(
          '[data-action="retry"]'
        )
        ?.addEventListener(
          'click',
          () => this._load()
        );

      this.shadowRoot
        .querySelector(
          '[data-action="add"]'
        )
        ?.addEventListener(
          'click',
          () => this._addService()
        );

      this.shadowRoot
        .querySelector(
          '[data-action="save"]'
        )
        ?.addEventListener(
          'click',
          () => this._save()
        );

      for (
        const button of
        this.shadowRoot.querySelectorAll(
          '[data-action="edit"]'
        )
      ) {
        button.addEventListener(
          'click',
          () => {
            this._editingIndex =
              Number(
                button.dataset.index
              );

            this._render();
          }
        );
      }

      const editor =
        this.shadowRoot.querySelector(
          '[data-editor-index]'
        );

      if (!editor) {
        return;
      }

      const index =
        Number(
          editor.dataset.editorIndex
        );

      const service =
        this._services[index];

      if (!service) {
        return;
      }

      const sync = () => {
        const value = (name) =>
          editor.querySelector(
            `[name="${name}"]`
          );

        service.id =
          value('id')?.value ?? '';

        service.name =
          value('name')?.value ?? '';

        service.provider =
          value('provider')?.value ?? '';

        service.category =
          value('category')?.value
          ?? 'preferences';

        service.purpose =
          value('purpose')?.value ?? '';

        service.privacy_url =
          value('privacy_url')?.value
            ?.trim()
          || null;

        service.enabled =
          value('enabled')?.checked
          === true;
      };

      editor.addEventListener(
        'input',
        sync
      );

      editor.addEventListener(
        'change',
        sync
      );

      editor
        .querySelector(
          '[data-action="add-cookie"]'
        )
        ?.addEventListener(
          'click',
          () => {
            sync();

            service.cookies.push({
              name: '',
              purpose: '',
              duration: null,
            });

            this._render();
          }
        );

      editor
        .querySelector(
          '[data-action="add-storage"]'
        )
        ?.addEventListener(
          'click',
          () => {
            sync();

            service.storage.push({
              type: 'local_storage',
              key: '',
              purpose: '',
            });

            this._render();
          }
        );

      for (
        const button of
        editor.querySelectorAll(
          '[data-action="remove-cookie"]'
        )
      ) {
        button.addEventListener(
          'click',
          () => {
            sync();

            const cookieIndex =
              Number(
                button.dataset.index
              );

            service.cookies.splice(
              cookieIndex,
              1
            );

            this._render();
          }
        );
      }

      for (
        const button of
        editor.querySelectorAll(
          '[data-action="remove-storage"]'
        )
      ) {
        button.addEventListener(
          'click',
          () => {
            sync();

            const storageIndex =
              Number(
                button.dataset.index
              );

            service.storage.splice(
              storageIndex,
              1
            );

            this._render();
          }
        );
      }

      const syncDeclarations = () => {
        for (
          const row of
          editor.querySelectorAll(
            '[data-cookie-index]'
          )
        ) {
          const cookieIndex =
            Number(
              row.dataset.cookieIndex
            );

          const cookie =
            service.cookies[
              cookieIndex
            ];

          if (!cookie) {
            continue;
          }

          cookie.name =
            row.querySelector(
              '[data-cookie-field="name"]'
            )?.value ?? '';

          cookie.purpose =
            row.querySelector(
              '[data-cookie-field="purpose"]'
            )?.value ?? '';

          cookie.duration =
            row.querySelector(
              '[data-cookie-field="duration"]'
            )?.value?.trim()
            || null;
        }

        for (
          const row of
          editor.querySelectorAll(
            '[data-storage-index]'
          )
        ) {
          const storageIndex =
            Number(
              row.dataset.storageIndex
            );

          const item =
            service.storage[
              storageIndex
            ];

          if (!item) {
            continue;
          }

          item.type =
            row.querySelector(
              '[data-storage-field="type"]'
            )?.value
            ?? 'local_storage';

          item.key =
            row.querySelector(
              '[data-storage-field="key"]'
            )?.value ?? '';

          item.purpose =
            row.querySelector(
              '[data-storage-field="purpose"]'
            )?.value ?? '';
        }
      };

      editor.addEventListener(
        'input',
        syncDeclarations
      );

      editor.addEventListener(
        'change',
        syncDeclarations
      );

      editor
        .querySelector(
          '[data-action="done"]'
        )
        ?.addEventListener(
          'click',
          () => {
            sync();
            this._editingIndex = null;
            this._render();
          }
        );

      editor
        .querySelector(
          '[data-action="remove"]'
        )
        ?.addEventListener(
          'click',
          () => {
            this._removeService(index);
          }
        );
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

          button,
          input,
          textarea,
          select {
            font: inherit;
          }

          .page {
            display: grid;
            gap: 1rem;
          }

          h1 {
            margin: 0;
            font-size: 1.6rem;
          }

          .subtitle {
            margin: 0.35rem 0 0;
            opacity: 0.7;
          }

          .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
          }

          .toolbar-actions,
          .editor-actions {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
          }

          .button {
            min-height: 2.5rem;
            padding: 0.55rem 0.9rem;
            color: #fff;
            background: #292330;
            border: 1px solid #4b3a5d;
            border-radius: 0.45rem;
            font-weight: 650;
            cursor: pointer;
          }

          .button--secondary {
            color:
              var(--foreground, #202124);
            background:
              var(--background, #fff);
            border-color:
              var(--border, #d5d7da);
          }

          .button--danger {
            background: #991b1b;
            border-color: #b91c1c;
          }

          .button:disabled {
            opacity: 0.6;
            cursor: wait;
          }

          .button:focus-visible,
          .link-button:focus-visible,
          input:focus-visible,
          textarea:focus-visible,
          select:focus-visible {
            outline:
              2px solid
              var(--primary, #a855f7);
            outline-offset: 2px;
          }

          .notice,
          .state-card,
          .editor {
            padding: 0.9rem 1rem;
            background:
              var(--card, #fff);
            border:
              1px solid
              var(--border, #d5d7da);
            border-radius: 0.55rem;
          }

          .notice--error,
          .state-card--error {
            border-color: #dc2626;
          }

          .notice--success {
            border-color: #16a34a;
          }

          .state-card {
            display: grid;
            gap: 0.5rem;
            text-align: center;
          }

          .editor {
            display: grid;
            gap: 1rem;
          }

          .editor-grid {
            display: grid;
            grid-template-columns:
              repeat(
                2,
                minmax(0, 1fr)
              );
            gap: 0.9rem;
          }

          .field {
            display: grid;
            gap: 0.35rem;
          }

          .field--wide {
            grid-column: 1 / -1;
          }

          .field > span,
          .checkbox-field > span {
            font-size: 0.82rem;
            font-weight: 650;
          }

          input,
          textarea,
          select {
            width: 100%;
            padding: 0.6rem 0.7rem;
            color:
              var(--foreground, #202124);
            background:
              var(--background, #fff);
            border:
              1px solid
              var(--border, #d5d7da);
            border-radius: 0.4rem;
          }

          .checkbox-field {
            display: flex;
            align-items: center;
            gap: 0.5rem;
          }

          .checkbox-field input {
            width: auto;
          }

          .declarations {
            display: grid;
            gap: 1rem;
          }

          .declaration-section {
            display: grid;
            gap: 0.75rem;
            padding: 0.85rem;
            border:
              1px solid
              var(--border, #d5d7da);
            border-radius: 0.5rem;
          }

          .declaration-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
          }

          .declaration-header > div {
            display: grid;
            gap: 0.15rem;
          }

          .declaration-header span {
            font-size: 0.78rem;
            opacity: 0.66;
          }

          .declaration-list {
            display: grid;
            gap: 0.55rem;
          }

          .declaration-row {
            display: grid;
            grid-template-columns:
              minmax(8rem, 0.8fr)
              minmax(12rem, 1.5fr)
              minmax(7rem, 0.7fr)
              auto;
            gap: 0.55rem;
            align-items: center;
          }

          .declaration-row--storage {
            grid-template-columns:
              minmax(8rem, 0.8fr)
              minmax(10rem, 1fr)
              minmax(14rem, 1.5fr)
              auto;
          }

          .declaration-empty {
            padding: 0.7rem;
            text-align: center;
            border:
              1px dashed
              var(--border, #d5d7da);
            border-radius: 0.4rem;
            font-size: 0.82rem;
            opacity: 0.65;
          }

          .link-button--danger {
            color: #b91c1c;
          }

          .editor-note {
            font-size: 0.84rem;
            opacity: 0.72;
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
            min-width: 56rem;
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

          .provider,
          code {
            display: block;
            margin-top: 0.2rem;
          }

          .provider {
            font-size: 0.8rem;
            opacity: 0.64;
          }

          code {
            font-size: 0.72rem;
            opacity: 0.6;
          }

          .link-button {
            padding: 0;
            color:
              var(--primary, #7c3aed);
            background: transparent;
            border: 0;
            font-weight: 650;
            cursor: pointer;
          }

          .empty {
            padding: 2rem 1rem;
            text-align: center;
            opacity: 0.68;
          }

          @media (max-width: 720px) {
            .editor-grid {
              grid-template-columns: 1fr;
            }

            .field--wide {
              grid-column: auto;
            }

            .declaration-row,
            .declaration-row--storage {
              grid-template-columns: 1fr;
            }
          }

          @media (
            prefers-reduced-motion:
            reduce
          ) {
            *,
            *::before,
            *::after {
              transition:
                none !important;
            }
          }
        </style>

        <section class="page">
          <header>
            <h1>
              Goosialize Cookies
            </h1>

            <p class="subtitle">
              Consent service registry.
            </p>
          </header>

          ${this._content()}
        </section>
      `;

      this._bind();
    }
  }

  customElements.define(
    TAG,
    GoosializeCookiesPage
  );
})();
