<?php

namespace App\Services;

use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ContactImporter
{
    protected const FIELD_ALIASES = [
        'name' => ['name', 'fullname', 'contactname'],
        'company' => ['company', 'companyname', 'organisation', 'organization'],
        'email' => ['email', 'emailaddress', 'e-mail'],
        'whatsapp' => ['whatsapp', 'whatsappnumber', 'phone', 'mobile', 'contactnumber', 'phonenumber'],
        'designation' => ['designation', 'title', 'role', 'jobtitle'],
        'date' => ['date', 'lastcontacted', 'lastcontacteddate', 'lastinteraction', 'lastinteractiondate'],
        'status' => ['status'],
        'notes' => ['notes', 'remark', 'remarks', 'note'],
    ];

    /**
     * @param  iterable<array<string, mixed>>  $rows  Rows keyed by raw header text.
     * @param  string  $source  'excel_import' or 'google_sheet'
     * @return array{created:int, updated:int, skipped:int}
     */
    public function import(iterable $rows, string $source = 'excel_import'): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $mapped = $this->mapRow($row);

            if (empty($mapped['email']) || empty($mapped['name'])) {
                $skipped++;

                continue;
            }

            $lastContactedAt = $this->parseDate($mapped['date'] ?? null);

            $status = $this->normalizeStatus($mapped['status'] ?? null)
                ?? Contact::computeStatusFromDate($lastContactedAt);

            $attributes = array_filter([
                'name' => $mapped['name'],
                'company' => $mapped['company'] ?? null,
                'whatsapp' => $mapped['whatsapp'] ?? null,
                'designation' => $mapped['designation'] ?? null,
                'notes' => $mapped['notes'] ?? null,
            ], fn ($v) => $v !== null && $v !== '');

            $attributes['status'] = $status;
            $attributes['source'] = $source;

            if ($lastContactedAt) {
                $attributes['last_contacted_at'] = $lastContactedAt;
            }

            $existing = Contact::where('email', $mapped['email'])->first();

            Contact::updateOrCreate(['email' => $mapped['email']], $attributes);

            $existing ? $updated++ : $created++;
        }

        return compact('created', 'updated', 'skipped');
    }

    protected function mapRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = Str::of((string) $key)->lower()->replaceMatches('/[^a-z0-9]/', '')->toString();
            $normalized[$normalizedKey] = is_string($value) ? trim($value) : $value;
        }

        $mapped = [];
        foreach (self::FIELD_ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $normalized) && $normalized[$alias] !== null && $normalized[$alias] !== '') {
                    $mapped[$field] = $normalized[$alias];
                    break;
                }
            }
        }

        return $mapped;
    }

    protected function parseDate(mixed $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function normalizeStatus(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        $value = Str::of((string) $value)->lower()->replace(['-', ' '], '_')->toString();

        return in_array($value, ['active', 'follow_up', 'inactive'], true) ? $value : null;
    }
}
