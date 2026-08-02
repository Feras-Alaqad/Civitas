<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasUuids;

    protected $table = 'Departments';
    protected $primaryKey = 'DepartmentID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'DepartmentName',
        'Description',
    ];
}
