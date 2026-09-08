<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'google_form_url', 'start_at', 'end_at', 'duration', 'max_violation', 'pkl_filter', 'status'
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
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
}
