<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 use App\Traits\ModelEventLogger;
class Service extends Model
{
    use HasFactory;

    use ModelEventLogger;

    protected $fillable = [
        'name',
        'price',
        'descr',
        'is_active',
        'cod',
        'photo',
    ];

    public function notes()
    {
        return $this->hasMany(ServiceNote::class);
    }







    protected $casts = [
        'is_active' => 'boolean',
        'is_fill_sender' => 'boolean',
    ];
}


