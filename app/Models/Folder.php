<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Folder extends Model
{
    protected $fillable = ['name', 'parent_id'];

    // A folder has many files
    public function files(): HasMany
    {
        return $this->hasMany(ResourceFile::class);
    }

    // A folder belongs to a parent folder
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    // A folder has many subfolders (1 level deep)
    public function subfolders(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    // CRITICAL: Recursively loads all nested subfolders and their files
    public function childrenRecursive(): HasMany
    {
        return $this->subfolders()->with(['childrenRecursive', 'files']);
    }
}