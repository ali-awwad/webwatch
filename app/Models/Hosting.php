<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Hosting extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function variations(): HasMany
    {
        return $this->hasMany(Variation::class);
    }

    public function websites(): HasManyThrough
    {
        return $this->hasManyThrough(Website::class, Variation::class, 'hosting_id', 'id', 'id', 'website_id');
    }
}
