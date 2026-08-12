<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappTemplate extends Model
{
    protected $fillable = ['user_id', 'name', 'message'];

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isShared(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Replace {name}, {company}, {employee}, {date} placeholders.
     */
    public function render(array $values): string
    {
        $defaults = [
            'name' => $values['name'] ?? '',
            'company' => $values['company'] ?? config('app.name'),
            'employee' => $values['employee'] ?? auth()->user()?->name ?? '',
            'date' => $values['date'] ?? now()->format('d M Y'),
        ];

        $message = $this->message;
        foreach ($defaults as $key => $value) {
            $message = str_replace('{'.$key.'}', $value, $message);
        }

        return $message;
    }
}
