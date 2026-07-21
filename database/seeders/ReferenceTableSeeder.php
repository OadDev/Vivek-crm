<?php

namespace Database\Seeders;

use App\Models\ReferenceTable;
use Illuminate\Database\Seeder;

class ReferenceTableSeeder extends Seeder
{
    /**
     * Seeded from the ASTM B88 / ASTM B280 / EN 13348 reference sheet
     * provided as screenshots. Transcribed as faithfully as possible from
     * the source images — please verify against your own sheet before
     * relying on it for engineering decisions, and correct any row via the
     * "Paste from Excel/Sheets" editor in Product Master if anything looks
     * off (that's the fastest way to get it byte-exact from your source).
     */
    public function run(): void
    {
        $tables = [
            [
                'title' => 'ASTM B88 — Seamless Copper Water Tubes, Type K (inches)',
                'description' => 'Dimensions and weight for Type K tube.',
                'headers' => ['Nominal Size (in)', 'Actual OD (in)', 'Actual OD (decimal)', 'Tolerance OD — Annealed (in)', 'Tolerance OD — Drawn (in)', 'Wall Thickness — Nominal (in)', 'Wall Thickness — Tolerance (in)', 'Weight (lb/ft)'],
                'rows' => [
                    ['1/4', '3/8', '0.375', '0.002', '0.001', '0.035', '0.0035', '0.133'],
                    ['3/8', '1/2', '0.5', '0.0025', '0.001', '0.049', '0.005', '0.269'],
                    ['1/2', '5/8', '0.625', '0.0025', '0.001', '0.049', '0.005', '0.344'],
                    ['5/8', '3/4', '0.75', '0.0025', '0.001', '0.049', '0.005', '0.418'],
                    ['3/4', '7/8', '0.875', '0.003', '0.001', '0.065', '0.006', '0.641'],
                    ['1', '1 1/8', '1.125', '0.0035', '0.0015', '0.065', '0.006', '0.837'],
                    ['1 1/4', '1 3/8', '1.375', '0.004', '0.0015', '0.065', '0.006', '1.04'],
                    ['1 1/2', '1 5/8', '1.625', '0.0045', '0.002', '0.072', '0.007', '1.36'],
                    ['2', '2 1/8', '2.125', '0.005', '0.002', '0.083', '0.008', '2.06'],
                    ['2 1/2', '2 5/8', '2.625', '0.005', '0.002', '0.095', '0.01', '2.92'],
                    ['3', '3 1/8', '3.125', '0.005', '0.002', '0.109', '0.011', '4'],
                    ['3 1/2', '3 5/8', '3.625', '0.005', '0.002', '0.12', '0.012', '5.12'],
                    ['4', '4 1/8', '4.125', '0.005', '0.002', '0.134', '0.013', '6.51'],
                    ['5', '5 1/8', '5.125', '0.005', '0.002', '0.16', '0.016', '9.67'],
                    ['6', '6 1/8', '6.125', '0.005', '0.002', '0.192', '0.019', '13.87'],
                ],
            ],
            [
                'title' => 'ASTM B88 — Seamless Copper Water Tubes, Type L (inches)',
                'description' => 'Dimensions and weight for Type L tube.',
                'headers' => ['Nominal Size (in)', 'Actual OD (in)', 'Actual OD (decimal)', 'Tolerance OD — Annealed (in)', 'Tolerance OD — Drawn (in)', 'Wall Thickness — Nominal (in)', 'Wall Thickness — Tolerance (in)', 'Weight (lb/ft)'],
                'rows' => [
                    ['1/4', '3/8', '0.375', '0.002', '0.001', '0.03', '0.003', '0.126'],
                    ['3/8', '1/2', '0.5', '0.0025', '0.001', '0.035', '0.004', '0.198'],
                    ['1/2', '5/8', '0.625', '0.0025', '0.001', '0.04', '0.004', '0.285'],
                    ['5/8', '3/4', '0.75', '0.0025', '0.001', '0.042', '0.004', '0.362'],
                    ['3/4', '7/8', '0.875', '0.003', '0.001', '0.045', '0.004', '0.455'],
                    ['1', '1 1/8', '1.125', '0.0035', '0.0015', '0.05', '0.005', '0.655'],
                    ['1 1/4', '1 3/8', '1.375', '0.004', '0.0015', '0.055', '0.006', '0.884'],
                    ['1 1/2', '1 5/8', '1.625', '0.0045', '0.002', '0.06', '0.006', '1.14'],
                    ['2', '2 1/8', '2.125', '0.005', '0.002', '0.07', '0.007', '1.75'],
                    ['2 1/2', '2 5/8', '2.625', '0.005', '0.002', '0.08', '0.008', '2.48'],
                    ['3', '3 1/8', '3.125', '0.005', '0.002', '0.09', '0.009', '3.33'],
                    ['3 1/2', '3 5/8', '3.625', '0.005', '0.002', '0.1', '0.01', '4.29'],
                    ['4', '4 1/8', '4.125', '0.005', '0.002', '0.114', '0.011', '5.38'],
                    ['5', '5 1/8', '5.125', '0.005', '0.002', '0.125', '0.012', '7.61'],
                    ['6', '6 1/8', '6.125', '0.005', '0.002', '0.14', '0.014', '10.2'],
                ],
            ],
            [
                'title' => 'ASTM B88 — Seamless Copper Water Tubes, Type M (inches)',
                'description' => 'Dimensions and weight for Type M tube.',
                'headers' => ['Nominal Size (in)', 'Actual OD (in)', 'Actual OD (decimal)', 'Tolerance OD — Annealed (in)', 'Tolerance OD — Drawn (in)', 'Wall Thickness — Nominal (in)', 'Wall Thickness — Tolerance (in)', 'Weight (lb/ft)'],
                'rows' => [
                    ['1/4', '3/8', '0.375', '0.002', '0.001', '-', '-', '0.106'],
                    ['3/8', '1/2', '0.5', '0.0025', '0.001', '0.025', '0.002', '0.144'],
                    ['1/2', '5/8', '0.625', '0.0025', '0.001', '0.028', '0.003', '0.203'],
                    ['5/8', '3/4', '0.75', '0.0025', '0.001', '-', '-', '0.263'],
                    ['3/4', '7/8', '0.875', '0.003', '0.001', '0.032', '0.003', '0.328'],
                    ['1', '1 1/8', '1.125', '0.0035', '0.0015', '0.035', '0.004', '0.464'],
                    ['1 1/4', '1 3/8', '1.375', '0.004', '0.0015', '0.042', '0.004', '0.681'],
                    ['1 1/2', '1 5/8', '1.625', '0.0045', '0.002', '0.049', '0.005', '0.94'],
                    ['2', '2 1/8', '2.125', '0.005', '0.002', '0.058', '0.006', '1.46'],
                    ['2 1/2', '2 5/8', '2.625', '0.005', '0.002', '0.065', '0.006', '2.03'],
                    ['3', '3 1/8', '3.125', '0.005', '0.002', '0.072', '0.007', '2.68'],
                    ['3 1/2', '3 5/8', '3.625', '0.005', '0.002', '0.083', '0.008', '3.58'],
                    ['4', '4 1/8', '4.125', '0.005', '0.002', '0.095', '0.01', '4.66'],
                    ['5', '5 1/8', '5.125', '0.005', '0.002', '0.109', '0.011', '6.66'],
                    ['6', '6 1/8', '6.125', '0.005', '0.002', '0.122', '0.012', '8.91'],
                ],
            ],
            [
                'title' => 'ASTM B280 — Copper Tube for Air Conditioning & Refrigeration (ACR), Dimensions (inches)',
                'description' => 'Standard specifications for seamless copper tube used in ACR field services.',
                'headers' => ['Nominal Size (in)', 'Temper (A=Annealed / D=Drawn)', 'Outside Diameter (in)', 'Inside Diameter (in)', 'Wall Thickness (in)', 'Cross Sectional Area of Bore (sq in)'],
                'rows' => [
                    ['1/8', 'A', '0.125', '0.065', '0.03', '0.0033'],
                    ['3/16', 'A', '0.187', '0.128', '0.03', '0.0129'],
                    ['1/4', 'A', '0.25', '0.19', '0.03', '0.0284'],
                    ['5/16', 'A', '0.312', '0.248', '0.032', '0.0483'],
                    ['3/8', 'A', '0.375', '0.311', '0.032', '0.076'],
                    ['3/8', 'D', '0.375', '0.315', '0.03', '0.078'],
                    ['1/2', 'A', '0.5', '0.436', '0.032', '0.149'],
                    ['1/2', 'D', '0.5', '0.43', '0.035', '0.145'],
                    ['5/8', 'A', '0.625', '0.555', '0.035', '0.242'],
                    ['5/8', 'D', '0.625', '0.545', '0.04', '0.233'],
                    ['3/4', 'A', '0.75', '0.68', '0.035', '0.363'],
                    ['3/4', 'A', '0.75', '0.666', '0.042', '0.348'],
                    ['3/4', 'D', '0.75', '0.666', '0.042', '0.348'],
                    ['7/8', 'A', '0.875', '0.785', '0.045', '0.484'],
                    ['7/8', 'D', '0.875', '0.785', '0.045', '0.484'],
                    ['1 1/8', 'A', '1.125', '1.025', '0.05', '0.825'],
                    ['1 1/8', 'D', '1.125', '1.025', '0.05', '0.825'],
                    ['1 3/8', 'A', '1.375', '1.265', '0.055', '1.26'],
                    ['1 3/8', 'D', '1.375', '1.265', '0.055', '1.26'],
                ],
            ],
            [
                'title' => 'ASTM B280 — Max Working Pressure (psi), Table 1',
                'description' => 'Standard specifications for seamless copper tube used in ACR field services.',
                'headers' => ['Nominal Size (in)', 'Annealed Coils (psi)', 'Annealed Straight Lengths (psi)', 'Drawn Straight Lengths (psi)'],
                'rows' => [
                    ['1/8', '2613', '-', '-'],
                    ['3/16', '1645', '-', '-'],
                    ['1/4', '1195', '-', '-'],
                    ['5/16', '1017', '-', '-'],
                    ['3/8', '836', '777', '1524'],
                    ['1/2', '618', '664', '1302'],
                    ['5/8', '525', '615', '1206'],
                    ['3/4', '435', '538', '1055'],
                    ['7/8', '495', '496', '972'],
                    ['1 1/8', '420', '421', '825'],
                    ['1 3/8', '373', '374', '733'],
                    ['1 5/8', '347', '348', '682'],
                    ['2 1/8', '-', '309', '607'],
                    ['2 5/8', '-', '286', '560'],
                    ['3 1/8', '-', '270', '529'],
                    ['3 5/8', '-', '258', '506'],
                    ['4 1/8', '-', '249', '489'],
                ],
            ],
            [
                'title' => 'ASTM B280 — Max Working Pressure (psi), Table 2',
                'description' => 'Second working-pressure table as provided in the source sheet — verify which applies to your alloy/temper before use.',
                'headers' => ['Nominal Size (in)', 'Annealed Coils (psi)', 'Annealed Straight Lengths (psi)', 'Drawn Straight Lengths (psi)'],
                'rows' => [
                    ['1/8', '18017', '-', '-'],
                    ['3/16', '11342', '-', '-'],
                    ['1/4', '8240', '-', '-'],
                    ['5/16', '7012', '-', '-'],
                    ['3/8', '5764', '5357', '10508'],
                    ['1/2', '4261', '4578', '8977'],
                    ['5/8', '3620', '4240', '8315'],
                    ['3/4', '2999', '3710', '7274'],
                    ['7/8', '3413', '3420', '6702'],
                    ['1 1/8', '2896', '2903', '5688'],
                    ['1 3/8', '2572', '2579', '5054'],
                    ['1 5/8', '2393', '2399', '4702'],
                    ['2 1/8', '-', '2131', '4185'],
                    ['2 5/8', '-', '1972', '3861'],
                    ['3 1/8', '-', '1862', '3647'],
                    ['3 5/8', '-', '1779', '3489'],
                    ['4 1/8', '-', '1717', '3372'],
                ],
            ],
            [
                'title' => 'EN 13348 — Hardness and Strength',
                'description' => 'Seamless round copper tubes for medical gases or vacuum.',
                'headers' => ['Designation', 'Commonly Used Term', 'Tensile Strength (MPa)', 'Elongation min. (%)', 'Vickers Hardness (HV5)'],
                'rows' => [
                    ['R250', 'half hard', '250', '30, 20', '75 - 100'],
                    ['R290', 'hard', '290', '3', 'over 100'],
                ],
            ],
            [
                'title' => 'EN 13348 — Typical Available Dimensions',
                'description' => 'Seamless round copper tubes for medical gases or vacuum.',
                'headers' => ['Nominal Outside Diameter (mm)', 'Tube Length (m)', 'Wall Thickness (mm)'],
                'rows' => [
                    ['12', '6', '0.6'],
                    ['15', '3, 6', '0.7'],
                    ['22', '3, 6', '0.9'],
                    ['28', '3, 6', '0.9'],
                    ['35', '6', '1'],
                    ['42', '6', '1'],
                    ['54', '6', '1'],
                    ['67', '6', '1.5'],
                    ['76', '6', '1.5'],
                    ['108', '6', '1.5'],
                    ['133', '6', '1.5'],
                    ['159', '6', '2'],
                    ['219', '6', '3'],
                ],
            ],
        ];

        foreach ($tables as $i => $t) {
            ReferenceTable::updateOrCreate(
                ['title' => $t['title']],
                [
                    'category' => 'copper',
                    'description' => $t['description'],
                    'headers' => $t['headers'],
                    'rows' => $t['rows'],
                    'sort_order' => $i,
                ]
            );
        }
    }
}
