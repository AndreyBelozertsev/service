<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReachGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'param',
        'value',
        'type'
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
