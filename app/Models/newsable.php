<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class newsable extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_id',
        'newsable_id',
        'newsable_type',
    ];
}
