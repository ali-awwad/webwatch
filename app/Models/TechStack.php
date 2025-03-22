<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TechStack extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];
    
    public function websites(): BelongsToMany
    {
        return $this->belongsToMany(Website::class);
    }
} 