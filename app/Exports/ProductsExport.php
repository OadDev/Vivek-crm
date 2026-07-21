<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Product::orderBy('code')->get()->map(fn (Product $p) => [
            'Product Code' => $p->code,
            'Product Name' => $p->name,
            'Size' => $p->size,
            'Weight' => $p->weight,
            'Unit' => $p->unit,
            'Rate' => $p->rate,
            'Specification' => $p->specification,
        ]);
    }

    public function headings(): array
    {
        return ['Product Code', 'Product Name', 'Size', 'Weight', 'Unit', 'Rate', 'Specification'];
    }
}
