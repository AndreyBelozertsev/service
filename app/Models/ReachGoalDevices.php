<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReachGoalDevices extends Model
{
    use HasFactory;

    protected $fillable = [
        'param',
        'value',
        'source'
    ];


    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
