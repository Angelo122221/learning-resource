<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceFile extends Model
{
    protected $fillable = [
        'folder_id', 
        'title', 
        'file_path', 
        'file_type', 
        'is_locked', 
        'opens_at'
    ];

    // This ensures Laravel treats the date and checkbox correctly
    protected $casts = [
        'is_locked' => 'boolean',
        'opens_at' => 'datetime',
    ];

    /**
     * Get the folder that owns the file.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }
}