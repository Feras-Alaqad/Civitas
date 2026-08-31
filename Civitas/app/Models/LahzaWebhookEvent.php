<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LahzaWebhookEvent extends Model
{
    protected $table = 'lahza_webhook_events';

    public $timestamps = false;

    protected $fillable = [
        'EventID',
        'EventType',
        'created_at',
    ];
}
