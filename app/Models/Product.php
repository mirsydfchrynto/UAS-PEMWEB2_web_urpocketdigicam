<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Binafy\LaravelCart\Cartable;


class Product extends Model implements Cartable
{
    public function category(){
        return $this->belongsTo(Categories::class,'product_category_id');
    }

    public function getPrice():float{
        return $this->price;
    }
}
