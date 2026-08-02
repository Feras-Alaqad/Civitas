<?php

namespace App\Models;

use App\Services\CitizensCacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Person extends Model
{
    use Searchable;
    protected $table = 'Persons';
    protected $primaryKey = 'PersonID';
    protected $keyType = 'string';
    public $incrementing = false;

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'GovernorateID', 'GovernorateID');
    }

    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class, 'NationalityID', 'NationalityID');
    }

    public function getGovernorateNameAttribute(): ?string
    {
        return $this->governorate?->GovernorateName;
    }

    public function getNationalityNameAttribute(): ?string
    {
        return $this->nationality?->NationalityName;
    }

    protected static function booted(): void
    {
        static::saving(function (Person $person) {
            $person->FullNameSearch = self::normalizeName($person->FullName ?? '');
        });

        static::saved(function (Person $person) {
            if ($person->isDirty('FullName')) {
                $person->FullNameSearch = self::normalizeName($person->FullName);
                $person->saveQuietly();
            }
            CitizensCacheService::flushCitizensCache();
        });

        static::deleted(function (Person $person) {
            CitizensCacheService::flushCitizensCache();
        });
    }

    public static function normalizeName(string $value): string
    {
        $value = str_replace(
            ['أ', 'إ', 'آ', 'ؤ', 'ئ', 'ة', 'ى', 'ﷲ'],
            ['ا', 'ا', 'ا', 'و', 'ي', 'ه', 'ي', 'الله'],
            $value
        );

        $value = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06DC}\x{06DF}-\x{06E4}\x{06E7}\x{06E8}\x{06EA}-\x{06ED}]/u', '', $value);

        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    public function toSearchableArray(): array
    {
        return [
            'PersonID'    => $this->PersonID,
            'FullName'    => $this->FullName,
            'NationalID'  => $this->NationalID,
            'Gender'      => $this->Gender,
            'DateOfBirth' => $this->DateOfBirth,
            'Phone'       => $this->Phone,
            'Email'       => $this->Email,
            'Address'     => $this->Address,
        ];
    }

    public function searchableAs(): string
    {
        return 'persons_index';
    }

    public function getScoutKey()
    {
        return $this->PersonID;
    }

    public function getScoutKeyName()
    {
        return 'PersonID';
    }
}
