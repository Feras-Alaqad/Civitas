<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Governorate extends Model
{
    use HasUuids;

    protected $table = 'Governorates';
    protected $primaryKey = 'GovernorateID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'GovernorateName',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'GovernorateID');
    }
}
