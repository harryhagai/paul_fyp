(function () {
    const page = document.querySelector('.seller-robot-page');
    if (!page) return;

    const csrf = page.dataset.csrf;
    const statusUrl = page.dataset.statusUrl;
    const pickUrl = page.dataset.pickUrl;
    const homeUrl = page.dataset.homeUrl;
    const stopUrl = page.dataset.stopUrl;
    const pollMs = 4000;
    let pollTimer = null;
    let pollInFlight = false;

    const els = {
        connection: document.getElementById('robotConnectionText'),
        status: document.getElementById('robotStatusText'),
        lastPoll: document.getElementById('robotLastPoll'),
        pill: document.getElementById('robotStatusPill'),
        activeCommand: document.getElementById('activeCommandText'),
        activeOrder: document.getElementById('activeOrderText'),
        activeLocation: document.getElementById('activeLocationText'),
        activeError: document.getElementById('activeErrorText'),
        history: document.getElementById('robotHistoryBody'),
        visual: document.querySelector('.robot-arm-visual'),
        order: document.getElementById('robotOrder'),
        location: document.getElementById('robotLocation'),
        pickForm: document.getElementById('robotPickForm'),
        pickBtn: document.getElementById('robotPickBtn'),
        refreshBtn: document.getElementById('robotRefreshBtn'),
        autoRefresh: document.getElementById('robotAutoRefresh'),
        homeBtn: document.getElementById('robotHomeBtn'),
        stopBtn: document.getElementById('robotStopBtn')
    };

    function request(url, options) {
        return fetch(url, Object.assign({
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }, options || {})).then(async function (response) {
            const data = await response.json().catch(function () { return {}; });
            if (!response.ok) {
                const message = data.message || 'Request failed';
                throw new Error(message);
            }
            return data;
        });
    }

    function post(url, body) {
        return request(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(body || {})
        });
    }

    function statusClass(status) {
        if (['ERROR', 'STOPPED'].includes(status)) return 'status-error';
        if (status === 'COMPLETED') return 'status-complete';
        if (['ACCEPTED', 'MOVING', 'PICKING', 'PLACING', 'PENDING'].includes(status)) return 'status-active';
        return '';
    }

    function setStatusBadge(el, status) {
        if (!el) return;
        el.textContent = status || 'IDLE';
        el.classList.remove('status-active', 'status-error', 'status-complete');
        const cls = statusClass(status || 'IDLE');
        if (cls) el.classList.add(cls);
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>'"]/g, function (character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#039;',
                '"': '&quot;'
            }[character];
        });
    }

    function updateProgress(status) {
        document.querySelectorAll('.robot-progress-step').forEach(function (step) {
            step.classList.toggle('active', step.dataset.step === status);
        });
        if (els.visual) {
            els.visual.classList.toggle('is-active', ['ACCEPTED', 'MOVING', 'PICKING', 'PLACING', 'PENDING'].includes(status));
        }
    }

    function renderCommand(command) {
        if (!command) {
            els.activeCommand.textContent = 'None';
            els.activeOrder.textContent = 'None';
            els.activeLocation.textContent = 'None';
            els.activeError.textContent = 'None';
            return;
        }

        els.activeCommand.textContent = command.command || 'None';
        els.activeOrder.textContent = command.order_reference || 'None';
        els.activeLocation.textContent = command.location ? `LOCATION ${command.location}` : 'None';
        els.activeError.textContent = command.error || 'None';
    }

    function renderHistory(commands) {
        if (!els.history) return;

        if (!commands || commands.length === 0) {
            els.history.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No robot commands yet.</td></tr>';
            return;
        }

        els.history.innerHTML = commands.map(function (command) {
            const cls = statusClass(command.status);
            const createdAt = escapeHtml(command.created_at || '-');
            const commandName = escapeHtml(command.command || '-');
            const orderReference = escapeHtml(command.order_reference || '-');
            const location = command.location ? `LOCATION ${escapeHtml(command.location)}` : '-';
            const status = escapeHtml(command.status || '-');
            const error = escapeHtml(command.error || '-');
            return `
                <tr>
                    <td>${createdAt}</td>
                    <td>${commandName}</td>
                    <td>${orderReference}</td>
                    <td>${location}</td>
                    <td><span class="robot-status-badge ${cls}">${status}</span></td>
                    <td>${error}</td>
                </tr>
            `;
        }).join('');
    }

    function updateFromPayload(payload) {
        const robot = payload.robot || {};
        const status = robot.status || payload.command?.status || 'IDLE';

        els.connection.textContent = robot.configured ? (robot.online ? 'Online' : 'Offline') : 'Not configured';
        els.status.textContent = status;
        els.lastPoll.textContent = new Date().toLocaleTimeString();
        setStatusBadge(els.pill, status);
        updateProgress(status);
        renderCommand(payload.active_command || payload.command || null);
        renderHistory(payload.recent_commands || []);
    }

    function pollStatus() {
        if (pollInFlight) return Promise.resolve();
        pollInFlight = true;

        return request(statusUrl)
            .then(updateFromPayload)
            .catch(function (error) {
                els.connection.textContent = 'Offline';
                els.status.textContent = 'ERROR';
                els.lastPoll.textContent = new Date().toLocaleTimeString();
                setStatusBadge(els.pill, 'ERROR');
                els.activeError.textContent = error.message;
                updateProgress('ERROR');
            }).finally(function () {
                pollInFlight = false;
            });
    }

    function setButtonLoading(button, loading) {
        if (!button) return;
        button.disabled = loading;
        const spinner = button.querySelector('.spinner-border');
        if (spinner) spinner.classList.toggle('d-none', !loading);
    }

    function notify(icon, title, text) {
        if (window.Swal) {
            Swal.fire({ icon, title, text, timer: icon === 'success' ? 1600 : undefined, showConfirmButton: icon !== 'success' });
        }
    }

    function startPolling() {
        stopPolling();
        pollTimer = setInterval(function () {
            if (!document.hidden) pollStatus();
        }, pollMs);
    }

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    els.order?.addEventListener('change', function () {
        const selected = els.order.options[els.order.selectedIndex];
        const location = selected ? selected.dataset.location : '';
        els.location.value = location || '';
    });

    els.pickForm?.addEventListener('submit', function (event) {
        event.preventDefault();
        setButtonLoading(els.pickBtn, true);
        post(pickUrl, {
            order_id: els.order.value,
            location: els.location.value || null
        }).then(function (payload) {
            notify('success', 'Command Sent', payload.message || 'PICK command sent.');
            return pollStatus();
        }).catch(function (error) {
            notify('error', 'Robot Error', error.message);
            return pollStatus();
        }).finally(function () {
            setButtonLoading(els.pickBtn, false);
        });
    });

    els.homeBtn?.addEventListener('click', function () {
        els.homeBtn.disabled = true;
        post(homeUrl).then(function (payload) {
            notify('success', 'Command Sent', payload.message || 'HOME command sent.');
            return pollStatus();
        }).catch(function (error) {
            notify('error', 'Robot Error', error.message);
        }).finally(function () {
            els.homeBtn.disabled = false;
        });
    });

    els.stopBtn?.addEventListener('click', function () {
        els.stopBtn.disabled = true;
        post(stopUrl).then(function (payload) {
            notify('success', 'Command Sent', payload.message || 'STOP command sent.');
            return pollStatus();
        }).catch(function (error) {
            notify('error', 'Robot Error', error.message);
        }).finally(function () {
            els.stopBtn.disabled = false;
        });
    });

    els.refreshBtn?.addEventListener('click', function () {
        pollStatus();
    });

    els.autoRefresh?.addEventListener('change', function () {
        if (els.autoRefresh.checked) startPolling();
        else stopPolling();
    });

    window.addEventListener('beforeunload', stopPolling);
    pollStatus();
    if (els.autoRefresh?.checked) startPolling();
})();
