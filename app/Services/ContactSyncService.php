<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ContactSyncSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ContactSyncService
{
    public function run(bool $force = false): array
    {
        $setting = ContactSyncSetting::current();

        if (! $force && ! $setting->is_enabled) {
            return ['success' => false, 'message' => 'Auto-sync is disabled.'];
        }

        if (! $force && $setting->last_synced_at
            && $setting->last_synced_at->diffInMinutes(now()) < $setting->interval_minutes) {
            return ['success' => false, 'message' => 'Sync interval has not elapsed yet.'];
        }

        try {
            $result = match ($setting->source_type) {
                'google_sheet' => $this->syncFromGoogleSheet($setting),
                default => $this->syncFromExcel($setting),
            };

            $message = "Created {$result['created']}, updated {$result['updated']}, skipped {$result['skipped']}.";

            $setting->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
                'last_sync_message' => $message,
            ]);

            Activity::log("Contacts auto-sync: {$message}", 'bi-arrow-repeat', 'info');

            return ['success' => true, 'message' => $message, ...$result];
        } catch (Throwable $e) {
            $setting->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'failed',
                'last_sync_message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function syncFromExcel(ContactSyncSetting $setting): array
    {
        if (! $setting->excel_file_path || ! Storage::disk('local')->exists($setting->excel_file_path)) {
            throw new \RuntimeException('No Excel file has been uploaded for auto-sync.');
        }

        $import = new \App\Imports\ContactsImport('excel_import');
        Excel::import($import, Storage::disk('local')->path($setting->excel_file_path));

        return $import->result;
    }

    protected function syncFromGoogleSheet(ContactSyncSetting $setting): array
    {
        if (! $setting->google_sheet_url) {
            throw new \RuntimeException('No Google Sheet URL has been configured for auto-sync.');
        }

        $csvUrl = $this->toCsvExportUrl($setting->google_sheet_url);
        $response = Http::timeout(20)->get($csvUrl);

        if (! $response->successful()) {
            throw new \RuntimeException('Could not fetch the Google Sheet (HTTP '.$response->status().'). Make sure it is shared as "Anyone with the link".');
        }

        $lines = array_filter(preg_split('/\r\n|\r|\n/', $response->body()));
        $rows = array_map('str_getcsv', $lines);
        $headers = array_map('trim', array_shift($rows) ?? []);

        $assocRows = [];
        foreach ($rows as $row) {
            $assocRows[] = array_combine($headers, array_pad($row, count($headers), null));
        }

        return (new ContactImporter)->import($assocRows, 'google_sheet');
    }

    protected function toCsvExportUrl(string $url): string
    {
        if (! preg_match('#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $url, $matches)) {
            throw new \RuntimeException('That does not look like a valid Google Sheets URL.');
        }

        $id = $matches[1];
        $gid = '0';

        if (preg_match('/[?&#]gid=(\d+)/', $url, $gidMatch)) {
            $gid = $gidMatch[1];
        }

        return "https://docs.google.com/spreadsheets/d/{$id}/export?format=csv&gid={$gid}";
    }
}
