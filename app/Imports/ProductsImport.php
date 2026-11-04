<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow
{
    public array $result = ['created' => 0, 'updated' => 0, 'skipped' => 0];

    protected const ALIASES = [
        'code' => ['productcode', 'code'],
        'name' => ['productname', 'name'],
        'size' => ['size'],
        'weight' => ['weight'],
        'unit' => ['unit'],
        'rate' => ['rate', 'price'],
        'specification' => ['specification', 'spec', 'specifications'],
    ];

    public function collection(Collection $rows): void
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $normalized = [];
            foreach ($row->toArray() as $key => $value) {
                $normalizedKey = Str::of((string) $key)->lower()->replaceMatches('/[^a-z0-9]/', '')->toString();
                $normalized[$normalizedKey] = is_string($value) ? trim($value) : $value;
            }

            $mapped = [];
            foreach (self::ALIASES as $field => $aliases) {
                foreach ($aliases as $alias) {
                    if (isset($normalized[$alias]) && $normalized[$alias] !== '') {
                        $mapped[$field] = $normalized[$alias];
                        break;
                    }
                }
            }

            // empty() treats the string "0" as empty too -- a product whose
            // code or name is literally "0" would otherwise be wrongly
            // skipped even though the sheet has a real value for it.
            if (($mapped['code'] ?? '') === '' || ($mapped['name'] ?? '') === '') {
                $skipped++;

                continue;
            }

            // The rate column is decimal(12,2) under strict SQL mode -- a
            // sheet cell like "N/A", "TBD", or "₹1,250.00" passed straight
            // through would throw an uncaught QueryException and abort the
            // whole import (rows already processed stay committed, with no
            // accurate created/updated/skipped counts reported back).
            $rateRaw = trim(str_replace([',', '₹', '$', ' '], '', (string) ($mapped['rate'] ?? '0')));

            if ($rateRaw !== '' && ! is_numeric($rateRaw)) {
                $skipped++;

                continue;
            }

            $existing = Product::where('code', $mapped['code'])->first();

            Product::updateOrCreate(['code' => $mapped['code']], [
                'name' => $mapped['name'],
                'size' => $mapped['size'] ?? null,
                'weight' => $mapped['weight'] ?? null,
                'unit' => $mapped['unit'] ?? 'PCS',
                'rate' => $rateRaw !== '' ? (float) $rateRaw : 0,
                'specification' => $mapped['specification'] ?? null,
            ]);

            $existing ? $updated++ : $created++;
        }

        $this->result = compact('created', 'updated', 'skipped');
    }
}
