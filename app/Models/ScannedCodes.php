<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ScannedCodes extends Model
{
    protected $table = 'scanned_codes';

    protected $fillable = [
        'scanned_codes',
        'status'
    ];
}
