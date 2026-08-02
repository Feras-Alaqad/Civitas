<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Persons per Governorate</h3>
    </div>

    @php
        $chartData = $governoratesChartData ?? collect([]);
        $labels = $chartData->pluck('name')->toArray();
        $values = $chartData->pluck('count')->toArray();
    @endphp

    <div style="position: relative; width: 100%; height: 320px;">
        <canvas id="govChart"></canvas>
    </div>
</div>

{{-- Modal --}}
<div id="govModal"
     class="fixed inset-0 z-[999] hidden items-center justify-center bg-black/45"
     style="display: none;">
    <div class="w-[340px] max-w-[90%] rounded-xl bg-white p-6 shadow-lg dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
            <span id="govModalTitle" class="text-lg font-semibold text-gray-800 dark:text-white/90"></span>
            <button id="govModalCloseBtn" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="govModalBody" class="flex flex-col gap-3"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = @json($labels);
        const values = @json($values);
        const allData = @json($chartData);

        const chart = new Chart(document.getElementById('govChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Persons',
                    data: values,
                    backgroundColor: '#465fff',
                    borderRadius: 6,
                    barPercentage: 0.5,
                    categoryPercentage: 0.7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return ctx.parsed.y + ' persons';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10 },
                            maxRotation: 45,
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            stepSize: 1,
                        }
                    }
                },
                onClick: function (e) {
                    const bars = chart.getElementsAtEventForMode(e, 'index', { intersect: true }, false);
                    if (bars.length === 0) return;
                    const idx = bars[0].index;
                    const d = allData[idx];
                    if (!d) return;

                    document.getElementById('govModalTitle').textContent = d.name;
                    document.getElementById('govModalBody').innerHTML = `
                        <div class="flex items-center justify-between rounded-lg bg-blue-50 px-4 py-3 dark:bg-blue-900/20">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Beneficiaries</span>
                            <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">${d.count.toLocaleString()}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-emerald-50 px-4 py-3 dark:bg-emerald-900/20">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Today's Requests</span>
                            <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">${d.today_requests}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-amber-50 px-4 py-3 dark:bg-amber-900/20">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Most Requested Service</span>
                            <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">${d.top_service}</span>
                        </div>
                    `;

                    document.getElementById('govModal').style.display = 'flex';
                }
            }
        });

        document.getElementById('govModalCloseBtn').addEventListener('click', function () {
            document.getElementById('govModal').style.display = 'none';
        });

        document.getElementById('govModal').addEventListener('click', function (e) {
            if (e.target === this) this.style.display = 'none';
        });
    });
</script>
