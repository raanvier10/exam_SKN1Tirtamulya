<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_change_password_page(): void
    {
        $response = $this->get(route('admin.change-password'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_change_password_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.change-password'));
        $response->assertStatus(200);
        $response->assertSee('Ganti Password Admin');
    }

    public function test_admin_cannot_change_password_with_wrong_current_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.change-password.update'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $this->assertTrue(Hash::check('oldpassword123', $admin->fresh()->password));
    }

    public function test_admin_can_successfully_change_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.change-password.update'), [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('newpassword123', $admin->fresh()->password));
    }
}
