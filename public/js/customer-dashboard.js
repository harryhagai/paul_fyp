(() => {
    const container = document.querySelector('.customer-dashboard-clean');
    if (!container) return;

    function isModifiedClick(event) {
        return event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0;
    }

    function addSpinnerState(el) {
        if (!el || el.dataset.loading === '1') return;
        el.dataset.loading = '1';
        el.dataset.originalHtml = el.innerHTML;
        el.setAttribute('aria-busy', 'true');
        el.classList.add('disabled');

        if ('disabled' in el) {
            el.disabled = true;
        }

        el.innerHTML =
            '<span class="dashboard-link-spinner" aria-hidden="true"><span></span><span></span><span></span></span><span>Please wait...</span>';
    }

    function formatNumberShort(amount, precision) {
        const value = Number(amount || 0);
        const abs = Math.abs(value);
        let short;
        let suffix = '';

        if (abs >= 1000000000) {
            short = value / 1000000000;
            suffix = 'B';
        } else if (abs >= 1000000) {
            short = value / 1000000;
            suffix = 'M';
        } else if (abs >= 1000) {
            short = value / 1000;
            suffix = 'K';
        } else {
            return new Intl.NumberFormat().format(Math.round(value));
        }

        const fixed = Number(short).toFixed(Math.max(0, precision));
        const trimmed = fixed.replace(/\.0+$|(\.\d*[1-9])0+$/, '$1');
        return `${trimmed}${suffix}`;
    }

    function formatMoneyShort(amount, precision, currency) {
        return `${currency} ${formatNumberShort(amount, precision)}`.trim();
    }

    function initMetricCards() {
        const counters = container.querySelectorAll('.summary-value[data-counter-target]');
        if (!counters.length) return;

        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const duration = reducedMotion ? 0 : 1200;

        counters.forEach((el, index) => {
            const targetRaw = Number(el.getAttribute('data-counter-target'));
            const target = Number.isFinite(targetRaw) ? targetRaw : 0;
            const type = (el.getAttribute('data-counter-type') || 'number').toLowerCase();
            const currency = el.getAttribute('data-counter-currency') || 'Tsh';
            const precision = Number(el.getAttribute('data-counter-precision') || 0);

            const render = (value) => {
                if (type === 'money') {
                    el.textContent = formatMoneyShort(value, precision, currency);
                    return;
                }
                el.textContent = new Intl.NumberFormat().format(Math.round(value));
            };

            if (duration === 0) {
                render(target);
                return;
            }

            const startAt = performance.now() + (index * 80);
            render(0);

            const tick = (now) => {
                if (now < startAt) {
                    requestAnimationFrame(tick);
                    return;
                }

                const progress = Math.min((now - startAt) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = target * eased;
                render(current);

                if (progress < 1) {
                    requestAnimationFrame(tick);
                } else {
                    render(target);
                }
            };

            requestAnimationFrame(tick);
        });
    }

    function initDonutCharts() {
        const charts = container.querySelectorAll('.order-donut-chart');
        if (!charts.length) return;

        charts.forEach((chart) => {
            const targetCompleted = Number(chart.dataset.completedDeg || 0);
            const targetPending = Number(chart.dataset.pendingDeg || 0);
            const targetCancelled = Number(chart.dataset.cancelledDeg || 0);
            const duration = 900;
            const startTime = performance.now();

            function frame(now) {
                const progress = Math.min(1, (now - startTime) / duration);
                const eased = 1 - Math.pow(1 - progress, 3);

                const completed = (targetCompleted * eased).toFixed(1);
                const pending = (targetPending * eased).toFixed(1);
                const cancelled = (targetCancelled * eased).toFixed(1);

                chart.style.setProperty('--completed-deg', `${completed}deg`);
                chart.style.setProperty('--pending-deg', `${pending}deg`);
                chart.style.setProperty('--cancelled-deg', `${cancelled}deg`);

                if (progress < 1) {
                    requestAnimationFrame(frame);
                } else {
                    chart.style.setProperty('--completed-deg', `${targetCompleted}deg`);
                    chart.style.setProperty('--pending-deg', `${targetPending}deg`);
                    chart.style.setProperty('--cancelled-deg', `${targetCancelled}deg`);
                }
            }

            requestAnimationFrame(frame);
        });
    }

    function initSpendingChart() {
        const el = document.getElementById('customerSpendingChart');
        if (!el || typeof Chart === 'undefined') return;

        let monthlySpending = [];
        let yAxis = {};
        try {
            monthlySpending = JSON.parse(el.dataset.monthlySpending || '[]');
        } catch (_) {
            monthlySpending = [];
        }
        try {
            yAxis = JSON.parse(el.dataset.yAxis || '{}');
        } catch (_) {
            yAxis = {};
        }

        if (!Array.isArray(monthlySpending) || monthlySpending.length === 0) return;

        const labels = monthlySpending.map((item) => item.month ?? '');
        const targetData = monthlySpending.map((item) => Number(item.amount ?? 0));
        const initialData = targetData.map(() => 0);
        const yAxisLabels = yAxis && typeof yAxis.labels === 'object' ? yAxis.labels : {};
        const yAxisStep = Number(yAxis.step || 0);
        const yAxisMax = Number(yAxis.max || 0);
        const lineShadowPlugin = {
            id: 'lineShadowPlugin',
            beforeDatasetDraw(chart, args) {
                if (args.index !== 0) return;
                const { ctx } = chart;
                ctx.save();
                ctx.shadowColor = 'rgba(13, 110, 253, 0.28)';
                ctx.shadowBlur = 14;
                ctx.shadowOffsetY = 6;
            },
            afterDatasetDraw(chart, args) {
                if (args.index !== 0) return;
                chart.ctx.restore();
            }
        };
        const valueLabelPlugin = {
            id: 'valueLabelPlugin',
            afterDatasetsDraw(chart) {
                const datasetMeta = chart.getDatasetMeta(0);
                const ctx = chart.ctx;
                ctx.save();
                ctx.fillStyle = '#475569';
                ctx.font = '500 11px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';

                datasetMeta.data.forEach((point, index) => {
                    const label = monthlySpending[index]?.amount_label ?? '';
                    if (!label) return;
                    const labelY = Math.max(chart.chartArea.top + 12, point.y - 8);
                    ctx.fillText(label, point.x, labelY);
                });

                ctx.restore();
            }
        };

        const spendingChart = new Chart(el.getContext('2d'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    data: initialData,
                    borderColor: '#0d6efd',
                    fill: true,
                    backgroundColor: (context) => {
                        const chart = context.chart;
                        const { ctx, chartArea } = chart;
                        if (!chartArea) {
                            return 'rgba(13, 110, 253, 0.18)';
                        }
                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(13, 110, 253, 0.30)');
                        gradient.addColorStop(1, 'rgba(13, 110, 253, 0.03)');
                        return gradient;
                    },
                    tension: 0.32,
                    borderWidth: 2.25,
                    pointRadius: 1.8,
                    pointHoverRadius: 3,
                    pointBackgroundColor: '#0d6efd',
                    pointBorderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                layout: {
                    padding: {
                        top: 18
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        displayColors: false,
                        callbacks: {
                            title: (items) => {
                                const idx = items?.[0]?.dataIndex ?? -1;
                                return idx >= 0 ? (labels[idx] || '') : '';
                            },
                            label: (ctx) => {
                                const point = monthlySpending[ctx.dataIndex] || {};
                                return point.amount_label || `Tsh ${Number(ctx.parsed.y || 0).toLocaleString()}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#64748b',
                            maxRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: yAxisMax > 0 ? yAxisMax : undefined,
                        grid: { color: 'rgba(148, 163, 184, 0.15)' },
                        ticks: {
                            color: '#64748b',
                            stepSize: yAxisStep > 0 ? yAxisStep : undefined,
                            callback: (value) => {
                                const key = String(Number(value));
                                return yAxisLabels[key] || yAxisLabels[String(value)] || `Tsh ${Number(value).toLocaleString()}`;
                            }
                        }
                    }
                }
            },
            plugins: [lineShadowPlugin, valueLabelPlugin]
        });

        // Smooth initialize from zero, similar feel to Order Overview.
        spendingChart.update('none');
        const duration = 1200;
        const startTime = performance.now();

        function frame(now) {
            const progress = Math.min(1, (now - startTime) / duration);
            const eased = 1 - Math.pow(1 - progress, 3);

            spendingChart.data.datasets[0].data = targetData.map((value) => value * eased);
            spendingChart.update('none');

            if (progress < 1) {
                requestAnimationFrame(frame);
            } else {
                spendingChart.data.datasets[0].data = targetData;
                spendingChart.update('none');
            }
        }

        requestAnimationFrame(frame);
    }

    container.addEventListener('click', (event) => {
        const target = event.target.closest('a, button');
        if (!target) return;

        if (target.tagName === 'A') {
            if (isModifiedClick(event)) return;
            const href = target.getAttribute('href') || '';
            if (!href || href === '#' || href.startsWith('javascript:')) return;
            if (target.target && target.target !== '_self') return;
        }

        addSpinnerState(target);
    });

    initMetricCards();
    initDonutCharts();
    initSpendingChart();
})();
