<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = [
        'public_id',
        'user_id',
        'category_id',
        'name',
        'slug',
        'old_price',
        'new_price',
        'discount',
        'rate',
        'stock',
        'thumbnail',
        'is_advertised'
    ];

    protected $casts = [
        'is_advertised' => 'boolean',
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function description()
    {
        return $this->hasOne(ProductDescription::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->old_price && $this->old_price > 0) {
            return round((($this->old_price - $this->new_price) / $this->old_price) * 100);
        }
        return 0;
    }
}
