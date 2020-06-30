<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes, HasDateTimeFormatter;

    public $fillable = [
        'name', 'intro', 'banner_img', 'background_img', 'price', 'on_sale'
    ];

    protected $casts = ['on_sale' => 'boolean'];

    public function getBannerImgAttribute($value)
    {
        return $value ?: 'images/default_banner.jpg';
    }

    public function getBackgroundImgAttribute($value)
    {
        return $value ?: 'images/default_background.jpg';
    }

    public function getPriceAttribute($value)
    {
        return sprintf("%.2f", $value);
    }
}
