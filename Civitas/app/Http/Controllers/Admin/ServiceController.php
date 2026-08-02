<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function create(Request $request)
    {
        $personId = $request->query('person_id');

        if (!$personId) {
            return redirect()->route('admin.citizens')->with('error', 'No person selected.');
        }

        $person = DB::table('Persons')
            ->leftJoin('Cities', 'Cities.CityID', '=', 'Persons.CityID')
            ->leftJoin('Governorates', 'Governorates.GovernorateID', '=', 'Cities.GovernorateID')
            ->where('Persons.PersonID', $personId)
            ->select('Persons.PersonID', 'Persons.FullName', 'Persons.NationalID', 'Governorates.GovernorateName')
            ->first();

        if (!$person) {
            return redirect()->route('admin.citizens')->with('error', 'Person not found.');
        }

        $serviceTypes = ServiceType::with('department')->orderBy('ServiceName')->get();

        $countByDept = [];
        foreach ($serviceTypes as $st) {
            $name = $st->department?->DepartmentName ?? '—';
            $countByDept[$name] = ($countByDept[$name] ?? 0) + 1;
        }

        $departments = collect($countByDept)
            ->map(fn ($count, $name) => (object) [
                'DepartmentName' => $name,
                'ServiceCount' => $count,
            ])
            ->sortBy('DepartmentName')
            ->values();

        return view('admin.service-application', [
            'person' => $person,
            'serviceTypes' => $serviceTypes,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'person_id' => 'required|string|exists:Persons,PersonID',
            'service_type_id' => 'required|string|exists:Service_Types,ServiceTypeID',
            'payment_method' => 'required|in:paypal',
        ]);

        $personId = $request->input('person_id');
        $serviceTypeId = $request->input('service_type_id');

        $serviceType = ServiceType::findOrFail($serviceTypeId);

        $requestId = Str::uuid();
        $paymentId = Str::uuid();

        DB::beginTransaction();

        try {
            $serviceRequest = ServiceRequest::create([
                'RequestID' => $requestId,
                'PersonID' => $personId,
                'UserID' => Auth::id(),
                'ServiceTypeID' => $serviceTypeId,
                'RequestDate' => now(),
                'Status' => 'Pending',
            ]);

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $index => $file) {
                    $docType = $request->input('document_types')[$index] ?? 'Document';
                    $path = $file->store('attachments/' . $requestId, 'public');
                    $attId = Str::uuid();

                    Attachment::create([
                        'AttachmentID' => $attId,
                        'RequestID' => $requestId,
                        'FilePath' => $path,
                        'DocumentType' => $docType,
                    ]);
                }
            }

            AuditLog::create([
                'UserID' => Auth::id(),
                'ActionType' => 'Service Request Created',
                'Description' => "Created service request for {$serviceType->ServiceName} (Person: {$personId})",
                'ReferenceID' => $requestId,
                'Timestamp' => now(),
                'IPAddress' => $request->ip(),
            ]);

            DB::commit();

            $cacheService = app(\App\Services\CitizensCacheService::class);
            Cache::forget($cacheService->buildRequestsCacheKey($personId));

            return response()->json([
                'success' => true,
                'request_id' => $requestId,
                'payment_id' => $paymentId,
                'amount' => $serviceType->Fees,
                'service_name' => $serviceType->ServiceName,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create service request: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function paypalCreateOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'request_id' => 'required|string',
        ]);

        $accessToken = $this->getPayPalAccessToken();

        if (!$accessToken) {
            return response()->json(['error' => 'Failed to authenticate with PayPal'], 500);
        }

        $response = Http::withToken($accessToken)
            ->post(config('services.paypal.mode') === 'sandbox'
                ? 'https://api-m.sandbox.paypal.com/v2/checkout/orders'
                : 'https://api-m.paypal.com/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $request->input('request_id'),
                    'amount' => [
                        'currency_code' => $request->input('currency', 'USD'),
                        'value' => number_format($request->input('amount'), 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => route('admin.service.paypal-return'),
                    'cancel_url' => route('admin.service.paypal-cancel'),
                ],
            ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'PayPal order creation failed', 'details' => $response->json()], 422);
    }

    public function paypalCaptureOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
            'request_id' => 'required|string',
        ]);

        $orderId = $request->input('order_id');
        $requestId = $request->input('request_id');
        $isSimulated = str_starts_with($orderId, 'SIMULATED-');

        if ($isSimulated) {
            $receiptNumber = 'RCPT-' . strtoupper(Str::random(10));

            DB::beginTransaction();

            try {
                Payment::create([
                    'PaymentID' => Str::uuid(),
                    'RequestID' => $requestId,
                    'Amount' => ServiceType::find(ServiceRequest::find($requestId)?->ServiceTypeID)?->Fees ?? 0,
                    'PaymentDate' => now(),
                    'ReceiptNumber' => $receiptNumber,
                    'PayPalOrderID' => $orderId,
                    'PayPalPayerID' => 'SIMULATED',
                    'Status' => 'Completed',
                ]);

                ServiceRequest::where('RequestID', $requestId)
                    ->update(['Status' => 'Completed']);

                AuditLog::create([
                    'UserID' => Auth::id(),
                    'ActionType' => 'Payment Completed (Simulated)',
                    'Description' => "Simulated payment completed (Order: {$orderId})",
                    'ReferenceID' => $requestId,
                    'Timestamp' => now(),
                    'IPAddress' => $request->ip(),
                ]);

                DB::commit();

                event(new \App\Events\PaymentCompleted(
                    \App\Models\Payment::where('RequestID', $requestId)->latest()->first()
                ));

                return response()->json([
                    'success' => true,
                    'status' => 'COMPLETED',
                    'receipt_number' => $receiptNumber,
                    'amount' => ServiceType::find(ServiceRequest::find($requestId)?->ServiceTypeID)?->Fees ?? 0,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Failed to save payment: ' . $e->getMessage()], 500);
            }
        }

        $accessToken = $this->getPayPalAccessToken();

        if (!$accessToken) {
            return response()->json(['error' => 'Failed to authenticate with PayPal'], 500);
        }

        $baseUrl = config('services.paypal.mode') === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        $response = Http::withToken($accessToken)
            ->post("{$baseUrl}/v2/checkout/orders/{$request->input('order_id')}/capture");

        if ($response->successful()) {
            $data = $response->json();
            $capture = $data['purchase_units'][0]['payments']['captures'][0] ?? null;
            $payerId = $data['payer']['payer_id'] ?? null;
            $status = $data['status'] ?? 'UNKNOWN';

            if ($status === 'COMPLETED' && $capture) {
                $requestId = $request->input('request_id');
                $receiptNumber = 'RCPT-' . strtoupper(Str::random(10));

                DB::beginTransaction();

                try {
                    Payment::create([
                        'PaymentID' => Str::uuid(),
                        'RequestID' => $requestId,
                        'Amount' => $capture['amount']['value'],
                        'PaymentDate' => now(),
                        'ReceiptNumber' => $receiptNumber,
                        'PayPalOrderID' => $request->input('order_id'),
                        'PayPalPayerID' => $payerId,
                        'Status' => 'Completed',
                    ]);

                    ServiceRequest::where('RequestID', $requestId)
                        ->update(['Status' => 'Completed']);

                    AuditLog::create([
                        'UserID' => Auth::id(),
                        'ActionType' => 'Payment Completed',
                        'Description' => "Payment of {$capture['amount']['value']} completed via PayPal (Order: {$request->input('order_id')})",
                        'ReferenceID' => $requestId,
                        'Timestamp' => now(),
                        'IPAddress' => $request->ip(),
                    ]);

                    DB::commit();

                    event(new \App\Events\PaymentCompleted(
                        \App\Models\Payment::where('RequestID', $requestId)->latest()->first()
                    ));

                    return response()->json([
                        'success' => true,
                        'status' => $status,
                        'receipt_number' => $receiptNumber,
                        'amount' => $capture['amount']['value'],
                        'capture_id' => $capture['id'],
                        'payer_id' => $payerId,
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['error' => 'Failed to save payment: ' . $e->getMessage()], 500);
                }
            }

            return response()->json(['error' => 'Payment not completed', 'status' => $status], 422);
        }

        return response()->json(['error' => 'PayPal capture failed', 'details' => $response->json()], 422);
    }

    public function paypalReturn(Request $request)
    {
        return redirect()->route('admin.citizens', ['person_id' => $request->query('person_id')])
            ->with('success', 'Payment completed successfully!');
    }

    public function paypalCancel(Request $request)
    {
        return redirect()->route('admin.citizens', ['person_id' => $request->query('person_id')])
            ->with('error', 'Payment was cancelled.');
    }

    private function getPayPalAccessToken(): ?string
    {
        $baseUrl = config('services.paypal.mode') === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        $response = Http::withBasicAuth(
            config('services.paypal.client_id'),
            config('services.paypal.client_secret')
        )->post("{$baseUrl}/v1/oauth2/token", [
            'grant_type' => 'client_credentials',
        ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        return null;
    }
}
