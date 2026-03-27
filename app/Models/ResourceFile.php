<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceFile extends Model
{
    // The $fillable array tells Laravel exactly which columns are allowed to be saved.
    // 'file_type' and 'is_locked' must be here!
    protected $fillable = [
        'folder_id',
        'title',
        'file_path',
        'preview_image_path',
        'file_type',
        'is_locked',
    ];

    // Automatically converts 0/1 to false/true in Vue
    protected $casts = [
        'is_locked' => 'boolean',
    ];

    // A file belongs to a specific folder
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function resourceTrackings(): HasMany
    {
        return $this->hasMany(ResourceTracking::class, 'resource_file_id');
    }
}
