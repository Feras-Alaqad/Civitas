<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $start = microtime(true);

        $query = DB::table('audit_logs')
            ->join('users', 'audit_logs.UserID', '=', 'users.id')
            ->leftJoin('service_requests', 'audit_logs.ReferenceID', '=', 'service_requests.RequestID')
            ->leftJoin('persons', 'service_requests.PersonID', '=', 'persons.PersonID')
            ->leftJoin('service_types', 'service_requests.ServiceTypeID', '=', 'service_types.ServiceTypeID')
            ->leftJoin('payments', 'service_requests.RequestID', '=', 'payments.RequestID')
            ->select(
                'audit_logs.LogID',
                'audit_logs.ActionType',
                'audit_logs.Description',
                'audit_logs.ReferenceID',
                'audit_logs.Timestamp',
                'audit_logs.IPAddress',
                'users.Username',
                'users.avatar',
                'persons.FullName',
                'service_types.ServiceName',
                'service_requests.Status as RequestStatus',
                'service_requests.RequestDate',
                'payments.Status as PaymentStatus',
                'payments.Amount',
                'payments.ReceiptNumber'
            );

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('audit_logs.Description', 'LIKE', "%{$search}%")
                  ->orWhere('users.Username', 'LIKE', "%{$search}%")
                  ->orWhere('persons.FullName', 'LIKE', "%{$search}%")
                  ->orWhere('audit_logs.IPAddress', 'LIKE', "%{$search}%");
            });
        }

        $actionType = trim((string) $request->input('action_type', ''));
        if ($actionType !== '') {
            $query->where('audit_logs.ActionType', '=', $actionType);
        }

        $status = trim((string) $request->input('status', ''));
        if ($status !== '') {
            $query->where('service_requests.Status', '=', $status);
        }

        $dateFrom = trim((string) $request->input('date_from', ''));
        if ($dateFrom !== '') {
            $query->where('audit_logs.Timestamp', '>=', $dateFrom . ' 00:00:00');
        }

        $dateTo = trim((string) $request->input('date_to', ''));
        if ($dateTo !== '') {
            $query->where('audit_logs.Timestamp', '<=', $dateTo . ' 23:59:59');
        }

        $userId = trim((string) $request->input('user_id', ''));
        if ($userId !== '') {
            $query->where('audit_logs.UserID', '=', $userId);
        }

        $auditLogs = $query->orderBy('audit_logs.Timestamp', 'desc')
            ->paginate(25)
            ->appends($request->only(['search', 'action_type', 'status', 'date_from', 'date_to', 'user_id']));

        $distinctActions = DB::table('audit_logs')
            ->distinct()
            ->pluck('ActionType')
            ->filter()
            ->values();

        $distinctUsers = DB::table('users')
            ->select('users.id', 'users.Username')
            ->distinct()
            ->orderBy('users.Username')
            ->get();

        return view('admin.audit-logs', [
            'auditLogs' => $auditLogs,
            'distinctActions' => $distinctActions,
            'distinctUsers' => $distinctUsers,
            'loadTimeMs' => round((microtime(true) - $start) * 1000, 2),
        ]);
    }

    public function auditTrail(Request $request, string $referenceId)
    {
        $auditLog = DB::table('audit_logs')->where('LogID', $referenceId)->first();
        $reqId = $auditLog ? $auditLog->ReferenceID : null;

        $timelineQuery = DB::table('audit_logs')
            ->join('users', 'audit_logs.UserID', '=', 'users.id')
            ->select(
                'audit_logs.LogID',
                'audit_logs.ActionType',
                'audit_logs.Description',
                'audit_logs.Timestamp',
                'audit_logs.IPAddress',
                'users.Username'
            )
            ->orderBy('audit_logs.Timestamp', 'asc');

        if ($reqId) {
            $timelineQuery->where('audit_logs.ReferenceID', $reqId);
        } else {
            $timelineQuery->where('audit_logs.LogID', $referenceId);
        }

        $timeline = $timelineQuery->get();

        $serviceRequest = null;
        $payments = collect();
        $attachments = collect();

        if ($reqId) {
            $serviceRequest = DB::table('service_requests')
                ->leftJoin('persons', 'service_requests.PersonID', '=', 'persons.PersonID')
                ->leftJoin('service_types', 'service_requests.ServiceTypeID', '=', 'service_types.ServiceTypeID')
                ->leftJoin('departments', 'service_types.DepartmentID', '=', 'departments.DepartmentID')
                ->where('service_requests.RequestID', $reqId)
                ->select(
                    'service_requests.*',
                    'persons.FullName',
                    'persons.NationalID',
                    'persons.Phone',
                    'persons.Email',
                    'service_types.ServiceName',
                    'service_types.Fees',
                    'service_types.RequiredDocuments',
                    'departments.DepartmentName'
                )
                ->first();

            $payments = DB::table('payments')
                ->where('payments.RequestID', $reqId)
                ->orderBy('payments.PaymentDate', 'asc')
                ->get();

            $attachments = DB::table('attachments')
                ->where('attachments.RequestID', $reqId)
                ->orderBy('attachments.created_at', 'asc')
                ->get();
        }

        $details = null;
        if (!$reqId && $auditLog) {
            $details = [
                'ActionType' => $auditLog->ActionType,
                'Description' => $auditLog->Description,
                'IPAddress' => $auditLog->IPAddress,
                'Timestamp' => $auditLog->Timestamp,
                'Username' => $timeline->first()->Username ?? null,
            ];
        }

        return response()->json([
            'request' => $serviceRequest,
            'timeline' => $timeline,
            'payments' => $payments,
            'attachments' => $attachments,
            'details' => $details,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = DB::table('audit_logs')
            ->join('users', 'audit_logs.UserID', '=', 'users.id')
            ->leftJoin('service_requests', 'audit_logs.ReferenceID', '=', 'service_requests.RequestID')
            ->leftJoin('persons', 'service_requests.PersonID', '=', 'persons.PersonID')
            ->leftJoin('service_types', 'service_requests.ServiceTypeID', '=', 'service_types.ServiceTypeID')
            ->leftJoin('payments', 'service_requests.RequestID', '=', 'payments.RequestID')
            ->select(
                'audit_logs.Timestamp',
                'users.Username',
                'audit_logs.ActionType',
                'audit_logs.Description',
                'persons.FullName',
                'service_types.ServiceName',
                'service_requests.Status as RequestStatus',
                'payments.Status as PaymentStatus',
                'payments.Amount',
                'audit_logs.IPAddress'
            );

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('audit_logs.Description', 'LIKE', "%{$search}%")
                  ->orWhere('users.Username', 'LIKE', "%{$search}%")
                  ->orWhere('persons.FullName', 'LIKE', "%{$search}%")
                  ->orWhere('audit_logs.IPAddress', 'LIKE', "%{$search}%");
            });
        }

        $actionType = trim((string) $request->input('action_type', ''));
        if ($actionType !== '') {
            $query->where('audit_logs.ActionType', '=', $actionType);
        }

        $status = trim((string) $request->input('status', ''));
        if ($status !== '') {
            $query->where('service_requests.Status', '=', $status);
        }

        $dateFrom = trim((string) $request->input('date_from', ''));
        if ($dateFrom !== '') {
            $query->where('audit_logs.Timestamp', '>=', $dateFrom . ' 00:00:00');
        }

        $dateTo = trim((string) $request->input('date_to', ''));
        if ($dateTo !== '') {
            $query->where('audit_logs.Timestamp', '<=', $dateTo . ' 23:59:59');
        }

        $userId = trim((string) $request->input('user_id', ''));
        if ($userId !== '') {
            $query->where('audit_logs.UserID', '=', $userId);
        }

        $query->orderBy('audit_logs.Timestamp', 'desc');

        $filename = 'audit-logs-' . date('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Timestamp', 'User', 'Action Type', 'Description',
                'Person', 'Service', 'Request Status',
                'Payment Status', 'Amount', 'IP Address',
            ]);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->Timestamp,
                        $row->Username,
                        $row->ActionType,
                        $row->Description,
                        $row->FullName ?? '',
                        $row->ServiceName ?? '',
                        $row->RequestStatus ?? '',
                        $row->PaymentStatus ?? '',
                        $row->Amount ? number_format($row->Amount, 2) : '',
                        $row->IPAddress ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment',
        ]);
    }
}
