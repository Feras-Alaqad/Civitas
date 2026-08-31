<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\AuditLog;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            ->select('Persons.PersonID', 'Persons.FullName', 'Persons.NationalID', 'Persons.Phone', 'Persons.Email', 'Governorates.GovernorateName')
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
            'payment_method' => 'required|in:stripe,lahza',
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
            Log::error('Service request creation failed', [
                'person_id' => $personId,
                'service_type_id' => $serviceTypeId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create service request. Please try again.',
            ], 500);
        }
    }

}
