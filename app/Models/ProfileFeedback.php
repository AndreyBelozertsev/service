<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfileFeedback extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'feedback_count',
        'client_profile_id'
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

}
