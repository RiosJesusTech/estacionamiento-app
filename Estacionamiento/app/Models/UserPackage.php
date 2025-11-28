<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'start_date',
        'end_date',
        'assigned_spot',
        'reservations_used_today',
        'last_reservation_date',
        'active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'last_reservation_date' => 'date',
        'active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'user_id', 'user_id');
    }

    public function canMakeReservation()
    {
        if (!$this->active) return false;

        $today = now()->toDateString();

        // Reset counter if it's a new day
        if ($this->last_reservation_date !== $today) {
            $this->reservations_used_today = 0;
            $this->last_reservation_date = $today;
            $this->save();
        }

        return $this->reservations_used_today < $this->package->max_reservations_per_day;
    }

    public function incrementReservationCount()
    {
        $today = now()->toDateString();

        if ($this->last_reservation_date !== $today) {
            $this->reservations_used_today = 1;
        } else {
            $this->reservations_used_today++;
        }

        $this->last_reservation_date = $today;
        $this->save();
    }
}
