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



    public static function getSubAreaIdOrCreateArea($cityId, $CountryId){
        $city = self::where("salla_id" , $cityId)->first();
        
        if($city->sub_area_id){
            return $city->sub_area_id;
        }

        $area_id = SallaCountry::getAreaIdOrCreateArea($CountryId);

        $sub_area = Area::create([
            'name' => $city->name,
            'area_id' => $area_id, 
        ]);

        $city->sub_area_id = $sub_area->id;
        $city->save();

        return $area->id; 
    }

}
