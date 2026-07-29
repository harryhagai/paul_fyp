<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = [
        'public_id',
        'name',
        'slug',
        'description',
    ];

    protected $casts = [
        // No casts needed for now
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // parent_id column not in DB — add migration if subcategories needed
}
