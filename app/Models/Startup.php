<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelLike\Traits\Likeable;

class Startup extends Model
{
    use HasFactory;
    use Likeable;

    protected $fillable = [
        'user_id',
        'name',
        'tagline',
        'logo',
    ];

    public function News()
    {
        return $this->morphToMany(News::class, 'newsable');
    }

    public function Milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function Funding()
    {
        return $this->hasMany(Funding::class);
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
