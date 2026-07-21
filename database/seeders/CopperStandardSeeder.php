<?php

namespace Database\Seeders;

use App\Models\CopperStandard;
use Illuminate\Database\Seeder;

class CopperStandardSeeder extends Seeder
{
    /**
     * Seeds indicative standard copper conductor reference values (nominal
     * cross-section, weight/km at ~8.89g/cm³ density, and typical current
     * rating for single-core PVC cable in free air). These are commonly
     * published approximations meant as a quick-lookup reference — edit
     * or replace rows from Product Master once you have your exact spec sheet.
     */
    public function run(): void
    {
        $rows = [
            ['1', '1', '8.9', '11', 'IS 8130 (Indicative)'],
            ['1.5', '1.5', '13.3', '16', 'IS 8130 (Indicative)'],
            ['2.5', '2.5', '22.2', '24', 'IS 8130 (Indicative)'],
            ['4', '4', '35.6', '32', 'IS 8130 (Indicative)'],
            ['6', '6', '53.3', '41', 'IS 8130 (Indicative)'],
            ['10', '10', '88.9', '57', 'IS 8130 (Indicative)'],
            ['16', '16', '142.2', '76', 'IS 8130 (Indicative)'],
            ['25', '25', '222.3', '101', 'IS 8130 (Indicative)'],
            ['35', '35', '311.2', '125', 'IS 8130 (Indicative)'],
            ['50', '50', '444.5', '151', 'IS 8130 (Indicative)'],
            ['70', '70', '622.3', '192', 'IS 8130 (Indicative)'],
            ['95', '95', '844.6', '232', 'IS 8130 (Indicative)'],
            ['120', '120', '1066.8', '269', 'IS 8130 (Indicative)'],
            ['150', '150', '1333.5', '300', 'IS 8130 (Indicative)'],
            ['185', '185', '1644.7', '341', 'IS 8130 (Indicative)'],
            ['240', '240', '2133.6', '400', 'IS 8130 (Indicative)'],
            ['300', '300', '2667.0', '458', 'IS 8130 (Indicative)'],
            ['400', '400', '3556.0', '546', 'IS 8130 (Indicative)'],
        ];

        foreach ($rows as $i => [$size, $csa, $weight, $amps, $ref]) {
            CopperStandard::updateOrCreate(
                ['size_designation' => $size.' sqmm'],
                [
                    'cross_section_sqmm' => $csa,
                    'weight_per_km_kg' => $weight,
                    'current_rating_amps' => $amps,
                    'standard_reference' => $ref,
                    'sort_order' => $i,
                ]
            );
        }
    }
}
