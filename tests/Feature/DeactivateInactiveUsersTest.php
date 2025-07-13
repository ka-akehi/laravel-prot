<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeactivateInactiveUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_not_post_to_un_active()
    {
        // Arrange
        $userWithPosts = User::factory()->has(Post::factory()->count(2))->create(['active' => true]);
        $userWithoutPosts = User::factory()->create(['active' => true]);

        // Act
        $this->artisan('users:deactivate-inactive')
            ->expectsOutput('非アクティブ化したユーザー数: 1')
            ->assertExitCode(0);

        // Assert
        $this->assertTrue($userWithPosts->fresh()->active); // 投稿があるユーザーは非アクティブ化されない
        $this->assertFalse($userWithoutPosts->fresh()->active); // 投稿がないユーザーは非アクティブ化される
    }
}
