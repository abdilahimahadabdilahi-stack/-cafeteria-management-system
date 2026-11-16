<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['item_name', 'unit', 'quantity', 'reorder_level'])]
class Inventory extends Model
{
    protected $table = 'inventory';

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'reorder_level' => 'decimal:2',
        ];
    }
}
