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

        // Decide the delimiter once, from the header line, instead of
        // per-line -- otherwise a single data row with no tab character
        // (e.g. only one populated cell) gets comma-split instead, and a
        // value containing a comma (like "1,250") silently splits into two
        // misaligned cells for just that one row.
        $headerLine = array_shift($lines);
        $splitLine = str_contains($headerLine, "\t")
            ? fn ($line) => explode("\t", $line)
            : fn ($line) => str_getcsv($line);

        $headers = array_map('trim', $splitLine($headerLine));
        $rows = array_map(fn ($line) => array_map('trim', $splitLine($line)), $lines);

        return [$headers, $rows];
    }
}
