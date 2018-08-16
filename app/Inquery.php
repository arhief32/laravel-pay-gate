<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inquery extends Model
{
    protected $table = 'inqueries';

    protected $fillable = [
        'id_app',
        'pass_app',
        'transmission_date_time',
        'bank_id',
        'terminal_id',
        'briva_number'
    ];
}
