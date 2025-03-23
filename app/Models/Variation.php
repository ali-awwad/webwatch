<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Builder;
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
        'status' => Status::class,
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

    public function hosting(): BelongsTo
    {
        return $this->belongsTo(Hosting::class);
    }

    // scope main with status in array
    public function scopeMainWithStatus(Builder $query, array|string $statuses): Builder
    {
        $query->where('is_main', true);

        if (is_array($statuses)) {
            $query->whereIn('status', $statuses);
        } else {
            $query->where('status', $statuses);
        }

        return $query;
    }
} 