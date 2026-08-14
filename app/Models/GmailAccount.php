<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class GmailAccount extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'client_secret',
        'google_id',
        'email',
        'name',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'history_id',
        'connected_at',
        'last_synced_at',
        'last_sync_status',
        'last_sync_message',
    ];

    protected function casts(): array
    {
        return [
            'client_secret' => 'encrypted',
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'connected_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Each user's own inbox connection (tokens, connected email).
     */
    public static function forUser(User $user): self
    {
        return static::firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * The one shared Google OAuth Client ID/Secret (a single Google Cloud
     * app) that every user's "Connect Gmail" authorizes against. Admin-only
     * to edit; every user's per-account connect/refresh reads it via
     * resolvedClientId()/resolvedClientSecret() below. Cached since it's
     * read on every Gmail connect/token-refresh across every user, and only
     * ever changes when an admin saves new credentials (see
     * forgetSharedClientCache()).
     */
    public static function sharedClient(): self
    {
        return Cache::rememberForever(
            'gmail:shared_client',
            fn () => static::firstOrCreate(['user_id' => null])
        );
    }

    public static function forgetSharedClientCache(): void
    {
        Cache::forget('gmail:shared_client');
    }

    public function hasCredentials(): bool
    {
        return ! empty($this->resolvedClientId()) && ! empty($this->resolvedClientSecret());
    }

    /**
     * Prefer the shared Client ID/Secret saved in Settings; fall back to
     * .env (GOOGLE_CLIENT_ID/GOOGLE_CLIENT_SECRET) for anyone who set it up
     * that way before this UI existed.
     */
    public function resolvedClientId(): ?string
    {
        $shared = $this->user_id === null ? $this : static::sharedClient();

        return $shared->client_id ?: config('services.google.client_id');
    }

    public function resolvedClientSecret(): ?string
    {
        $shared = $this->user_id === null ? $this : static::sharedClient();

        return $shared->client_secret ?: config('services.google.client_secret');
    }

    public function isConnected(): bool
    {
        return ! empty($this->refresh_token);
    }

    public function disconnect(): void
    {
        $this->update([
            'google_id' => null,
            'email' => null,
            'name' => null,
            'access_token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
            'history_id' => null,
            'connected_at' => null,
        ]);
    }
}
