<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SallaCountry extends Model
{
    use HasFactory;

    public $fillable = [
      "name", 
      "name_en",
      "code", 
      "mobile_code", 
      "capital", 
      "area_id", 
    ];
}
