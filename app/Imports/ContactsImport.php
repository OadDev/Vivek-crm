<?php

namespace App\Imports;

use App\Services\ContactImporter;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ContactsImport implements ToCollection, WithHeadingRow
{
    public array $result = ['created' => 0, 'updated' => 0, 'skipped' => 0];

    protected string $source;

    public function __construct(string $source = 'excel_import')
    {
        $this->source = $source;
    }

    public function collection(Collection $rows): void
    {
        $this->result = (new ContactImporter)->import($rows->map(fn ($row) => $row->toArray()), $this->source);
    }
}
