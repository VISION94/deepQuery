<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'startup_id',
        'title',
        'link',
        'date_published',
    ];

    public function Startup()
    {
        return $this->belongsTo(Startup::class);
    }
}
