<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetail extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id', 'phone', 'total_price', 'kind_of_payment', 'status', 'address', 'date_order', 'date_receipt'];
    public function product() {
        return $this->belongsTo(Product::class);
    }
}
