/* Endpoint dan URL setempat diberikan oleh halaman induk selepas guard PPF. */
(() => {
    'use strict';
    const root = document.getElementById('ppf-dashboard');
    if (!root) return;
    const find = (id) => root.querySelector('#ppf-' + id);
    const form = find('filters');
    const fields = [...form.elements].filter((field) => field.name);
    const names = fields.map((field) => field.name);
    const views = ['all', 'requested', 'completed'];
    const number = new Intl.NumberFormat('ms-MY');
    const labels = { all: 'Semua aduan', requested: 'Aduan baharu', completed: 'Aduan siap' };
    let controller;
    let currentPage = 1;
    let pages = 1;
    let options = {};
    let selected = new URLSearchParams(location.search);
    const panels = ['terima-aduan', 'maklum-balas', 'graf-maklum-balas'];
    function syncPanel() {
        const active = location.hash.slice(1);
        const hasPanel = panels.includes(active);
        panels.forEach((name) => { find(name).hidden = name !== active; });
        find('results').hidden = hasPanel;
        root.querySelectorAll('[data-panel]').forEach((link) => {
            if (hasPanel && link.dataset.panel === active) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
        if (hasPanel) {
            root.querySelectorAll('.ppf-sidebar [data-view]').forEach((link) => link.removeAttribute('aria-current'));
            status.textContent = '';
            errorPanel.hidden = true;
        }
        return hasPanel;
    }
    function localUrl(value) {
        if (!value) throw new Error('configuration');
        const url = new URL(value, location.href);
        if (url.origin !== location.origin || !/^https?:$/.test(url.protocol)) throw new Error('configuration');
        return url;
    }
    function element(tag, text, className) {
        const node = document.createElement(tag);
        if (text !== undefined) node.textContent = text;
        if (className) node.className = className;
        return node;
    }
    const status = find('status');
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');
    const errorPanel = find('error');
    errorPanel.hidden = true;
    errorPanel.setAttribute('role', 'alert');
    const summaryNodes = [...root.querySelectorAll('.ppf-stat')];
    function clear() {
        summaryNodes.forEach((node) => node.querySelector('strong').textContent = '—');
        find('rows').replaceChildren();
        find('empty').hidden = true;
        find('prev').disabled = true;
        find('next').disabled = true;
        find('updated').textContent = 'Data belum dimuatkan';
        find('page-label').textContent = 'Data belum dimuatkan';
    }
    function fail(message, retry) {
        clear();
        errorPanel.replaceChildren(element('p', message));
        if (retry) {
            const button = element('button', 'Cuba semula');
            button.type = 'button';
            button.addEventListener('click', () => load());
            errorPanel.append(button);
        }
        errorPanel.hidden = false;
        status.textContent = '';
    }
    function normalize() {
        const params = new URLSearchParams();
        params.set('view', views.includes(selected.get('view')) ? selected.get('view') : 'all');
        names.forEach((name) => { if (selected.get(name)) params.set(name, selected.get(name)); });
        const page = Number(selected.get('page'));
        params.set('page', Number.isSafeInteger(page) && page > 0 ? String(page) : '1');
        params.set('per_page', '10');
        selected = params;
    }
    function syncForm() {
        fields.forEach((field) => {
            const value = selected.get(field.name) || '';
            if (field.tagName === 'SELECT') {
                const first = field.options[0].cloneNode(true);
                field.replaceChildren(first);
                (options[field.dataset.options] || []).forEach((item) => field.add(new Option(item.label, item.value)));
                if (value && ![...field.options].some((opt) => opt.value === value)) field.add(new Option(value, value));
            }
            field.value = value;
        });
        root.querySelectorAll('.ppf-sidebar [data-view]').forEach((link) => {
            const active = link.dataset.view === selected.get('view') && (link.hasAttribute('data-overview') === (location.hash === '#ringkasan'));
            if (active) link.setAttribute('aria-current', 'page'); else link.removeAttribute('aria-current');
        });
        find('list-title').textContent = labels[selected.get('view')];
    }
    function navigate(params, hash = '') {
        selected = params;
        normalize();
        const url = new URL(location.href);
        url.search = selected.toString();
        url.hash = hash;
        history.pushState(null, '', url);
        syncForm();
        load();
    }
    function detailLink(record) {
        const id = Number(record.wr_id);
        if (!Number.isSafeInteger(id) || id < 1) throw new Error('response');
        const url = localUrl(root.dataset.detailUrl);
        url.searchParams.set('wr_id', String(id));
        selected.forEach((value, key) => {
            if (key !== 'wr_id' && key !== 'per_page') url.searchParams.set(key, value);
        });
        const link = element('a', '#' + id);
        link.href = url.href;
        return link;
    }
    function cell(row, label, content, secondary) {
        const td = element('td');
        td.dataset.label = label;
        const box = element('div');
        box.append(content instanceof Node ? content : element('span', content || '—'));
        if (secondary) box.append(element('small', secondary));
        td.append(box);
        row.append(td);
    }
    function render(data) {
        if (!data || data.ok !== true || !data.summary || !Array.isArray(data.complaints) || !Array.isArray(data.attention) || !data.pagination || !data.filters) throw new Error('response');
        ['all', 'requested', 'completed'].forEach((key, i) => {
            if (!Number.isSafeInteger(data.summary[key]) || data.summary[key] < 0) throw new Error('response');
            summaryNodes[i].querySelector('strong').textContent = number.format(data.summary[key]);
        });
        const p = data.pagination;
        if (![p.page, p.per_page, p.total, p.pages].every(Number.isSafeInteger) || p.page < 1 || p.per_page !== 10 || p.total < 0 || p.pages < 0 || p.page > Math.max(1, p.pages)) throw new Error('response');
        options = data.filters;
        for (const key of ['states', 'sites', 'zones', 'sections', 'problem_groups']) {
            if (!Array.isArray(options[key]) || options[key].some((item) => !item || typeof item.label !== 'string' || typeof item.value !== 'string')) throw new Error('response');
        }
        const rows = document.createDocumentFragment();
        data.complaints.forEach((record) => {
            const row = element('tr');
            cell(row, 'No. / Tarikh', detailLink(record), record.date_requested);
            cell(row, 'Pengadu', record.requestor, record.requestor_id);
            cell(row, 'Kampus / Lokasi', record.site_name, record.location_label);
            cell(row, 'Kategori', record.section_label, record.problem_group_label);
            cell(row, 'Status', element('span', record.status_label || '—', 'ppf-badge'));
            rows.append(row);
        });
        find('rows').replaceChildren(rows);
        find('empty').hidden = data.complaints.length !== 0;
        currentPage = p.page;
        pages = p.pages;
        selected.set('page', String(currentPage));
        const corrected = new URL(location.href);
        corrected.search = selected.toString();
        history.replaceState(null, '', corrected);
        find('prev').disabled = currentPage <= 1;
        find('next').disabled = currentPage >= pages;
        find('page-label').textContent = number.format(p.total) + ' aduan · Halaman ' + currentPage + ' daripada ' + Math.max(1, pages);
        const date = new Date(data.updated_at);
        find('updated').textContent = Number.isNaN(date.getTime()) ? 'Data diterima' : 'Dikemas kini ' + date.toLocaleString('ms-MY');
        syncForm();
        status.textContent = number.format(p.total) + ' aduan ditemui.';
    }
    async function load() {
        if (controller) controller.abort();
        if (syncPanel()) { clear(); find('results').setAttribute('aria-busy', 'false'); return; }
        const request = new AbortController();
        controller = request;
        clear();
        errorPanel.hidden = true;
        const results = find('results');
        results.setAttribute('aria-busy', 'true');
        status.textContent = 'Memuatkan aduan…';
        try {
            const url = localUrl(root.dataset.endpoint);
            selected.forEach((value, key) => url.searchParams.set(key, value));
            const response = await fetch(url, { credentials: 'same-origin', cache: 'no-store', signal: request.signal, headers: { Accept: 'application/json' } });
            if (request.signal.aborted) return;
            if (response.status === 401) {
                fail('Sesi telah tamat. Sila log masuk semula.', false);
                const link = element('a', 'Log masuk');
                link.href = localUrl(root.dataset.loginUrl).href;
                errorPanel.append(link);
                return;
            }
            if (response.status === 403) {
                let reason;
                try { reason = (await response.json()).reason; } catch { /* HTTP status kekal sumber keputusan akses. */ }
                if (request.signal.aborted) return;
                fail(reason === 'intranet_required' ? 'Akses rangkaian dalaman diperlukan. Sambungkan peranti ke intranet UiTM.' : 'Akses Portal PPF hanya dibenarkan kepada staf PPF.', false);
                return;
            }
            if (response.status === 400) { fail('Penapis tidak sah. Semak pilihan carian dan julat tarikh.', false); return; }
            if (response.status === 405) { fail('Kaedah permintaan tidak dibenarkan. Hubungi pentadbir sistem.', false); return; }
            if (response.status === 500) { fail('Pelayan tidak dapat memuatkan aduan. Cuba semula sebentar lagi.', true); return; }
            if (!response.ok) throw new Error('network');
            const data = await response.json();
            if (!request.signal.aborted) render(data);
        } catch (error) {
            if (request.signal.aborted) return;
            fail(error.message === 'configuration' ? 'Portal belum disambungkan. Sila hubungi pentadbir sistem.' : 'Data tidak dapat dimuatkan. Semak sambungan dan cuba semula.', error.message !== 'configuration');
        } finally {
            if (!request.signal.aborted) results.setAttribute('aria-busy', 'false');
        }
    }
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const from = form.elements.date_from;
        const to = form.elements.date_to;
        to.setCustomValidity(from.value && to.value && from.value > to.value ? 'Tarikh akhir mesti selepas atau sama dengan tarikh mula.' : '');
        if (!form.reportValidity()) return;
        const params = new URLSearchParams(new FormData(form));
        params.set('view', selected.get('view'));
        navigate(params);
    });
    form.addEventListener('input', () => form.elements.date_to.setCustomValidity(''));
    form.addEventListener('reset', (event) => { event.preventDefault(); form.elements.date_to.setCustomValidity(''); navigate(new URLSearchParams()); });
    find('empty-reset').addEventListener('click', () => form.reset());
    form.addEventListener('change', (event) => {
        const dependencies = { state_id: ['site_id', 'zone_id'], site_id: ['zone_id'], section: ['prob_group'] };
        const children = dependencies[event.target.name];
        if (!children) return;
        children.forEach((name) => form.elements[name].value = '');
        const params = new URLSearchParams(new FormData(form));
        params.set('view', selected.get('view'));
        navigate(params);
    });
    root.querySelectorAll('[data-view]').forEach((link) => link.addEventListener('click', (event) => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
        event.preventDefault();
        const params = new URLSearchParams(selected);
        params.set('view', link.dataset.view);
        params.delete('page');
        navigate(params, link.hasAttribute('data-overview') ? 'ringkasan' : '');
    }));
    ['prev', 'next'].forEach((key) => find(key).addEventListener('click', () => {
        const params = new URLSearchParams(selected);
        params.set('page', String(currentPage + (key === 'prev' ? -1 : 1)));
        navigate(params);
    }));
    find('toggle').addEventListener('click', () => {
        form.hidden = !form.hidden;
        find('toggle').setAttribute('aria-expanded', String(!form.hidden));
    });
    window.addEventListener('popstate', () => { selected = new URLSearchParams(location.search); normalize(); syncForm(); load(); });
    window.addEventListener('hashchange', () => { syncForm(); load(); });
    const entry = find('entry-form');
    const mediumGroup = find('medium-group');
    const mediumOptions = find('medium-options');
    function updateMedium() {
        mediumOptions.hidden = !mediumGroup.value;
        mediumOptions.querySelectorAll('label').forEach((label) => {
            const input = label.querySelector('input');
            const visible = label.dataset.mediumGroup === mediumGroup.value;
            label.hidden = !visible;
            input.disabled = !visible;
            input.required = visible;
            input.checked = visible && mediumGroup.value !== 'bertulis';
        });
    }
    mediumGroup.addEventListener('change', updateMedium);
    entry.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!entry.reportValidity()) return;
        const values = new FormData(entry);
        try {
            const url = localUrl(root.dataset.entryUrl);
            url.searchParams.set('txt_emnumber', values.get('txt_emnumber'));
            url.searchParams.set('medium', values.get('medium'));
            location.assign(url.href);
        } catch {
            find('entry-status').textContent = 'Borang penuh belum disambungkan untuk pekerja yang dipilih. Sila hubungi pentadbir sistem.';
        }
    });
    entry.addEventListener('reset', () => {
        mediumOptions.hidden = true;
        mediumOptions.querySelectorAll('input').forEach((input) => { input.disabled = true; input.required = false; });
        find('entry-status').textContent = '';
    });
    window.addEventListener('pagehide', () => { entry.reset(); });
    updateMedium();
    window.addEventListener('pagehide', () => { if (controller) controller.abort(); clear(); });
    window.addEventListener('pageshow', (event) => { if (event.persisted) load(); });
    normalize();
    syncForm();
    load();
})();
