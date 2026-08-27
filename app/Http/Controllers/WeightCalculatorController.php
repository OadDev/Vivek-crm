<?php

namespace App\Http\Controllers;

class WeightCalculatorController extends Controller
{
    /**
     * Densities in g/cm^3. Matches standard metal-weight-calculator
     * references (e.g. copperfitting.in, calculatoredge.com).
     */
    public const DENSITIES = [
        'copper' => ['label' => 'Copper', 'value' => 8.96],
        'brass' => ['label' => 'Brass', 'value' => 8.53],
        'bronze' => ['label' => 'Bronze', 'value' => 8.80],
        'aluminium' => ['label' => 'Aluminium', 'value' => 2.70],
        'mild_steel' => ['label' => 'Mild Steel', 'value' => 7.85],
        'stainless_steel' => ['label' => 'Stainless Steel (304)', 'value' => 8.00],
        'zinc' => ['label' => 'Zinc', 'value' => 7.14],
        'lead' => ['label' => 'Lead', 'value' => 11.34],
        'cast_iron' => ['label' => 'Cast Iron', 'value' => 7.20],
        'nickel' => ['label' => 'Nickel', 'value' => 8.90],
    ];

    public function index()
    {
        return view('weight-calculator.index', ['densities' => self::DENSITIES]);
    }
}
