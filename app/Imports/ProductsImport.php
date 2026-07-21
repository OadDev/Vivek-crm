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
                    if (! empty($normalized[$alias])) {
                        $mapped[$field] = $normalized[$alias];
                        break;
                    }
                }
            }

            if (empty($mapped['code']) || empty($mapped['name'])) {
                $skipped++;

                continue;
            }

            $existing = Product::where('code', $mapped['code'])->first();

            Product::updateOrCreate(['code' => $mapped['code']], [
                'name' => $mapped['name'],
                'size' => $mapped['size'] ?? null,
                'weight' => $mapped['weight'] ?? null,
                'unit' => $mapped['unit'] ?? 'PCS',
                'rate' => $mapped['rate'] ?? 0,
                'specification' => $mapped['specification'] ?? null,
            ]);

            $existing ? $updated++ : $created++;
        }

        $this->result = compact('created', 'updated', 'skipped');
    }
}
