<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class funding_investor extends Model
{
    use HasFactory;

    protected $fillable = [
        'investor_id',
        'funding_id',
    ];
}
