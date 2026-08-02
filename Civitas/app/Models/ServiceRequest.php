<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasUuids;

    protected $table = 'Service_Requests';
    protected $primaryKey = 'RequestID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'RequestID',
        'PersonID',
        'UserID',
        'ServiceTypeID',
        'RequestDate',
        'Status',
    ];

    protected function casts(): array
    {
        return [
            'RequestDate' => 'datetime',
        ];
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'PersonID', 'PersonID');
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'ServiceTypeID', 'ServiceTypeID');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'RequestID', 'RequestID');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'RequestID', 'RequestID');
    }
}
