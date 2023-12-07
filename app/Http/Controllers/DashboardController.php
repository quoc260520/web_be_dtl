<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function statistics()
    {
        $user = User::count();
        $products = Product::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        $productCount = [];
        $orderCount = [];
        $convertStatusProduct = [
            'un_approve',
            'approve',
            'out_stock'
        ];
        $convertStatusOrder = [
            1 => 'status_ordered',
            2 => 'status_delivering',
            3 => 'status_payment_success',
            4 => 'status_success',
            5 => 'status_cancel'
        ];
        foreach ($products as $product) {
            $productCount[$convertStatusProduct[$product->status]] = $product->total;
        }
        $orders = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        foreach ($orders as $order) {
            $orderCount[$convertStatusOrder[$order->status]] = $order->total;
        }
        return [
            'user' => $user,
            'productCount' => $productCount,
            'orderCount' => $orderCount
        ];
    }
}
