<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\ResourceFile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    public function index()
    {
        // 1. Build a flat map of all folders with full paths (e.g., "Math > Algebra")
        $allFolders = Folder::with('parent')->get()->map(function ($folder) {
            $pathParts = [$folder->name];
            $parent = $folder->parent;
            while ($parent) {
                array_unshift($pathParts, $parent->name);
                $parent = $parent->parent;
            }
            $folder->full_path = implode(' > ', $pathParts);
            return $folder;
        })->sortBy('full_path')->values();

        return Inertia::render('Admin/Resources/Index', [
            // 2. Load the recursive tree starting ONLY from root folders (parent_id = null)
            'folders' => Folder::whereNull('parent_id')
                ->with(['files', 'childrenRecursive']) 
                ->orderBy('name', 'asc')
                ->get(),
            'allFolders' => $allFolders,
            'stats' => [
                'total_folders' => Folder::count(),
                'total_files' => ResourceFile::count(),
            ]
        ]);
    }

    public function storeFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255', 
            'parent_id' => 'nullable|exists:folders,id'
        ]);
        Folder::create($request->only('name', 'parent_id'));
        return back();
    }

    public function storeFile(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // 10MB Max
            'folder_id' => 'required|exists:folders,id',
        ]);

        $path = $request->file('file')->store('resources', 'public');

        ResourceFile::create([
            'folder_id' => $request->folder_id,
            'title' => $request->title,
            'file_path' => $path,
            'is_locked' => false,
        ]);

        return back();
    }

    public function destroyFolder(Folder $folder) 
    { 
        $folder->delete(); 
        return back(); 
    }
    
    public function destroyFile(ResourceFile $file) 
    { 
        if ($file->file_path) Storage::disk('public')->delete($file->file_path);
        $file->delete(); 
        return back(); 
    }

    public function toggleFileLock(ResourceFile $file) 
    {
        $file->update(['is_locked' => !$file->is_locked]);
        return back();
    }
}