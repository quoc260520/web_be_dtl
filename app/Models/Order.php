<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    const KIND_MANUAL = 1;
    const KIND_PAYPAL = 2;
    const STATUS_ORDERED = 1;
    const STATUS_DELIVERING = 2;
    const STATUS_PAYMENT_SUCCESS = 3;
    const STATUS_SUCCESS = 4;
    const STATUS_CANCEL = 5;

    protected $fillable = ['user_id', 'phone', 'total_price', 'kind_of_payment','bill_id', 'status', 'address', 'date_order', 'date_receipt'];
    public function orderDetails() {
        return $this->hasMany(OrderDetail::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}
