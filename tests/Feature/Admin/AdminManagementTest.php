<?php

namespace Tests\Feature\Admin;

use App\Models\CarouselImage;
use App\Models\FeaturedVideo;
use App\Models\Folder;
use App\Models\ResourceFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_users_and_analytics_pages(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/analytics')
            ->assertOk();
    }

    public function test_admin_can_create_update_and_delete_teacher_accounts(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'Teacher One',
                'email' => 'teacher.one@deped.gov.ph',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'teacher',
                'district' => 'District A',
                'school_name' => 'School A',
            ])
            ->assertRedirect();

        $teacher = User::where('email', 'teacher.one@deped.gov.ph')->firstOrFail();

        $this->actingAs($admin)
            ->patch("/admin/users/{$teacher->id}", [
                'name' => 'Teacher Updated',
                'email' => 'teacher.updated@deped.gov.ph',
                'role' => 'teacher',
                'district' => 'District B',
                'school_name' => 'School B',
            ])
            ->assertRedirect();

        $teacher->refresh();
        $this->assertSame('Teacher Updated', $teacher->name);
        $this->assertSame('District B', $teacher->district);

        $this->actingAs($admin)
            ->patch("/admin/users/{$teacher->id}/password", [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertRedirect();

        $teacher->refresh();
        $this->assertTrue(Hash::check('newpassword123', $teacher->password));

        $this->actingAs($admin)
            ->delete("/admin/users/{$teacher->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('users', [
            'id' => $teacher->id,
        ]);
    }

    public function test_admin_can_create_update_and_delete_carousel_entries(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post('/admin/carousel', [
                'title' => 'Slide 1',
                'image' => UploadedFile::fake()->image('slide1.jpg'),
            ])
            ->assertRedirect();

        $carousel = CarouselImage::firstOrFail();

        $this->actingAs($admin)
            ->patch("/admin/carousel/{$carousel->id}", [
                'title' => 'Slide Updated',
            ])
            ->assertRedirect();

        $carousel->refresh();
        $this->assertSame('Slide Updated', $carousel->title);

        $this->actingAs($admin)
            ->delete("/admin/carousel/{$carousel->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('carousel_images', [
            'id' => $carousel->id,
        ]);
    }

    public function test_admin_can_create_update_and_delete_featured_videos(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post('/admin/videos', [
                'youtube_link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'description' => 'First video',
            ])
            ->assertRedirect();

        $video = FeaturedVideo::firstOrFail();

        $this->actingAs($admin)
            ->patch("/admin/videos/{$video->id}", [
                'youtube_link' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
                'description' => 'Updated video',
            ])
            ->assertRedirect();

        $video->refresh();
        $this->assertSame('Updated video', $video->description);

        $this->actingAs($admin)
            ->delete("/admin/videos/{$video->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('featured_videos', [
            'id' => $video->id,
        ]);
    }

    public function test_folder_open_tracking_endpoint_records_activity(): void
    {
        $teacher = User::factory()->create([
            'role' => 'teacher',
            'district' => 'District C',
            'school_name' => 'School C',
        ]);

        $folder = Folder::create([
            'name' => 'Science',
            'parent_id' => null,
        ]);

        ResourceFile::create([
            'folder_id' => $folder->id,
            'title' => 'Lesson 1',
            'file_path' => 'resources/lesson1.pdf',
            'file_type' => 'pdf',
            'is_locked' => false,
        ]);

        $this->actingAs($teacher)
            ->post("/resources/folders/{$folder->id}/open")
            ->assertNoContent();

        $this->assertDatabaseHas('resource_trackings', [
            'user_id' => $teacher->id,
            'folder_id' => $folder->id,
            'action' => 'folder_opened',
        ]);
    }
}
