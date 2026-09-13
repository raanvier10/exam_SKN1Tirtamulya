<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_teachers_page(): void
    {
        $response = $this->get(route('admin.teachers.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_teacher_cannot_access_teachers_page(): void
    {
        $teacher = User::factory()->create([
            'role' => 'guru',
            'status' => 'active',
        ]);

        $response = $this->actingAs($teacher)->get(route('admin.teachers.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_teachers_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.teachers.index'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Guru');
    }

    public function test_admin_can_create_teacher(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.teachers.store'), [
            'name' => 'Guru Matematika',
            'username' => 'guru_math_01',
            'email' => 'gurumath@smkn1tirtamulya.online',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.teachers.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'guru_math_01',
            'role' => 'guru',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_update_teacher(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $teacher = User::factory()->create([
            'name' => 'Guru Lama',
            'username' => 'guru_lama',
            'role' => 'guru',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.teachers.update', $teacher->id), [
            'name' => 'Guru Baru',
            'username' => 'guru_baru',
            'email' => 'gurubaru@smkn1tirtamulya.online',
            'status' => 'inactive',
        ]);

        $response->assertRedirect(route('admin.teachers.index'));
        $this->assertDatabaseHas('users', [
            'id' => $teacher->id,
            'name' => 'Guru Baru',
            'username' => 'guru_baru',
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_delete_teacher(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $teacher = User::factory()->create([
            'role' => 'guru',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.teachers.destroy', $teacher->id));
        $response->assertRedirect(route('admin.teachers.index'));
        $this->assertDatabaseMissing('users', [
            'id' => $teacher->id,
        ]);
    }
}
