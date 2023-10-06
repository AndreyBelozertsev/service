<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportParam extends Model
{
    use HasFactory;

    protected $fillable = [
        'address',
        'service',
        'param',
        'value',
        'type'
    ];


    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
    
}
