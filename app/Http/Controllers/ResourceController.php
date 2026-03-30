<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\CarouselImage;
use App\Models\FeaturedVideo;
use App\Models\Folder;
use App\Models\ResourceFile;
use App\Models\ResourceTracking;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResourceController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('User/Resources/Index', [
            'folders' => Folder::whereNull('parent_id')
                ->with(['files', 'childrenRecursive'])
                ->orderBy('name', 'asc')
                ->get(),
            'announcements' => Announcement::latest()->limit(6)->get(),
            'carouselImages' => CarouselImage::latest()->get(),
            'featuredVideos' => FeaturedVideo::latest()->get(),
        ]);
    }

    public function openFolder(Folder $folder): HttpResponse
    {
        if ($folder->is_locked) {
            abort(403, 'This folder is currently locked.');
        }

        $this->trackAction('folder_opened', $folder, null);

        return response()->noContent();
    }

    public function download(ResourceFile $file): BinaryFileResponse
    {
        if ($file->is_locked) {
            abort(403, 'This file is currently locked and cannot be downloaded.');
        }

        $this->trackAction('file_downloaded', $file->folder, $file);

        return response()->download(
            Storage::disk('public')->path($file->file_path),
            $file->title
        );
    }

    public function preview(ResourceFile $file): BinaryFileResponse
    {
        if ($file->is_locked) {
            abort(403, 'This file is currently locked and cannot be previewed.');
        }

        $this->trackAction('file_opened', $file->folder, $file);

        return response()->file(Storage::disk('public')->path($file->file_path));
    }

    private function trackAction(string $action, ?Folder $folder, ?ResourceFile $file): void
    {
        if (! Auth::check()) {
            return;
        }

        ResourceTracking::create([
            'user_id' => Auth::id(),
            'folder_id' => $folder?->id,
            'resource_file_id' => $file?->id,
            'action' => $action,
        ]);
    }
}
