<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['starting_balance'])]
class AccountSetting extends Model
{
    protected function casts(): array
    {
        return [
            'starting_balance' => 'decimal:2',
        ];
    }
}
