<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CopperStandard extends Model
{
    protected $fillable = [
        'size_designation',
        'cross_section_sqmm',
        'weight_per_km_kg',
        'current_rating_amps',
        'standard_reference',
        'sort_order',
    ];
}
