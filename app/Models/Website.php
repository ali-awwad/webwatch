<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Website extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'is_waf_enabled' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'company_id', 'id');
    }

    public function checks(): HasManyThrough
    {
        return $this->hasManyThrough(Check::class, Variation::class);
    }

    public function developerTeam(): BelongsTo
    {
        return $this->belongsTo(DeveloperTeam::class);
    }

    public function variations(): HasMany
    {
        return $this->hasMany(Variation::class);
    }

    // Certificates (Variation has certificate_id and website_id)
    public function certificates(): HasManyThrough
    {
        return $this->hasManyThrough(Certificate::class, Variation::class, 'website_id', 'id', 'id', 'certificate_id')->distinct();
    }

    // Hostings (Variation has hosting_id and website_id)
    public function hostings(): HasManyThrough
    {
        return $this->hasManyThrough(Hosting::class, Variation::class, 'website_id', 'id', 'id', 'hosting_id')->distinct();
    }


    public function techStacks(): BelongsToMany
    {
        return $this->belongsToMany(TechStack::class);
    }
}
