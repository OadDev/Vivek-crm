<?php

namespace App\Services;

use App\Models\DataSheet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Throwable;

/**
 * Manual-only sync for generic (non-CRM) reference data -- courier lists,
 * datasheets, etc. Unlike contacts, there's no dedup key across arbitrary
 * sheets, so every sync simply replaces this sheet's rows wholesale with
 * whatever the source currently contains.
 */
class DataSheetSyncService
{
    public function sync(DataSheet $sheet): array
    {
        try {
            [$headers, $rows] = match ($sheet->source_type) {
                'excel_upload' => $this->fromExcel($sheet),
                default => $this->fromGoogleSheet($sheet),
            };

            if (empty($headers)) {
                throw new RuntimeException('No data found -- the first row must contain column headers.');
            }

            // Two columns sharing the same header text would otherwise
            // collapse into one in array_combine() below, silently
            // discarding whichever column's value lost (e.g. a sheet with
            // two "Phone" columns, the second meant to be "Alt Phone").
            $headers = $this->deduplicateHeaders($headers);

            DB::transaction(function () use ($sheet, $headers, $rows) {
                $sheet->rows()->delete();

                $order = 0;
                foreach ($rows as $row) {
                    $assoc = array_combine($headers, array_pad(array_slice($row, 0, count($headers)), count($headers), null));

                    // Skip fully-blank rows (trailing empty lines in the sheet).
                    if (empty(array_filter($assoc, fn ($v) => $v !== null && $v !== ''))) {
                        continue;
                    }

                    $sheet->rows()->create([
                        'data' => $assoc,
                        'search_text' => strtolower(implode(' ', array_map(fn ($v) => (string) $v, $assoc))),
                        'sort_order' => $order++,
                    ]);
                }

                $sheet->update([
                    'headers' => $headers,
                    'last_synced_at' => now(),
                    'last_sync_status' => 'success',
                    'last_sync_message' => "Imported {$order} row(s).",
                ]);
            });

            return ['success' => true, 'message' => "Imported ".$sheet->rows()->count().' row(s).'];
        } catch (Throwable $e) {
            $sheet->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'failed',
                'last_sync_message' => $e->getMessage(),
            ]);

            Log::error("Data sheet '{$sheet->name}' sync failed: ".$e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function deduplicateHeaders(array $headers): array
    {
        $seen = [];
        foreach ($headers as $i => $header) {
            $count = $seen[$header] = ($seen[$header] ?? 0) + 1;
            if ($count > 1) {
                $headers[$i] = "{$header} ({$count})";
            }
        }

        return $headers;
    }

    protected function fromExcel(DataSheet $sheet): array
    {
        if (! $sheet->excel_file_path || ! Storage::disk('local')->exists($sheet->excel_file_path)) {
            throw new RuntimeException('No Excel/CSV file has been uploaded for this data sheet.');
        }

        $sheets = Excel::toArray(null, Storage::disk('local')->path($sheet->excel_file_path));
        $rows = $sheets[0] ?? [];

        if (empty($rows)) {
            return [[], []];
        }

        $headers = array_map(fn ($h) => trim((string) $h), array_shift($rows));

        return [$headers, $rows];
    }

    protected function fromGoogleSheet(DataSheet $sheet): array
    {
        if (! $sheet->google_sheet_url) {
            throw new RuntimeException('No Google Sheet URL has been configured for this data sheet.');
        }

        $csvUrl = $this->toCsvExportUrl($sheet->google_sheet_url);
        $response = Http::timeout(20)->get($csvUrl);

        if (! $response->successful()) {
            throw new RuntimeException('Could not fetch the Google Sheet (HTTP '.$response->status().'). Make sure it is shared as "Anyone with the link".');
        }

        $contentType = $response->header('Content-Type');
        $body = $response->body();

        if (str_contains((string) $contentType, 'text/html') || stripos(ltrim($body), '<!DOCTYPE html') === 0 || stripos(ltrim($body), '<html') === 0) {
            throw new RuntimeException('Google returned a sign-in page instead of your sheet data. Open the sheet, click Share, and set general access to "Anyone with the link — Viewer", then try again.');
        }

        $rows = $this->parseCsv($body);
        $headers = array_map('trim', array_shift($rows) ?? []);

        return [$headers, $rows];
    }

    /**
     * Splitting the body into lines before parsing breaks any row whose
     * cell contains a real newline (a multi-line address, for example) --
     * a quoted CSV field is allowed to span multiple physical lines per
     * the CSV spec, so this has to be parsed as one stream, not line-by-
     * line (see the identical fix in ContactSyncService::parseCsv()).
     *
     * @return array<int, array<int, string|null>>
     */
    protected function parseCsv(string $body): array
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $body);
        rewind($stream);

        $rows = [];
        while (($row = fgetcsv($stream)) !== false) {
            if ($row === [null]) {
                continue;
            }

            $rows[] = $row;
        }

        fclose($stream);

        return $rows;
    }

    protected function toCsvExportUrl(string $url): string
    {
        if (! preg_match('#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $url, $matches)) {
            throw new RuntimeException('That does not look like a valid Google Sheets URL.');
        }

        $id = $matches[1];
        $gid = '0';

        if (preg_match('/[?&#]gid=(\d+)/', $url, $gidMatch)) {
            $gid = $gidMatch[1];
        }

        return "https://docs.google.com/spreadsheets/d/{$id}/export?format=csv&gid={$gid}";
    }
}
