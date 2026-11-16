/* Panel maklum balas dan graf; semua data datang daripada endpoint PPF terlindung. */
(() => {
    'use strict';
    const root = document.getElementById('ppf-dashboard');
    if (!root) return;
    const common = ['state_id', 'site_id', 'zone_id', 'section', 'prob_group', 'date_from', 'date_to'];
    const labels = { all: 'Keseluruhan', ada: 'Ada maklum balas', memuaskan: 'Memuaskan', tidak_memuaskan: 'Tidak memuaskan', tiada: 'Tiada maklum balas', total: 'Jumlah' };
    const countKeys = ['memuaskan', 'tidak_memuaskan', 'tiada'];
    const number = new Intl.NumberFormat('ms-MY');
    const scriptUrl = new URL(document.currentScript.src);
    const ajaxBase = new URL('../../ajax/', scriptUrl);
    const pageBase = new URL('../../pages/', scriptUrl);
    function node(tag, text, cls) {
        const el = document.createElement(tag);
        if (text !== undefined) el.textContent = text;
        if (cls) el.className = cls;
        return el;
    }
    function count(value) {
        if (!Number.isSafeInteger(value) || value < 0) throw new Error('invalid_response');
        return value;
    }
    const panels = [setup('maklum-balas', false), setup('graf-maklum-balas', true)];
    function setup(hash, chart) {
        const panel = document.getElementById('ppf-' + hash);
        const title = panel.querySelector('h2');
        const intro = panel.querySelector('.ppf-muted');
        panel.replaceChildren(title, intro);
        const status = node('p', '', 'ppf-status');
        status.setAttribute('role', 'status');
        const error = node('div', undefined, 'ppf-alert');
        error.setAttribute('role', 'alert'); error.hidden = true;
        const form = root.querySelector('#ppf-filters').cloneNode(true);
        form.id = 'ppf-' + hash + '-filters';
        form.hidden = false;
        [...form.elements].forEach(el => {
            if (el.name && !common.includes(el.name) && (chart || el.name !== 'wr_id')) el.closest('label').remove();
        });
        const mode = node('select'); mode.name = chart ? 'group' : 'feedback';
        (chart ? ['site', 'month'] : ['all', 'ada', ...countKeys]).forEach(value => mode.add(new Option(chart ? (value === 'site' ? 'Mengikut kampus' : 'Mengikut bulan') : labels[value], value)));
        const modeLabel = node('label', chart ? 'Kumpulan graf' : 'Penilaian'); modeLabel.append(mode); form.prepend(modeLabel);
        form.querySelector('[type=submit]').textContent = chart ? 'Papar graf' : 'Cari maklum balas';
        const summary = node('div', undefined, 'ppf-feedback-stats');
        const content = node('div', undefined, 'ppf-feedback-content');
        const footer = node('footer', undefined, 'ppf-pagination');
        const paginationText = node('p');
        const buttons = node('div'); const prev = node('button', 'Sebelumnya'); const next = node('button', 'Seterusnya');
        prev.type = next.type = 'button'; buttons.append(prev, next); footer.append(paginationText, buttons); footer.hidden = chart;
        panel.append(form, status, error, summary, content, footer);
        const state = { panel, hash, chart, form, status, error, summary, content, footer, prev, next, paginationText, page: 1, controller: null };
        const dateTo = form.elements.date_to;
        form.addEventListener('input', () => dateTo.setCustomValidity(''));
        form.addEventListener('submit', event => {
            event.preventDefault();
            dateTo.setCustomValidity(form.elements.date_from.value && dateTo.value && form.elements.date_from.value > dateTo.value ? 'Tarikh akhir mesti selepas atau sama dengan tarikh mula.' : '');
            if (form.reportValidity()) { state.page = 1; updateUrl(state); load(state); }
        });
        form.addEventListener('reset', event => {
            event.preventDefault();
            [...form.elements].forEach(el => { if (el.name) el.value = el === mode ? (chart ? 'site' : 'all') : ''; });
            dateTo.setCustomValidity(''); state.page = 1; updateUrl(state); load(state);
        });
        form.addEventListener('change', event => {
            const children = { state_id: ['site_id', 'zone_id'], site_id: ['zone_id'], section: ['prob_group'] }[event.target.name];
            if (!children) return;
            children.forEach(name => { form.elements[name].value = ''; });
            if (form.reportValidity()) { state.page = 1; updateUrl(state); load(state); }
        });
        prev.addEventListener('click', () => { state.page--; updateUrl(state); load(state); });
        next.addEventListener('click', () => { state.page++; updateUrl(state); load(state); });
        return state;
    }
    function params(state) {
        const p = new URLSearchParams(new FormData(state.form));
        [...p.keys()].forEach(key => { if (!p.get(key)) p.delete(key); });
        if (!state.chart) p.set('page', String(state.page));
        return p;
    }
    function updateUrl(state) {
        const url = new URL(location.href); url.search = params(state).toString(); url.hash = state.hash;
        history.pushState(null, '', url);
    }
    function options(state, filters) {
        for (const select of state.form.querySelectorAll('[data-options]')) {
            const list = filters?.[select.dataset.options];
            if (!Array.isArray(list)) throw new Error('invalid_response');
            const value = select.value; const first = select.options[0].cloneNode(true); select.replaceChildren(first);
            list.forEach(item => { if (typeof item?.value !== 'string' || typeof item.label !== 'string') throw new Error('invalid_response'); select.add(new Option(item.label, item.value)); });
            select.value = value;
        }
    }
    function renderStats(state, data) {
        const source = state.chart ? data.totals : data.summary;
        (state.chart ? ['total', ...countKeys] : ['all', 'ada', ...countKeys]).forEach(key => {
            const card = node('div', undefined, 'ppf-feedback-stat'); card.append(node('span', labels[key]), node('strong', number.format(count(source?.[key])))); state.summary.append(card);
        });
    }
    function table(headers, caption) {
        const wrap = node('div', undefined, 'ppf-table-wrap'); const t = node('table', undefined, 'ppf-table');
        const cap = node('caption', caption, 'ppf-sr-only'); const head = node('thead'); const tr = node('tr');
        headers.forEach(label => { const th = node('th', label); th.scope = 'col'; tr.append(th); });
        head.append(tr); const body = node('tbody'); t.append(cap, head, body); wrap.append(t);
        return {wrap, body};
    }
    function cell(row, label, value) {
        const td = node('td'); td.dataset.label = label; td.append(value instanceof Node ? value : node('span', value || '—')); row.append(td);
    }
    function renderFeedback(state, data) {
        if (!Array.isArray(data.complaints)) throw new Error('invalid_response');
        const p = data.pagination;
        if (!p || count(p.page) < 1 || count(p.per_page) !== 10 || count(p.total) < 0 || count(p.pages) < 1 || p.page > p.pages) throw new Error('invalid_response');
        state.page = p.page;
        const headers = ['No. / Tarikh', 'Pengadu', 'Kampus / Lokasi', 'Penilaian', 'Catatan'];
        const t = table(headers, 'Maklum balas pengguna bagi aduan siap');
        data.complaints.forEach(record => {
            const id = count(Number(record.wr_id)); if (!id) throw new Error('invalid_response');
            const link = node('a', '#' + id); const url = new URL('ppf_detail.php', pageBase); url.searchParams.set('wr_id', String(id)); link.href = url.href;
            const ref = node('div'); ref.append(link, node('small', record.date_requested || '—'));
            const site = node('div', record.site_name || '—'); site.append(node('small', record.location_label || '—'));
            const rating = node('div'); rating.append(node('span', record.feedback_label || '—', 'ppf-badge'), node('small', record.feedback_date || '—'));
            const row = node('tr'); [ref, record.requestor, site, rating, node('span', record.feedback_notes || '—', 'ppf-feedback-note')].forEach((value, i) => cell(row, headers[i], value)); t.body.append(row);
        });
        if (data.complaints.length) state.content.append(t.wrap); else state.content.append(node('p', 'Tiada maklum balas sepadan dengan penapis ini.', 'ppf-empty'));
        state.prev.disabled = p.page <= 1; state.next.disabled = p.page >= p.pages;
        state.paginationText.textContent = number.format(p.total) + ' aduan · Halaman ' + p.page + ' daripada ' + p.pages;
    }
    function renderChart(state, data) {
        if (!['site', 'month'].includes(data.group) || !Array.isArray(data.series)) throw new Error('invalid_response');
        if (!data.series.length || data.totals.total === 0) { state.content.append(node('p', 'Tiada data penilaian untuk penapis ini.', 'ppf-empty')); return; }
        const legend = node('ul', undefined, 'ppf-chart-legend'); countKeys.forEach(key => legend.append(node('li', labels[key], 'ppf-chart-' + key))); state.content.append(legend);
        const chart = node('div', undefined, 'ppf-chart'); chart.setAttribute('aria-hidden', 'true');
        const max = Math.max(1, ...data.series.map(row => count(row.total)));
        const headers = [data.group === 'site' ? 'Kampus' : 'Bulan', ...countKeys.map(key => labels[key]), 'Jumlah'];
        const t = table(headers, 'Angka bagi graf maklum balas');
        data.series.forEach(item => {
            const row = node('div', undefined, 'ppf-chart-row'); row.append(node('strong', item.label || item.key || '—'));
            const track = node('div', undefined, 'ppf-chart-track'); const stack = node('div', undefined, 'ppf-chart-stack'); stack.style.width = (count(item.total) / max * 100) + '%';
            countKeys.forEach(key => { const bar = node('span', undefined, 'ppf-chart-' + key); bar.style.width = (item.total ? count(item[key]) / item.total * 100 : 0) + '%'; stack.append(bar); });
            track.append(stack); row.append(track, node('span', number.format(item.total))); chart.append(row);
            const tr = node('tr'); [item.label || item.key, ...countKeys.map(key => number.format(count(item[key]))), number.format(item.total)].forEach((value, i) => cell(tr, headers[i], value)); t.body.append(tr);
        });
        state.content.append(chart, t.wrap);
    }
    function clear(state) { state.summary.replaceChildren(); state.content.replaceChildren(); state.paginationText.textContent = ''; state.prev.disabled = state.next.disabled = true; }
    async function load(state) {
        state.controller?.abort(); const request = new AbortController(); state.controller = request;
        clear(state); state.error.hidden = true; state.status.textContent = 'Memuatkan ' + (state.chart ? 'graf…' : 'maklum balas…'); state.content.setAttribute('aria-busy', 'true');
        try {
            const endpoint = new URL(state.chart ? 'ppf_feedback_chart.php' : 'ppf_feedback.php', ajaxBase); endpoint.search = params(state).toString();
            if (endpoint.origin !== location.origin) throw new Error('configuration');
            const response = await fetch(endpoint, {credentials:'same-origin', cache:'no-store', signal:request.signal, headers:{Accept:'application/json'}});
            const data = await response.json(); if (request.signal.aborted) return;
            if (!response.ok || data.ok !== true) {
                const messages = {401:'Sesi telah tamat. Sila log masuk semula.',403:data.reason === 'intranet_required' ? 'Akses rangkaian dalaman diperlukan.' : 'Akses hanya dibenarkan kepada staf PPF atau role DFMS.',400:'Penapis tidak sah. Semak pilihan dan julat tarikh.',405:'Kaedah permintaan tidak dibenarkan.'};
                const error = new Error(messages[response.status] || 'Data tidak dapat dimuatkan. Cuba semula sebentar lagi.'); error.noRetry = response.status >= 400 && response.status < 500; error.login = response.status === 401; throw error;
            }
            options(state, data.filters); renderStats(state, data);
            if (state.chart) renderChart(state, data); else renderFeedback(state, data);
            const time = new Date(data.updated_at); state.status.textContent = Number.isNaN(time.getTime()) ? 'Data diterima.' : 'Dikemas kini ' + time.toLocaleString('ms-MY');
        } catch(error) {
            if (request.signal.aborted) return;
            clear(state); state.status.textContent = ''; state.error.replaceChildren(node('p', error.noRetry ? error.message : 'Data tidak dapat dimuatkan. Semak sambungan dan cuba semula.'));
            if (error.login) { const link = node('a', 'Log masuk'); link.href = new URL('../../index.php', scriptUrl).href; state.error.append(link); }
            else if (!error.noRetry) { const button = node('button', 'Cuba semula'); button.type = 'button'; button.addEventListener('click', () => load(state)); state.error.append(button); }
            state.error.hidden = false;
        } finally { if (!request.signal.aborted) state.content.setAttribute('aria-busy', 'false'); }
    }
    function activate() {
        panels.forEach(state => {
            state.controller?.abort(); clear(state); state.error.hidden = true; state.status.textContent = '';
            if (location.hash !== '#' + state.hash) return;
            const p = new URLSearchParams(location.search);
            [...state.form.elements].forEach(el => {
                if (!el.name) return; const value = p.get(el.name);
                if (el.matches('[data-options]') && value && ![...el.options].some(o => o.value === value)) el.add(new Option(value,value));
                el.value = value || (el.name === 'group' ? 'site' : el.name === 'feedback' ? 'all' : '');
            });
            const page = Number(p.get('page')); state.page = Number.isSafeInteger(page) && page > 0 ? page : 1;
            load(state);
        });
    }
    window.addEventListener('hashchange', activate); window.addEventListener('popstate', activate);
    window.addEventListener('pagehide', () => panels.forEach(state => { state.controller?.abort(); clear(state); }));
    window.addEventListener('pageshow', event => { if (event.persisted) activate(); });
    activate();
})();
