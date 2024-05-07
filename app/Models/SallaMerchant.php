<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SallaMerchant extends Model
{
    use HasFactory;

    public $fillable = [
        'merchant_id',
        "client_id", 
        'access_token', 
        'refresh_token',
        'expired_date',
        'client_json', 
    ];


    public function Client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
