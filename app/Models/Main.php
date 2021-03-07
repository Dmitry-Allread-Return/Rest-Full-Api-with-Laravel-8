<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Main extends Model
{
    use HasFactory;
    protected $table = 'users';
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'document_number',
        'password',
        'api_token',
    ];
    public function passenger() {
        return $this->hasMany(passengers::class, 'document_number', 'document_number');
    }
}
