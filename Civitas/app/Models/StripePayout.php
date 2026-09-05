<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripePayout extends Model
{
    protected $table = 'Stripe_Payouts';

    protected $primaryKey = 'PayoutID';

    protected $fillable = [
        'StripePayoutID',
        'Amount',
        'Currency',
        'Status',
        'Destination',
        'DestinationName',
        'FailureReason',
        'FailureCode',
        'ArrivalDate',
        'Description',
        'RequestedBy',
    ];

    protected $casts = [
        'ArrivalDate' => 'datetime',
    ];
}
