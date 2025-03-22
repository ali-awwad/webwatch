<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Website extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'last_status' => Status::class,
        'is_waf_enabled' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }

    public function checks(): HasMany
    {
        return $this->hasMany(Check::class);
    }

    public function hosting(): BelongsTo
    {
        return $this->belongsTo(Hosting::class);
    }
    
    public function developerTeam(): BelongsTo
    {
        return $this->belongsTo(DeveloperTeam::class);
    }
    
    public function techStacks(): BelongsToMany
    {
        return $this->belongsToMany(TechStack::class);
    }
} 