<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class booking extends Model
{
    use HasFactory;
    protected $table = 'bookings';
    public $timestamps = false;
    protected $fillable = [
        'flight_from',
        'flight_back',
        'date_from',
        'date_back',
        'code',
    ];

    public function fTo() {
        return $this->hasOne(flights::class, 'id', 'flight_from');
    }

    public function fBack() {
        return $this->hasOne(flights::class, 'id', 'flight_back');
    }

    public function passengers() {
        return $this->hasMany(passengers::class);
    }
}
