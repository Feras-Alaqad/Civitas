<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Nationality extends Model
{
    use HasUuids;

    protected $table = 'Nationalities';
    protected $primaryKey = 'NationalityID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'NationalityName',
    ];
}
