<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mapping extends Model
{
    protected $connection = 'paygate';
    protected $table = 'mappings';

    protected $fillable = [
        'corp_code',
        'corp_name',
        'url'
    ];
}