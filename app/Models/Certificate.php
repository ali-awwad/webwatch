<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Certificate extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'sans' => 'array',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }
} 