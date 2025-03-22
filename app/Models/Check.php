<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Check extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'status' => Status::class,
    ];

    public function variation(): BelongsTo
    {
        return $this->belongsTo(Variation::class);
    }
    
    public function website()
    {
        return $this->variation->website();
    }
} 