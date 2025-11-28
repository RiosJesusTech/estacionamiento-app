<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_days',
        'max_reservations_per_day',
        'fixed_spot',
        'preferred_schedule',
        'active',
    ];

    protected $casts = [
        'fixed_spot' => 'boolean',
        'preferred_schedule' => 'array',
        'active' => 'boolean',
    ];

    public function userPackages()
    {
        return $this->hasMany(UserPackage::class);
    }
}
