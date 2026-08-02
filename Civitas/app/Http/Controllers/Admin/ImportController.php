<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportPersonsCsvJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:614400',
        ]);

        $file = $request->file('csv_file');
        $importId = (string) Str::uuid();

        $path = $file->storeAs('imports', $importId . '.csv');

        if (!$path) {
            return response()->json(['message' => 'Failed to store file.'], 500);
        }

        ImportPersonsCsvJob::dispatch($importId);

        return response()->json(['import_id' => $importId]);
    }

    public function progress($importId)
    {
        $data = Cache::get("import.{$importId}", [
            'percent' => 0,
            'processed' => 0,
            'total' => 0,
            'status' => 'processing',
        ]);

        return response()->json($data);
    }
}
