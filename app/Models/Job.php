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

    // Return skills as array — strip Quill HTML first, then split by comma/newline
    public function skillsArray(): array
    {
        $raw = $this->key_skills ?? '';
        // Replace <li>, <br>, <p>, <div>, <ul>, <ol> with commas so list items become separate entries
        $raw = preg_replace('/<\/?(li|br|p|h[1-6]|div|ul|ol)[^>]*>/i', ',', $raw);
        // Strip remaining HTML tags
        $raw = strip_tags($raw);
        // Decode HTML entities (e.g. &amp; &nbsp;)
        $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Replace non-breaking spaces (UTF-8 \xc2\xa0) with regular space, then collapse whitespace
        $raw = preg_replace('/[\xc2\xa0\s]+/', ' ', $raw);
        // Trim regular AND non-breaking spaces from each element
        $trimNbsp = fn(string $s): string => trim($s, " \t\n\r\0\x0B\xc2\xa0");
        // Split by comma or newline, trim, remove empties
        return array_values(array_filter(array_map($trimNbsp, preg_split('/[,\n]+/', $raw))));
    }

}
