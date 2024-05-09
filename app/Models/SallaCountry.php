<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SallaCountry extends Model
{
    use HasFactory;

    public $fillable = [
     "salla_id", 
      "name", 
      "name_en",
      "code", 
      "mobile_code", 
      "capital", 
      "area_id", 
    ];

    public static function getAreaIdOrCreateArea($area_id){
        $country = self::where('salla_id' , $area_id)->first();
        if($country->area_id){
            return $country->area_id;
        }
        $area = Area::create([
            'name' => $country->name,
            'fees' => 0,
            'country_code' => $country->code
        ]);

        $country->area_id = $area->id;
        $country->save();

        return $area->id; 
    }
}
