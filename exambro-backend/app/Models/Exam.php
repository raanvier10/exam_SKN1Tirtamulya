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

    private const ACCESS_CODE_ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    protected $fillable = [
        'title', 'type', 'description', 'google_form_url', 'start_at', 'end_at', 'duration', 'max_violation', 'access_code', 'status', 'created_by'
    ];

    protected static function booted(): void
    {
        static::creating(function (Exam $exam) {
            if (!array_key_exists('access_code', $exam->getAttributes())) {
                $exam->access_code = static::generateAccessCode();
            } elseif (trim((string) $exam->access_code) === '') {
                $exam->access_code = null;
            } else {
                $exam->access_code = strtoupper(trim((string) $exam->access_code));
            }
        });
    }

    public static function generateAccessCode(int $length = 6): string
    {
        $alphabet = self::ACCESS_CODE_ALPHABET;
        $max = strlen($alphabet) - 1;
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $alphabet[random_int(0, $max)];
        }
        return $code;
    }

    public function isUas(): bool
    {
        return strtolower($this->type ?? '') === 'uas' || (strtolower($this->type ?? '') !== 'uts' && !preg_match('/\b(uts|pts|tengah)/i', $this->title ?? ''));
    }

    public function isUts(): bool
    {
        return strtolower($this->type ?? '') === 'uts' || preg_match('/\b(uts|pts|tengah)/i', $this->title ?? '');
    }

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
            if (array_key_exists('has_active_session', $this->attributes)) {
                return (bool) $this->attributes['has_active_session'];
            }
            return $this->sessions()->whereIn('status', ['ACTIVE', 'LOCKED'])->exists();
        }

        return false;
    }
}
