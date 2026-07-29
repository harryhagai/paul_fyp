document.addEventListener('DOMContentLoaded', function () {
    const dataEl = document.getElementById('sellerDashboardChartsData');
    let payload = {};
    if (dataEl) {
        try {
            payload = JSON.parse(dataEl.textContent || '{}');
        } catch (e) {
            payload = {};
        }
    }

    const theme = getComputedStyle(document.documentElement);
    const primaryColor = theme.getPropertyValue('--teal-primary').trim() || '#0d9488';
    const secondaryColor = theme.getPropertyValue('--teal-secondary').trim() || '#0f766e';

    function colorWithAlpha(hexColor, alpha) {
        const hex = (hexColor || '').trim().replace('#', '');
        if (hex.length !== 6) return `rgba(13, 148, 136, ${alpha})`;
        const r = parseInt(hex.slice(0, 2), 16);
        const g = parseInt(hex.slice(2, 4), 16);
        const b = parseInt(hex.slice(4, 6), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
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
        const counters = document.querySelectorAll('.summary-value[data-counter-target]');
        if (!counters.length) return;

        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const duration = reducedMotion ? 0 : 1200;

        counters.forEach(function (el, index) {
            const targetRaw = Number(el.getAttribute('data-counter-target'));
            const target = Number.isFinite(targetRaw) ? targetRaw : 0;
            const type = (el.getAttribute('data-counter-type') || 'number').toLowerCase();
            const currency = el.getAttribute('data-counter-currency') || 'Tsh';
            const precision = Number(el.getAttribute('data-counter-precision') || 0);

            const render = function (value) {
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

            const start = performance.now() + (index * 80);
            const startValue = 0;
            render(startValue);

            const tick = function (now) {
                if (now < start) {
                    requestAnimationFrame(tick);
                    return;
                }
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = startValue + ((target - startValue) * eased);
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

    function initOrdersTrendChart() {
        if (typeof Chart === 'undefined') return;
        const canvas = document.getElementById('ordersTrendChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const values = Array.isArray(payload.trendOrders) ? payload.trendOrders : [];
        const maxValue = Math.max(...values, 0);
        const yStep = maxValue <= 10 ? 2
            : maxValue <= 50 ? 5
            : maxValue <= 100 ? 10
            : maxValue <= 250 ? 25
            : maxValue <= 500 ? 50
            : 100;
        const yMax = Math.max(yStep, Math.ceil((maxValue + yStep) / yStep) * yStep);
        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.24)');
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0.02)');

        const pointValueLabels = {
            id: 'ordersPointValueLabels',
            afterDatasetsDraw(chart) {
                const chartCtx = chart.ctx;
                const meta = chart.getDatasetMeta(0);
                const dataset = chart.data.datasets[0];
                if (!meta || meta.hidden || !dataset) return;

                chartCtx.save();
                chartCtx.fillStyle = '#64748b';
                chartCtx.font = '600 11px system-ui, -apple-system, Segoe UI, sans-serif';
                chartCtx.textAlign = 'center';
                chartCtx.textBaseline = 'bottom';

                meta.data.forEach((point, index) => {
                    const value = dataset.data[index];
                    if (value === null || value === undefined) return;
                    chartCtx.fillText(String(value), point.x, point.y - 10);
                });

                chartCtx.restore();
            }
        };

        new Chart(ctx, {
            plugins: [pointValueLabels],
            type: 'line',
            data: {
                labels: payload.trendLabels || [],
                datasets: [{
                    label: 'Orders',
                    data: values,
                    borderColor: '#2563eb',
                    backgroundColor: gradient,
                    borderWidth: 2.25,
                    tension: 0.42,
                    cubicInterpolationMode: 'monotone',
                    fill: true,
                    pointRadius: 3.5,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 900,
                    easing: 'easeOutCubic'
                },
                animations: {
                    y: { from: 0 }
                },
                layout: {
                    padding: { top: 22, right: 8, bottom: 0, left: 2 }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#e2e8f0',
                        bodyColor: '#f8fafc',
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return `Orders: ${context.parsed.y}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: yMax,
                        ticks: {
                            stepSize: yStep,
                            precision: 0,
                            color: '#94a3b8',
                            font: { size: 11 },
                            maxTicksLimit: 5
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.24)',
                            borderDash: [4, 4],
                            drawBorder: false
                        },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11, weight: '500' },
                            padding: 8
                        },
                        border: { display: false }
                    }
                }
            }
        });
    }

    function initRevenueChart() {
        if (typeof Chart === 'undefined') return;
        const canvas = document.getElementById('revenueTrendChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const values = Array.isArray(payload.revenueAmounts) ? payload.revenueAmounts : [];
        const pointLabels = Array.isArray(payload.revenuePointLabels) ? payload.revenuePointLabels : [];
        const axisLabelMap = payload.revenueAxisLabelMap || {};
        const yStep = Number(payload.revenueStep) || 1;
        const yMax = Number(payload.revenueAxisMax) || yStep;
        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, colorWithAlpha(secondaryColor, 0.24));
        gradient.addColorStop(1, colorWithAlpha(secondaryColor, 0.02));

        const pointValueLabels = {
            id: 'revenuePointValueLabels',
            afterDatasetsDraw(chart) {
                const chartCtx = chart.ctx;
                const meta = chart.getDatasetMeta(0);
                if (!meta || meta.hidden) return;

                chartCtx.save();
                chartCtx.fillStyle = '#64748b';
                chartCtx.font = '600 10.5px system-ui, -apple-system, Segoe UI, sans-serif';
                chartCtx.textAlign = 'center';
                chartCtx.textBaseline = 'bottom';

                meta.data.forEach((point, index) => {
                    const label = pointLabels[index];
                    if (!label) return;
                    chartCtx.fillText(label, point.x, point.y - 10);
                });

                chartCtx.restore();
            }
        };

        new Chart(ctx, {
            plugins: [pointValueLabels],
            type: 'line',
            data: {
                labels: payload.revenueLabels || [],
                datasets: [{
                    label: 'Revenue',
                    data: values,
                    borderColor: primaryColor,
                    backgroundColor: gradient,
                    borderWidth: 2.3,
                    tension: 0.4,
                    cubicInterpolationMode: 'monotone',
                    fill: true,
                    pointRadius: 3.2,
                    pointHoverRadius: 5,
                    pointBackgroundColor: primaryColor,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 900,
                    easing: 'easeOutCubic'
                },
                animations: {
                    y: { from: 0 }
                },
                layout: {
                    padding: { top: 20, right: 8, bottom: 0, left: 2 }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#e2e8f0',
                        bodyColor: '#f8fafc',
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return `Revenue: ${pointLabels[context.dataIndex] ?? context.parsed.y}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: yMax,
                        ticks: {
                            stepSize: yStep,
                            color: '#94a3b8',
                            font: { size: 11 },
                            callback: function (value) {
                                return axisLabelMap[String(value)] ?? value;
                            }
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.2)',
                            borderDash: [4, 4],
                            drawBorder: false
                        },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11, weight: '500' },
                            padding: 8
                        },
                        border: { display: false }
                    }
                }
            }
        });
    }

    function initTopSellingProductsChart() {
        if (typeof Chart === 'undefined') return;
        const canvas = document.getElementById('topSellingProductsChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const topProductsValueLabels = {
            id: 'topProductsValueLabels',
            afterDatasetsDraw(chart) {
                if (chart.animating) return;
                const chartCtx = chart.ctx;
                const meta = chart.getDatasetMeta(0);
                const dataset = chart.data.datasets[0];
                if (!meta || meta.hidden || !dataset) return;

                chartCtx.save();
                chartCtx.fillStyle = '#64748b';
                chartCtx.font = '600 11px system-ui, -apple-system, Segoe UI, sans-serif';
                chartCtx.textAlign = 'center';
                chartCtx.textBaseline = 'bottom';

                meta.data.forEach((bar, index) => {
                    const value = dataset.data[index];
                    if (value === null || value === undefined) return;
                    chartCtx.fillText(String(value), bar.x, bar.y - 8);
                });

                chartCtx.restore();
            }
        };

        const topChart = new Chart(ctx, {
            plugins: [topProductsValueLabels],
            type: 'bar',
            data: {
                labels: payload.topProductLabels || [],
                datasets: [{
                    label: 'Sold',
                    data: payload.topProductSales || [],
                    backgroundColor: colorWithAlpha(primaryColor, 0.78),
                    borderColor: primaryColor,
                    borderWidth: 1.2,
                    borderRadius: 8,
                    barThickness: 34,
                    maxBarThickness: 38,
                    categoryPercentage: 0.72,
                    barPercentage: 0.92
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1400,
                    easing: 'easeOutQuart'
                },
                transitions: {
                    show: {
                        animations: {
                            y: { from: 0 }
                        }
                    },
                    hide: {
                        animations: {
                            y: { to: 0 }
                        }
                    }
                },
                layout: {
                    padding: { top: 18, right: 8, bottom: 0, left: 2 }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#e2e8f0',
                        bodyColor: '#f8fafc',
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return `${context.parsed.y} sold`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            stepSize: 1,
                            color: '#94a3b8',
                            font: { size: 11 }
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.18)',
                            borderDash: [3, 4],
                            drawBorder: false
                        },
                        border: { display: false }
                    },
                    x: {
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11, weight: '500' },
                            maxRotation: 35,
                            minRotation: 20
                        },
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
        topChart.reset();
        topChart.update();
    }

    function initCategorySummaryBars() {
        const bars = document.querySelectorAll('.category-summary-bar[data-target-width]');
        if (!bars.length) return;

        bars.forEach(function (bar, index) {
            const target = Number(bar.getAttribute('data-target-width'));
            const clamped = Number.isFinite(target) ? Math.max(0, Math.min(100, target)) : 0;
            bar.style.width = '0%';
            setTimeout(function () {
                bar.style.width = `${clamped}%`;
            }, 120 + (index * 90));
        });
    }

    initMetricCards();
    initOrdersTrendChart();
    initRevenueChart();
    initTopSellingProductsChart();
    initCategorySummaryBars();
});
