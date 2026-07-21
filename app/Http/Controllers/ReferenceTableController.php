<?php

namespace App\Http\Controllers;

use App\Models\ReferenceTable;
use Illuminate\Http\Request;

class ReferenceTableController extends Controller
{
    /**
     * Create a new reference table by pasting a range copied straight out
     * of Excel/Google Sheets (first line = headers, rest = rows).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'pasted_data' => ['required', 'string'],
        ]);

        [$headers, $rows] = ReferenceTable::parsePastedGrid($data['pasted_data']);

        if (empty($headers) || empty($rows)) {
            return redirect()->route('products.index')->with('error', 'Could not parse that paste — make sure the first line is the header row.');
        }

        ReferenceTable::create([
            'category' => 'copper',
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'headers' => $headers,
            'rows' => $rows,
            'sort_order' => ReferenceTable::max('sort_order') + 1,
        ]);

        return redirect()->route('products.index')->with('success', 'Reference table added.');
    }

    /**
     * Replace a table's data with a fresh paste, and/or update its title.
     */
    public function update(Request $request, ReferenceTable $referenceTable)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'pasted_data' => ['nullable', 'string'],
        ]);

        $update = ['title' => $data['title'], 'description' => $data['description'] ?? null];

        if (! empty($data['pasted_data'])) {
            [$headers, $rows] = ReferenceTable::parsePastedGrid($data['pasted_data']);

            if (empty($headers) || empty($rows)) {
                return redirect()->route('products.index')->with('error', 'Could not parse that paste — make sure the first line is the header row.');
            }

            $update['headers'] = $headers;
            $update['rows'] = $rows;
        }

        $referenceTable->update($update);

        return redirect()->route('products.index')->with('success', 'Reference table updated.');
    }

    public function destroy(ReferenceTable $referenceTable)
    {
        $referenceTable->delete();

        return redirect()->route('products.index')->with('success', 'Reference table removed.');
    }
}
