<?php

namespace App\Models;

use App\Enums\CompanyPersonType;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyPerson extends Pivot
{
    protected $fillable = ['company_id', 'person_id', 'type'];

    protected function casts(): array
    {
        return [
            'type' => CompanyPersonType::class,
        ];
    }
}
