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
    ];
}
