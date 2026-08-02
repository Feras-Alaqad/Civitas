<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Department Completion</h3>
    </div>

    @php
        $deptData = $departmentStats ?? collect([]);
        $labels = $deptData->pluck('department')->toArray();
        $completedData = $deptData->pluck('completed_pct')->toArray();
        $pendingData = $deptData->pluck('pending_pct')->toArray();
    @endphp

    <div style="position: relative; width: 100%; height: 300px;">
        <canvas id="deptCompletion"></canvas>
    </div>
</div>

{{-- Modal --}}
<div id="deptModal"
     class="fixed inset-0 z-[999] hidden items-center justify-center bg-black/45"
     style="display: none;">
    <div class="w-[340px] max-w-[90%] rounded-xl bg-white p-6 shadow-lg dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
            <span id="modalTitle" class="text-lg font-semibold text-gray-800 dark:text-white/90"></span>
            <button id="modalCloseBtn" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="modalBody" class="flex flex-col gap-3"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = @json($labels);
        const completedData = @json($completedData);
        const pendingData = @json($pendingData);
        const allDeptData = @json($departmentStats);

        const chart = new Chart(document.getElementById('deptCompletion'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Completed %',
                        data: completedData,
                        backgroundColor: '#10b981',
                        borderRadius: 4,
                        barPercentage: 0.4,
                        categoryPercentage: 0.6,
                    },
                    {
                        label: 'Pending %',
                        data: pendingData,
                        backgroundColor: '#f59e0b',
                        borderRadius: 4,
                        barPercentage: 0.4,
                        categoryPercentage: 0.6,
                    },
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Outfit, sans-serif', size: 12 },
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return ctx.dataset.label + ': ' + ctx.parsed.x + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        beginAtZero: true,
                        max: 100,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            callback: function (value) {
                                return value + '%';
                            },
                            stepSize: 25,
                        }
                    },
                    y: {
                        stacked: true,
                        grid: { display: false },
                        ticks: {
                            font: { size: 12 },
                        }
                    }
                },
                onClick: function (e) {
                    const bars = chart.getElementsAtEventForMode(e, 'index', { intersect: true }, false);
                    if (bars.length === 0) return;
                    const idx = bars[0].index;
                    const dept = allDeptData[idx];
                    if (!dept) return;

                    const avgTime = dept.avg_completion_time
                        ? dept.avg_completion_time + ' hours'
                        : '—';

                    document.getElementById('modalTitle').textContent = dept.department;
                    document.getElementById('modalBody').innerHTML = `
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-700/50">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Total Requests</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-white/90">${dept.total}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-green-50 px-4 py-3 dark:bg-green-900/20">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Completed</span>
                            <span class="text-sm font-semibold text-green-600 dark:text-green-400">${dept.completed}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-amber-50 px-4 py-3 dark:bg-amber-900/20">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Pending</span>
                            <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">${dept.pending}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-blue-50 px-4 py-3 dark:bg-blue-900/20">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Avg Completion Time</span>
                            <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">${avgTime}</span>
                        </div>
                    `;

                    const modal = document.getElementById('deptModal');
                    modal.style.display = 'flex';
                }
            }
        });

        document.getElementById('modalCloseBtn').addEventListener('click', function () {
            document.getElementById('deptModal').style.display = 'none';
        });

        document.getElementById('deptModal').addEventListener('click', function (e) {
            if (e.target === this) this.style.display = 'none';
        });
    });
</script>
