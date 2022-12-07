<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    const PRODUCT_PUBLISH_KEY = 'product-publish-key-__USER_ID__';
    const PRODUCT_PUBLISHING_KEY = 'product-publishing-key';

    protected $fillable = [
        'id',
        'ebay_id',
        'ebay_url',
        'description',
        'publish_date',
        'deleted_at',
        'publisher'
    ];

    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('description', 'like', "%" . $searchTerm . "%")
            ->orWhere('ebay_id', $searchTerm);
    }

    public function scopeSearchUser($query, $searchUser)
    {
        return $query->where('publisher', 'like', "%" . $searchUser . "%");
    }

    public function getEbayUrlAttribute($value)
    {
        return "https://www.ebay-kleinanzeigen.de$value";
    }
}
