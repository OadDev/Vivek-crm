<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_FOLLOW_UP = 'follow_up';

    public const STATUS_INACTIVE = 'inactive';

    /** Below this many days since the quotation date, a lead is Active. */
    public const FOLLOW_UP_AFTER_DAYS = 7;

    /** Beyond this many months since the quotation date, a lead is Inactive. */
    public const INACTIVE_AFTER_MONTHS = 2;

    protected $fillable = [
        'quote_no',
        'quotation_date',
        'name',
        'company',
        'email',
        'whatsapp',
        'designation',
        'sales_man',
        'gst_number',
        'transport',
        'shipping_address',
        'stage',
        'priority',
        'status',
        'is_starred',
        'is_archived',
        'archived_at',
        'is_won',
        'won_at',
        'last_contacted_at',
        'source',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_starred' => 'boolean',
            'is_archived' => 'boolean',
            'is_won' => 'boolean',
            'quotation_date' => 'date',
            'archived_at' => 'datetime',
            'won_at' => 'datetime',
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
     * Compute the status a lead should have given its quotation date:
     * Active under 7 days old, Follow-up from 7 days to 2 months,
     * Inactive beyond 2 months.
     */
    public static function computeStatusFromDate(?Carbon $quotationDate): string
    {
        if (! $quotationDate) {
            return self::STATUS_ACTIVE;
        }

        if ($quotationDate->lt(now()->subMonths(self::INACTIVE_AFTER_MONTHS))) {
            return self::STATUS_INACTIVE;
        }

        if ($quotationDate->diffInDays(now()) >= self::FOLLOW_UP_AFTER_DAYS) {
            return self::STATUS_FOLLOW_UP;
        }

        return self::STATUS_ACTIVE;
    }

    public function scopeStarredFirst(Builder $query): Builder
    {
        return $query->orderByDesc('is_starred');
    }

    public function scopePipeline(Builder $query): Builder
    {
        return $query->where('is_archived', false)->where('is_won', false);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('is_archived', true);
    }

    public function scopeWon(Builder $query): Builder
    {
        return $query->where('is_won', true);
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
