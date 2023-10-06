<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Parser;

class ClientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'yandex_map_url',
        'status'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(ProfileFeedback::class);
    }

    public static function boot()
    { 
        parent::boot(); 

        static::created(function($profile)  // Функция обработчика в качестве аргумента принимает объект модели
        {
            $profile->feedbacks()->create([
                'feedback_count' => Parser::parserFeedbackCount($profile->yandex_map_url),
                'updated_at' => now()
            ]);
        });
    } 
}
