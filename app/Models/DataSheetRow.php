<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataSheetRow extends Model
{
    protected $fillable = ['data_sheet_id', 'data', 'search_text', 'sort_order'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    public function dataSheet(): BelongsTo
    {
        return $this->belongsTo(DataSheet::class);
    }
}
