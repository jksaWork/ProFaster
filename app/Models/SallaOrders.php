<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SallaOrders extends Model
{
    use HasFactory;

    public $fillable = [
        'order_id' , 
        'salla_order_id', 
        'shipping_number',  
        'tracking_number', 
        'merchant' , 
        'shipment_id', 
        'policy_file',
        // Reserver Data
        "receiver_country", 
        "receiver_country_code", 
        "receiver_city", 
        "receiver_address_line", 
        "receiver_street_number",
        "receiver_block",
        "receiver_postal_code",
    ];

    public function Order(){
        return $this->belongsTo(Order::class, 'order_id');
    }
}
