<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StripePayout;
use App\Services\StripePayoutsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $start = microtime(true);

        $query = DB::table('Payments')
            ->join('Service_Requests', 'Payments.RequestID', '=', 'Service_Requests.RequestID')
            ->leftJoin('Persons', 'Service_Requests.PersonID', '=', 'Persons.PersonID')
            ->leftJoin('Service_Types', 'Service_Requests.ServiceTypeID', '=', 'Service_Types.ServiceTypeID')
            ->leftJoin('Departments', 'Service_Types.DepartmentID', '=', 'Departments.DepartmentID')
            ->select(
                'Payments.PaymentID',
                'Payments.RequestID',
                'Payments.Amount',
                'Payments.Currency',
                'Payments.Status',
                'Payments.PaymentDate',
                'Payments.PaidAt',
                'Payments.ReceiptNumber',
                'Payments.StripePaymentIntentID',
                'Payments.LahzaReference',
                'Payments.NowPaymentsPaymentID',
                'Payments.FailureReason',
                'Persons.FullName',
                'Persons.NationalID',
                'Persons.Phone',
                'Service_Types.ServiceName',
                'Service_Types.Fees',
                'Departments.DepartmentName'
            );

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('Persons.FullName', 'LIKE', "%{$search}%")
                    ->orWhere('Persons.NationalID', 'LIKE', "%{$search}%")
                    ->orWhere('Payments.ReceiptNumber', 'LIKE', "%{$search}%")
                    ->orWhere('Service_Types.ServiceName', 'LIKE', "%{$search}%")
                    ->orWhere('Payments.StripePaymentIntentID', 'LIKE', "%{$search}%")
                    ->orWhere('Payments.LahzaReference', 'LIKE', "%{$search}%")
                    ->orWhere('Payments.NowPaymentsPaymentID', 'LIKE', "%{$search}%");
            });
        }

        $status = trim((string) $request->input('status', ''));
        if ($status !== '') {
            $query->where('Payments.Status', '=', $status);
        }

        $gateway = trim((string) $request->input('gateway', ''));
        if ($gateway === 'stripe') {
            $query->whereNotNull('Payments.StripePaymentIntentID');
        } elseif ($gateway === 'lahza') {
            $query->whereNotNull('Payments.LahzaReference');
        } elseif ($gateway === 'nowpayments') {
            $query->whereNotNull('Payments.NowPaymentsPaymentID');
        }

        $dateFrom = trim((string) $request->input('date_from', ''));
        if ($dateFrom !== '') {
            $query->where('Payments.PaymentDate', '>=', $dateFrom.' 00:00:00');
        }

        $dateTo = trim((string) $request->input('date_to', ''));
        if ($dateTo !== '') {
            $query->where('Payments.PaymentDate', '<=', $dateTo.' 23:59:59');
        }

        $payments = $query->orderBy('Payments.PaymentDate', 'desc')
            ->paginate(25)
            ->appends($request->only(['search', 'status', 'gateway', 'date_from', 'date_to']));

        $stats = DB::table('Payments')
            ->select(
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(CASE WHEN Status = \'succeeded\' THEN Amount ELSE 0 END) as successful_amount'),
                DB::raw('SUM(CASE WHEN Status = \'pending\' THEN 1 ELSE 0 END) as pending_count'),
                DB::raw('SUM(CASE WHEN Status = \'failed\' THEN 1 ELSE 0 END) as failed_count')
            )
            ->first();

        $stripeService = app(StripePayoutsService::class);
        $stripeExternalAccounts = $stripeService->getExternalAccounts();

        return view('admin.payments', [
            'payments' => $payments,
            'stats' => $stats,
            'loadTimeMs' => round((microtime(true) - $start) * 1000, 2),
            'stripeBalance' => $stripeService->getBalance(),
            'stripeExternalAccounts' => $stripeExternalAccounts,
            'stripeAccountsAttached' => ($stripeExternalAccounts['available'] ?? false) === true && ! empty($stripeExternalAccounts['data']),
            'stripeTestMode' => $stripeService->isTestMode(),
            'withdrawals' => StripePayout::orderBy('created_at', 'desc')->limit(10)->get(),
        ]);
    }
}
