<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataSheet extends Model
{
    protected $fillable = [
        'name',
        'source_type',
        'google_sheet_url',
        'excel_file_path',
        'excel_original_name',
        'headers',
        'last_synced_at',
        'last_sync_status',
        'last_sync_message',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function rows(): HasMany
    {
        return $this->hasMany(DataSheetRow::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
