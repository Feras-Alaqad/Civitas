<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Services\DatabaseCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class CsvImportTest extends TestCase
{
    use RefreshDatabase;

    private string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tmpDir = sys_get_temp_dir() . '/civitas_csv_test_' . uniqid();
        mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("{$this->tmpDir}/*") ?: []);
        rmdir($this->tmpDir);

        parent::tearDown();
    }

    private function seedLookups(): array
    {
        $govId = (string) Str::uuid();
        $cityId = (string) Str::uuid();
        $natId = (string) Str::uuid();

        DB::table('Governorates')->insert([
            'GovernorateID'   => $govId,
            'GovernorateName' => 'Test Governorate',
            'source_code'     => 5,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        DB::table('Cities')->insert([
            'CityID'         => $cityId,
            'CityName'       => 'Test City',
            'GovernorateID'  => $govId,
            'source_code'    => 74,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        DB::table('Nationalities')->insert([
            'NationalityID'   => $natId,
            'NationalityName' => 'Test Nationality',
            'source_code'     => 94,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return [
            'governorateId' => $govId,
            'cityId'        => $cityId,
            'nationalityId' => $natId,
        ];
    }

    private function writeCsv(string $name, array $rows, ?string $header = null): string
    {
        $header ??= 'ID,FirstName,FatherName,MotherName,FamilyName,CityID,GovernorateID,NationalityID,PhoneNumber,Email';
        $path = "{$this->tmpDir}/{$name}";

        file_put_contents($path, "\xEF\xBB\xBF" . $header . "\n" . implode("\n", $rows) . "\n");

        return $path;
    }

    public function test_import_maps_codes_no_random_ids_and_deduplicates(): void
    {
        $lookups = $this->seedLookups();

        $path = $this->writeCsv('database.csv', [
            '633024311,أحمد,محمد,فاطمة,خالد,74,5,94,0534302187,a@example.com',
            '633024312,علي,حسن,سمية,يوسف,999,5,94,0534302188,b@example.com',
            '633024311,مكرر,مكرر,مكرر,مكرر,74,5,94,0534302189,c@example.com',
        ]);

        $importer = new DatabaseCsvImporter();
        $count = $importer->import($path);

        // The importer counts every parsed data row (3), while insertOrIgnore
        // skips the duplicate NationalID, so only 2 rows are actually stored.
        $this->assertSame(3, $count);
        $this->assertSame(2, DB::table('Persons')->count());

        $first = DB::table('Persons')->where('NationalID', '633024311')->first();
        $this->assertNotNull($first);
        $this->assertNotSame('633024311', $first->PersonID);
        $this->assertTrue(Str::isUuid($first->PersonID));
        $this->assertSame($lookups['cityId'], $first->CityID);
        $this->assertSame($lookups['governorateId'], $first->GovernorateID);
        $this->assertSame($lookups['nationalityId'], $first->NationalityID);
        $this->assertSame(
            Person::normalizeName('أحمد محمد فاطمة خالد'),
            $first->FullNameSearch
        );
        $this->assertSame('0534302187', $first->Phone);
        $this->assertSame('a@example.com', $first->Email);

        $second = DB::table('Persons')->where('NationalID', '633024312')->first();
        $this->assertNotNull($second);
        $this->assertNull($second->CityID);
        $this->assertSame($lookups['governorateId'], $second->GovernorateID);
        $this->assertSame($lookups['nationalityId'], $second->NationalityID);
    }

    public function test_absent_optional_columns_stay_null(): void
    {
        $this->seedLookups();

        $path = $this->writeCsv(
            'optional.csv',
            [
                '633024311,أحمد,محمد,فاطمة,خالد,74,5,94,1990-05-20,غزة - الشجاعية,M,0534302187,a@example.com',
                '633024312,علي,حسن,سمية,يوسف,74,5,94,not-a-date,,,0534302188,b@example.com',
            ],
            'ID,FirstName,FatherName,MotherName,FamilyName,CityID,GovernorateID,NationalityID,DateOfBirth,Address,Gender,PhoneNumber,Email'
        );

        $importer = new DatabaseCsvImporter();
        $importer->import($path);

        $withDate = DB::table('Persons')->where('NationalID', '633024311')->first();
        $this->assertSame('1990-05-20', $withDate->DateOfBirth);
        $this->assertSame('غزة - الشجاعية', $withDate->Address);
        $this->assertSame('M', $withDate->Gender);

        $noDate = DB::table('Persons')->where('NationalID', '633024312')->first();
        $this->assertNull($noDate->DateOfBirth);
        $this->assertNull($noDate->Address);
        $this->assertNull($noDate->Gender);
    }

    public function test_indexes_and_foreign_keys_restored_after_import(): void
    {
        $this->seedLookups();

        $path = $this->writeCsv('database.csv', [
            '633024311,أحمد,محمد,فاطمة,خالد,74,5,94,0534302187,a@example.com',
        ]);

        $importer = new DatabaseCsvImporter();
        $importer->import($path);

        foreach (['idx_persons_national_id', 'idx_persons_city_id', 'idx_persons_search', 'idx_persons_email', 'idx_persons_phone', 'idx_persons_governorate_id', 'idx_persons_gov_person', 'ft_persons_full_name'] as $index) {
            $this->assertTrue(Schema::hasIndex('Persons', $index), "Index {$index} should exist after import");
        }

        $fk = DB::selectOne(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Persons' AND CONSTRAINT_NAME = 'persons_cityid_foreign' LIMIT 1"
        );
        $this->assertNotNull($fk);
    }

    public function test_import_handles_multiple_part_files(): void
    {
        $this->seedLookups();

        $part1 = $this->writeCsv('database.csv.00', [
            '633024311,أحمد,محمد,فاطمة,خالد,74,5,94,0534302187,a@example.com',
        ]);
        $part2 = $this->writeCsv('database.csv.01', [
            '633024312,علي,حسن,سمية,يوسف,74,5,94,0534302188,b@example.com',
        ]);

        $importer = new DatabaseCsvImporter();
        $count = $importer->importFiles([$part1, $part2]);

        $this->assertSame(2, $count);
        $this->assertSame(2, DB::table('Persons')->count());

        $this->assertNotNull(DB::table('Persons')->where('NationalID', '633024311')->first());
        $this->assertNotNull(DB::table('Persons')->where('NationalID', '633024312')->first());
    }

    public function test_import_database_csv_command_sync_mode(): void
    {
        $lookups = $this->seedLookups();

        $path = $this->writeCsv('database.csv', [
            '633024311,أحمد,محمد,فاطمة,خالد,74,5,94,0534302187,a@example.com',
        ]);

        $this->artisan('app:import-database-csv', [
            '--path'   => [$path],
            '--sync'   => true,
        ])->assertSuccessful();

        $this->assertSame(1, DB::table('Persons')->count());
        $person = DB::table('Persons')->where('NationalID', '633024311')->first();
        $this->assertSame($lookups['cityId'], $person->CityID);
    }
}