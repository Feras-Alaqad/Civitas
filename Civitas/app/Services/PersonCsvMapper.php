<?php

namespace App\Services;

use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PersonCsvMapper
{
    private ?array $cityMap = null;

    private ?array $governorateMap = null;

    private ?array $nationalityMap = null;

    public function readHeaders($handle): ?array
    {
        $headers = fgetcsv($handle, 0, ',', '"', '');

        if (!$headers) {
            return null;
        }

        return array_map(
            static fn ($header) => trim(preg_replace('/^\xEF\xBB\xBF/', '', $header)),
            $headers
        );
    }

    public function buildRow(array $record): array
    {
        $fullName = trim(implode(' ', [
            $record['FirstName'] ?? '',
            $record['FatherName'] ?? '',
            $record['MotherName'] ?? '',
            $record['FamilyName'] ?? '',
        ]));

        if ($fullName === '') {
            $fullName = trim((string) ($record['FullName'] ?? ''));
        }

        return [
            'PersonID'       => (string) Str::uuid(),
            'FullName'       => $fullName,
            'FullNameSearch' => Person::normalizeName($fullName),
            'DateOfBirth'    => $this->mapDate($record['DateOfBirth'] ?? null),
            'NationalID'     => $this->toNullable($record['ID'] ?? null),
            'Address'        => $this->toNullable($record['Address'] ?? null),
            'Gender'         => $this->toNullable($record['Gender'] ?? null),
            'NationalityID'  => $this->nationalityIdForCode($this->toNullable($record['NationalityID'] ?? null)),
            'CityID'         => $this->cityIdForCode($this->toNullable($record['CityID'] ?? null)),
            'GovernorateID'  => $this->governorateIdForCode($this->toNullable($record['GovernorateID'] ?? null)),
            'Phone'          => $this->toNullable($record['PhoneNumber'] ?? null),
            'Email'          => $this->toNullable($record['Email'] ?? null),
            'created_at'     => now(),
            'updated_at'     => now(),
        ];
    }

    public function cityIdForCode(string|int|null $code): ?string
    {
        return $this->lookup($this->load('CityID', 'Cities', 'cityMap'), $code);
    }

    public function governorateIdForCode(string|int|null $code): ?string
    {
        return $this->lookup($this->load('GovernorateID', 'Governorates', 'governorateMap'), $code);
    }

    public function nationalityIdForCode(string|int|null $code): ?string
    {
        return $this->lookup($this->load('NationalityID', 'Nationalities', 'nationalityMap'), $code);
    }

    public function refreshLookups(): void
    {
        $this->cityMap = null;
        $this->governorateMap = null;
        $this->nationalityMap = null;
    }

    private function lookup(array $map, string|int|null $code): ?string
    {
        if ($code === null || $code === '' || !is_numeric($code)) {
            return null;
        }

        return $map[(string) (int) $code] ?? null;
    }

    private function load(string $key, string $table, string $property): array
    {
        if ($this->{$property} !== null) {
            return $this->{$property};
        }

        $rows = DB::table($table)
            ->whereNotNull('source_code')
            ->pluck($key, 'source_code');

        $map = [];

        foreach ($rows as $code => $id) {
            $map[(string) (int) $code] = (string) $id;
        }

        return $this->{$property} = $map;
    }

    private function mapDate(mixed $raw): ?string
    {
        $value = $this->toNullable($raw);

        if ($value === null) {
            return null;
        }

        try {
            $date = Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }

        $year = (int) $date->format('Y');

        if ($year < 1900 || $year > ((int) now()->format('Y') + 1)) {
            return null;
        }

        return $date->format('Y-m-d');
    }

    private function toNullable(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}