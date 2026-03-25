<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceTracking extends Model
{
    protected $fillable = [
        'user_id',
        'folder_id',
        'resource_file_id',
        'action',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(ResourceFile::class, 'resource_file_id');
    }
}
