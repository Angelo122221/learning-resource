<?php

namespace Tests\Feature\User;

use App\Models\Announcement;
use App\Models\AnnouncementUserState;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_resources_page_uses_user_specific_read_and_dismiss_state(): void
    {
        $teacher = User::factory()->createOne();
        $otherTeacher = User::factory()->createOne();

        $readAnnouncement = Announcement::create([
            'title' => 'Read update',
            'content' => 'Already opened by this teacher.',
        ]);

        $dismissedAnnouncement = Announcement::create([
            'title' => 'Dismissed update',
            'content' => 'This should be hidden only for the current teacher.',
        ]);

        $visibleUnreadAnnouncement = Announcement::create([
            'title' => 'Visible unread update',
            'content' => 'This should still appear as unread.',
        ]);

        AnnouncementUserState::create([
            'announcement_id' => $readAnnouncement->id,
            'user_id' => $teacher->id,
            'read_at' => now(),
        ]);

        AnnouncementUserState::create([
            'announcement_id' => $dismissedAnnouncement->id,
            'user_id' => $teacher->id,
            'dismissed_at' => now(),
        ]);

        AnnouncementUserState::create([
            'announcement_id' => $visibleUnreadAnnouncement->id,
            'user_id' => $otherTeacher->id,
            'dismissed_at' => now(),
        ]);

        $response = $this->actingAs($teacher)->get('/resources');

        $response->assertOk();

        $page = $response->viewData('page');
        $announcements = collect($page['props']['announcements']);

        $this->assertCount(2, $announcements);
        $this->assertTrue($announcements->contains(fn (array $announcement) => $announcement['id'] === $readAnnouncement->id && $announcement['is_read'] === true));
        $this->assertTrue($announcements->contains(fn (array $announcement) => $announcement['id'] === $visibleUnreadAnnouncement->id && $announcement['is_read'] === false));
        $this->assertFalse($announcements->contains(fn (array $announcement) => $announcement['id'] === $dismissedAnnouncement->id));
    }

    public function test_user_can_mark_an_announcement_as_read_without_affecting_other_users(): void
    {
        $teacher = User::factory()->createOne();
        $otherTeacher = User::factory()->createOne();

        $announcement = Announcement::create([
            'title' => 'Portal bulletin',
            'content' => 'Please review the latest schedule update.',
        ]);

        $this->actingAs($teacher)
            ->post("/resources/announcements/{$announcement->id}/read")
            ->assertNoContent();

        $this->assertDatabaseHas('announcement_user_states', [
            'announcement_id' => $announcement->id,
            'user_id' => $teacher->id,
        ]);

        $this->assertDatabaseMissing('announcement_user_states', [
            'announcement_id' => $announcement->id,
            'user_id' => $otherTeacher->id,
        ]);
    }

    public function test_user_can_mark_all_visible_announcements_as_read_and_dismiss_an_announcement_without_global_delete(): void
    {
        $teacher = User::factory()->createOne();
        $otherTeacher = User::factory()->createOne();

        $firstAnnouncement = Announcement::create([
            'title' => 'First update',
            'content' => 'This one should be marked as read.',
        ]);

        $dismissedAnnouncement = Announcement::create([
            'title' => 'Dismissible update',
            'content' => 'This one should disappear only for one user.',
        ]);

        $this->actingAs($teacher)
            ->post('/resources/announcements/read-all')
            ->assertNoContent();

        $this->assertDatabaseHas('announcement_user_states', [
            'announcement_id' => $firstAnnouncement->id,
            'user_id' => $teacher->id,
        ]);

        $this->assertDatabaseHas('announcement_user_states', [
            'announcement_id' => $dismissedAnnouncement->id,
            'user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->post("/resources/announcements/{$dismissedAnnouncement->id}/dismiss")
            ->assertNoContent();

        $this->assertDatabaseHas('announcements', [
            'id' => $dismissedAnnouncement->id,
            'title' => 'Dismissible update',
        ]);

        $this->assertDatabaseHas('announcement_user_states', [
            'announcement_id' => $dismissedAnnouncement->id,
            'user_id' => $teacher->id,
        ]);

        $otherUserResponse = $this->actingAs($otherTeacher)->get('/resources');
        $otherUserResponse->assertOk();

        $page = $otherUserResponse->viewData('page');
        $announcements = collect($page['props']['announcements']);

        $this->assertTrue($announcements->contains(fn (array $announcement) => $announcement['id'] === $dismissedAnnouncement->id));
    }
}
