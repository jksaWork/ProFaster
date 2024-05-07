<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SallaCity extends Model
{
    use HasFactory;

    public $fillable = [
        "salla_id", 
        "salla_name", 
        "salla_name_en", 
        "country_id", 
        "sub_area_id", 
    ];

}
