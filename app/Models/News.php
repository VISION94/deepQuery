<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'link',
        'date_published',
    ];

    public function Startup()
    {
        return $this->morphToMany(Startup::class, 'newsable');
    }

    public function Investor()
    {
        return $this->morphToMany(Investor::class, 'newsable');
    }
}
