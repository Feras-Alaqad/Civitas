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

        Cache::put("import.{$importId}", [
            'percent' => 0,
            'processed' => 0,
            'total' => 0,
            'remaining' => 0,
            'status' => 'processing',
            'updated_at' => now()->timestamp,
        ], 172800);

        $this->trackActive($importId);

        ImportPersonsCsvJob::dispatch($importId);

        return response()->json(['import_id' => $importId]);
    }

    public function progress($importId)
    {
        $data = Cache::get("import.{$importId}", [
            'percent' => 0,
            'processed' => 0,
            'total' => 0,
            'remaining' => 0,
            'status' => 'processing',
        ]);

        $data['remaining'] = max(0, (int) ($data['total'] ?? 0) - (int) ($data['processed'] ?? 0));

        return response()->json($data);
    }

    private function trackActive(string $importId): void
    {
        $ids = Cache::get('imports:active_ids', []);

        if (!in_array($importId, $ids, true)) {
            $ids[] = $importId;
            Cache::forever('imports:active_ids', $ids);
        }
    }
}
