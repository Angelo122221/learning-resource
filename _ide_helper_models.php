<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int|null $parent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ResourceFile> $files
 * @property-read int|null $files_count
 * @property-read Folder|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Folder> $subfolders
 * @property-read int|null $subfolders_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Folder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Folder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Folder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Folder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Folder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Folder whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Folder whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Folder whereUpdatedAt($value)
 */
	class Folder extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $folder_id
 * @property string $title
 * @property string $file_path
 * @property string $file_type
 * @property bool $is_locked
 * @property \Illuminate\Support\Carbon|null $opens_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Folder $folder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceFile whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceFile whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceFile whereFolderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceFile whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceFile whereOpensAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceFile whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceFile whereUpdatedAt($value)
 */
	class ResourceFile extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property bool $is_admin
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

