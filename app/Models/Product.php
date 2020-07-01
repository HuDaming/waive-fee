<?php

namespace App\Models;

use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property string $name 产品名称
 * @property string|null $intro 产品简介
 * @property string|null $banner_img 头图
 * @property string|null $background_img 二维码背景图
 * @property float $price 价格
 * @property bool $on_sale 是否上架
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereBackgroundImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereBannerImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereOnSale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Product withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Product withoutTrashed()
 * @mixin \Eloquent
 */
class Product extends Model
{
    use SoftDeletes, HasDateTimeFormatter;

    public $fillable = [
        'name', 'intro', 'banner_img', 'background_img', 'price', 'on_sale'
    ];

    protected $casts = ['on_sale' => 'boolean'];

    public function getFullBackgroundImgUrlAttribute()
    {
        return Str::startsWith('http', $this->background_img) ?
            $this->background_img :
            config('filesystems.disks.qiniu.url') . $this->background_img;
    }

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

    /**
     * 用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(Administrator::class, 'product_user', 'product_id', 'user_id')
            ->withPivot('qr_code')
            ->withTimestamps();
    }
}
