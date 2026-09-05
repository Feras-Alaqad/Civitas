<?php

namespace App\Services;

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DatabaseCsvImporter
{
    private const CHUNK_SIZE = 1000;
    private const MAX_RECORDS = 4000000;

    private const IMPORT_INDEXES = [
        ['name' => 'idx_persons_national_id', 'columns' => ['NationalID'], 'type' => 'index'],
        ['name' => 'idx_persons_city_id', 'columns' => ['CityID'], 'type' => 'index'],
        ['name' => 'idx_persons_search', 'columns' => ['FullName', 'Phone', 'Email'], 'type' => 'index'],
        ['name' => 'idx_persons_email', 'columns' => ['Email'], 'type' => 'index'],
        ['name' => 'idx_persons_phone', 'columns' => ['Phone'], 'type' => 'index'],
        ['name' => 'ft_persons_full_name', 'columns' => ['FullNameSearch'], 'type' => 'fulltext'],
        ['name' => 'idx_persons_governorate_id', 'columns' => ['GovernorateID'], 'type' => 'index'],
        ['name' => 'idx_persons_gov_person', 'columns' => ['GovernorateID', 'PersonID'], 'type' => 'index'],
    ];

    private const PERSONS_FOREIGN_KEYS = [
        [
            'name' => 'persons_cityid_foreign',
            'column' => 'CityID',
            'references' => 'CityID',
            'on' => 'Cities',
            'onDelete' => 'SET NULL',
        ],
    ];

    private PersonCsvMapper $mapper;

    public function __construct(?PersonCsvMapper $mapper = null)
    {
        $this->mapper = $mapper ?? new PersonCsvMapper();
    }

    public function import(string $path, ?callable $onProgress = null, ?int $limit = null): int
    {
        return $this->importFiles([$path], $onProgress, $limit);
    }

    public function importFiles(array $paths, ?callable $onProgress = null, ?int $limit = null): int
    {
        $paths = array_values(array_filter($paths));

        if (empty($paths)) {
            throw new RuntimeException('No CSV files provided.');
        }

        foreach ($paths as $path) {
            if (!file_exists($path)) {
                throw new RuntimeException("CSV file not found: {$path}");
            }
        }

        $limit = min($limit ?? self::MAX_RECORDS, self::MAX_RECORDS);
        $total = min($this->countDataLinesAcross($paths), $limit);

        $this->mapper->refreshLookups();

        $processed = 0;

        try {
            $this->dropForeignKeys();
            $this->dropSecondaryIndexes();

            foreach ($paths as $fileIndex => $path) {
                $handle = fopen($path, 'r');

                if (!$handle) {
                    throw new RuntimeException("Could not open CSV file for reading: {$path}");
                }

                $headers = $this->mapper->readHeaders($handle);

                if (!$headers) {
                    fclose($handle);
                    throw new RuntimeException("Could not read CSV headers from: {$path}");
                }

                $batch = [];

                while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                    if ($processed >= $limit) {
                        break;
                    }

                    if (count($line) !== count($headers)) {
                        continue;
                    }

                    $batch[] = $this->mapper->buildRow(array_combine($headers, $line));
                    $processed++;

                    if (count($batch) >= self::CHUNK_SIZE) {
                        DB::transaction(fn () => DB::table('Persons')->insertOrIgnore($batch));
                        $batch = [];

                        $this->reportProgress($onProgress, $processed, $total, $fileIndex, count($paths), $path);
                    }
                }

                if (!empty($batch)) {
                    DB::transaction(fn () => DB::table('Persons')->insertOrIgnore($batch));
                }

                fclose($handle);

                $this->reportProgress($onProgress, $processed, $total, $fileIndex, count($paths), $path);
            }
        } finally {
            $this->recreateIndexes();
            $this->recreateForeignKeys();
        }

        $this->clearCaches();

        return $processed;
    }

    public function countDataLines(string $path): int
    {
        $count = 0;
        $handle = fopen($path, 'r');

        if (!$handle) {
            return 0;
        }

        while (($line = fgets($handle)) !== false) {
            if (trim($line) !== '') {
                $count++;
            }
        }

        fclose($handle);

        return max(0, $count - 1);
    }

    public function truncatePersons(): void
    {
        DB::table('Service_Requests')->update(['PersonID' => null]);
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('Persons')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->clearCaches();
    }

    private function countDataLinesAcross(array $paths): int
    {
        $total = 0;

        foreach ($paths as $path) {
            $total += $this->countDataLines($path);
        }

        return $total;
    }

    private function reportProgress(?callable $onProgress, int $processed, int $total, int $fileIndex, int $fileCount, string $path): void
    {
        if ($onProgress) {
            $onProgress($processed, $total, $fileIndex, $fileCount, $path);
        }
    }

    private function dropForeignKeys(): void
    {
        foreach (self::PERSONS_FOREIGN_KEYS as $fk) {
            if (!$this->hasForeignKey($fk['name'])) {
                continue;
            }

            Schema::table('Persons', function (Blueprint $table) use ($fk) {
                $table->dropForeign($fk['name']);
            });
        }
    }

    private function recreateForeignKeys(): void
    {
        foreach (self::PERSONS_FOREIGN_KEYS as $fk) {
            if ($this->hasForeignKey($fk['name'])) {
                continue;
            }

            Schema::table('Persons', function (Blueprint $table) use ($fk) {
                $foreign = $table->foreign($fk['column'])->references($fk['references'])->on($fk['on']);

                if ($fk['onDelete'] === 'CASCADE') {
                    $foreign->cascadeOnDelete();
                } elseif ($fk['onDelete'] === 'SET NULL') {
                    $foreign->nullOnDelete();
                } elseif ($fk['onDelete'] === 'RESTRICT') {
                    $foreign->restrictOnDelete();
                }
            });
        }
    }

    private function hasForeignKey(string $name): bool
    {
        return (bool) DB::selectOne(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Persons' AND CONSTRAINT_NAME = ? LIMIT 1",
            [$name]
        );
    }

    private function dropSecondaryIndexes(): void
    {
        foreach (self::IMPORT_INDEXES as $index) {
            if (!Schema::hasIndex('Persons', $index['name'])) {
                continue;
            }

            Schema::table('Persons', function (Blueprint $table) use ($index) {
                $table->dropIndex($index['name']);
            });
        }
    }

    private function recreateIndexes(): void
    {
        foreach (self::IMPORT_INDEXES as $index) {
            if (Schema::hasIndex('Persons', $index['name'])) {
                continue;
            }

            Schema::table('Persons', function (Blueprint $table) use ($index) {
                if ($index['type'] === 'fulltext') {
                    $table->fullText($index['columns'], $index['name']);
                } else {
                    $table->index($index['columns'], $index['name']);
                }
            });
        }
    }

    private function clearCaches(): void
    {
        CitizensCacheService::flushCitizensCache();
        Cache::forget('citizens:total_count');
        DashboardController::clearCache();
    }
}