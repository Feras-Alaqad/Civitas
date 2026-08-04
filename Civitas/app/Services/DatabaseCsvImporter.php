<?php

namespace App\Services;

use App\Http\Controllers\Admin\DashboardController;
use App\Models\Person;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class DatabaseCsvImporter
{
    private const CHUNK_SIZE = 500;
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

    private array $cityIds = [];
    private array $governorateIds = [];
    private array $nationalityIds = [];

    public function import(string $path, ?callable $onProgress = null, ?int $limit = null): int
    {
        if (!file_exists($path)) {
            throw new RuntimeException("CSV file not found: {$path}");
        }

        $limit = min($limit ?? self::MAX_RECORDS, self::MAX_RECORDS);

        $this->loadRandomIds();

        try {
            $this->dropForeignKeys();
            $this->dropSecondaryIndexes();

            $handle = fopen($path, 'r');
            $headers = $this->readHeaders($handle);

            if (!$headers) {
                fclose($handle);
                throw new RuntimeException("Could not read CSV headers from: {$path}");
            }

            $total = min($this->countDataLines($path), $limit);
            $batch = [];
            $processed = 0;

            while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if ($processed >= $limit) {
                    break;
                }

                if (count($line) !== count($headers)) {
                    continue;
                }

                $batch[] = $this->buildRow(array_combine($headers, $line));
                $processed++;

                if (count($batch) >= self::CHUNK_SIZE) {
                    DB::table('Persons')->insertOrIgnore($batch);
                    $batch = [];

                    if ($onProgress) {
                        $onProgress($processed, $total);
                    }
                }
            }

            if (!empty($batch)) {
                DB::table('Persons')->insertOrIgnore($batch);
            }

            fclose($handle);

            if ($onProgress) {
                $onProgress($processed, $total);
            }

            $this->clearCaches();

            return $processed;
        } finally {
            $this->recreateIndexes();
            $this->recreateForeignKeys();
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

    private function loadRandomIds(): void
    {
        $this->cityIds = DB::table('Cities')->pluck('CityID')->toArray();
        $this->governorateIds = DB::table('Governorates')->pluck('GovernorateID')->toArray();
        $this->nationalityIds = DB::table('Nationalities')->pluck('NationalityID')->toArray();
    }

    private function readHeaders($handle): ?array
    {
        $headers = fgetcsv($handle, 0, ',', '"', '');

        if (!$headers) {
            return null;
        }

        return array_map(
            fn ($header) => trim(preg_replace('/^\xEF\xBB\xBF/', '', $header)),
            $headers
        );
    }

    private function buildRow(array $record): array
    {
        $fullName = trim(implode(' ', [
            $record['FirstName'] ?? '',
            $record['FatherName'] ?? '',
            $record['MotherName'] ?? '',
            $record['FamilyName'] ?? '',
        ]));

        return [
            'PersonID'       => (string) Str::uuid(),
            'FullName'       => $fullName ?: '',
            'FullNameSearch' => Person::normalizeName($fullName),
            'DateOfBirth'    => null,
            'NationalID'     => ($record['ID'] ?? '') !== '' ? $record['ID'] : null,
            'Address'        => null,
            'Gender'         => null,
            'NationalityID'  => $this->nationalityIds ? $this->nationalityIds[array_rand($this->nationalityIds)] : null,
            'CityID'         => $this->cityIds ? $this->cityIds[array_rand($this->cityIds)] : null,
            'GovernorateID'  => $this->governorateIds ? $this->governorateIds[array_rand($this->governorateIds)] : null,
            'Phone'          => $record['PhoneNumber'] ?? null,
            'Email'          => $record['Email'] ?? null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ];
    }

    private function clearCaches(): void
    {
        CitizensCacheService::flushCitizensCache();
        Cache::forget('citizens:total_count');
        DashboardController::clearCache();
    }
}
