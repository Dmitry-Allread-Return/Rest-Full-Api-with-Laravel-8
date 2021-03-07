<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class passengers extends Model
{
    use HasFactory;
    protected $table = 'passengers';
    public $timestamps = false;
    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'document_number',
        'booking_id',
    ];

    public function booking() {
        return $this->belongsTo(booking::class);
    }
    public function main() {
        return $this->belongsTo(Main::class, 'document_number', 'document_number');
    }
}
