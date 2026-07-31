<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GmailAccount extends Model
{
    protected $fillable = [
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

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    public function hasCredentials(): bool
    {
        return ! empty($this->resolvedClientId()) && ! empty($this->resolvedClientSecret());
    }

    /**
     * Prefer credentials saved on this Settings page; fall back to .env
     * (GOOGLE_CLIENT_ID/GOOGLE_CLIENT_SECRET) for anyone who set it up that
     * way before this UI existed.
     */
    public function resolvedClientId(): ?string
    {
        return $this->client_id ?: config('services.google.client_id');
    }

    public function resolvedClientSecret(): ?string
    {
        return $this->client_secret ?: config('services.google.client_secret');
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
