<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'id_app',
        'pass_app',
        'transmission_date_time',
        'bank_id',
        'terminal_id',
        'briva_number',
        'payment_amount',
        'transaction_id'
    ];
}
