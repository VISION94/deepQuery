<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Overtrue\LaravelLike\Traits\Liker;

class User extends Model
{
    use HasFactory;
    use Liker;

    protected $fillable = [
        'name',
        'email',
        'image',
    ];

    public function Startup(): hasMany
    {
        return $this->hasMany(Startup::class);
    }
}
