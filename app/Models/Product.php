<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['code', 'name', 'size', 'weight', 'unit', 'rate', 'specification'];

    protected function casts(): array
    {
        return ['rate' => 'decimal:2'];
    }
}
