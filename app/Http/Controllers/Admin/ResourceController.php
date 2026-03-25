<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarouselImage;
use App\Models\FeaturedVideo;
use App\Models\Folder;
use App\Models\ResourceFile;
use App\Models\ResourceTracking;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ResourceController extends Controller
{
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
            'carouselImages' => CarouselImage::latest()->get(),
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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [...$this->depedEmailRules(), Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(['teacher', 'user', 'admin'])],
            'district' => ['nullable', 'string', 'max:255'],
            'school_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['role'] === 'teacher') {
            $request->validate([
                'district' => ['required', 'string', 'max:255'],
                'school_name' => ['required', 'string', 'max:255'],
            ]);
        }

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

        return back()->with('success', 'User account created successfully.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [...$this->depedEmailRules(), Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['teacher', 'user', 'admin'])],
            'district' => ['nullable', 'string', 'max:255'],
            'school_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['role'] === 'teacher') {
            $request->validate([
                'district' => ['required', 'string', 'max:255'],
                'school_name' => ['required', 'string', 'max:255'],
            ]);
        }

        $isAdmin = $validated['role'] === 'admin';

        // Keep at least one admin account in the system.
        if ($user->is_admin && ! $isAdmin && User::where('is_admin', true)->count() <= 1) {
            return back()->withErrors([
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

        return back()->with('success', 'User profile updated successfully.');
    }

    public function updateUserPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'User password updated successfully.');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return back()->withErrors([
                'user' => 'You cannot delete your own account while logged in.',
            ]);
        }

        if ($user->is_admin && User::where('is_admin', true)->count() <= 1) {
            return back()->withErrors([
                'user' => 'At least one admin account is required.',
            ]);
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240',
            'folder_id' => 'required|exists:folders,id',
            'preview_image' => 'nullable|image|max:5120',
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

        return back()->with('success', 'Folder deleted successfully.');
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

        return back()->with('success', 'File deleted successfully.');
    }

    public function toggleFolderLock(Folder $folder): RedirectResponse
    {
        $folder->update(['is_locked' => ! $folder->is_locked]);

        return back(303);
    }

    public function toggleFileLock(ResourceFile $file): RedirectResponse
    {
        $file->update(['is_locked' => ! $file->is_locked]);

        return back(303);
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

        return back()->with('success', 'Carousel image deleted successfully.');
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

        return back()->with('success', 'Featured video deleted successfully.');
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

    private function readableAction(string $action): string
    {
        return match ($action) {
            'folder_opened' => 'Opened Folder',
            'file_opened' => 'Opened File',
            'file_downloaded' => 'Downloaded File',
            default => ucfirst(str_replace('_', ' ', $action)),
        };
    }
}
