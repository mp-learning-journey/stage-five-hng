<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transcription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'position',
        'start',
        'end',
        'description',
    ];

    public function recording(): BelongsTo{
        return $this->belongsTo(Recording::class);
    }
}
