<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Payments Trend (Last 7 Days)</h3>
        <button id="resetDayBtn" class="hidden text-xs font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
            ← Back to Today
        </button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="flex items-center justify-between mb-2">
                <span id="card1-label" class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Today Total</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-4 w-4 text-brand-500 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p id="card1-value" class="text-2xl font-bold text-gray-800 dark:text-white/90">${{ number_format($stats['today_total'], 2) }}</p>
            <div class="mt-2 flex items-center gap-1 text-xs">
                <span id="card1-change" class="@if($stats['total_change_pct'] >= 0) inline-flex items-center gap-0.5 text-green-600 dark:text-green-400 @else inline-flex items-center gap-0.5 text-red-600 dark:text-red-400 @endif">
                    @if($stats['total_change_pct'] >= 0)<svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg> @else<svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg> @endif
                    <span id="card1-change-text">{{ abs($stats['total_change_pct']) }}%</span>
                </span>
                <span class="text-gray-400" id="card1-change-label">vs yesterday</span>
            </div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="flex items-center justify-between mb-2">
                <span id="card2-label" class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Transactions</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-4 w-4 text-brand-500 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>
            <p id="card2-value" class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['today_count'] }}</p>
            <div class="mt-2 flex items-center gap-1 text-xs">
                <span id="card2-change" class="@if($stats['count_change'] >= 0) inline-flex items-center gap-0.5 text-green-600 dark:text-green-400 @else inline-flex items-center gap-0.5 text-red-600 dark:text-red-400 @endif">
                    @if($stats['count_change'] >= 0)<svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg> @else<svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg> @endif
                    <span id="card2-change-text">{{ $stats['count_change'] }}</span>
                </span>
                <span class="text-gray-400" id="card2-change-label">vs yesterday</span>
            </div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="flex items-center justify-between mb-2">
                <span id="card3-label" class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Average</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-4 w-4 text-brand-500 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <p id="card3-value" class="text-2xl font-bold text-gray-800 dark:text-white/90">${{ number_format($stats['today_avg'], 2) }}</p>
            <div class="mt-2 flex items-center gap-1 text-xs">
                <span id="card3-change" class="@if($stats['avg_change_pct'] >= 0) inline-flex items-center gap-0.5 text-green-600 dark:text-green-400 @else inline-flex items-center gap-0.5 text-red-600 dark:text-red-400 @endif">
                    @if($stats['avg_change_pct'] >= 0)<svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg> @else<svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg> @endif
                    <span id="card3-change-text">{{ abs($stats['avg_change_pct']) }}%</span>
                </span>
                <span class="text-gray-400" id="card3-change-label">vs yesterday</span>
            </div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="flex items-center justify-between mb-2">
                <span id="card4-label" class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">vs Yesterday</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-4 w-4 text-brand-500 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            @php
                $best = max($stats['total_change_pct'], $stats['count_change'], $stats['avg_change_pct']);
                $bestLabel = $best === $stats['total_change_pct'] ? 'Revenue' : ($best === $stats['count_change'] ? 'Transactions' : 'Average');
            @endphp
            <p id="card4-value" class="text-2xl font-bold text-gray-800 dark:text-white/90">
                @if($best >= 0)
                    <span class="text-green-600 dark:text-green-400">+{{ $best }}%</span>
                @else
                    <span class="text-red-600 dark:text-red-400">{{ $best }}%</span>
                @endif
            </p>
            <div id="card4-sub" class="mt-2 text-xs text-gray-400">Best: {{ $bestLabel }}</div>
        </div>
    </div>
    <div style="position: relative; width: 100%; height: 250px;">
        <canvas id="paymentsTrend"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = @json($stats['trend_labels']);
        const values = @json($stats['trend_values']);
        const perDayCount = @json($stats['per_day_count']);
        const todayIndex = labels.length - 1;

        const arrowUp = '<svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>';
        const arrowDown = '<svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>';
        const dollarIcon = '<svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        const txIcon = '<svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>';
        const avgIcon = '<svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>';
        const trendIcon = '<svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>';

        let selectedIndex = todayIndex;

        function getArrowSvg(isPositive) {
            return isPositive ? arrowUp : arrowDown;
        }

        function updateCards(idx) {
            selectedIndex = idx;
            const total = values[idx];
            const count = perDayCount[idx];
            const avg = count > 0 ? (total / count) : 0;
            const dayLabel = labels[idx];

            const prevIdx = idx - 1;
            const prevTotal = prevIdx >= 0 ? values[prevIdx] : 0;
            const prevCount = prevIdx >= 0 ? perDayCount[prevIdx] : 0;
            const prevAvg = prevCount > 0 ? (prevTotal / prevCount) : 0;

            const totalChange = prevTotal > 0 ? (((total - prevTotal) / prevTotal) * 100).toFixed(1) : 0;
            const countChange = count - prevCount;
            const avgChange = prevAvg > 0 ? (((avg - prevAvg) / prevAvg) * 100).toFixed(1) : 0;

            const isToday = idx === todayIndex;
            const prefix = isToday ? 'Today' : dayLabel;
            const prevLabel = prevIdx >= 0 ? labels[prevIdx] : '—';

            document.getElementById('card1-label').textContent = prefix + ' Total';
            document.getElementById('card1-value').textContent = '$' + total.toFixed(2);
            const c1change = document.getElementById('card1-change');
            const c1changeText = document.getElementById('card1-change-text');
            c1change.className = totalChange >= 0
                ? 'inline-flex items-center gap-0.5 text-green-600 dark:text-green-400'
                : 'inline-flex items-center gap-0.5 text-red-600 dark:text-red-400';
            c1change.innerHTML = getArrowSvg(totalChange >= 0) + ' <span id="card1-change-text">' + Math.abs(totalChange) + '%</span>';
            document.getElementById('card1-change-label').textContent = prevIdx >= 0 ? 'vs ' + prevLabel : '—';

            document.getElementById('card2-label').textContent = 'Transactions' + (isToday ? '' : ' on ' + dayLabel);
            document.getElementById('card2-value').textContent = count;
            const c2change = document.getElementById('card2-change');
            c2change.className = countChange >= 0
                ? 'inline-flex items-center gap-0.5 text-green-600 dark:text-green-400'
                : 'inline-flex items-center gap-0.5 text-red-600 dark:text-red-400';
            c2change.innerHTML = getArrowSvg(countChange >= 0) + ' <span id="card2-change-text">' + (countChange >= 0 ? '+' : '') + countChange + '</span>';
            document.getElementById('card2-change-label').textContent = prevIdx >= 0 ? 'vs ' + prevLabel : '—';

            document.getElementById('card3-label').textContent = 'Average' + (isToday ? '' : ' on ' + dayLabel);
            document.getElementById('card3-value').textContent = '$' + avg.toFixed(2);
            const c3change = document.getElementById('card3-change');
            c3change.className = avgChange >= 0
                ? 'inline-flex items-center gap-0.5 text-green-600 dark:text-green-400'
                : 'inline-flex items-center gap-0.5 text-red-600 dark:text-red-400';
            c3change.innerHTML = getArrowSvg(avgChange >= 0) + ' <span id="card3-change-text">' + Math.abs(avgChange) + '%</span>';
            document.getElementById('card3-change-label').textContent = prevIdx >= 0 ? 'vs ' + prevLabel : '—';

            const changes = [
                { key: 'Revenue', val: parseFloat(totalChange) },
                { key: 'Transactions', val: parseInt(countChange) },
                { key: 'Average', val: parseFloat(avgChange) }
            ];
            let bestItem = changes[0];
            for (const c of changes) {
                if (c.val > bestItem.val) bestItem = c;
            }
            const bestVal = bestItem.val;
            document.getElementById('card4-label').textContent = isToday ? 'vs Yesterday' : 'vs ' + prevLabel;
            document.getElementById('card4-value').innerHTML = bestVal >= 0
                ? '<span class="text-green-600 dark:text-green-400">+' + bestVal + '%</span>'
                : '<span class="text-red-600 dark:text-red-400">' + bestVal + '%</span>';
            document.getElementById('card4-sub').textContent = 'Best: ' + bestItem.key;

            document.getElementById('resetDayBtn').classList.toggle('hidden', isToday);
        }

        const ctx = document.getElementById('paymentsTrend');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Payments',
                    data: values,
                    borderColor: '#465fff',
                    backgroundColor: 'rgba(201, 169, 110, 0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#465fff',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    borderWidth: 2.5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: function (event, elements) {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        updateCards(idx);
                        highlightPoint(chart, idx);
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return '$' + ctx.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)',
                        },
                        ticks: {
                            callback: function (value) {
                                return '$' + value;
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                    }
                }
            }
        });

        function highlightPoint(chartInstance, idx) {
            const meta = chartInstance.getDatasetMeta(0);
            meta.data.forEach(function (point, i) {
                if (i === idx) {
                    point.options.radius = 7;
                    point.options.backgroundColor = '#465fff';
                    point.options.borderColor = '#465fff';
                    point.options.borderWidth = 3;
                } else {
                    point.options.radius = 4;
                    point.options.backgroundColor = '#465fff';
                    point.options.borderColor = '#fff';
                    point.options.borderWidth = 2;
                }
            });
            chartInstance.update();
        }

        highlightPoint(chart, todayIndex);

        document.getElementById('resetDayBtn').addEventListener('click', function () {
            updateCards(todayIndex);
            highlightPoint(chart, todayIndex);
        });
    });
</script>
