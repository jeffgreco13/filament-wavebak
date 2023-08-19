<?php

namespace Jeffgreco13\FilamentWave\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'wave_customers';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $incrementing = false;
}
