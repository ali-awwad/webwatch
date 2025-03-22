<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Division extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function websites(): HasManyThrough
    {
        return $this->hasManyThrough(Website::class, Company::class);
    }
} 