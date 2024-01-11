<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function statistics(Request $request)
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
        $filter = $request->get('filter');
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $category = $request->get('category');
        switch ($filter) {
            case 'date':
                $formatDate = "%Y-%m-%d";
                break;
            case 'month':
                $formatDate = "%Y-%m";
                break;
            case 'year':
                $formatDate = "%Y";
                break;
            default:
                $formatDate = "%Y-%m-%d";
                break;
        }
        $order = Order::when($status, function ($query) use ($status) {
            return $query->where('status', $status);
        })->when($startDate, function ($query) use ($startDate) {
            return $query->whereDate('date_order','>=',$startDate);
        })->when($endDate, function ($query) use ($endDate) {
            return $query->whereDate('date_order','<=', $endDate);
        })
        ->orderBy('date_order', 'ASC')
        ->select('id','user_id','status', 'total_price', 'kind_of_payment',DB::raw("DATE_FORMAT(date_order, '$formatDate') as date_order"))
        ->get();
        $orderFormat = [];
        foreach ($order as $item) {
            if(array_key_exists($item->date_order, $orderFormat)) {
                $old = $orderFormat[$item->date_order];
                $orderFormat[$item->date_order] = [
                    'total_quantity' => $old['total_quantity'] + 1,
                    'total_price' => $item->total_price + $old['total_price']
                ];
            } else {
                $orderFormat[$item->date_order] = [
                    'total_quantity' => 1,
                    'total_price' => $item->total_price
                ];
            }
        }
        $newFormat = [];
        foreach ($orderFormat as $key => $item) {
            array_push($newFormat, array_merge([
                'date' => $key
            ], $item));
        }
        return [
            'user' => $user,
            'productCount' => $productCount,
            'orderCount' => $orderCount,
            'orderFilter' => $newFormat
        ];
    }
    public function product(Request $request) {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $order = Order::join('order_details', 'orders.id', 'order_details.order_id')
        ->whereDate('date_order', '>=', $startDate)
        ->whereDate('date_order', '<=', $endDate)
        ->distinct()
        ->pluck('product_id');
        $product = Product::whereNotIn('id',$order)->get();
        return $product;
    }
    public function category(Request $request) {
        $category = Order::where('date_order','<=' ,Carbon::now())
        ->where('date_order','>=' ,Carbon::now()->subDays(30))
        ->leftJoin('order_details', 'orders.id', 'order_details.order_id')
        ->leftJoin('products', 'order_details.product_id', 'products.id')
        ->leftJoin('categories', 'products.category_id', 'categories.id')
        ->groupBy('categories.id')
        ->select('categories.id', 'categories.name', DB::raw('COUNT(categories.id) as total'), DB::raw('SUM(order_details.quantity) as total_quantity'))
        ->orderBy('total_quantity', 'desc')
        ->take(5)
        ->get();
        return $category;
    }
}
