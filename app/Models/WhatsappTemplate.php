<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

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
     * The shared/company template list, shown to everyone. Rarely changes
     * (admin-managed), read on every Settings page load and every one-click
     * WhatsApp send — cached until a shared template is written.
     */
    public static function cachedCompany(): Collection
    {
        return Cache::rememberForever(
            'whatsapp:company_templates',
            fn () => static::whereNull('user_id')->orderBy('name')->get()
        );
    }

    /**
     * One user's own personal template list.
     */
    public static function cachedForUser(int $userId): Collection
    {
        return Cache::rememberForever(
            "whatsapp:my_templates:{$userId}",
            fn () => static::where('user_id', $userId)->orderBy('name')->get()
        );
    }

    /**
     * Call after creating/updating/deleting a template. Pass the template's
     * user_id (null for a shared/company template).
     */
    public static function forgetCache(?int $userId): void
    {
        Cache::forget('whatsapp:company_templates');

        if ($userId !== null) {
            Cache::forget("whatsapp:my_templates:{$userId}");
        }
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
