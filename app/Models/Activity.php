<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    protected $fillable = ['icon', 'color', 'description', 'subject_type', 'subject_id'];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log(string $description, string $icon = 'bi-info-circle-fill', string $color = 'primary', ?Model $subject = null): self
    {
        return static::create([
            'description' => $description,
            'icon' => $icon,
            'color' => $color,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
        ]);
    }
}
