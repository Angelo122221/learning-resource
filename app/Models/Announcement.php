<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'image_path',
    ];

    public function userStates(): HasMany
    {
        return $this->hasMany(AnnouncementUserState::class);
    }
}
