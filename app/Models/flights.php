<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class flights extends Model
{
    use HasFactory;
    protected $table = 'flights';
    protected $fillable = [
        'flight_code',
        'from_id',
        'to_id',
        'time_from',
        'time_to',
        'cost',
    ];
    // public function from() {
    //     return $this->hasOne(Airports::class, 'id', 'from_id');
    // }
    // public function to() {
    //     return $this->hasOne(Airports::class, 'id', 'to_id');
    // }
    
    public function bTo() {
        return $this->belongsTo(booking::class, 'id', 'flight_from');
    }

    public function bBack() {
        return $this->belongsTo(booking::class, 'id', 'flight_back');
    }
}
