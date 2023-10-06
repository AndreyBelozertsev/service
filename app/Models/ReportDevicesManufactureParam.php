<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportDevicesManufactureParam extends Model
{
    use HasFactory;

    protected $fillable = [
        'address',
        'param',
        'value',
    ];


    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
