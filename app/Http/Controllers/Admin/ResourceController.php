<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\CarouselImage;
use App\Models\FeaturedVideo;
use App\Models\Folder;
use App\Models\ResourceFile;
use App\Models\ResourceTracking;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ResourceController extends Controller
{
    private const MAX_FILE_KB = 10485760; // 10 GB

    private const MAX_PREVIEW_IMAGE_KB = 5120;

    public function index(): Response
    {
        return Inertia::render('Admin/Resources/Index', [
            'folders' => Folder::whereNull('parent_id')
                ->with(['files', 'childrenRecursive'])
                ->orderBy('name', 'asc')
                ->get(),
            'allFolders' => $this->allFoldersWithPath(),
            'stats' => [
                'total_folders' => Folder::count(),
                'total_files' => ResourceFile::count(),
                'total_users' => User::count(),
                'total_teachers' => User::where('role', 'teacher')->count(),
            ],
            'uploadLimits' => $this->uploadLimits(),
        ]);
    }

    public function announcements(): Response
    {
        return Inertia::render('Admin/Resources/Announcements', [
            'announcements' => Announcement::latest()->get(),
        ]);
    }

    public function carousel(): Response
    {
        return Inertia::render('Admin/Resources/Carousel', [
            'carouselImages' => CarouselImage::latest()->get(),
        ]);
    }

    public function videos(): Response
    {
        return Inertia::render('Admin/Resources/Videos', [
            'featuredVideos' => FeaturedVideo::latest()->get(),
        ]);
    }

    public function users(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::query()
                ->select([
                    'id',
                    'name',
                    'email',
                    'is_admin',
                    'role',
                    'district',
                    'school_name',
                    'email_verified_at',
                    'created_at',
                ])
                ->orderByDesc('is_admin')
                ->orderBy('name')
                ->get(),
            'stats' => [
                'total_users' => User::count(),
                'total_admins' => User::where('is_admin', true)->count(),
                'total_teachers' => User::where('role', 'teacher')->count(),
            ],
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => [...$this->depedEmailRules(), Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(['teacher', 'user', 'admin'])],
            'district' => ['nullable', 'string', 'max:255', Rule::requiredIf($request->input('role') === 'teacher')],
            'school_name' => ['nullable', 'string', 'max:255', Rule::requiredIf($request->input('role') === 'teacher')],
        ]);

        if ($validator->fails()) {
            return to_route('admin.users', [], 303)
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => $validated['password'],
            'role' => $validated['role'],
            'is_admin' => $validated['role'] === 'admin',
            'district' => $validated['district'] ?: null,
            'school_name' => $validated['school_name'] ?: null,
            'email_verified_at' => now(),
        ]);

        return to_route('admin.users', [], 303)
            ->with('success', 'User account created successfully.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => [...$this->depedEmailRules(), Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['teacher', 'user', 'admin'])],
            'district' => ['nullable', 'string', 'max:255', Rule::requiredIf($request->input('role') === 'teacher')],
            'school_name' => ['nullable', 'string', 'max:255', Rule::requiredIf($request->input('role') === 'teacher')],
        ]);

        if ($validator->fails()) {
            return to_route('admin.users', [], 303)
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        $isAdmin = $validated['role'] === 'admin';

        // Keep at least one admin account in the system.
        if ($user->is_admin && ! $isAdmin && User::where('is_admin', true)->count() <= 1) {
            return to_route('admin.users', [], 303)->withErrors([
                'role' => 'At least one admin account is required.',
            ]);
        }

        $user->update([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'role' => $validated['role'],
            'is_admin' => $isAdmin,
            'district' => $validated['district'] ?: null,
            'school_name' => $validated['school_name'] ?: null,
        ]);

        return to_route('admin.users', [], 303)
            ->with('success', 'User profile updated successfully.');
    }

    public function updateUserPassword(Request $request, User $user): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return to_route('admin.users', [], 303)
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        $user->update([
            'password' => $validated['password'],
        ]);

        return to_route('admin.users', [], 303)
            ->with('success', 'User password updated successfully.');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return to_route('admin.users', [], 303)->withErrors([
                'user' => 'You cannot delete your own account while logged in.',
            ]);
        }

        if ($user->is_admin && User::where('is_admin', true)->count() <= 1) {
            return to_route('admin.users', [], 303)->withErrors([
                'user' => 'At least one admin account is required.',
            ]);
        }

        $user->delete();

        return to_route('admin.users', [], 303)
            ->with('success', 'User deleted successfully.');
    }

    public function storeFolder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'description' => 'nullable|string|max:2000',
        ]);

        Folder::create($validated);

        return back()->with('success', 'Folder created successfully.');
    }

    public function storeFile(Request $request): RedirectResponse
    {
        if (is_array($request->file('file'))) {
            return back()->withErrors([
                'file' => 'Please upload only one file at a time.',
            ])->withInput();
        }

        if (is_array($request->file('preview_image'))) {
            return back()->withErrors([
                'preview_image' => 'Please upload only one preview image at a time.',
            ])->withInput();
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:'.self::MAX_FILE_KB,
            'folder_id' => 'required|exists:folders,id',
            'preview_image' => 'nullable|image|max:'.self::MAX_PREVIEW_IMAGE_KB,
        ]);

        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store('resources', 'public');
        $extension = $uploadedFile->getClientOriginalExtension();

        $previewImagePath = null;
        if ($request->hasFile('preview_image')) {
            $previewImagePath = $request->file('preview_image')->store('resources/previews', 'public');
        }

        ResourceFile::create([
            'folder_id' => $validated['folder_id'],
            'title' => $validated['title'],
            'file_path' => $path,
            'preview_image_path' => $previewImagePath,
            'file_type' => $extension,
            'is_locked' => false,
        ]);

        return back()->with('success', 'File uploaded successfully.');
    }

    public function destroyFolder(Folder $folder): RedirectResponse
    {
        $folder->delete();

        return to_route('admin.resources', [], 303)
            ->with('success', 'Folder deleted successfully.');
    }

    public function destroyFile(ResourceFile $file): RedirectResponse
    {
        if ($file->file_path) {
            Storage::disk('public')->delete($file->file_path);
        }

        if ($file->preview_image_path) {
            Storage::disk('public')->delete($file->preview_image_path);
        }

        $file->delete();

        return to_route('admin.resources', [], 303)
            ->with('success', 'File deleted successfully.');
    }

    public function toggleFolderLock(Folder $folder): RedirectResponse
    {
        $unlocking = $folder->is_locked;

        $folder->update([
            'is_locked' => ! $folder->is_locked,
            'unlock_starts_at' => $unlocking ? null : $folder->unlock_starts_at,
            'unlock_ends_at' => $unlocking ? null : $folder->unlock_ends_at,
        ]);

        return back(303);
    }

    public function toggleFileLock(ResourceFile $file): RedirectResponse
    {
        $unlocking = $file->is_locked;

        $file->update([
            'is_locked' => ! $file->is_locked,
            'unlock_starts_at' => $unlocking ? null : $file->unlock_starts_at,
            'unlock_ends_at' => $unlocking ? null : $file->unlock_ends_at,
        ]);

        return back(303);
    }

    public function setFolderUnlockWindow(Request $request, Folder $folder): RedirectResponse
    {
        $message = $this->applyUnlockWindow($request, $folder, 'folder');

        return back(303)->with('success', $message);
    }

    public function setFileUnlockWindow(Request $request, ResourceFile $file): RedirectResponse
    {
        $message = $this->applyUnlockWindow($request, $file, 'file');

        return back(303)->with('success', $message);
    }

    public function storeCarousel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $imagePath = $request->file('image')->store('carousel', 'public');

        CarouselImage::create([
            'title' => $validated['title'],
            'image_path' => $imagePath,
        ]);

        return back()->with('success', 'Carousel image added successfully.');
    }

    public function updateCarousel(Request $request, CarouselImage $carousel): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $imagePath = $carousel->image_path;

        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request->file('image')->store('carousel', 'public');
        }

        $carousel->update([
            'title' => $validated['title'],
            'image_path' => $imagePath,
        ]);

        return back()->with('success', 'Carousel image updated successfully.');
    }

    public function destroyCarousel(CarouselImage $carousel): RedirectResponse
    {
        if ($carousel->image_path) {
            Storage::disk('public')->delete($carousel->image_path);
        }

        $carousel->delete();

        return to_route('admin.carousel', [], 303)
            ->with('success', 'Carousel image deleted successfully.');
    }

    public function storeVideo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'youtube_link' => ['required', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        FeaturedVideo::create($validated);

        return back()->with('success', 'Featured video added successfully.');
    }

    public function updateVideo(Request $request, FeaturedVideo $video): RedirectResponse
    {
        $validated = $request->validate([
            'youtube_link' => ['required', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $video->update($validated);

        return back()->with('success', 'Featured video updated successfully.');
    }

    public function destroyVideo(FeaturedVideo $video): RedirectResponse
    {
        $video->delete();

        return to_route('admin.videos', [], 303)
            ->with('success', 'Featured video deleted successfully.');
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Announcement published successfully.');
    }

    public function updateAnnouncement(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Announcement updated successfully.');
    }

    public function destroyAnnouncement(Announcement $announcement): RedirectResponse
    {
        if ($announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
        }

        $announcement->delete();

        return to_route('admin.announcements', [], 303)
            ->with('success', 'Announcement deleted successfully.');
    }

    public function analytics(): Response
    {
        $emptyAnalyticsPayload = [
            'stats' => [
                'total_downloads' => 0,
                'total_file_opens' => 0,
                'total_folder_opens' => 0,
                'active_users' => 0,
                'storage_used' => '0 MB',
            ],
            'districtStats' => [],
            'schoolStats' => [],
            'topFolders' => [],
            'topFiles' => [],
            'recentActivity' => [],
            'usersByRole' => [],
            'loadError' => null,
        ];

        if (
            ! Schema::hasTable('resource_trackings') ||
            ! Schema::hasTable('users') ||
            ! Schema::hasTable('resource_files')
        ) {
            return Inertia::render('Admin/Resources/Analytics', $emptyAnalyticsPayload);
        }

        $hasRoleColumn = Schema::hasColumn('users', 'role');
        $hasDistrictColumn = Schema::hasColumn('users', 'district');
        $hasSchoolColumn = Schema::hasColumn('users', 'school_name');

        try {
            $totalDownloads = ResourceTracking::where('action', 'file_downloaded')->count();
            $totalFileOpens = ResourceTracking::where('action', 'file_opened')->count();
            $totalFolderOpens = ResourceTracking::where('action', 'folder_opened')->count();
            $activeUsers = ResourceTracking::query()->distinct('user_id')->count('user_id');

            $storageUsedBytes = ResourceFile::query()
                ->pluck('file_path')
                ->sum(fn ($path) => $path && Storage::disk('public')->exists($path)
                    ? Storage::disk('public')->size($path)
                    : 0);

            $districtExpr = $hasDistrictColumn
                ? "COALESCE(NULLIF(users.district, ''), 'Unknown District')"
                : "'Unknown District'";
            $schoolExpr = $hasSchoolColumn
                ? "COALESCE(NULLIF(users.school_name, ''), 'Unknown School')"
                : "'Unknown School'";

            $districtStats = ResourceTracking::query()
                ->leftJoin('users', 'users.id', '=', 'resource_trackings.user_id')
                ->selectRaw("{$districtExpr} as district")
                ->selectRaw('COUNT(*) as total_actions')
                ->selectRaw("SUM(CASE WHEN resource_trackings.action = 'folder_opened' THEN 1 ELSE 0 END) as folders_opened")
                ->selectRaw("SUM(CASE WHEN resource_trackings.action = 'file_opened' THEN 1 ELSE 0 END) as files_opened")
                ->selectRaw("SUM(CASE WHEN resource_trackings.action = 'file_downloaded' THEN 1 ELSE 0 END) as files_downloaded")
                ->groupBy(DB::raw($districtExpr))
                ->orderByDesc('total_actions')
                ->get();

            $schoolStats = ResourceTracking::query()
                ->leftJoin('users', 'users.id', '=', 'resource_trackings.user_id')
                ->selectRaw("{$districtExpr} as district")
                ->selectRaw("{$schoolExpr} as school_name")
                ->selectRaw('COUNT(*) as total_actions')
                ->selectRaw("SUM(CASE WHEN resource_trackings.action = 'folder_opened' THEN 1 ELSE 0 END) as folders_opened")
                ->selectRaw("SUM(CASE WHEN resource_trackings.action = 'file_opened' THEN 1 ELSE 0 END) as files_opened")
                ->selectRaw("SUM(CASE WHEN resource_trackings.action = 'file_downloaded' THEN 1 ELSE 0 END) as files_downloaded")
                ->groupBy(DB::raw($districtExpr), DB::raw($schoolExpr))
                ->orderByDesc('total_actions')
                ->get();

            $topFolders = ResourceTracking::query()
                ->where('action', 'folder_opened')
                ->whereNotNull('folder_id')
                ->select('folder_id')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('folder_id')
                ->orderByDesc('total')
                ->with('folder:id,name')
                ->limit(10)
                ->get()
                ->map(fn ($row) => [
                    'folder_name' => $row->folder?->name ?? 'Unknown Folder',
                    'total' => (int) $row->total,
                ]);

            $topFiles = ResourceTracking::query()
                ->whereIn('action', ['file_opened', 'file_downloaded'])
                ->whereNotNull('resource_file_id')
                ->select('resource_file_id')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('resource_file_id')
                ->orderByDesc('total')
                ->with('file:id,title')
                ->limit(10)
                ->get()
                ->map(fn ($row) => [
                    'file_title' => $row->file?->title ?? 'Unknown File',
                    'total' => (int) $row->total,
                ]);

            $userSelectColumns = ['id', 'name', 'email'];
            if ($hasDistrictColumn) {
                $userSelectColumns[] = 'district';
            }
            if ($hasSchoolColumn) {
                $userSelectColumns[] = 'school_name';
            }

            $recentActivity = ResourceTracking::query()
                ->with([
                    'user:'.implode(',', $userSelectColumns),
                    'folder:id,name',
                    'file:id,title',
                ])
                ->latest()
                ->limit(50)
                ->get()
                ->map(function (ResourceTracking $tracking) use ($hasDistrictColumn, $hasSchoolColumn) {
                    return [
                        'id' => $tracking->id,
                        'action' => $this->readableAction($tracking->action),
                        'district' => $hasDistrictColumn
                            ? ($tracking->user?->district ?: 'Unknown District')
                            : 'Unknown District',
                        'school_name' => $hasSchoolColumn
                            ? ($tracking->user?->school_name ?: 'Unknown School')
                            : 'Unknown School',
                        'user_name' => $tracking->user?->name ?: 'Unknown User',
                        'user_email' => $tracking->user?->email ?: 'N/A',
                        'target' => $tracking->folder?->name
                            ?? $tracking->file?->title
                            ?? 'N/A',
                        'time' => $tracking->created_at?->toDateTimeString(),
                    ];
                });

            $usersByRole = User::query()
                ->select(['role', 'is_admin'])
                ->get()
                ->map(function (User $user) use ($hasRoleColumn) {
                    $role = $hasRoleColumn ? strtolower(trim((string) $user->role)) : '';

                    if ($role === '') {
                        $role = $user->is_admin ? 'admin' : 'user';
                    }

                    return $role;
                })
                ->countBy()
                ->map(fn ($total, $role) => [
                    'role' => $role,
                    'total' => (int) $total,
                ])
                ->sortByDesc('total')
                ->values();

            return Inertia::render('Admin/Resources/Analytics', [
                'stats' => [
                    'total_downloads' => $totalDownloads,
                    'total_file_opens' => $totalFileOpens,
                    'total_folder_opens' => $totalFolderOpens,
                    'active_users' => $activeUsers,
                    'storage_used' => round($storageUsedBytes / (1024 * 1024), 2).' MB',
                ],
                'districtStats' => $districtStats,
                'schoolStats' => $schoolStats,
                'topFolders' => $topFolders,
                'topFiles' => $topFiles,
                'recentActivity' => $recentActivity,
                'usersByRole' => $usersByRole,
                'loadError' => null,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to load analytics dashboard.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $emptyAnalyticsPayload['loadError'] = 'Analytics data could not be fully loaded. Please check tracking tables/migrations.';

            return Inertia::render('Admin/Resources/Analytics', $emptyAnalyticsPayload);
        }
    }

    private function allFoldersWithPath()
    {
        return Folder::with('parent')
            ->get()
            ->map(function (Folder $folder) {
                $pathParts = [$folder->name];
                $parent = $folder->parent;

                while ($parent) {
                    array_unshift($pathParts, $parent->name);
                    $parent = $parent->parent;
                }

                $folder->full_path = implode(' > ', $pathParts);

                return $folder;
            })
            ->sortBy('full_path')
            ->values();
    }

    private function depedEmailRules(): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            'regex:/@deped\.gov\.ph$/i',
        ];
    }

    private function applyUnlockWindow(Request $request, Folder|ResourceFile $resource, string $type): string
    {
        if ($request->boolean('clear')) {
            $resource->update([
                'unlock_starts_at' => null,
                'unlock_ends_at' => null,
            ]);

            return ucfirst($type).' unlock schedule cleared.';
        }

        $validated = $request->validate([
            'start_at' => ['required', 'date'],
            'duration_value' => ['required', 'integer', 'min:1', 'max:365'],
            'duration_unit' => ['required', Rule::in(['days', 'weeks', 'months'])],
        ]);

        $startAt = Carbon::parse($validated['start_at']);
        $durationValue = (int) $validated['duration_value'];
        $durationUnit = $validated['duration_unit'];

        $endAt = match ($durationUnit) {
            'days' => $startAt->copy()->addDays($durationValue),
            'weeks' => $startAt->copy()->addWeeks($durationValue),
            'months' => $startAt->copy()->addMonths($durationValue),
            default => $startAt->copy()->addDays($durationValue),
        };

        $resource->update([
            'is_locked' => true,
            'unlock_starts_at' => $startAt,
            'unlock_ends_at' => $endAt,
        ]);

        return ucfirst($type).' unlock schedule saved.';
    }

    private function readableAction(string $action): string
    {
        return match ($action) {
            'folder_opened' => 'Opened Folder',
            'file_opened' => 'Opened File',
            'file_downloaded' => 'Downloaded File',
            default => ucfirst(str_replace('_', ' ', $action)),
        };
    }

    private function uploadLimits(): array
    {
        $phpUploadMaxBytes = $this->normalizeIniSizeToBytes((string) ini_get('upload_max_filesize'));
        $phpPostMaxBytes = $this->normalizeIniSizeToBytes((string) ini_get('post_max_size'));

        $appFileMaxBytes = self::MAX_FILE_KB * 1024;
        $appPreviewMaxBytes = self::MAX_PREVIEW_IMAGE_KB * 1024;

        $effectiveFileMaxBytes = min($appFileMaxBytes, $phpUploadMaxBytes, $phpPostMaxBytes);
        $effectivePreviewMaxBytes = min($appPreviewMaxBytes, $phpUploadMaxBytes, $phpPostMaxBytes);

        return [
            'max_file_bytes' => $effectiveFileMaxBytes,
            'max_preview_bytes' => $effectivePreviewMaxBytes,
            'max_file_label' => $this->formatBytes($effectiveFileMaxBytes),
            'max_preview_label' => $this->formatBytes($effectivePreviewMaxBytes),
            'php_upload_max_filesize' => (string) ini_get('upload_max_filesize'),
            'php_post_max_size' => (string) ini_get('post_max_size'),
        ];
    }

    private function normalizeIniSizeToBytes(string $size): int
    {
        $value = trim($size);
        if ($value === '') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;
        if (! is_numeric($value)) {
            $number = (float) substr($value, 0, -1);
        }

        $multiplier = match ($unit) {
            'g' => 1024 * 1024 * 1024,
            'm' => 1024 * 1024,
            'k' => 1024,
            default => 1,
        };

        $bytes = (int) round($number * $multiplier);

        return $bytes > 0 ? $bytes : PHP_INT_MAX;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return round($bytes / (1024 * 1024 * 1024), 1).' GB';
        }

        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 1).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }
}
