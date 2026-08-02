<?php

namespace App\Models;

use Database\Factories\FuelStationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelStation extends Model
{
    /** @use HasFactory<FuelStationFactory> */
    use HasFactory;

    protected $fillable = [
        'station_id',
        'name',
        'mobile_money_phone',
        'mobile_money_provider',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'mobile_money_phone' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }
}
