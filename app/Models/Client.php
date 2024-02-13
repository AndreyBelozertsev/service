<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'counter_number',
        'type'
    ];


    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function profiles()
    {
        return $this->hasMany(ClientProfile::class);
    }

    //Manager
    public function main_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'main_user_id');
    }

    public function feedback_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'feedback_user_id');
    }

    public function content_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'content_user_id');
    }

    public function control_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'control_user_id');
    }
}
