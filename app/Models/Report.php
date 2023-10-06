<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_start',
        'date_end',
        'client_id'
    ];

    /**
     * Метод «booted» модели.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleted(function ($report) {
        
            if( Storage::disk('report')->exists($report->id) ){
                Storage::disk('report')->deleteDirectory($report->id);
            }
            
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function params(): HasMany
    {
        return $this->hasMany(ReportParam::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(ReportDevicesParam::class);
    }

    public function deviceManufactures(): HasMany
    {
        return $this->hasMany(ReportDevicesManufactureParam::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(ReachGoal::class);
    }

    public function goalDevices(): HasMany
    {
        return $this->hasMany(ReachGoalDevices::class);
    }
}
