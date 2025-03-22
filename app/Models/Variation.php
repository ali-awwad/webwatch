<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variation extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'is_main' => 'boolean',
    ];
    
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
    
    public function checks(): HasMany
    {
        return $this->hasMany(Check::class);
    }
    
    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }
} 