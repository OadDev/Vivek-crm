<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ReferenceTable extends Model
{
    protected $fillable = ['category', 'title', 'description', 'headers', 'rows', 'sort_order'];

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'rows' => 'array',
        ];
    }

    /**
     * The "Standard Copper Conductor Reference" popup data on Product
     * Master — near-static reference data, read on every products page
     * load. Cached until an admin edits/adds/removes a table.
     */
    public static function cachedCopper(): Collection
    {
        return Cache::rememberForever(
            'reference_tables:copper',
            fn () => static::where('category', 'copper')->orderBy('sort_order')->get()
        );
    }

    public static function forgetCopperCache(): void
    {
        Cache::forget('reference_tables:copper');
    }

    /**
     * Parse text pasted straight out of Excel/Google Sheets (tab-separated,
     * one line per row — exactly what a copy of a cell range puts on the
     * clipboard) into a headers array + rows array of arrays.
     */
    public static function parsePastedGrid(string $text): array
    {
        $lines = array_values(array_filter(preg_split('/\r\n|\r|\n/', trim($text)), fn ($l) => trim($l) !== ''));

        if (empty($lines)) {
            return [[], []];
        }

        $splitLine = fn ($line) => str_contains($line, "\t") ? explode("\t", $line) : str_getcsv($line);

        $headers = array_map('trim', $splitLine(array_shift($lines)));
        $rows = array_map(fn ($line) => array_map('trim', $splitLine($line)), $lines);

        return [$headers, $rows];
    }
}
