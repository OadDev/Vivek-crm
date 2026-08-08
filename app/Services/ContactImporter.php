<?php

namespace App\Services;

use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ContactImporter
{
    protected const FIELD_ALIASES = [
        'quoteno' => ['quoteno', 'quotationno', 'quotenumber', 'quotationnumber', 'quotenu'],
        'company' => ['company', 'companyname', 'organisation', 'organization'],
        'name' => ['name', 'fullname', 'contactname'],
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

            $quotationDate = $this->parseDate($mapped['quotationdate'] ?? null);

            $status = $this->normalizeStatus($mapped['status'] ?? null)
                ?? Contact::computeStatusFromDate($quotationDate);

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

            $key = $quoteNo ? ['quote_no' => $quoteNo] : ['email' => $email];

            $existing = Contact::withTrashed()->where($key)->first();

            // A lead the team explicitly removed (deleted or archived) must
            // not come back just because it's still present in the source
            // sheet on the next sync.
            if ($existing && ($existing->trashed() || $existing->is_archived)) {
                $skipped++;

                continue;
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
