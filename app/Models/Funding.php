<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funding extends Model
{
    use HasFactory;

    protected $fillable = [
        'startup_id',
        'round',
        'amount',
        'date_announced',
    ];

    public function Startup()
    {
        return $this->belongsTo(Startup::class);
    }

    public function Investor()
    {
        return $this->morphToMany(Investor::class, 'funding_investor');
    }
}
