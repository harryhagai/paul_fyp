<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'description',
        'specifications',
        'details'
    ];

    protected $casts = [
        'description' => 'string',
        'specifications' => 'string',
        'details' => 'string',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
