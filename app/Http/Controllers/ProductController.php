<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use App\Models\Activity;
use App\Models\CopperStandard;
use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('specification', 'like', "%{$search}%");
            });
        }

        if ($request->filled('unit') && $request->input('unit') !== 'all') {
            $query->where('unit', $request->input('unit'));
        }

        $products = $query->orderBy('code')->paginate(15)->withQueryString();
        $copperStandards = CopperStandard::orderBy('sort_order')->get();

        return view('products.index', compact('products', 'copperStandards'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $product = Product::create($data);

        Activity::log("Product <b>{$product->name}</b> added", 'bi-box-seam-fill', 'success', $product);

        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);
        $product->update($data);

        Activity::log("Product <b>{$product->name}</b> updated", 'bi-pencil-fill', 'primary', $product);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete();

        Activity::log("Product <b>{$name}</b> deleted", 'bi-trash-fill', 'danger');

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $import = new ProductsImport;
        Excel::import($import, $request->file('file'));

        Activity::log(
            "Products imported from Excel: {$import->result['created']} created, {$import->result['updated']} updated",
            'bi-file-earmark-spreadsheet-fill',
            'info'
        );

        return redirect()->route('products.index')->with(
            'success',
            "Import complete — {$import->result['created']} created, {$import->result['updated']} updated, {$import->result['skipped']} skipped."
        );
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products_export_'.now()->format('Ymd_His').'.xlsx');
    }

    protected function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:products,code'.($product ? ','.$product->id : '')],
            'name' => ['required', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:20'],
            'rate' => ['required', 'numeric', 'min:0'],
            'specification' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
