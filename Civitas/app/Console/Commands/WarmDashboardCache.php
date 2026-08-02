<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Admin\DashboardController;

class WarmDashboardCache extends Command
{
    protected $signature = 'cache:warm-dashboard';
    protected $description = 'Pre-compute and cache all dashboard data';

    public function handle(): int
    {
        $start = microtime(true);

        DashboardController::clearCache();

        $this->warmCounts();
        $this->warmGovernoratesChart();
        $this->warmPaymentsTrend();
        $this->warmDepartmentStats();

        $elapsed = round((microtime(true) - $start) * 1000, 2);
        $this->info("Dashboard cache warmed in {$elapsed}ms");

        return self::SUCCESS;
    }

    private function warmCounts(): void
    {
        $this->line('Caching counts...');

        Cache::forever('dashboard:persons_count', DB::table('Persons')->count());
        Cache::forever('dashboard:governorates_count', DB::table('Governorates')->count());
        Cache::forever('dashboard:payments_count', DB::table('Payments')->count());
        Cache::forever('dashboard:requests_count', DB::table('Service_Requests')->count());
        Cache::forever('dashboard:departments_count', DB::table('Departments')->count());

        $this->line('  Counts cached.');
    }

    private function warmGovernoratesChart(): void
    {
        $this->line('Caching governorates chart...');

        $todayDate = now()->toDateString();
        $todayStart = $todayDate . ' 00:00:00';
        $todayEnd = $todayDate . ' 23:59:59';

        $rows = DB::select("
            SELECT
                g.GovernorateID,
                g.GovernorateName AS name,
                COALESCE(pc.person_count, 0) AS `count`,
                COALESCE(tr.today_requests, 0) AS today_requests,
                COALESCE(ts.top_service, '\u2014') AS top_service
            FROM Governorates g
            LEFT JOIN (
                SELECT GovernorateID, COUNT(DISTINCT PersonID) AS person_count
                FROM Persons
                GROUP BY GovernorateID
            ) pc ON pc.GovernorateID = g.GovernorateID
            LEFT JOIN (
                SELECT p.GovernorateID, COUNT(*) AS today_requests
                FROM Service_Requests sr
                JOIN Persons p ON p.PersonID = sr.PersonID
                WHERE sr.RequestDate >= ? AND sr.RequestDate <= ?
                GROUP BY p.GovernorateID
            ) tr ON tr.GovernorateID = g.GovernorateID
            LEFT JOIN (
                SELECT GovernorateID,
                       SUBSTRING_INDEX(GROUP_CONCAT(ServiceName ORDER BY total DESC SEPARATOR ','), ',', 1) AS top_service
                FROM (
                    SELECT p.GovernorateID, st.ServiceName, COUNT(*) AS total
                    FROM Service_Requests sr
                    JOIN Persons p ON p.PersonID = sr.PersonID
                    JOIN Service_Types st ON st.ServiceTypeID = sr.ServiceTypeID
                    GROUP BY p.GovernorateID, st.ServiceName
                ) counts
                GROUP BY GovernorateID
            ) ts ON ts.GovernorateID = g.GovernorateID
        ", [$todayStart, $todayEnd]);

        $data = array_map(fn($row) => [
            'name' => $row->name,
            'count' => (int) $row->count,
            'today_requests' => (int) $row->today_requests,
            'top_service' => $row->top_service,
        ], $rows);

        Cache::forever('dashboard:governorates_chart', $data);
        $this->line('  Governorates chart cached. ' . count($data) . ' governorates.');
    }

    private function warmPaymentsTrend(): void
    {
        $this->line('Caching payments trend...');

        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        $paymentsByDay = DB::table('Payments')
            ->whereBetween('PaymentDate', [$startDate, $endDate])
            ->selectRaw('DATE(PaymentDate) as day, SUM(Amount) as total, COUNT(*) as cnt')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $trendLabels = [];
        $trendValues = [];
        $perDayCount = [];
        $todayTotal = 0;
        $todayCount = 0;
        $yesterdayTotal = 0;
        $yesterdayCount = 0;

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $row = $paymentsByDay->get($date);

            $dayTotal = $row ? (float) $row->total : 0;
            $dayCount = $row ? (int) $row->cnt : 0;

            $trendLabels[] = now()->subDays($i)->format('M d');
            $trendValues[] = $dayTotal;
            $perDayCount[] = $dayCount;

            if ($i === 0) {
                $todayTotal = $dayTotal;
                $todayCount = $dayCount;
            }
            if ($i === 1) {
                $yesterdayTotal = $dayTotal;
                $yesterdayCount = $dayCount;
            }
        }

        $todayAvg = $todayCount > 0 ? round($todayTotal / $todayCount, 2) : 0;
        $yesterdayAvg = $yesterdayCount > 0 ? $yesterdayTotal / $yesterdayCount : 0;

        $data = [
            'today_total' => $todayTotal,
            'today_count' => $todayCount,
            'today_avg' => $todayAvg,
            'total_change_pct' => $yesterdayTotal > 0
                ? round((($todayTotal - $yesterdayTotal) / $yesterdayTotal) * 100, 1) : 0,
            'count_change' => $todayCount - $yesterdayCount,
            'avg_change_pct' => $yesterdayAvg > 0
                ? round((($todayAvg - $yesterdayAvg) / $yesterdayAvg) * 100, 1) : 0,
            'trend_labels' => $trendLabels,
            'trend_values' => $trendValues,
            'per_day_count' => $perDayCount,
        ];

        Cache::forever('dashboard:payments_trend', $data);
        $this->line('  Payments trend cached.');
    }

    private function warmDepartmentStats(): void
    {
        $this->line('Caching department stats...');

        $data = DB::table('Service_Requests')
            ->join('Service_Types', 'Service_Types.ServiceTypeID', '=', 'Service_Requests.ServiceTypeID')
            ->join('Departments', 'Departments.DepartmentID', '=', 'Service_Types.DepartmentID')
            ->select(
                'Departments.DepartmentID',
                'Departments.DepartmentName as department_name',
                DB::raw("COUNT(*) as total_count"),
                DB::raw("SUM(CASE WHEN Service_Requests.Status = 'Completed' THEN 1 ELSE 0 END) as completed_count"),
                DB::raw("SUM(CASE WHEN Service_Requests.Status = 'Pending' THEN 1 ELSE 0 END) as pending_count"),
                DB::raw("AVG(CASE WHEN Service_Requests.Status = 'Completed' THEN TIMESTAMPDIFF(HOUR, Service_Requests.created_at, Service_Requests.updated_at) ELSE NULL END) as avg_hours")
            )
            ->groupBy('Departments.DepartmentID', 'Departments.DepartmentName')
            ->get()
            ->map(function ($row) {
                $total = $row->total_count ?: 1;
                $hours = $row->avg_hours ? round((float) $row->avg_hours, 1) : null;
                return [
                    'department' => $row->department_name,
                    'completed_pct' => round(($row->completed_count / $total) * 100, 1),
                    'pending_pct' => round(($row->pending_count / $total) * 100, 1),
                    'total' => (int) $row->total_count,
                    'completed' => (int) $row->completed_count,
                    'pending' => (int) $row->pending_count,
                    'avg_completion_time' => $hours,
                ];
            })->values()->toArray();

        Cache::forever('dashboard:department_stats', $data);
        $this->line('  Department stats cached. ' . count($data) . ' departments.');
    }
}
