<?php

namespace App\Services;

use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ContactImporter
{
    protected const FIELD_ALIASES = [
        'quoteno' => ['quoteno', 'quotationno', 'quotenumber', 'quotationnumber', 'quotenu'],
        'company' => ['company', 'companyname', 'organisation', 'organization', 'party', 'partyname', 'partynames', 'clientname', 'customername'],
        'name' => ['name', 'fullname', 'contactname', 'contactperson'],
        'email' => ['email', 'emailaddress', 'e-mail', 'mail'],
        'whatsapp' => ['whatsapp', 'whatsappnumber', 'phone', 'mobile', 'contactnumber', 'phonenumber', 'phoneno'],
        'designation' => ['designation', 'title', 'role', 'jobtitle'],
        'salesman' => ['salesman', 'salesperson', 'salesrep', 'salesexecutive'],
        'address' => ['address'],
        'gstnumber' => ['gst', 'gstno', 'gstnumber'],
        'transport' => ['transport'],
        'shippingaddress' => ['shippingaddress'],
        'stage' => ['stage'],
        'priority' => ['priority'],
        'quotationdate' => ['date', 'quotationdate', 'quotedate', 'lastcontacted', 'lastcontacteddate', 'lastinteraction', 'lastinteractiondate'],
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

            $company = $mapped['company'] ?? null;
            $name = $mapped['name'] ?? $company;
            $quoteNo = isset($mapped['quoteno']) ? (string) $mapped['quoteno'] : null;
            $email = $mapped['email'] ?? null;

            // Every row needs an identity (company or name) and a way to key
            // off it (quote number, falling back to email for non-quotation
            // sheets) — otherwise there's nothing to track it by.
            if (empty($name) || (empty($quoteNo) && empty($email))) {
                $skipped++;

                continue;
            }

            $key = $quoteNo ? ['quote_no' => $quoteNo] : ['email' => $email];

            $existing = Contact::withTrashed()->where($key)->first();

            // A lead the team explicitly removed (deleted or archived) must
            // not come back just because it's still present in the source
            // sheet on the next sync.
            if ($existing && ($existing->trashed() || $existing->is_archived)) {
                $skipped++;

                continue;
            }

            $quotationDate = $this->parseDate($mapped['quotationdate'] ?? null);

            // Fall back to the already-stored date when this row's date cell
            // is blank/unparseable on a resync -- otherwise a lead's status
            // gets silently reset to Active every time the sheet has a blank
            // date for it, even though its real quotation date hasn't changed.
            $status = $this->normalizeStatus($mapped['status'] ?? null)
                ?? Contact::computeStatusFromDate($quotationDate ?? $existing?->quotation_date);

            $attributes = array_filter([
                'company' => $company,
                'whatsapp' => $mapped['whatsapp'] ?? null,
                'designation' => $mapped['designation'] ?? null,
                'sales_man' => $mapped['salesman'] ?? null,
                'gst_number' => $mapped['gstnumber'] ?? null,
                'transport' => $mapped['transport'] ?? null,
                'shipping_address' => $mapped['shippingaddress'] ?? $mapped['address'] ?? null,
                'stage' => $mapped['stage'] ?? null,
                'priority' => $mapped['priority'] ?? null,
                'notes' => $mapped['notes'] ?? null,
            ], fn ($v) => $v !== null && $v !== '');

            $attributes['name'] = $name;
            $attributes['status'] = $status;
            $attributes['source'] = $source;

            if ($email) {
                $attributes['email'] = $email;
            }

            if ($quotationDate) {
                $attributes['quotation_date'] = $quotationDate;
            }

            if ($quoteNo) {
                $attributes['quote_no'] = $quoteNo;
            }

            Contact::updateOrCreate($key, $attributes);

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
        $claimed = [];
        foreach (self::FIELD_ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $normalized) && $normalized[$alias] !== null && $normalized[$alias] !== '') {
                    $mapped[$field] = $normalized[$alias];
                    $claimed[$alias] = true;
                    break;
                }
            }
        }

        // The exact-alias list above won't match every header a client's
        // sheet might actually use for the quotation date (e.g. "Qtn Date",
        // "Date of Quotation"). Fall back to any remaining, not-yet-claimed
        // column whose normalized header contains "date", rather than
        // leaving quotationdate unset and silently losing the value.
        if (! isset($mapped['quotationdate'])) {
            foreach ($normalized as $key => $value) {
                if (! isset($claimed[$key]) && str_contains($key, 'date') && $value !== null && $value !== '') {
                    $mapped['quotationdate'] = $value;
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
                $date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            } catch (\Throwable) {
                return null;
            }

            // A genuine Excel/Sheets date serial lands in a sane range --
            // anything else numeric that ends up here (a GST/PAN fragment,
            // a phone number, a misaligned column from a CSV parsing bug)
            // produces a nonsense date like the year 3269 rather than
            // failing cleanly, so reject anything implausible instead of
            // silently saving garbage.
            return $date->year >= 2000 && $date->year <= (now()->year + 5) ? $date : null;
        }

        $value = (string) $value;

        // Google Sheets' CSV export sometimes writes date-time cells as
        // "04/11/2026 0:00:00" -- match against the date portion only, so
        // a trailing time doesn't fall through to the ambiguous MM/DD/YYYY
        // handling below.
        $datePart = preg_split('/[\sT]+/', trim($value), 2)[0] ?? $value;

        // This client's actual sheet writes dates as MM/DD/YYYY -- confirmed
        // from real rows like "10/22/2026" (Oct 22: 22 can't be a month, so
        // this is unambiguous), which an earlier version of this code
        // forced into day-first and rejected outright (checkdate(22, 10, ...)
        // is invalid), silently losing the date. Try both orderings and use
        // whichever is a real calendar date; when both are valid (day and
        // month both <= 12, genuinely ambiguous), prefer MM/DD/YYYY to match
        // this sheet's confirmed format rather than guessing day-first.
        if (preg_match('#^(\d{1,2})[/-](\d{1,2})[/-](\d{4})$#', $datePart, $m)) {
            [$first, $second, $year] = [(int) $m[1], (int) $m[2], (int) $m[3]];

            if (checkdate($first, $second, $year)) {
                [$month, $day] = [$first, $second];
            } elseif (checkdate($second, $first, $year)) {
                [$month, $day] = [$second, $first];
            } else {
                // Neither ordering is a real calendar date (e.g. day 31 in
                // a 30-day month) -- fail cleanly instead of letting
                // DateTime silently overflow into the next month.
                return null;
            }

            try {
                return Carbon::createFromFormat('n/j/Y', "{$month}/{$day}/{$year}")->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse($value);
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
