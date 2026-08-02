<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasUuids;

    protected $table = 'Service_Types';
    protected $primaryKey = 'ServiceTypeID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ServiceName',
        'DepartmentID',
        'Fees',
        'RequiredDocuments',
    ];

    protected function casts(): array
    {
        return [
            'Fees' => 'decimal:2',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'DepartmentID', 'DepartmentID');
    }
}
