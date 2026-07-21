<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_FOLLOW_UP = 'follow_up';

    public const STATUS_INACTIVE = 'inactive';

    public const FOLLOW_UP_AFTER_DAYS = 7;

    public const INACTIVE_AFTER_DAYS = 20;

    protected $fillable = [
        'name',
        'company',
        'email',
        'whatsapp',
        'designation',
        'status',
        'is_starred',
        'last_contacted_at',
        'source',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_starred' => 'boolean',
            'last_contacted_at' => 'datetime',
        ];
    }

    public function emailConversations(): HasMany
    {
        return $this->hasMany(EmailConversation::class);
    }

    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class)->latest('sent_at');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_FOLLOW_UP => 'Follow-up',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public static function statusChipClass(string $status): string
    {
        return match ($status) {
            self::STATUS_ACTIVE => 'chip-success',
            self::STATUS_FOLLOW_UP => 'chip-warning',
            self::STATUS_INACTIVE => 'chip-neutral',
            default => 'chip-neutral',
        };
    }

    /**
     * Compute the status a contact should have given a "last contacted" date,
     * per the 7-day follow-up / 20-day inactive business rule.
     */
    public static function computeStatusFromDate(?Carbon $lastContactedAt): string
    {
        if (! $lastContactedAt) {
            return self::STATUS_ACTIVE;
        }

        $daysSince = $lastContactedAt->diffInDays(now());

        if ($daysSince >= self::INACTIVE_AFTER_DAYS) {
            return self::STATUS_INACTIVE;
        }

        if ($daysSince >= self::FOLLOW_UP_AFTER_DAYS) {
            return self::STATUS_FOLLOW_UP;
        }

        return self::STATUS_ACTIVE;
    }

    public function scopeStarredFirst(Builder $query): Builder
    {
        return $query->orderByDesc('is_starred');
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));
        $letters = array_map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)), array_slice($parts, 0, 2));

        return implode('', $letters);
    }

    public function avatarColor(): string
    {
        $palette = ['#4F46E5', '#0891B2', '#16A34A', '#D97706', '#DB2777', '#7C3AED', '#0D9488', '#DC2626'];
        $hash = crc32($this->name);

        return $palette[$hash % count($palette)];
    }
}
