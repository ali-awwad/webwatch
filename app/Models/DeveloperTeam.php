<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeveloperTeam extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];
    
    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }
} 