<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NowPaymentsWebhookEvent extends Model
{
    protected $table = 'nowpayments_webhook_events';

    protected $fillable = [
        'PaymentID',
        'OrderID',
        'Payload',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'Payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
