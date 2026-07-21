<?php

namespace App\Http\Controllers;

use App\Models\CopperStandard;
use Illuminate\Http\Request;

class CopperStandardController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);
        CopperStandard::create($data);

        return redirect()->route('products.index')->with('success', 'Copper standard reference row added.');
    }

    public function update(Request $request, CopperStandard $copperStandard)
    {
        $data = $this->validated($request);
        $copperStandard->update($data);

        return redirect()->route('products.index')->with('success', 'Copper standard reference row updated.');
    }

    public function destroy(CopperStandard $copperStandard)
    {
        $copperStandard->delete();

        return redirect()->route('products.index')->with('success', 'Copper standard reference row removed.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'size_designation' => ['required', 'string', 'max:100'],
            'cross_section_sqmm' => ['nullable', 'string', 'max:100'],
            'weight_per_km_kg' => ['nullable', 'string', 'max:100'],
            'current_rating_amps' => ['nullable', 'string', 'max:100'],
            'standard_reference' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
        ]);
    }
}
