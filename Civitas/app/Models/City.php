<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasUuids;

    protected $primaryKey = 'CityID';

    protected $fillable = [
        'CityName',
        'GovernorateID',
    ];

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'GovernorateID');
    }

    public function persons(): HasMany
    {
        return $this->hasMany(Person::class, 'CityID');
    }
}
