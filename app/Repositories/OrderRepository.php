<?php

namespace App\Repositories;

use App\Models\CartDetail;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class OrderRepository extends BaseRepository
{
    const PAGE = 20;
    protected $model;
    protected $orderDetail;

    protected $cartDetail;
    protected $product;


    public function __construct(Order $model, OrderDetail $orderDetail, CartDetail $cartDetail, Product $product)
    {
        $this->model = $model;
        $this->orderDetail = $orderDetail;
        $this->cartDetail = $cartDetail;
        $this->product = $product;
    }
    public function index($request, $userId = null)
    {
        $nameUser = $request->name_user;
        $nameProduct = $request->name_product;
        $paginate = $request->get('limit') ?? self::PAGE;
        $order = $this->model->when($nameUser, function ($query, $nameUser) {
            $query->whereHas('user', function (Builder $q) use ($nameUser) {
                $q->where('name', 'like', ['%' . $nameUser . '%']);
            });
        })->when($nameProduct, function ($query, $nameProduct) {
            $query->whereHas('orderDetails.product', function (Builder $q) use ($nameProduct) {
                $q->withTrashed()->where('name', 'like', ['%' . $nameProduct . '%']);
            });
        })->when($userId, function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['user:id,name,email', 'orderDetails.product.user:id,name,email', 'orderDetails.product' => function ($query) {
            return $query->withTrashed();
        }])
            ->paginate($paginate);
        return [
            'path_image' => asset('storage/product'),
            'order' => $order,
        ];
    }
    public function getById($id)
    {
        return $this->model->with([
            'user:id,name,email',
            'orderDetails.product.user:id,name,email',
            'orderDetails.product' => function ($query) {
                return $query->withTrashed();
            }
        ])->findOrFail($id);
    }
    public function create($data)
    {
        try {
            DB::beginTransaction();
            $cartDetail = $this->cartDetail->whereIn('id', $data->cart_detail)
                ->with(['product' => function ($query) {
                    return $query->where('status', Product::STATUS_APPROVE);
                }])
                ->get();
            $orderDetailData = [];
            $totalOrder = 0;
            foreach ($cartDetail as $item) {
                if (!$item->product) {
                    return [
                        'code' => 404,
                        'errors' => 'Not found',
                        'message' => 'Sản phẩm không tồn tại',
                    ];
                }
                if ($item->product->quantity  < $item->quantity) {
                    return [
                        'code' => 401,
                        'errors' => 'Over',
                        'message' => 'Số lượng sản phẩm không đủ',
                    ];
                }
                $orderDetailData[] = [
                    'product_id' => (int)$item->product_id,
                    'quantity' =>  (int)$item->quantity,
                    'price' => $item->product->price
                ];
                $totalOrder += (float)($item->quantity * $item->product->price);
                Product::find($item->product_id)->decrement('quantity', $item->quantity);
                $item->delete();
            }
            $order = $this->model->create([
                'user_id' => Auth::user()->id,
                'phone' => $data->phone,
                'total_price' => $totalOrder,
                'kind_of_payment' => $data->kind_of_payment,
                'status' => Order::STATUS_ORDERED,
                'address' => $data->address,
                'date_order' => now(),
            ]);
            $order->orderDetails()->createMany($orderDetailData);
            DB::commit();
            return [
                'order' => $order
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error' => true,
                'message' => 'Đã có lỗi xảy ra',
            ];
        }
    }
    public function update($id, $data)
    {
        $order =  $this->model->find($id);
        if (!$order) {
            return [
                'errors' => 'Not found',
                'message' => 'Đơn hàng không tồn tại',
            ];
        }
        $order->update([
            'status' =>  $data->status ?? $order->status,
            'date_receipt' =>  $data->status == Order::STATUS_SUCCESS ? now() : $order->date_receipt,
        ]);
        return [
            'message' => 'Cập nhật đơn hàng thành công',
        ];
    }
    public function delete($id)
    {
        $order =  $this->model->find($id);
        if (!$order) {
            return [
                'errors' => 'Not found',
                'message' => 'Đơn hàng không tồn tại',
            ];
        }
        $order->delete();
        return [
            'message' => 'Xóa đơn hàng thành công',
        ];
    }
    public function changeStatus($data)
    {
        $this->model->whereIn('id', $data->products)->update(['status' => $data->status]);
        return [
            'message' => 'Success',
        ];
    }
    public function checkPaypalPayment($data)
    {
        $provider = new PayPalClient;
        $provider->getAccessToken();
        $billDetail = $provider->showOrderDetails($data->bill_id);
        if (!array_key_exists('id', $billDetail)) {
            return [
                'errors' => true,
                'message' => 'Payment not found',
            ];
        }
        $order = $this->model->where('id', $billDetail['purchase_units'][0]['description'])->where('kind_of_payment', Order::KIND_PAYPAL)->first();
        if (!$order || $billDetail['status'] != 'COMPLETED') {
            return [
                'errors' => true,
                'message' => 'Error',
            ];
        }
        $order->update([
            'status' => Order::STATUS_PAYMENT_SUCCESS,
            'bill_id' => $billDetail['id'],
        ]);
        return [
            'message' => 'Success',
        ];
    }
    public function cancel($id)
    {
        $order = $this->model->with('orderDetails')->findOrFail($id);
        if ($order->status == Order::STATUS_CANCEL) {
            return [
                'errors' => true,
                'message' => 'Đơn hàng đã hủy',
            ];
        }
        try {
            DB::beginTransaction();
            $order->update(['status' => Order::STATUS_CANCEL]);
            foreach ($order->orderDetails as $detail) {
                $this->product->where('id', $detail->product_id)->increment('quantity', $detail->quantity);
            }
            DB::commit();
            return [
                'message' => 'Cancel success',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'errors' => true,
                'message' => 'Cancel failed',
            ];
        }
    }
    public function getOrderSell($data, $sellId)
    {
        $order = $this->model
            ->whereHas('orderDetails.product', function (Builder $q) use ($sellId) {
                $q->where('user_id', $sellId);
            })
            ->with(['user:id,name,email', 'orderDetails.product.user:id,name,email', 'orderDetails' => function ($query) use ($sellId) {
                return  $query->whereHas('product', function (Builder $q) use ($sellId) {
                    $q->where('user_id', $sellId);
                });
            }])
            ->paginate(20);
        return [
            'path_image' => asset('storage/product'),
            'order' => $order,
        ];
    }
}
