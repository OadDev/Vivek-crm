<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSyncSetting extends Model
{
    protected $fillable = [
        'source_type',
        'excel_file_path',
        'excel_original_name',
        'google_sheet_url',
        'interval_minutes',
        'is_enabled',
        'last_synced_at',
        'last_sync_status',
        'last_sync_message',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], ['source_type' => 'google_sheet']);
    }
}
