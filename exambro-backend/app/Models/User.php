<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'department', 'class_id', 'status', 'is_pkl', 'google_connected', 'google_email'])]
#[Hidden(['password', 'remember_token', 'api_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_pkl' => 'boolean',
            'google_connected' => 'boolean',
        ];
    }

    public function class()
    {
        return $this->belongsTo(StudentClass::class, 'class_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'guru';
    }

    public function departmentCode(): ?string
    {
        $dept = strtoupper(trim((string) ($this->department ?? '')));
        return $dept !== '' ? $dept : null;
    }

    public function isDepartmentPic(): bool
    {
        return $this->isAdmin() && $this->departmentCode() !== null;
    }

    /**
     * Kembalikan daftar class_id milik jurusan PIC ini, atau null jika Super Admin (akses semua jurusan).
     *
     * @return array<int>|null
     */
    public function departmentClassIds(): ?array
    {
        $dept = $this->departmentCode();
        if (!$this->isAdmin() || $dept === null) {
            return null;
        }

        $pattern = '/(^|[^A-Z])' . preg_quote($dept, '/') . '([^A-Z]|$)/i';

        return StudentClass::query()
            ->get(['id', 'name'])
            ->filter(fn (StudentClass $cls) => preg_match($pattern, (string) $cls->name) === 1)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function createdExams()
    {
        return $this->hasMany(Exam::class, 'created_by');
    }
}
