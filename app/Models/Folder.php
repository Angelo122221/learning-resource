<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Folder extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'is_locked',
        'description',
        'unlock_starts_at',
        'unlock_ends_at',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'unlock_starts_at' => 'datetime',
        'unlock_ends_at' => 'datetime',
    ];

    protected $appends = [
        'is_effectively_locked',
        'is_temporarily_unlocked',
    ];

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

    public function resourceTrackings(): HasMany
    {
        return $this->hasMany(ResourceTracking::class);
    }

    public function isWithinUnlockWindow(?Carbon $referenceTime = null): bool
    {
        if (! $this->unlock_starts_at || ! $this->unlock_ends_at) {
            return false;
        }

        $time = $referenceTime ?? now();

        return $time->greaterThanOrEqualTo($this->unlock_starts_at)
            && $time->lessThanOrEqualTo($this->unlock_ends_at);
    }

    public function isCurrentlyLocked(?Carbon $referenceTime = null): bool
    {
        if (! $this->is_locked) {
            return false;
        }

        return ! $this->isWithinUnlockWindow($referenceTime);
    }

    protected function isEffectivelyLocked(): Attribute
    {
        return Attribute::get(fn () => $this->isCurrentlyLocked());
    }

    protected function isTemporarilyUnlocked(): Attribute
    {
        return Attribute::get(fn () => $this->is_locked && $this->isWithinUnlockWindow());
    }
}
