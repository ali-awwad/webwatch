<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Certificate extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'sans' => 'array',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    public function variations(): HasMany
    {
        return $this->hasMany(Variation::class);
    }
    
    public function websites(): HasManyThrough
    {
        return $this->hasManyThrough(Website::class, Variation::class, 'certificate_id', 'id', 'id', 'website_id');
    }
} 