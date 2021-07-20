<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelLike\Traits\Likeable;

class Investor extends Model
{
    use HasFactory;
    use Likeable;

    protected $fillable = [
        'name',
        'email',
        'image',
    ];

    public function News()
    {
        return $this->morphToMany(News::class, 'newsable');
    }

    public function Funding()
    {
        return $this->morphToMany(Funding::class, 'funding_investor');
    }
}
