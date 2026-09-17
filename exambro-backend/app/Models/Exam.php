<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'google_form_url', 'start_at', 'end_at', 'duration', 'max_violation', 'status', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'duration' => 'integer',
            'max_violation' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(StudentClass::class, 'exam_classes', 'exam_id', 'class_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ExamParticipant::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }

    public function isExpired(): bool
    {
        return $this->end_at ? now()->gt($this->end_at) : false;
    }

    public function isOngoing(): bool
    {
        $now = now();

        // Jika waktu akhir ujian sudah lewat, ujian sudah SELESAI (bukan berlangsung)
        if ($this->end_at && $now->gt($this->end_at)) {
            return false;
        }

        // Berlangsung jika status aktif dan waktu saat ini di antara start_at dan end_at
        if ($this->status === 'active' && $this->start_at && $this->end_at && $now->between($this->start_at, $this->end_at)) {
            return true;
        }

        // Atau jika ada sesi ujian siswa yang masih aktif atau terkunci, tapi hanya selama belum lewat batas end_at
        if ($this->status === 'active') {
            return $this->sessions()->whereIn('status', ['ACTIVE', 'LOCKED'])->exists();
        }

        return false;
    }
}
