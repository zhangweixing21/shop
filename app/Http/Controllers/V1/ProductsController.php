<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\InvalidRequestException;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class ProductsController extends AuthController
{

    public function detail(Request $request){
        $params = $request->only('id');
        if (!isset($params['id']) && empty($params['id'])){
            return $this->errorHandler(1000);
        }
        $product = Product::select(['id','title','description','image','rating','sold_count','review_count','on_sale','price'])->find($params['id']);

        if ($product->on_sale == 0) {
            return $this->errorHandler(1003);
        }
        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku']) // 预先加载关联关系
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at') // 筛选出已评价的
            ->orderBy('reviewed_at', 'desc') // 按评价时间倒序
            ->limit(10) // 取出 10 条
            ->get();

        $favored = 0;
        $users = $this->guard()->user();
        // 从当前用户已收藏的商品中搜索 id 为当前商品 id 的商品
        $favorite_products = DB::table('user_favorite_products')->where(['product_id' => $params['id'],'user_id' => $users->id])->value('id');
        $favored = $favorite_products ? 1 : 0;
        $data = [];
        $data['product'] = $product;
        $data['reviews'] = $reviews;
        $data['favored'] = $favored;

        return $this->formatAsjson($data);
    }

    public function favor(Request $request){
        $params = $request->only('id');
        if (!isset($params['id']) && empty($params['id'])){
            return $this->errorHandler(1000);
        }
        $users = $this->guard()->user();
        $favorite_products = DB::table('user_favorite_products')->where(['product_id' => $params['id'],'user_id' => $users->id])->value('id');
        if ($favorite_products){
            return $this->errorHandler(1004);
        }
        $result = DB::insert('insert into user_favorite_products (user_id,product_id) values (?,?)', [$users->id,$params['id']]);
        if ($result){
            return $this->errorHandler(1005);
        }else{
            return $this->errorHandler(1006);
        }

    }

    public function disfavor(Request $request){
        $params = $request->only('id');
        if (!isset($params['id']) && empty($params['id'])){
            return $this->errorHandler(1000);
        }
        $users = $this->guard()->user();

        $result = DB::table('user_favorite_products')->where(['product_id' => $params['id'],'user_id' => $users->id])->delete();
        if ($result){
            return $this->errorHandler(1007);
        }else{
            return $this->errorHandler(1008);
        }

    }

}
