<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
