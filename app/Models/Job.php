<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    protected $fillable = [
        'user_id', 'title', 'company', 'location', 'type',
        'description', 'requirements', 'experience_required',
        'salary_range', 'status', 'key_skills',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    // Return skills as array
    public function skillsArray(): array
    {
        return array_filter(array_map('trim', explode(',', $this->key_skills ?? '')));
    }
}
