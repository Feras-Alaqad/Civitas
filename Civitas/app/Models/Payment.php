<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasUuids;

    protected $table = 'Payments';
    protected $primaryKey = 'PaymentID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'RequestID',
        'Amount',
        'PaymentDate',
        'ReceiptNumber',
        'StripePaymentIntentID',
        'Currency',
        'Status',
        'Metadata',
        'PaidAt',
        'FailureReason',
    ];

    protected function casts(): array
    {
        return [
            'Amount' => 'decimal:2',
            'PaymentDate' => 'datetime',
            'PaidAt' => 'datetime',
            'Metadata' => 'array',
        ];
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'RequestID', 'RequestID');
    }
}
