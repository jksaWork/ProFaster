<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ModelEventLogger;


class IssueClientStatement extends Model
{
    use HasFactory;
    use ModelEventLogger;
    protected $guarded = [];

    public static function boot(){
        parent::boot();
        self::Created(function($model){
            $model->issue_date = date('y-m-d');
            $model->save();
        });
    }

    public function Client(){
        return $this->belongsTo(Client::class);
    }
    public function getOrdersIdsAttribute($key)
    {
        return json_decode($key);
    }

    public function Photos(){
        return $this->hasMany(IssuePhotos::class , 'issue');
    }
}
