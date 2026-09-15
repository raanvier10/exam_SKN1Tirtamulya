<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_bulk_delete_students(): void
    {
        $response = $this->post(route('admin.students.bulk-delete'), [
            'student_ids' => [1, 2],
        ]);

        $response->assertRedirect(route('admin.login'));
    }

    public function test_teacher_cannot_bulk_delete_students(): void
    {
        $teacher = User::factory()->create([
            'role' => 'guru',
            'status' => 'active',
        ]);

        $response = $this->actingAs($teacher)->post(route('admin.students.bulk-delete'), [
            'student_ids' => [1, 2],
        ]);

        $response->assertRedirect(route('admin.exams.index'));
    }

    public function test_admin_can_bulk_delete_students(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $student1 = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
        ]);

        $student2 = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
        ]);

        $student3 = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.students.bulk-delete'), [
            'student_ids' => [$student1->id, $student2->id],
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $student1->id]);
        $this->assertDatabaseMissing('users', ['id' => $student2->id]);
        $this->assertDatabaseHas('users', ['id' => $student3->id]);
    }

    public function test_bulk_delete_does_not_delete_non_student_users(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $teacher = User::factory()->create([
            'role' => 'guru',
            'status' => 'active',
        ]);

        $student = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.students.bulk-delete'), [
            'student_ids' => [$teacher->id, $student->id],
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $this->assertDatabaseMissing('users', ['id' => $student->id]);
        $this->assertDatabaseHas('users', ['id' => $teacher->id]);
    }
}
