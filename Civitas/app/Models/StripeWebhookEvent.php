<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeWebhookEvent extends Model
{
    protected $table = 'Stripe_Webhook_Events';
    public $timestamps = false;

    protected $fillable = [
        'EventID',
        'EventType',
        'created_at',
    ];
}
