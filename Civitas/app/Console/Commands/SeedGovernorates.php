<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeedGovernorates extends Command
{
    protected $signature = 'governorates:seed';
    protected $description = 'Import governorates from CSV and assign randomly to persons';

    public function handle(): int
    {
        $existingGovernorates = DB::table('Governorates')->pluck('GovernorateName', 'GovernorateID');

        if ($existingGovernorates->isEmpty()) {
            $csvPath = base_path('governorates.csv');

            if (!file_exists($csvPath)) {
                $this->error("No governorates found and CSV file not found at: {$csvPath}");
                return self::FAILURE;
            }

            $rows = array_map('str_getcsv', file($csvPath));
            array_shift($rows);

            foreach ($rows as $row) {
                DB::table('Governorates')->insert([
                    'GovernorateID' => (string) Str::uuid(),
                    'GovernorateName' => $row[1],
                ]);
            }

            $existingGovernorates = DB::table('Governorates')->pluck('GovernorateName', 'GovernorateID');
            $this->info("Imported " . $existingGovernorates->count() . " governorates from CSV.");
        } else {
            $this->info("Using " . $existingGovernorates->count() . " existing governorates.");
        }

        $governorateIds = $existingGovernorates->keys()->toArray();
        $count = count($governorateIds);

        $total = DB::table('Persons')->count();
        $this->info("Assigning random governorates to {$total} persons...");

        $quotedIds = array_map(fn($id) => "'" . addslashes($id) . "'", $governorateIds);
        $idList = implode(',', $quotedIds);
        $sql = "UPDATE Persons SET GovernorateID = ELT(1 + FLOOR(RAND() * {$count}), {$idList})";

        DB::statement($sql);
        $this->info('Updated all persons.');

        // 4. Show distribution
        $this->newLine();
        $this->info('Distribution:');
        $distribution = DB::table('Persons')
            ->join('Governorates', 'Persons.GovernorateID', '=', 'Governorates.GovernorateID')
            ->select('Governorates.GovernorateName', DB::raw('count(*) as total'))
            ->groupBy('Governorates.GovernorateName')
            ->orderByDesc('total')
            ->get();

        foreach ($distribution as $row) {
            $this->line("  {$row->GovernorateName}: {$row->total}");
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
